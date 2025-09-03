<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DealClientService;
use App\DTO\DealClientDTO;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DealClientController extends Controller
{
    protected DealClientService $dealClientService;

    public function __construct(DealClientService $dealClientService)
    {
        $this->dealClientService = $dealClientService;
    }

    /**
     * Получить данные клиента по ID сделки
     */
    public function show(int $dealId): JsonResponse
    {
        try {
            $client = $this->dealClientService->getByDealId($dealId);

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Данные клиента не найдены для указанной сделки'
                ], 404);
            }

            $clientDTO = DealClientDTO::fromModel($client);

            return response()->json([
                'success' => true,
                'data' => $clientDTO->toApiArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка получения данных клиента', [
                'deal_id' => $dealId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения данных клиента'
            ], 500);
        }
    }

    /**
     * Создать или обновить данные клиента
     */
    public function createOrUpdate(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'deal_id' => 'required|integer|exists:deals,id',
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'email' => 'nullable|email|max:100',
                'city' => 'nullable|string|max:100',
                'timezone' => 'nullable|string|max:100',
                'info' => 'nullable|string',
                'account_link' => 'nullable|string|max:255',
            ]);

            // Проверяем права доступа к сделке
            $deal = Deal::with('dealClient')->findOrFail($validated['deal_id']);
            
            // Здесь можно добавить проверку прав доступа пользователя к сделке
            // $this->authorize('update', $deal);

            $clientDTO = DealClientDTO::fromArray($validated);
            $client = $this->dealClientService->createOrUpdate($clientDTO);

            $responseDTO = DealClientDTO::fromModel($client);

            return response()->json([
                'success' => true,
                'message' => 'Данные клиента успешно сохранены',
                'data' => $responseDTO->toApiArray()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации данных клиента',
                'details' => $e->getMessage()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Ошибка создания/обновления данных клиента', [
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка сохранения данных клиента'
            ], 500);
        }
    }

    /**
     * Удалить данные клиента
     */
    public function destroy(int $dealId): JsonResponse
    {
        try {
            // Проверяем права доступа к сделке
            $deal = Deal::with('dealClient')->findOrFail($dealId);
            
            // Здесь можно добавить проверку прав доступа пользователя к сделке
            // $this->authorize('update', $deal);

            $deleted = $this->dealClientService->delete($dealId);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Данные клиента успешно удалены'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Данные клиента не найдены'
                ], 404);
            }

        } catch (\Exception $e) {
            Log::error('Ошибка удаления данных клиента', [
                'deal_id' => $dealId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка удаления данных клиента'
            ], 500);
        }
    }

    /**
     * Поиск клиентов по различным критериям
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'name' => 'nullable|string',
                'limit' => 'nullable|integer|min:1|max:100'
            ]);

            $limit = $validated['limit'] ?? 10;
            $clients = collect();

            if (!empty($validated['phone'])) {
                $clients = $this->dealClientService->findByPhone($validated['phone']);
            } elseif (!empty($validated['email'])) {
                $clients = $this->dealClientService->findByEmail($validated['email']);
            } elseif (!empty($validated['name'])) {
                $clients = $this->dealClientService->findByName($validated['name']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Необходимо указать хотя бы один критерий поиска'
                ], 422);
            }

            // Ограничиваем количество результатов
            $clients = $clients->take($limit);

            $results = $clients->map(function ($client) {
                $dto = DealClientDTO::fromModel($client);
                return $dto->toApiArray();
            });

            return response()->json([
                'success' => true,
                'data' => $results->values(),
                'count' => $results->count()
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Ошибка поиска клиентов', [
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка поиска клиентов'
            ], 500);
        }
    }

    /**
     * Получить статистику по клиентам
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->dealClientService->getStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Ошибка получения статистики клиентов', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ошибка получения статистики'
            ], 500);
        }
    }
}