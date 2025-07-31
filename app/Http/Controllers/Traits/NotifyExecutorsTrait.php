<?php

namespace App\Http\Controllers\Traits;

use App\Models\User;
use App\Services\SmsService;

trait NotifyExecutorsTrait
{
    /**
     * Отправить SMS-уведомление архитектору, дизайнеру и визуализатору о прикреплении к сделке
     *
     * @param \App\Models\Deal $deal
     * @return void
     */
    protected function notifyExecutorsAboutAttach($deal)
    {
        \Illuminate\Support\Facades\Log::info("Начало отправки SMS исполнителям для сделки #{$deal->id} / #{$deal->project_number}");
        
        $executors = [
            'architect' => $deal->architect,
            'designer' => $deal->designer,
            'visualizer' => $deal->visualizer,
        ];
        
        $smsService = new SmsService();
        
        foreach ($executors as $role => $user) {
            if (!$user) {
                \Illuminate\Support\Facades\Log::info("Исполнитель ({$role}) не назначен для сделки #{$deal->id}");
                continue;
            }
            
            if (empty($user->phone)) {
                \Illuminate\Support\Facades\Log::warning("У исполнителя {$role} (ID: {$user->id}, имя: {$user->name}) не указан номер телефона", [
                    'deal_id' => $deal->id,
                    'project_number' => $deal->project_number
                ]);
                continue;
            }
            
            $message = "Вы прикреплены к сделке #{$deal->project_number} как " . $this->getRoleLabel($role) . ".";
            
            // Логируем отправку
            \Illuminate\Support\Facades\Log::info("Отправка SMS о прикреплении к сделке #{$deal->project_number}", [
                'role' => $role,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'phone' => $user->phone,
                'formatted_phone' => preg_replace('/[^0-9]/', '', $user->phone) // Показываем, как будет отформатирован номер
            ]);
            
            $result = $smsService->sendSms($user->phone, $message);
            
            if ($result) {
                \Illuminate\Support\Facades\Log::info("SMS успешно отправлено исполнителю {$role} ({$user->name}) на номер {$user->phone}");
            } else {
                \Illuminate\Support\Facades\Log::error("Ошибка при отправке SMS исполнителю {$role} ({$user->name}) на номер {$user->phone}");
            }
        }
    }

    /**
     * Получить человекочитаемое название роли
     *
     * @param string $role
     * @return string
     */
    protected function getRoleLabel($role)
    {
        switch ($role) {
            case 'architect':
                return 'Архитектор';
            case 'designer':
                return 'Дизайнер';
            case 'visualizer':
                return 'Визуализатор';
            default:
                return 'Исполнитель';
        }
    }
}
