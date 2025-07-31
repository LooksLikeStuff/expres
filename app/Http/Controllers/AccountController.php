<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Показывает страницу удаления аккаунта (с объединенной информацией)
     */
    public function showDeleteAccountForm()
    {
        $title_site = "Удаление аккаунта | Экспресс-дизайн";
        return view('account.delete', compact('title_site'));
    }

    /**
     * Отправляет код подтверждения на телефон
     */
    public function sendDeleteAccountCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
        ]);

        $inputPhone = $request->input('phone');
        $rawPhone = preg_replace('/\D/', '', $inputPhone);
        
        // Логируем исходные данные для отладки
        Log::info('Получен запрос на удаление аккаунта', [
            'raw_input' => $inputPhone,
            'cleaned_phone' => $rawPhone
        ]);
        
        // Проверка правильности номера телефона
        if (strlen($rawPhone) < 10) {
            Log::error('Неверный формат номера телефона', ['phone' => $rawPhone]);
            return response()->json([
                'success' => false,
                'message' => 'Неверный формат номера телефона.'
            ]);
        }

        // Разные форматы для поиска
        $last10 = substr($rawPhone, -10);
        $full11 = $rawPhone;
        if (strlen($rawPhone) == 10) {
            $full11 = '7' . $rawPhone;
        } elseif (strlen($rawPhone) == 11 && $rawPhone[0] == '8') {
            $full11 = '7' . substr($rawPhone, 1);
        }
        
        // Форматированные номера для поиска
        $formats = [
            '+7 (' . substr($last10, 0, 3) . ') ' . substr($last10, 3, 3) . '-' . substr($last10, 6, 2) . '-' . substr($last10, 8, 2),
            '8(' . substr($last10, 0, 3) . ')' . substr($last10, 3),
            '+7(' . substr($last10, 0, 3) . ')' . substr($last10, 3),
            '7(' . substr($last10, 0, 3) . ')' . substr($last10, 3),
            '8 (' . substr($last10, 0, 3) . ') ' . substr($last10, 3, 3) . '-' . substr($last10, 6, 2) . '-' . substr($last10, 8, 2),
            '7 (' . substr($last10, 0, 3) . ') ' . substr($last10, 3, 3) . '-' . substr($last10, 6, 2) . '-' . substr($last10, 8, 2),
            '+' . $full11,
            $full11,
            $last10,
        ];
        
        Log::info('Поиск по форматам телефонов', [
            'formats' => $formats
        ]);

        // Ищем пользователя
        $user = null;
        
        // Сначала ищем по точному совпадению
        $user = User::where('phone', $inputPhone)
            ->orWhere('temp_phone', $rawPhone)
            ->first();
            
        if (!$user) {
            // Затем ищем по различным форматам
            $user = User::where(function($query) use ($formats) {
                foreach ($formats as $format) {
                    $query->orWhere('phone', 'LIKE', '%' . $format . '%')
                          ->orWhere('phone', $format);
                }
            })->first();
        }
            
        if (!$user) {
            // Наконец, ищем в любом месте номера по последним цифрам
            $user = User::where('phone', 'LIKE', '%' . substr($last10, -6) . '%')
                ->orWhere('temp_phone', 'LIKE', '%' . substr($last10, -6) . '%')
                ->first();
        }
            
        // Отладка поиска
        if (!$user) {
            // Дополнительная отладка - получим все номера из базы
            $sampleUsers = DB::table('users')
                ->select('id', 'name', 'phone', 'temp_phone')
                ->limit(5)
                ->get();
                
            Log::warning('Пользователь не найден по номеру телефона, примеры форматов из базы:', [
                'sample_users' => $sampleUsers
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким номером телефона не найден. Пожалуйста, проверьте правильность ввода номера.'
            ]);
        }

        // Логируем найденного пользователя
        Log::info('Пользователь найден по номеру телефона', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_phone' => $user->phone,
            'user_temp_phone' => $user->temp_phone ?? 'не указан'
        ]);

        // Генерация кода подтверждения
        $verificationCode = rand(1000, 9999);
        $apiKey = config('services.smsru.api_id', '6CDCE0B0-6091-278C-5145-360657FF0F9B');

        Log::info('Отправка кода подтверждения для удаления аккаунта', [
            'user_id' => $user->id,
            'phone' => $rawPhone,
            'code' => $verificationCode
        ]);

        try {
            $response = Http::get("https://sms.ru/sms/send", [
                'api_id' => $apiKey,
                'to'     => $rawPhone,
                'msg'    => "Ваш код для удаления аккаунта: $verificationCode",
                'json'   => 1
            ]);

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
            // Сохраняем код подтверждения в профиле пользователя
            $user->verification_code = $verificationCode;
            $user->verification_code_expires_at = now()->addMinutes(10);
            $user->save();
            
            Log::info('Верификационный код для удаления аккаунта успешно сохранен в БД', [
                'user_id' => $user->id,
                'verification_code' => $verificationCode
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен.',
                'debug_code' => $verificationCode // для отладки, потом удалить
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

    /**
     * Проверяет код подтверждения и "удаляет" аккаунт (перенаправляя на страницу входа)
     */
    public function verifyDeleteAccountCode(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required|string|size:4',
        ]);

        $inputPhone = $request->input('phone');
        $rawPhone = preg_replace('/\D/', '', $inputPhone);
        $code = $request->input('code');

        Log::info('Получена попытка подтверждения кода для удаления аккаунта', [
            'raw_input' => $inputPhone,
            'cleaned_phone' => $rawPhone,
            'code' => $code
        ]);
        
        // Аналогично, используем более гибкий поиск как в методе sendDeleteAccountCode
        $last10 = substr($rawPhone, -10);
        $full11 = $rawPhone;
        if (strlen($rawPhone) == 10) {
            $full11 = '7' . $rawPhone;
        } elseif (strlen($rawPhone) == 11 && $rawPhone[0] == '8') {
            $full11 = '7' . substr($rawPhone, 1);
        }
        
        // Ищем пользователя всеми доступными способами
        $user = User::where(function($query) use ($rawPhone, $last10, $full11, $inputPhone) {
            $query->where('phone', $inputPhone)
                  ->orWhere('temp_phone', $rawPhone)
                  ->orWhere('phone', 'LIKE', '%' . $last10 . '%')
                  ->orWhere('temp_phone', 'LIKE', '%' . $last10 . '%');
        })->first();

        if (!$user) {
            Log::error('Пользователь не найден при проверке кода удаления аккаунта', [
                'phone' => $rawPhone,
                'input_phone' => $inputPhone
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким номером телефона не найден.'
            ]);
        }

        Log::info('Проверка кода подтверждения', [
            'user_id' => $user->id,
            'user_code' => $user->verification_code,
            'input_code' => $code,
            'expires_at' => $user->verification_code_expires_at,
            'now' => now()
        ]);

        // Проверяем код
        if ($user->verification_code != $code || now()->greaterThan($user->verification_code_expires_at)) {
            Log::warning('Неверный или просроченный код подтверждения', [
                'user_id' => $user->id,
                'user_code' => $user->verification_code,
                'input_code' => $code,
                'expires_at' => $user->verification_code_expires_at,
                'now' => now()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Неверный или просроченный код подтверждения.'
            ]);
        }

        // В реальном сценарии тут могло бы быть удаление аккаунта
        // Но согласно заданию делаем только заглушку
        
        // Очищаем код верификации
        $user->verification_code = null;
        $user->verification_code_expires_at = null;
        $user->save();
        
        Log::info('Запрос на удаление аккаунта успешно обработан', [
            'user_id' => $user->id,
            'phone' => $rawPhone
        ]);

        // Проверяем, если пользователь авторизован, разлогиниваем его
        if (Auth::check()) {
            $authUserId = Auth::id();
            
            Log::info('Сброс сессии пользователя при удалении аккаунта', [
                'auth_user_id' => $authUserId,
                'target_user_id' => $user->id
            ]);
            
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Аккаунт успешно удален.',
            'redirect' => route('login.password')
        ]);
    }
}
       