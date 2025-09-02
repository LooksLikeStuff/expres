<?php

namespace App\Services;

use App\DTO\DealClientDTO;
use App\Models\Deal;
use App\Models\DealClient;
use Illuminate\Database\Eloquent\Collection;

class DealClientService
{
    /**
     * Создать или обновить клиента сделки
     */
    public function createOrUpdate(DealClientDTO $clientDTO): DealClient
    {
        // Валидируем данные
        $errors = $clientDTO->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed: ' . implode(', ', $errors));
        }

        // Ищем существующего клиента
        $client = DealClient::where('deal_id', $clientDTO->dealId)->first();

        if ($client) {
            // Обновляем существующего клиента
            $client->update([
                'name' => $clientDTO->name,
                'phone' => $clientDTO->phone,
                'email' => $clientDTO->email,
                'city' => $clientDTO->city,
                'timezone' => $clientDTO->timezone,
                'info' => $clientDTO->info,
                'account_link' => $clientDTO->accountLink,
            ]);
        } else {
            // Создаем нового клиента
            $client = DealClient::create([
                'deal_id' => $clientDTO->dealId,
                'name' => $clientDTO->name,
                'phone' => $clientDTO->phone,
                'email' => $clientDTO->email,
                'city' => $clientDTO->city,
                'timezone' => $clientDTO->timezone,
                'info' => $clientDTO->info,
                'account_link' => $clientDTO->accountLink,
            ]);
        }

        return $client;
    }

    /**
     * Получить клиента по ID сделки
     */
    public function getByDealId(int $dealId): ?DealClient
    {
        return DealClient::where('deal_id', $dealId)->first();
    }

    /**
     * Получить DTO клиента по ID сделки
     */
    public function getDTOByDealId(int $dealId): ?DealClientDTO
    {
        $client = $this->getByDealId($dealId);
        
        return $client ? DealClientDTO::fromModel($client) : null;
    }

    /**
     * Найти клиентов по телефону
     */
    public function findByPhone(string $phone): Collection
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        
        return DealClient::where('phone', 'like', '%' . $normalizedPhone . '%')->get();
    }

    /**
     * Найти клиентов по email
     */
    public function findByEmail(string $email): Collection
    {
        return DealClient::where('email', $email)->get();
    }

    /**
     * Найти клиентов по имени
     */
    public function findByName(string $name): Collection
    {
        return DealClient::where('name', 'like', '%' . $name . '%')->get();
    }

    /**
     * Удалить клиента сделки
     */
    public function delete(int $dealId): bool
    {
        $client = $this->getByDealId($dealId);
        
        if ($client) {
            return $client->delete();
        }
        
        return false;
    }

    /**
     * Мигрировать данные клиента из старых полей Deal
     */
    public function migrateFromDeal(Deal $deal): ?DealClient
    {
        // Проверяем, есть ли уже клиент для этой сделки
        $existingClient = $this->getByDealId($deal->id);
        if ($existingClient) {
            return $existingClient;
        }

        // Проверяем, есть ли данные для миграции
        if (empty($deal->getRawOriginal('client_name')) && empty($deal->getRawOriginal('client_phone'))) {
            return null;
        }

        // Создаем DTO из старых полей
        $clientDTO = DealClientDTO::fromDealFields([
            'deal_id' => $deal->id,
            'client_name' => $deal->getRawOriginal('client_name'),
            'client_phone' => $deal->getRawOriginal('client_phone'),
            'client_email' => $deal->getRawOriginal('client_email'),
            'client_city' => $deal->getRawOriginal('client_city'),
            'client_timezone' => $deal->getRawOriginal('client_timezone'),
            'client_info' => $deal->getRawOriginal('client_info'),
            'client_account_link' => $deal->getRawOriginal('client_account_link'),
        ]);

        // Создаем клиента если данные валидны
        try {
            return $this->createOrUpdate($clientDTO);
        } catch (\InvalidArgumentException $e) {
            // Логируем ошибку валидации, но не бросаем исключение
            \Log::warning("Failed to migrate client data for deal {$deal->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Получить статистику по клиентам
     */
    public function getStatistics(): array
    {
        $total = DealClient::count();
        $withEmail = DealClient::whereNotNull('email')->count();
        $withCity = DealClient::whereNotNull('city')->count();
        $withTimezone = DealClient::whereNotNull('timezone')->count();
        $withInfo = DealClient::whereNotNull('info')->count();
        
        return [
            'total_clients' => $total,
            'clients_with_email' => $withEmail,
            'clients_with_city' => $withCity,
            'clients_with_timezone' => $withTimezone,
            'clients_with_info' => $withInfo,
            'completion_rate' => [
                'email' => $total > 0 ? round(($withEmail / $total) * 100, 2) : 0,
                'city' => $total > 0 ? round(($withCity / $total) * 100, 2) : 0,
                'timezone' => $total > 0 ? round(($withTimezone / $total) * 100, 2) : 0,
                'info' => $total > 0 ? round(($withInfo / $total) * 100, 2) : 0,
            ]
        ];
    }

    /**
     * Синхронизировать клиентские данные между старой и новой структурой
     * (временный метод для переходного периода)
     */
    public function syncWithDeal(Deal $deal): void
    {
        $client = $this->getByDealId($deal->id);
        
        if ($client) {
            // Обновляем старые поля в Deal из новой структуры
            $deal->updateQuietly([
                'client_name' => $client->name,
                'client_phone' => $client->phone,
                'client_email' => $client->email,
                'client_city' => $client->city,
                'client_timezone' => $client->timezone,
                'client_info' => $client->info,
                'client_account_link' => $client->account_link,
            ]);
        }
    }
}

