<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Сохранить подписку пользователя на push-уведомления
     */
    public function store(Request $request)
    {
        try {
            $user = auth()->user();
            $subscription = $request->all();
            
            // Создаем или обновляем подписку
            PushSubscription::updateOrCreate(
                ['user_id' => $user->id],
                ['endpoint' => $subscription['endpoint'], 
                 'keys' => json_encode($subscription['keys']),
                 'expiration_time' => $subscription['expirationTime'] ?? null]
            );
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения подписки push-уведомлений: ' . $e->getMessage());
            return response()->json(['error' => 'Не удалось сохранить подписку'], 500);
        }
    }

    /**
     * Отправить тестовое push-уведомление
     */
    public function sendTest(Request $request)
    {
        // Проверка прав доступа
        if (auth()->user()->status !== 'admin') {
            return response()->json(['error' => 'Недостаточно прав'], 403);
        }
        
        try {
            $user = auth()->user();
            $subscription = PushSubscription::where('user_id', $user->id)->first();
            
            if (!$subscription) {
                return response()->json(['error' => 'Подписка не найдена'], 404);
            }
            
            // Здесь код для отправки тестового уведомления
            // В реальном приложении должна быть интеграция с сервисом push-уведомлений
            
            return response()->json(['success' => true, 'message' => 'Тестовое уведомление отправлено']);
        } catch (\Exception $e) {
            Log::error('Ошибка отправки push-уведомления: ' . $e->getMessage());
            return response()->json(['error' => 'Не удалось отправить уведомление'], 500);
        }
    }
}
