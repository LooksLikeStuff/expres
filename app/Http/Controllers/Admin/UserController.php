<?php

namespace App\Http\Controllers\Admin;

use App\Models\Common;
use App\Models\Commercial;
use App\Models\Deal;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserController extends BaseAdminController
{
    /**
     * Показать список пользователей
     */
    public function index(Request $request)
    {
        $title_site = "Управление пользователями | Личный кабинет Экспресс-дизайн";
        $user = $this->getAdminUser();

        // Получение данных
        $usersCount = User::count();
        $commonsCount = Common::count();
        $commercialsCount = Commercial::count();
        $dealsCount = Deal::count();
        $estimatesCount = Estimate::count();

        // Создаем запрос для получения пользователей с фильтрацией
        $query = User::query();

        // Применяем поиск по тексту
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Применяем фильтр по статусам
        if ($request->filled('status')) {
            $statuses = $request->status;
            if (!is_array($statuses)) {
                $statuses = [$statuses]; // Преобразуем в массив если передан одиночный статус
            }
            $query->whereIn('status', $statuses);
        }

        // Применяем фильтр по диапазону дат
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', Carbon::today());
                    break;
                case 'week':
                    $query->where('created_at', '>=', Carbon::now()->subWeek());
                    break;
                case 'month':
                    $query->where('created_at', '>=', Carbon::now()->subMonth());
                    break;
                case 'quarter':
                    $query->where('created_at', '>=', Carbon::now()->subMonths(3));
                    break;
                case 'year':
                    $query->where('created_at', '>=', Carbon::now()->subYear());
                    break;
                case 'custom':
                    if ($request->filled('date_from')) {
                        $query->whereDate('created_at', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('created_at', '<=', $request->date_to);
                    }
                    break;
            }
        }

        // Фильтр по количеству брифов
        if ($request->filled('briefs')) {
            switch ($request->briefs) {
                case 'has':
                    $query->whereHas('commons')->orWhereHas('commercials');
                    break;
                case 'no':
                    $query->whereDoesntHave('commons')->whereDoesntHave('commercials');
                    break;
                case 'many':
                    $query->where(function($q) {
                        $q->whereHas('commons', '>', 5)->orWhereHas('commercials', '>', 5);
                    });
                    break;
            }
        }

        // Фильтр по активности пользователей
        if ($request->filled('activity')) {
            switch ($request->activity) {
                case 'active':
                    // Пользователи активны сейчас (онлайн)
                    $query->where('last_activity', '>=', Carbon::now()->subMinutes(15));
                    break;
                case 'recent':
                    // Активны за последний день
                    $query->where('last_activity', '>=', Carbon::now()->subDay());
                    break;
                case 'inactive':
                    // Неактивны более 30 дней
                    $query->where(function($q) {
                        $q->where('last_activity', '<', Carbon::now()->subDays(30))
                          ->orWhereNull('last_activity');
                    });
                    break;
            }
        }

        // Фильтр по домену email
        if ($request->filled('email_domain')) {
            $domain = $request->email_domain;
            $query->where('email', 'like', "%@{$domain}");
        }

        // Фильтр по наличию сделок
        if ($request->filled('deals')) {
            switch ($request->deals) {
                case 'has':
                    $query->whereHas('deals');
                    break;
                case 'no':
                    $query->whereDoesntHave('deals');
                    break;
                case 'completed':
                    $query->whereHas('deals', function($q) {
                        $q->where('status', 'completed');
                    });
                    break;
            }
        }        // Фильтр по номеру телефона
        if ($request->filled('phone')) {
            // Получаем и декодируем номер телефона
            $phoneInput = urldecode($request->phone);

            // Логируем входящие данные для отладки
            Log::info('Фильтр по телефону', [
                'original_phone' => $request->phone,
                'url_decoded' => $phoneInput,
                'raw_input' => $request->input('phone')
            ]);

            // Нормализуем номер телефона - оставляем только цифры
            $phoneNumbers = [];
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);

            if (strlen($cleanPhone) >= 7) { // Изменили минимальную длину на 7
                // Добавляем различные варианты поиска
                $phoneNumbers[] = $cleanPhone; // Чистые цифры

                // Если номер российский (начинается с 7 или 8)
                if (strlen($cleanPhone) == 11) {
                    if ($cleanPhone[0] == '7') {
                        $phoneNumbers[] = '8' . substr($cleanPhone, 1); // Заменяем 7 на 8
                        $phoneNumbers[] = '+7' . substr($cleanPhone, 1); // Добавляем +7
                    } elseif ($cleanPhone[0] == '8') {
                        $phoneNumbers[] = '7' . substr($cleanPhone, 1); // Заменяем 8 на 7
                        $phoneNumbers[] = '+7' . substr($cleanPhone, 1); // Добавляем +7
                    }
                }

                // Последние 10 цифр для поиска без кода страны
                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    $phoneNumbers[] = $last10;
                }

                // Форматированный номер как в исходном запросе
                $phoneNumbers[] = $phoneInput;

                // Добавляем варианты с различными форматами для российских номеров
                if (strlen($cleanPhone) == 11 && $cleanPhone[0] == '7') {
                    $number = substr($cleanPhone, 1);
                    $phoneNumbers[] = '+7 (' . substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6, 2) . '-' . substr($number, 8, 2);
                    $phoneNumbers[] = '8 (' . substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6, 2) . '-' . substr($number, 8, 2);
                }

                // Убираем дубликаты
                $phoneNumbers = array_unique($phoneNumbers);
            }

            Log::info('Варианты поиска номера телефона', [
                'cleaned_phone' => $cleanPhone,
                'search_variants' => $phoneNumbers
            ]);

            // Поиск по всем вариантам номера
            if (!empty($phoneNumbers)) {
                $query->where(function($q) use ($phoneNumbers) {
                    foreach ($phoneNumbers as $phone) {
                        $q->orWhere('phone', 'LIKE', "%{$phone}%");
                    }
                });
            }
        }        // Получение SQL запроса для отладки (до выполнения)
        $sqlQuery = $query->toSql();
        $sqlBindings = $query->getBindings();

        // Получение отфильтрованных пользователей
        $users = $query->get();

        // Добавляем отладочную информацию
        $phoneDebug = null;
        if ($request->filled('phone')) {
            $phoneInput = urldecode($request->phone);
            $cleanPhone = preg_replace('/[^0-9]/', '', $phoneInput);

            $phoneNumbers = [];
            if (strlen($cleanPhone) >= 7) {
                $phoneNumbers[] = $cleanPhone;

                if (strlen($cleanPhone) == 11) {
                    if ($cleanPhone[0] == '7') {
                        $phoneNumbers[] = '8' . substr($cleanPhone, 1);
                        $phoneNumbers[] = '+7' . substr($cleanPhone, 1);
                    } elseif ($cleanPhone[0] == '8') {
                        $phoneNumbers[] = '7' . substr($cleanPhone, 1);
                        $phoneNumbers[] = '+7' . substr($cleanPhone, 1);
                    }
                }

                if (strlen($cleanPhone) >= 10) {
                    $last10 = substr($cleanPhone, -10);
                    $phoneNumbers[] = $last10;
                }

                $phoneNumbers[] = $phoneInput;
                $phoneNumbers = array_unique($phoneNumbers);
            }

            $phoneDebug = [
                'original_input' => $request->phone,
                'url_decoded' => $phoneInput,
                'cleaned_phone' => $cleanPhone,
                'search_variants' => $phoneNumbers
            ];
        }
          $debugInfo = [
            'request_params' => $request->all(),
            'phone_filter_applied' => $request->filled('phone'),
            'phone_value' => $request->phone ?? 'не указан',
            'phone_debug' => $phoneDebug,
            'users_found' => $users->count(),
            'total_users' => User::count(),
            'sql_query' => $sqlQuery,
            'sql_bindings' => $sqlBindings
        ];return view('admin.users', compact(
            'user',
            'title_site',
            'usersCount',
            'commonsCount',
            'commercialsCount',
            'dealsCount',
            'estimatesCount',
            'users',
            'debugInfo'
        ));
    }

    /**
     * Обновить информацию о пользователе
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Проверяем, был ли передан новый пароль
        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password); // Хешируем пароль перед сохранением
        }

        // Обновляем остальные данные пользователя
        $user->update($request->only(['name', 'phone', 'status']));

        return response()->json(['success' => true]);
    }

    /**
     * Удалить пользователя
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

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

            // Обновляем сделки, где пользователь был координатором
            \DB::table('deals')
                ->where('coordinator_id', $user->id)
                ->update([
                    'deleted_coordinator_id' => $user->id,
                    'updated_at' => now(),
                ]);

            // Обновляем сделки, где пользователь был архитектором
            \DB::table('deals')
                ->where('architect_id', $user->id)
                ->update([
                    'deleted_architect_id' => $user->id,
                    'updated_at' => now(),
                ]);

            // Обновляем сделки, где пользователь был дизайнером
            \DB::table('deals')
                ->where('designer_id', $user->id)
                ->update([
                    'deleted_designer_id' => $user->id,
                    'updated_at' => now(),
                ]);

            // Обновляем сделки, где пользователь был визуализатором
            \DB::table('deals')
                ->where('visualizer_id', $user->id)
                ->update([
                    'deleted_visualizer_id' => $user->id,
                    'updated_at' => now(),
                ]);

            // Сохраняем информацию об удаленном пользователе в pivot-таблицу
            \DB::table('deal_users')
                ->where('user_id', $user->id)
                ->update([
                    'deleted_user_data' => json_encode($userData),
                    'updated_at' => now(),
                ]);

            // Мягкое удаление пользователя
            $user->delete();

            \DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Ошибка при удалении пользователя: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка при удалении пользователя'], 500);
        }
    }

    /**
     * Показать брифы пользователя
     */
    public function briefs($id)
    {
        $user = User::findOrFail($id);
        $title_site = "Управление брифами пользователя | Личный кабинет Экспресс-дизайн";
        // Получаем брифы пользователя
        $commonBriefs = Common::where('user_id', $id)->get();
        $commercialBriefs = Commercial::where('user_id', $id)->get();

        return view('admin.user_briefs', compact('user', 'commonBriefs', 'commercialBriefs', 'title_site'));
    }

    /**
     * Отображает брифы конкретного пользователя
     *
     * @param int $id ID пользователя
     * @return \Illuminate\View\View
     */
    public function userBriefs($id)
    {
        $user = User::findOrFail($id);
        $commonBriefs = $user->commons()->get();
        $commercialBriefs = $user->commercials()->get();

        return view('admin.user_briefs', compact('user', 'commonBriefs', 'commercialBriefs'));
    }

    /**
     * Показать список удаленных пользователей
     */
    public function trashed(Request $request)
    {
        $title_site = "Удаленные пользователи | Личный кабинет Экспресс-дизайн";
        $user = $this->getAdminUser();

        // Получаем только удаленных пользователей
        $trashed_users = User::onlyTrashed();

        // Применяем поиск по тексту
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $trashed_users->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Применяем фильтр по статусам
        if ($request->filled('statuses') && is_array($request->statuses)) {
            $trashed_users->whereIn('status', $request->statuses);
        }

        // Получение отфильтрованных удаленных пользователей
        $trashed_users = $trashed_users->get();

        return view('admin.users_trashed', compact(
            'user',
            'title_site',
            'trashed_users'
        ));
    }

    /**
     * Восстановить удаленного пользователя
     */
    public function restore($id)
    {
        try {
            // Начинаем транзакцию
            \DB::beginTransaction();

            // Находим удаленного пользователя
            $user = User::onlyTrashed()->findOrFail($id);

            // Восстанавливаем связи в брифах
            \DB::table('commons')
                ->where('user_id_before_deletion', $user->id)
                ->update([
                    'user_id' => $user->id,
                    'user_id_before_deletion' => null,
                    'updated_at' => now()
                ]);

            \DB::table('commercials')
                ->where('user_id_before_deletion', $user->id)
                ->update([
                    'user_id' => $user->id,
                    'user_id_before_deletion' => null,
                    'updated_at' => now()
                ]);

            // Восстанавливаем связи в сделках
            $deals = \DB::table('deals')->where('deleted_user_id', $user->id)->get();
            foreach ($deals as $deal) {
                \DB::table('deals')
                    ->where('id', $deal->id)
                    ->update([
                        'user_id' => $user->id,
                        'deleted_user_id' => null,
                        'deleted_user_name' => null,
                        'deleted_user_email' => null,
                        'deleted_user_phone' => null,
                        'updated_at' => now()
                    ]);
            }

            // Восстанавливаем связи с координаторами
            \DB::table('deals')
                ->where('deleted_coordinator_id', $user->id)
                ->update([
                    'coordinator_id' => $user->id,
                    'deleted_coordinator_id' => null,
                    'updated_at' => now()
                ]);

            // Восстанавливаем связи для архитекторов
            \DB::table('deals')
                ->where('deleted_architect_id', $user->id)
                ->update([
                    'architect_id' => $user->id,
                    'deleted_architect_id' => null,
                    'updated_at' => now()
                ]);

            // Восстанавливаем связи для дизайнеров
            \DB::table('deals')
                ->where('deleted_designer_id', $user->id)
                ->update([
                    'designer_id' => $user->id,
                    'deleted_designer_id' => null,
                    'updated_at' => now()
                ]);

            // Восстанавливаем связи для визуализаторов
            \DB::table('deals')
                ->where('deleted_visualizer_id', $user->id)
                ->update([
                    'visualizer_id' => $user->id,
                    'deleted_visualizer_id' => null,
                    'updated_at' => now()
                ]);

            // Восстанавливаем пользователя в промежуточных таблицах
            $dealUsers = \DB::table('deal_users')
                ->whereNotNull('deleted_user_data')
                ->get();

            foreach ($dealUsers as $dealUser) {
                $deletedUserData = json_decode($dealUser->deleted_user_data, true);
                if (isset($deletedUserData['id']) && $deletedUserData['id'] == $user->id) {
                    \DB::table('deal_users')
                        ->where('id', $dealUser->id)
                        ->update([
                            'user_id' => $user->id,
                            'deleted_user_data' => null,
                            'updated_at' => now()
                        ]);
                }
            }

            // Восстанавливаем пользователя
            $user->restore();

            \DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Ошибка при восстановлении пользователя: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Произошла ошибка при восстановлении пользователя'], 500);
        }
    }

    /**
     * Окончательно удалить пользователя
     */
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['success' => true]);
    }

    /**
     * Нормализует номер телефона для поиска
     * Удаляет все нецифровые символы, кроме +
     */
    private function normalizePhoneNumber($phone)
    {
        // Удаляем все символы, кроме цифр и +
        $normalizedPhone = preg_replace('/[^0-9+]/', '', $phone);

        // Если номер начинается с 8 и его длина 11, меняем на +7
        if (strlen($normalizedPhone) == 11 && $normalizedPhone[0] == '8') {
            $normalizedPhone = '+7' . substr($normalizedPhone, 1);
        }

        // Если номер начинается с 7 и нет "+", добавляем "+"
        if (strlen($normalizedPhone) == 11 && $normalizedPhone[0] == '7' && strpos($normalizedPhone, '+') !== 0) {
            $normalizedPhone = '+' . $normalizedPhone;
        }

        return $normalizedPhone;
    }

    /**
     * Добавляет условия для нечеткого поиска по телефону
     */
    private function addFuzzyPhoneSearch($query, $searchPhone)
    {
        // Удаляем все форматирование для поиска
        $cleanNumber = preg_replace('/[^0-9]/', '', $searchPhone);

        // Если мало цифр, нет смысла делать нечеткий поиск
        if (strlen($cleanNumber) < 5) {
            return;
        }

        // Разбиваем номер на части для поиска частичных совпадений
        $parts = [];
        for ($i = 0; $i < strlen($cleanNumber) - 3; $i++) {
            if ($i % 2 == 0) { // Берем каждый второй символ для уменьшения количества запросов
                $parts[] = substr($cleanNumber, $i, 4);
            }
        }

        foreach ($parts as $part) {
            $query->orWhere(DB::raw('REPLACE(REPLACE(REPLACE(phone, "+", ""), "-", ""), " ", "")'), 'LIKE', '%' . $part . '%');
        }
    }

    /**
     * Рассчитывает процент сходства между двумя строками
     */
    private function calculateStringSimilarity($string1, $string2)
    {
        // Приводим к нижнему регистру
        $string1 = mb_strtolower($string1);
        $string2 = mb_strtolower($string2);

        // Удаляем лишние пробелы
        $string1 = trim($string1);
        $string2 = trim($string2);

        // Для коротких строк используем функцию similar_text
        if (strlen($string1) <= 50 && strlen($string2) <= 50) {
            similar_text($string1, $string2, $percent);
            return $percent;
        }

        // Для длинных строк используем расчет по алгоритму Левенштейна
        $lev = levenshtein($string1, $string2);
        $maxLen = max(strlen($string1), strlen($string2));

        if ($maxLen == 0) return 100; // Обе строки пустые

        return (1 - $lev / $maxLen) * 100;
    }

    // Другие методы контроллера...
}
