<?php

namespace App\Observers;

use App\Models\Deal;
use App\Models\DealChangeLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DealObserver
{
    /**
     * Обработка события создания сделки.
     */
    public function created(Deal $deal)
    {
        try {
            $user = Auth::user();
            $request = request();
            
            // Формируем изменения при создании
            $changes = [];
            foreach ($deal->getAttributes() as $key => $value) {
                if (!in_array($key, ['id', 'created_at', 'updated_at']) && $value !== null) {
                    $changes[$key] = [
                        'old' => null,
                        'new' => $value
                    ];
                }
            }
            
            DealChangeLog::create([
                'deal_id' => $deal->id,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Система',
                'action_type' => 'create',
                'changes' => $changes,
                'description' => "Создана новая сделка #{$deal->id}" . ($deal->project_name ? " - {$deal->project_name}" : ''),
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
            ]);
            
            Log::info("Создана сделка #{$deal->id}", [
                'deal_id' => $deal->id,
                'user_id' => $user ? $user->id : null,
                'changes_count' => count($changes),
                'ip_address' => $request ? $request->ip() : null,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Ошибка логирования создания сделки: " . $e->getMessage(), [
                'deal_id' => $deal->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Обработка события обновления сделки.
     */
    public function updated(Deal $deal)
    {
        try {
            $user = Auth::user();
            $request = request();
            $changes = [];
            
            // Получаем измененные атрибуты
            $changedAttributes = $deal->getDirty();
            $originalAttributes = $deal->getOriginal();
            
            // Определяем тип действия
            $actionType = 'update';
            $description = "Обновлена сделка #{$deal->id}";
            
            foreach ($changedAttributes as $key => $newValue) {
                if (!in_array($key, ['updated_at'])) {
                    $oldValue = $originalAttributes[$key] ?? null;
                    
                    // Записываем изменение только если значения действительно разные
                    if ($oldValue != $newValue) {
                        $changes[$key] = [
                            'old' => $oldValue,
                            'new' => $newValue
                        ];
                        
                        // Определяем специальные типы изменений
                        if ($key === 'status') {
                            $actionType = 'status_change';
                            $description = "Изменен статус сделки #{$deal->id} с '{$oldValue}' на '{$newValue}'";
                        }
                    }
                }
            }
            
            // Создаем лог только если есть реальные изменения
            if (!empty($changes)) {
                DealChangeLog::create([
                    'deal_id' => $deal->id,
                    'user_id' => $user ? $user->id : null,
                    'user_name' => $user ? $user->name : 'Система',
                    'action_type' => $actionType,
                    'changes' => $changes,
                    'description' => $description,
                    'ip_address' => $request ? $request->ip() : null,
                    'user_agent' => $request ? $request->userAgent() : null,
                ]);
                
                Log::info("Обновлена сделка #{$deal->id}", [
                    'deal_id' => $deal->id,
                    'user_id' => $user ? $user->id : null,
                    'action_type' => $actionType,
                    'changed_fields' => array_keys($changes),
                    'ip_address' => $request ? $request->ip() : null,
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error("Ошибка логирования обновления сделки: " . $e->getMessage(), [
                'deal_id' => $deal->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Обработка события удаления сделки.
     */
    public function deleting(Deal $deal)
    {
        try {
            $user = Auth::user();
            $request = request();
            
            // Сохраняем информацию о сделке перед удалением
            $dealData = $deal->toArray();
            
            $changes = [
                'action' => [
                    'old' => 'существует',
                    'new' => 'удалена'
                ],
                'deal_data_backup' => [
                    'old' => null,
                    'new' => $dealData
                ]
            ];
            
            $description = "Удалена сделка #{$deal->id}" . 
                          ($deal->project_name ? " - {$deal->project_name}" : '') .
                          ($deal->client_name ? " (клиент: {$deal->client_name})" : '');
            
            DealChangeLog::create([
                'deal_id' => $deal->id,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Система',
                'action_type' => 'delete',
                'changes' => $changes,
                'description' => $description,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
            ]);
            
            Log::warning("Удаляется сделка #{$deal->id}", [
                'deal_id' => $deal->id,
                'user_id' => $user ? $user->id : null,
                'deal_name' => $deal->project_name ?? $deal->client_name ?? 'Без названия',
                'ip_address' => $request ? $request->ip() : null,
            ]);
            
        } catch (\Exception $e) {
            Log::error("Ошибка логирования удаления сделки: " . $e->getMessage(), [
                'deal_id' => $deal->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Обработка события после удаления сделки.
     */
    public function deleted(Deal $deal)
    {
        try {
            Log::warning("Сделка #{$deal->id} успешно удалена", [
                'deal_id' => $deal->id
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка логирования после удаления сделки: " . $e->getMessage(), [
                'deal_id' => $deal->id,
                'exception' => $e
            ]);
        }
    }

    /**
     * Обработка события восстановления сделки (если используется soft delete).
     */
    public function restored(Deal $deal)
    {
        try {
            $user = Auth::user();
            
            $changes = [
                'action' => [
                    'old' => 'удалена',
                    'new' => 'восстановлена'
                ]
            ];
            
            DealChangeLog::create([
                'deal_id' => $deal->id,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Система',
                'changes' => $changes,
            ]);
            
            Log::info("Восстановлена сделка #{$deal->id}", [
                'deal_id' => $deal->id,
                'user_id' => $user ? $user->id : null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Ошибка логирования восстановления сделки: " . $e->getMessage(), [
                'deal_id' => $deal->id,
                'exception' => $e
            ]);
        }
    }
}
