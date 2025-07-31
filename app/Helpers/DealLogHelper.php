<?php

namespace App\Helpers;

use App\Models\DealChangeLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DealLogHelper
{
    /**
     * Создать лог действия со сделкой
     *
     * @param int $dealId ID сделки
     * @param string $actionType Тип действия (create, update, delete, status_change)
     * @param array $changes Массив изменений
     * @param string|null $description Описание действия
     * @param int|null $userId ID пользователя (если не передан, берется из Auth)
     * @return DealChangeLog|null
     */
    public static function createLog(
        int $dealId,
        string $actionType,
        array $changes = [],
        ?string $description = null,
        ?int $userId = null
    ): ?DealChangeLog {
        try {
            $user = $userId ? \App\Models\User::find($userId) : Auth::user();
            $request = request();

            $logData = [
                'deal_id' => $dealId,
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Система',
                'action_type' => $actionType,
                'changes' => $changes,
                'description' => $description,
                'ip_address' => $request ? $request->ip() : null,
                'user_agent' => $request ? $request->userAgent() : null,
            ];

            $log = DealChangeLog::create($logData);

            Log::info("Создан лог действия со сделкой", [
                'log_id' => $log->id,
                'deal_id' => $dealId,
                'action_type' => $actionType,
                'user_id' => $user ? $user->id : null,
            ]);

            return $log;

        } catch (\Exception $e) {
            Log::error("Ошибка создания лога действия со сделкой: " . $e->getMessage(), [
                'deal_id' => $dealId,
                'action_type' => $actionType,
                'exception' => $e
            ]);
            return null;
        }
    }

    /**
     * Логировать создание сделки
     *
     * @param \App\Models\Deal $deal
     * @param array $additionalData Дополнительные данные для лога
     * @return DealChangeLog|null
     */
    public static function logDealCreation($deal, array $additionalData = []): ?DealChangeLog
    {
        $changes = [];
        foreach ($deal->getAttributes() as $key => $value) {
            if (!in_array($key, ['id', 'created_at', 'updated_at']) && $value !== null) {
                $changes[$key] = [
                    'old' => null,
                    'new' => $value
                ];
            }
        }

        $changes = array_merge($changes, $additionalData);

        $description = "Создана новая сделка #{$deal->id}";
        if ($deal->project_name) {
            $description .= " - {$deal->project_name}";
        }
        if ($deal->client_name) {
            $description .= " (клиент: {$deal->client_name})";
        }

        return self::createLog($deal->id, 'create', $changes, $description);
    }

    /**
     * Логировать обновление сделки
     *
     * @param \App\Models\Deal $deal
     * @param array $originalData Оригинальные данные до изменения
     * @param array $additionalData Дополнительные данные для лога
     * @return DealChangeLog|null
     */
    public static function logDealUpdate($deal, array $originalData = [], array $additionalData = []): ?DealChangeLog
    {
        $changes = [];
        $actionType = 'update';
        $changedFields = [];

        // Получаем измененные атрибуты
        $changedAttributes = $deal->getDirty();

        foreach ($changedAttributes as $key => $newValue) {
            if (!in_array($key, ['updated_at'])) {
                $oldValue = $originalData[$key] ?? $deal->getOriginal($key);

                if ($oldValue != $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                    $changedFields[] = $key;

                    // Определяем специальные типы изменений
                    if ($key === 'status') {
                        $actionType = 'status_change';
                    }
                }
            }
        }

        if (empty($changes) && empty($additionalData)) {
            return null; // Нет изменений для логирования
        }

        $changes = array_merge($changes, $additionalData);

        $description = "Обновлена сделка #{$deal->id}";
        if ($actionType === 'status_change' && isset($changes['status'])) {
            $description = "Изменен статус сделки #{$deal->id} с '{$changes['status']['old']}' на '{$changes['status']['new']}'";
        } elseif (!empty($changedFields)) {
            $description .= " (изменены поля: " . implode(', ', $changedFields) . ")";
        }

        return self::createLog($deal->id, $actionType, $changes, $description);
    }

    /**
     * Логировать удаление сделки
     *
     * @param \App\Models\Deal $deal
     * @param array $additionalData Дополнительные данные для лога
     * @return DealChangeLog|null
     */
    public static function logDealDeletion($deal, array $additionalData = []): ?DealChangeLog
    {
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

        $changes = array_merge($changes, $additionalData);

        $description = "Удалена сделка #{$deal->id}";
        if ($deal->project_name) {
            $description .= " - {$deal->project_name}";
        }
        if ($deal->client_name) {
            $description .= " (клиент: {$deal->client_name})";
        }

        return self::createLog($deal->id, 'delete', $changes, $description);
    }

    /**
     * Логировать восстановление сделки
     *
     * @param \App\Models\Deal $deal
     * @param array $additionalData Дополнительные данные для лога
     * @return DealChangeLog|null
     */
    public static function logDealRestore($deal, array $additionalData = []): ?DealChangeLog
    {
        $dealData = $deal->toArray();

        $changes = [
            'action' => [
                'old' => 'удалена',
                'new' => 'восстановлена'
            ],
            'deal_data_current' => [
                'old' => null,
                'new' => $dealData
            ]
        ];

        $changes = array_merge($changes, $additionalData);

        $description = "Восстановлена сделка #{$deal->id}";
        if ($deal->name) {
            $description .= " - {$deal->name}";
        }
        if ($deal->client_name) {
            $description .= " (клиент: {$deal->client_name})";
        }

        return self::createLog($deal->id, 'restore', $changes, $description);
    }

    /**
     * Логировать кастомное действие со сделкой
     *
     * @param int $dealId ID сделки
     * @param string $action Описание действия
     * @param array $data Данные действия
     * @param string|null $actionType Тип действия для категоризации
     * @return DealChangeLog|null
     */
    public static function logCustomAction(
        int $dealId,
        string $action,
        array $data = [],
        ?string $actionType = null
    ): ?DealChangeLog {
        $actionType = $actionType ?: 'custom';

        $changes = [
            'custom_action' => [
                'old' => null,
                'new' => $action
            ],
            'action_data' => [
                'old' => null,
                'new' => $data
            ]
        ];

        return self::createLog($dealId, $actionType, $changes, $action);
    }

    /**
     * Получить статистику логов для админ-панели
     *
     * @return array
     */
    public static function getLogStatistics(): array
    {
        try {
            return [
                'total_logs' => DealChangeLog::count(),
                'today_logs' => DealChangeLog::whereDate('created_at', today())->count(),
                'week_logs' => DealChangeLog::where('created_at', '>=', now()->subWeek())->count(),
                'month_logs' => DealChangeLog::where('created_at', '>=', now()->subMonth())->count(),
                'create_actions' => DealChangeLog::where('action_type', 'create')->count(),
                'update_actions' => DealChangeLog::where('action_type', 'update')->count(),
                'delete_actions' => DealChangeLog::where('action_type', 'delete')->count(),
                'restore_actions' => DealChangeLog::where('action_type', 'restore')->count(),
                'status_changes' => DealChangeLog::where('action_type', 'status_change')->count(),
            ];
        } catch (\Exception $e) {
            Log::error("Ошибка получения статистики логов: " . $e->getMessage());
            return [
                'total_logs' => 0,
                'today_logs' => 0,
                'week_logs' => 0,
                'month_logs' => 0,
                'create_actions' => 0,
                'update_actions' => 0,
                'delete_actions' => 0,
                'restore_actions' => 0,
                'status_changes' => 0,
            ];
        }
    }
}
