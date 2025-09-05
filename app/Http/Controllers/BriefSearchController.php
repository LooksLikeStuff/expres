<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Deal;
use App\Models\Common;
use App\Models\Commercial;
use Illuminate\Support\Facades\Schema;
use Exception;

class BriefSearchController extends Controller
{    /**
     * Поиск брифов по номеру телефона клиента
     *
     * @param Request $request
     * @param int $deal ID сделки из маршрута
     * @return \Illuminate\Http\JsonResponse
     */
    public function findBriefsByClientPhone(Request $request, $deal)
    {
        try {
            $clientPhone = $request->input('client_phone');
            $dealId = $deal; // Используем ID из URL маршрута

            Log::info('Поиск брифов по номеру телефона', [
                'phone' => $clientPhone,
                'deal_id' => $dealId
            ]);

            if (empty($clientPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Номер телефона клиента не указан'
                ]);
            }

            // Нормализация номера телефона (удаление всех нецифровых символов)
            //$normalizedPhone = preg_replace('/[^0-9]/', '', $clientPhone);

            $normalizedPhone = normalizePhone($clientPhone);

            Log::info('Нормализованный телефон: ' . $normalizedPhone);

            // Поиск пользователей по номеру телефона
            $user = User::where('phone', $normalizedPhone)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Пользователь с указанным номером телефона не найден'
                ]);
            }


            // Проверяем, привязан ли уже бриф к сделке (новая и старая система)
            $deal = Deal::with('client', 'brief')->find($dealId);
            $hasAttachedBrief = false;
            $attachedBriefType = null;

            if ($deal) {
                // Проверяем новую систему брифов
                if ($deal->brief) {
                    $hasAttachedBrief = true;
                    $activeBrief = $deal->brief;
                    $attachedBriefType = $activeBrief ? $activeBrief->type->value : 'unified';
                }
            }

            // НОВАЯ СИСТЕМА: Ищем унифицированные брифы
            $briefs = collect([]);

            try {
                if (class_exists(\App\Models\Brief::class)) {
                    $briefs = \App\Models\Brief::where('user_id', $user->id)
                        ->whereIn('status', [\App\Enums\Briefs\BriefStatus::COMPLETED])
                        ->where(function($query) use ($dealId) {
                            $query->whereNull('deal_id')
                                  ->orWhere('deal_id', $dealId);
                        })
                        ->with('user')
                        ->get()
                        ->map(function($brief) use ($dealId) {
                            return [
                                'id' => $brief->id,
                                'title' => $brief->title ?? ('Бриф #' . $brief->id),
                                'type' => $brief->type->value,
                                'user_name' => $brief->user->name ?? 'Неизвестный пользователь',
                                'created_at' => \Carbon\Carbon::parse($brief->created_at)->format('d.m.Y H:i'),
                                'already_linked' => $brief->deal_id == $dealId,
                                'status' => $brief->status->value ?? 'Не указан',
                                'can_attach' => !$brief->deal_id || $brief->deal_id == $dealId,
                                'system' => 'unified'
                            ];
                        });
                }
            } catch (Exception $e) {
                Log::error('Ошибка при поиске общих брифов: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
            }


            $totalBriefs = $briefs->count();

            return response()->json([
                'success' => true,
                'user' =>  [
                    'id' => $user?->id,
                    'name' => $user?->name,
                    'email' => $user?->email,
                    'phone' => $user?->phone
                ],
                'briefs' => $briefs,
                'has_attached_brief' => $hasAttachedBrief,
                'attached_brief_type' => $attachedBriefType,
                'total_found' => $totalBriefs,
                'message' => 'Найдено доступных для привязки брифов: ' . $totalBriefs
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка в методе findBriefsByClientPhone: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при поиске брифов: ' . $e->getMessage()
            ], 500);
        }
    }    /**
     * Привязка брифа к сделке
     *
     * @param Request $request
     * @param int $deal ID сделки из маршрута
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkBriefToDeal(Request $request, $deal)
    {
        try {
            $dealId = $deal; // Используем ID из URL маршрута
            $briefId = $request->input('brief_id');

            Log::info('Запрос на привязку брифа', [
                'deal_id' => $dealId,
                'brief_id' => $briefId,
                'all_params' => $request->all()
            ]);

            if (!$dealId || !$briefId) {
                Log::warning('Отсутствуют обязательные параметры для привязки брифа', [
                    'deal_id' => $dealId,
                    'brief_id' => $briefId,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Не указаны обязательные параметры',
                    'missing_params' => [
                        'deal_id' => empty($dealId),
                        'brief_id' => empty($briefId),
                    ]
                ]);
            }

            $deal = Deal::with('dealClient')->find($dealId);

            if (!$deal) {
                Log::warning('Сделка не найдена при привязке брифа', ['deal_id' => $dealId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ]);
            }

            $brief = \App\Models\Brief::find($briefId);

            if (!$brief) {
                Log::warning('Унифицированный бриф не найден', ['brief_id' => $briefId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Бриф не найден'
                ]);
            }

            // Проверяем, можно ли привязать бриф
            if (!$deal->attachBrief($brief)) {
                Log::warning('Не удалось привязать унифицированный бриф', [
                    'deal_id' => $dealId,
                    'brief_id' => $briefId,
                    'already_attached_to' => $brief->deal_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Бриф уже привязан к другой сделке'
                ]);
            }

            Log::info('Унифицированный бриф успешно привязан', [
                'deal_id' => $dealId,
                'brief_id' => $briefId,
                'brief_type' => $brief->type->value
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Бриф успешно привязан к сделке',
                'reload_required' => true,
                'deal' => [
                    'id' => $deal->id,
                    'brief' => $brief,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при привязке брифа: ' . $e->getMessage(), [
                'deal_id' => $deal ?? null,
                'brief_id' => $request->input('brief_id'),
                'brief_type' => $request->input('brief_type'),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при привязке брифа: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
      /**
     * Отвязка брифа от сделки
     *
     * @param Request $request
     * @param int $deal ID сделки из маршрута
     * @return \Illuminate\Http\JsonRespons
       *
       * */
    public function unlinkBriefFromDeal(Request $request, $deal)
    {
        try {
            $dealId = $deal; // Используем ID из URL маршрута
            $briefType = $request->input('brief_type');

            Log::info('Запрос на отвязку брифа', [
                'deal_id' => $dealId,
                'brief_type' => $briefType,
                'all_params' => $request->all()
            ]);

            // Находим сделку
            $deal = Deal::with('dealClient')->find($dealId);

            if (!$deal) {
                Log::warning('Сделка не найдена при отвязке брифа', ['deal_id' => $dealId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Сделка не найдена'
                ]);
            }

            // Проверяем, есть ли брифы, привязанные к сделке
            if (!$deal->common_id && !$deal->commercial_id) {
                Log::warning('К сделке не привязан ни один бриф', ['deal_id' => $dealId]);
                return response()->json([
                    'success' => false,
                    'message' => 'К сделке не привязан ни один бриф'
                ]);
            }

            // Автоматически определяем тип брифа, если не указан
            if (!$briefType) {
                // Проверяем, какой тип брифа привязан к сделке
                if ($deal->common_id) {
                    $briefType = 'common';
                    Log::info('Автоопределен тип брифа: common');
                } elseif ($deal->commercial_id) {
                    $briefType = 'commercial';
                    Log::info('Автоопределен тип брифа: commercial');
                }
            }

            // Проверяем тип брифа
            if (!in_array($briefType, ['common', 'commercial'])) {
                Log::warning('Неверный тип брифа при отвязке', ['brief_type' => $briefType]);
                return response()->json([
                    'success' => false,
                    'message' => 'Неверный тип брифа. Допустимые значения: common, commercial'
                ]);
            }
              // Отвязываем бриф от сделки в зависимости от типа
            try {                if ($briefType === 'common') {
                    // Логируем текущее состояние
                    Log::info('Отвязка общего брифа', [
                        'deal_id' => $deal->id,
                        'common_id_before' => $deal->common_id
                    ]);
                      $deal->common_id = null;

                    // Проверяем наличие поля has_brief перед обновлением
                    $tableColumns = Schema::getColumnListing('deals');
                    if (in_array('has_brief', $tableColumns)) {
                        $deal->has_brief = $deal->commercial_id ? true : false;
                    }
                    if (in_array('brief_attached_at', $tableColumns)) {
                        $deal->brief_attached_at = null;
                    }

                    $deal->save();

                    Log::info('Общий бриф успешно отвязан', [
                        'deal_id' => $deal->id,
                        'has_brief_field_exists' => in_array('has_brief', $tableColumns),
                        'has_brief' => isset($deal->has_brief) ? $deal->has_brief : 'поле отсутствует'
                    ]);

                } elseif ($briefType === 'commercial') {
                    // Логируем текущее состояние
                    Log::info('Отвязка коммерческого брифа', [
                        'deal_id' => $deal->id,
                        'commercial_id_before' => $deal->commercial_id
                    ]);

                    $deal->commercial_id = null;
                      // Проверяем наличие поля has_brief перед обновлением
                    $tableColumns = Schema::getColumnListing('deals');
                    if (in_array('has_brief', $tableColumns)) {
                        $deal->has_brief = $deal->common_id ? true : false;
                        if (!$deal->has_brief && in_array('brief_attached_at', $tableColumns)) {
                            $deal->brief_attached_at = null;
                        }
                    }

                    $deal->save();

                    Log::info('Коммерческий бриф успешно отвязан', [
                        'deal_id' => $deal->id,
                        'has_brief_field_exists' => in_array('has_brief', $tableColumns),
                        'has_brief' => isset($deal->has_brief) ? $deal->has_brief : 'поле отсутствует'
                    ]);                } else {
                    // Отвязываем оба типа брифов для надежности
                    Log::info('Отвязка всех брифов от сделки', [
                        'deal_id' => $deal->id,
                        'common_id_before' => $deal->common_id,
                        'commercial_id_before' => $deal->commercial_id
                    ]);

                    $deal->common_id = null;
                    $deal->commercial_id = null;

                    // Проверяем наличие полей перед обновлением
                    $tableColumns = Schema::getColumnListing('deals');
                    if (in_array('has_brief', $tableColumns)) {
                        $deal->has_brief = false;
                    }
                    if (in_array('brief_attached_at', $tableColumns)) {
                        $deal->brief_attached_at = null;
                    }

                    $deal->save();

                    Log::info('Все брифы успешно отвязаны', [
                        'deal_id' => $deal->id
                    ]);
                }
            } catch (Exception $e) {
                Log::error('Ошибка при сохранении сделки после отвязки брифа', [
                    'deal_id' => $deal->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Ошибка при сохранении сделки: ' . $e->getMessage()
                ], 500);
            }
              return response()->json([
                'success' => true,
                'message' => 'Бриф успешно отвязан от сделки',
                'deal' => [
                    'id' => $deal->id,
                    'common_id' => $deal->common_id,
                    'commercial_id' => $deal->commercial_id,
                    'has_brief' => $deal->has_brief,
                    'brief_attached_at' => $deal->brief_attached_at
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Ошибка при отвязке брифа: ' . $e->getMessage(), [
                'deal_id' => $deal ?? null,
                'brief_type' => $request->input('brief_type'),
                'stack_trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при отвязке брифа: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Безопасное обновление полей брифа в сделке
     * Проверяет наличие полей перед обновлением
     */
    private function safeUpdateBriefFields($deal, $updateData = [])
    {
        try {
            // Получаем список колонок таблицы deals
            $tableColumns = Schema::getColumnListing('deals');

            // Безопасно обновляем только существующие поля
            foreach ($updateData as $field => $value) {
                if (in_array($field, $tableColumns)) {
                    $deal->$field = $value;
                } else {
                    Log::warning("Поле $field не существует в таблице deals, пропускаем обновление");
                }
            }

            $deal->save();
            return true;

        } catch (Exception $e) {
            Log::error('Ошибка при безопасном обновлении полей брифа', [
                'deal_id' => $deal->id,
                'error' => $e->getMessage(),
                'update_data' => $updateData
            ]);
            return false;
        }
    }
}
