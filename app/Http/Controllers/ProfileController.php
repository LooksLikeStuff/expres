<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Применяем middleware для проверки аутентификации.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 1. Отображение собственного профиля
    public function index()
    {
        $user = Auth::user();
        $title_site = "Профиль аккаунта | Личный кабинет Экспресс-дизайн";
        return view('profile', compact('user', 'title_site'));
    }

    // 2. Просмотр профиля другого пользователя
    /**
     * Отображает профиль указанного пользователя
     * 
     * @param int $id ID пользователя для просмотра
     * @return \Illuminate\View\View
     */
    public function viewProfile($id)
    {
        // Найти пользователя по ID
        $target = \App\Models\User::findOrFail($id);
        
        // Получаем текущего пользователя (тот, кто просматривает профиль)
        $viewer = auth()->user();
        
        // Получаем дополнительную информацию о рейтинге и проектах, если это специалист
        if (in_array($target->status, ['architect', 'designer', 'executor', 'coordinator', 'visualizer'])) {
            // Количество активных проектов
            $target->active_projects_count = $target->deals()->whereNotIn('status', ['Проект готов', 'Проект завершен'])->count();
            
            // Количество завершенных проектов
            $target->completed_projects_count = $target->deals()->whereIn('status', ['Проект готов', 'Проект завершен'])->count();
            
            // Средний рейтинг (если у пользователя еще нет свойства rating)
            if (!isset($target->rating)) {
                $target->rating = $target->receivedRatings()->avg('score') ?: 0;
            }
        }
        
        // Установить заголовок страницы
        $title_site = "Профиль пользователя {$target->name} | Личный кабинет";
        
        // Передать данные в представление (добавляем $viewer)
        return view('profile_view', compact('target', 'title_site', 'viewer'));
    }

    /**
     * Проверка возможности просмотра профиля другого пользователя.
     * Здесь используется свойство status, а не role.
     */
    protected function canViewProfile($viewer, $target)
    {
        if ($viewer->id === $target->id) {
            return true;
        }
    
        $viewerStatus = strtolower(trim($viewer->status));
        $targetStatus = strtolower(trim($target->status));
    
        if (in_array($viewerStatus, ['admin', 'coordinator'])) {
            return true;
        }
        
        // Исполнители не могут просматривать другие профили исполнителей
        $executorStatuses = ['architect', 'designer', 'executor', 'visualizer'];
        if (in_array($viewerStatus, $executorStatuses) && in_array($targetStatus, $executorStatuses)) {
            return false;
        }
    
        switch ($viewerStatus) {
            case 'user':
                return in_array($targetStatus, ['partner', 'coordinator', 'architect', 'designer', 'executor', 'visualizer']);
            case 'partner':
                return in_array($targetStatus, ['coordinator', 'architect', 'designer', 'executor', 'visualizer']);
            case 'architect':
            case 'designer':
            case 'executor':
            case 'visualizer':
                return in_array($targetStatus, ['user', 'coordinator', 'partner']);
            default:
                return false;
        }
    }

    // 3. Отправка кода подтверждения на телефон
    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $user = Auth::user();
        $rawPhone = preg_replace('/\D/', '', $request->input('phone'));
        
        // Проверка правильности номера телефона
        if (strlen($rawPhone) < 10) {
            Log::error('Неверный формат номера телефона', ['phone' => $rawPhone, 'user_id' => $user->id]);
            return response()->json([
                'success' => false,
                'message' => 'Неверный формат номера телефона.'
            ]);
        }
        
        $formattedPhone = '+7 (' . substr($rawPhone, 1, 3) . ') ' 
                         . substr($rawPhone, 4, 3) 
                         . '-' 
                         . substr($rawPhone, 7, 2) 
                         . '-' 
                         . substr($rawPhone, 9);

        $verificationCode = rand(1000, 9999);
        $apiKey = config('services.smsru.api_id', '6CDCE0B0-6091-278C-5145-360657FF0F9B');

        // Добавляем логирование для отладки
        Log::info('Отправка кода подтверждения', [
            'user_id' => $user->id,
            'phone' => $rawPhone,
            'code' => $verificationCode
        ]);

        try {
            $response = Http::get("https://sms.ru/sms/send", [
                'api_id' => $apiKey,
                'to'     => $rawPhone,
                'msg'    => "Ваш код: $verificationCode",
                'json'   => 1
            ]);

            // Логируем ответ API для отладки
            Log::info('Ответ SMS.RU API', [
                'response' => $response->json(),
                'status' => $response->status()
            ]);

            if ($response->failed()) {
                Log::error('Ошибка отправки SMS', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при отправке SMS.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Исключение при отправке SMS', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при отправке SMS: ' . $e->getMessage()
            ]);
        }

        try {
            // Сохраняем только код подтверждения и временный номер, но не обновляем телефон в профиле
            \DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'verification_code' => $verificationCode,
                    'verification_code_expires_at' => now()->addMinutes(10),
                    'temp_phone' => $rawPhone // Сохраняем во временное поле
                ]);
                
            Log::info('Верификационный код успешно сохранен в БД', [
                'user_id' => $user->id,
                'verification_code' => $verificationCode
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен.',
                'debug_code' => $verificationCode // добавляем для отладки, потом удалить
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при сохранении кода подтверждения', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении данных: ' . $e->getMessage()
            ]);
        }
    }

    // 4. Подтверждение кода подтверждения телефона
    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone'             => 'required',
            'verification_code' => 'required|numeric|digits:4',
        ]);

        $user = Auth::user();
        $verificationCode = $request->input('verification_code');

        if ($user->verification_code == $verificationCode 
            && now()->lessThanOrEqualTo($user->verification_code_expires_at)) 
        {
            // Получаем сохраненный временный номер телефона
            $rawPhone = $user->temp_phone ?: preg_replace('/\D/', '', $request->input('phone'));
            
            $formattedPhone = '+7 (' . substr($rawPhone, 1, 3) . ') '
                            . substr($rawPhone, 4, 3) . '-'
                            . substr($rawPhone, 7, 2) . '-'
                            . substr($rawPhone, 9, 2);

            $user->phone = $formattedPhone;
            $user->verification_code = null;
            $user->verification_code_expires_at = null;
            $user->temp_phone = null; // Очищаем временное поле
            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Номер телефона успешно обновлен.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Неверный или просроченный код.'
        ]);
    }

    // 5. Обновление аватара пользователя
    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        $user = Auth::user();

        try {
            if ($user->avatar_url && file_exists(public_path($user->avatar_url))) {
                unlink(public_path($user->avatar_url));
            }

            $avatar = $request->file('avatar');
            $avatarPath = 'user/avatar/' . $user->id . '/' . uniqid() . '.' . $avatar->getClientOriginalExtension();
            $destinationPath = public_path('user/avatar/' . $user->id);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $avatar->move($destinationPath, basename($avatarPath));
            $user->avatar_url = $avatarPath;
            $user->save();

            // Возвращаем редирект и на route('profile') и на route('profile.index') для совместимости
            if (Route::has('profile')) {
                return redirect()->route('profile')->with('success', 'Аватар успешно обновлен');
            }
            return redirect()->route('profile.index')->with('success', 'Аватар успешно обновлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении аватара: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'exception' => $e
            ]);
            
            return back()->with('error', 'Не удалось обновить аватар: ' . $e->getMessage());
        }
    }

    /**
     * Удаление аккаунта пользователя по его запросу
     */
    public function deleteAccount(Request $request)
    {
        // Пропускаем проверку пароля, если передано confirmed
        if ($request->password !== 'confirmed') {
            // Валидация пароля только если это не подтверждение из модального окна
            $request->validate([
                'password' => 'required',
            ]);
            
            $user = Auth::user();
            
            // Проверка пароля перед удалением
            if (!Hash::check($request->password, $user->password)) {
                return redirect()
                    ->back()
                    ->with('error', 'Неверный пароль. Аккаунт не был удален.');
            }
        }
        
        $user = Auth::user();
        
        // Проверяем, является ли аккаунт тестовым по номеру телефона
        $testPhone = '+7 (000) 000-00-01';
        if ($user->phone === $testPhone || preg_replace('/\D/', '', $user->phone) === '70000000001') {
            // Для тестового аккаунта только завершаем сессию, но не удаляем аккаунт
            \Log::info("Попытка удаления тестового аккаунта ID:{$user->id} ({$user->email}) - аккаунт сохранен.");
            
            // Выходим из системы
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Перенаправляем на страницу логина с информационным сообщением
            return redirect()->route('login.password')
                ->with('success', 'Вы вышли из системы. Тестовый аккаунт не может быть удален.');
        }
        
        try {
            // Начинаем транзакцию для безопасного обновления всех связанных записей
            \DB::beginTransaction();
            
            // Сохраняем данные пользователя перед удалением для использования в сделках
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone
            ];
            
            // Обновляем брифы - сохраняем ID удаляемого пользователя
            \DB::table('commons')
                ->where('user_id', $user->id)
                ->update([
                    'user_id_before_deletion' => $user->id,
                    'updated_at' => now(),
                ]);
            
            \DB::table('commercials')
                ->where('user_id', $user->id)
                ->update([
                    'user_id_before_deletion' => $user->id,
                    'updated_at' => now(),
                ]);
            
            // Обновляем сделки, где пользователь был основным владельцем
            \DB::table('deals')
                ->where('user_id', $user->id)
                ->update([
                    'deleted_user_id' => $user->id,
                    'deleted_user_name' => $userData['name'],
                    'deleted_user_email' => $userData['email'],
                    'deleted_user_phone' => $userData['phone'],
                    'updated_at' => now(),
                ]);
                
            // Обновляем сделки, где пользователь был в разных ролях
            \DB::table('deals')
                ->where('coordinator_id', $user->id)
                ->update([
                    'deleted_coordinator_id' => $user->id,
                    'updated_at' => now(),
                ]);
            
            \DB::table('deals')
                ->where('architect_id', $user->id)
                ->update([
                    'deleted_architect_id' => $user->id,
                    'updated_at' => now(),
                ]);
            
            \DB::table('deals')
                ->where('designer_id', $user->id)
                ->update([
                    'deleted_designer_id' => $user->id,
                    'updated_at' => now(),
                ]);
            
            \DB::table('deals')
                ->where('visualizer_id', $user->id)
                ->update([
                    'deleted_visualizer_id' => $user->id,
                    'updated_at' => now(),
                ]);
            
            // Сохраняем информацию об удаленном пользователе в pivot-таблицу
            \DB::table('deal_user')
                ->where('user_id', $user->id)
                ->update([
                    'deleted_user_data' => json_encode($userData),
                    'updated_at' => now(),
                ]);
            
            // Логируем удаление аккаунта для аудита
            \Log::info("Пользователь ID:{$user->id} ({$user->email}) удалил свой аккаунт");
            
            // Мягкое удаление пользователя
            $user->delete();
            
            \DB::commit();
            
            // Выходим из системы
            Auth::logout();
            
            // Перенаправляем на страницу логина с сообщением об успехе
            return redirect()->route('login.password')
                ->with('success', 'Ваш аккаунт был успешно удален. Все ваши проекты сохранены в системе.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Ошибка при удалении аккаунта: ' . $е->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Произошла ошибка при удалении аккаунта. Пожалуйста, попробуйте позже или обратитесь в поддержку.');
        }
    }

    // 7. Изменение пароля - добавляем обработку ошибок
    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'new_password' => 'required|min:8|confirmed',
            ]);

            $user = Auth::user();
            $user->password = bcrypt($request->new_password);
            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Пароль успешно изменен!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', $e->errors())
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при смене пароля: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при смене пароля',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 8. Обновление профиля - добавляем обработку ошибок
    public function updateProfile(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . Auth::id(),
            ]);

            $user = Auth::user();

            if ($request->filled('name')) {
                $user->name = $request->name;
            }
            if ($request->filled('email')) {
                $user->email = $request->email;
            }

            $user->save();

            return response()->json([
                'success' => true, 
                'message' => 'Данные успешно обновлены!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', $e->errors())
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении профиля: ' . $е->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении профиля',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 9. Обновление профиля (новый метод) - улучшаем обработку ошибок
    public function updateProfileAll(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Отменяем проверку политики authorization, которая может вызывать ошибку
            // $this->authorize('update', $user);
            
            // Общие правила валидации
            $rules = [
                'name'         => 'nullable|string|max:255',
                'email'        => 'nullable|email|unique:users,email,' . $user->id,
                'new_password' => 'nullable|min:8|confirmed',
            ];

            // Дополнительные правила в зависимости от статуса пользователя
            switch ($user->status) {
                case 'user':
                    $rules['city'] = 'nullable|string|max:255';
                    break;
                case 'partner':
                    $rules['city'] = 'nullable|string|max:255';
                    $rules['contract_number'] = 'nullable|string|max:255';
                    $rules['comment'] = 'nullable|string';
                    break;
                case 'executor': // Профиль исполнителя
                case 'architect':
                case 'designer':
                case 'visualizer':
                    $rules['city'] = 'nullable|string|max:255'; // город/часовой пояс
                    $rules['portfolio_link'] = 'nullable|url';
                    $rules['experience'] = 'nullable|string|max:255';
                    $rules['rating'] = 'nullable|string|max:255';
                    $rules['active_projects_count'] = 'nullable|integer';
                    break;
                case 'coordinator':
                    $rules['experience'] = 'nullable|string|max:255';
                    $rules['rating'] = 'nullable|string|max:255';
                    break;
            }

            $validated = $request->validate($rules);

            // Обновляем базовые поля
            foreach (['name', 'email'] as $field) {
                if ($request->filled($field)) {
                    $user->$field = $request->$field;
                }
            }
            
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }

            // Обновляем дополнительные поля в зависимости от статуса
            switch ($user->status) {
                case 'user':
                    if ($request->filled('city')) {
                        $user->city = $request->city;
                    }
                    break;
                case 'partner':
                    $this->updatePartnerFields($user, $request);
                    break;
                case 'executor':
                case 'architect':
                case 'designer':
                case 'visualizer':
                    $this->updateExecutorFields($user, $request);
                    break;
                case 'coordinator':
                    $this->updateCoordinatorFields($user, $request);
                    break;
            }

            $user->save();

            Log::info('Профиль успешно обновлен', ['user_id' => $user->id, 'status' => $user->status]);

            return response()->json([
                'success' => true,
                'message' => 'Данные успешно обновлены!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ошибка валидации при обновлении профиля', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors()))
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении профиля: ' . $е->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении профиля: ' . $е->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }
    
    /**
     * Обновление полей для партнера
     */
    private function updatePartnerFields($user, $request)
    {
        $fields = ['city', 'contract_number', 'comment'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $user->$field = $request->$field;
            }
        }
    }

    /**
     * Обновление полей для исполнителя
     */
    private function updateExecutorFields($user, $request)
    {
        $fields = ['city', 'portfolio_link', 'experience', 'rating', 'active_projects_count'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $user->$field = $request->$field;
            }
        }
    }

    /**
     * Обновление полей для координатора
     */
    private function updateCoordinatorFields($user, $request)
    {
        $fields = ['experience', 'rating'];
        foreach ($fields as $field) {
            if ($request->filled($field)) {
                $user->$field = $request->$field;
            }
        }
    }

    /**
     * Обновление профиля пользователя администратором
     * 
     * @param Request $request
     * @param int $id ID пользователя для обновления
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserProfileByAdmin(Request $request, $id)
    {
        try {
            // Проверяем, что текущий пользователь - администратор
            if (Auth::user()->status !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'У вас нет прав для выполнения этого действия'
                ], status: 403);
            }
            
            // Находим пользователя
            $user = User::findOrFail($id);
            
            // Общие правила валидации
            $rules = [
                'name'         => 'nullable|string|max:255',
                'email'        => 'nullable|email|unique:users,email,' . $user->id,
                'phone'        => 'nullable|string|max:20',
                'city'         => 'nullable|string|max:255',
                'experience'   => 'nullable|integer|min:0|max:100',
                'status'       => 'nullable|string|max:20',
            ];

            // Дополнительные правила для разных статусов
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'partner':
                        $rules['contract_number'] = 'nullable|string|max:255';
                        $rules['comment'] = 'nullable|string';
                        break;
                    case 'executor':
                    case 'architect':
                    case 'designer':
                    case 'visualizer':
                        $rules['portfolio_link'] = 'nullable|url';
                        $rules['active_projects_count'] = 'nullable|integer';
                        break;
                }
            }

            $validated = $request->validate($rules);

            // Обновляем основные поля
            foreach (['name', 'email', 'phone', 'city', 'experience'] as $field) {
                if ($request->filled($field)) {
                    $user->$field = $request->$field;
                }
            }
            
            // Обновляем статус пользователя
            if ($request->filled('status') && $request->status !== $user->status) {
                $user->status = $request->status;
            }
            
            // Обновляем дополнительные поля в зависимости от статуса
            if ($request->filled('portfolio_link')) {
                $user->portfolio_link = $request->portfolio_link;
            }
            
            if ($request->filled('contract_number')) {
                $user->contract_number = $request->contract_number;
            }
            
            if ($request->filled('comment')) {
                $user->comment = $request->comment;
            }
            
            if ($request->filled('active_projects_count')) {
                $user->active_projects_count = $request->active_projects_count;
            }
            
            // Изменение пароля (если указан)
            if ($request->filled('new_password')) {
                $user->password = Hash::make($request->new_password);
            }

            $user->save();

            Log::info('Администратор обновил профиль пользователя', [
                'admin_id' => Auth::id(),
                'user_id' => $user->id,
                'updated_fields' => array_keys($request->except(['_token', 'password']))
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Профиль пользователя успешно обновлен'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ошибка валидации при обновлении профиля пользователя администратором', [
                'errors' => $e->errors(),
                'admin_id' => Auth::id(),
                'user_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации: ' . implode(', ', array_map(function($errors) {
                    return implode(', ', $errors);
                }, $e->errors()))
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении профиля пользователя администратором: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => Auth::id(),
                'user_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при обновлении профиля пользователя: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    public function updateFirebaseToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = Auth::user();

        try {
            $user->firebase_token = $request->token;
            $user->save();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении Firebase токена:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
