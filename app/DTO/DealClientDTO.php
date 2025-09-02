<?php

namespace App\DTO;

use App\Models\DealClient;

class DealClientDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $dealId,
        public readonly string $name,
        public readonly string $phone,
        public readonly ?string $email = null,
        public readonly ?string $city = null,
        public readonly ?string $timezone = null,
        public readonly ?string $info = null,
        public readonly ?string $accountLink = null,
        public readonly ?\DateTime $createdAt = null,
        public readonly ?\DateTime $updatedAt = null,
    ) {
    }

    /**
     * Создать DTO из массива данных
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            dealId: $data['deal_id'] ?? $data['dealId'],
            name: $data['name'],
            phone: $data['phone'],
            email: $data['email'] ?? null,
            city: $data['city'] ?? null,
            timezone: $data['timezone'] ?? null,
            info: $data['info'] ?? null,
            accountLink: $data['account_link'] ?? $data['accountLink'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new \DateTime($data['updated_at']) : null,
        );
    }

    /**
     * Создать DTO из модели DealClient
     */
    public static function fromModel(DealClient $client): self
    {
        return new self(
            id: $client->id,
            dealId: $client->deal_id,
            name: $client->name,
            phone: $client->phone,
            email: $client->email,
            city: $client->city,
            timezone: $client->timezone,
            info: $client->info,
            accountLink: $client->account_link,
            createdAt: $client->created_at,
            updatedAt: $client->updated_at,
        );
    }

    /**
     * Создать DTO из старых полей сделки для миграции
     */
    public static function fromDealFields(array $dealData): self
    {
        return new self(
            id: null,
            dealId: $dealData['deal_id'] ?? $dealData['id'],
            name: $dealData['client_name'] ?? 'Клиент',
            phone: $dealData['client_phone'] ?? '',
            email: $dealData['client_email'] ?? null,
            city: $dealData['client_city'] ?? null,
            timezone: $dealData['client_timezone'] ?? null,
            info: $dealData['client_info'] ?? null,
            accountLink: $dealData['client_account_link'] ?? null,
        );
    }

    /**
     * Преобразовать в массив для сохранения в базе
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'deal_id' => $this->dealId,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'timezone' => $this->timezone,
            'info' => $this->info,
            'account_link' => $this->accountLink,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Преобразовать в массив для API ответа
     */
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'dealId' => $this->dealId,
            'name' => $this->name,
            'phone' => $this->getFormattedPhone(),
            'email' => $this->email,
            'city' => $this->city,
            'timezone' => $this->timezone,
            'info' => $this->info,
            'accountLink' => $this->accountLink,
            'locationInfo' => $this->getLocationInfo(),
            'hasFullContactInfo' => $this->hasFullContactInfo(),
            'shortName' => $this->getShortName(),
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Получить отформатированный номер телефона
     */
    public function getFormattedPhone(): string
    {
        // Убираем все символы кроме цифр
        $cleaned = preg_replace('/[^0-9]/', '', $this->phone);
        
        // Если номер начинается с 8, заменяем на +7
        if (str_starts_with($cleaned, '8') && strlen($cleaned) === 11) {
            $cleaned = '7' . substr($cleaned, 1);
        }
        
        // Если номер начинается с 7 и содержит 11 цифр
        if (str_starts_with($cleaned, '7') && strlen($cleaned) === 11) {
            return '+7 (' . substr($cleaned, 1, 3) . ') ' . 
                   substr($cleaned, 4, 3) . '-' . 
                   substr($cleaned, 7, 2) . '-' . 
                   substr($cleaned, 9, 2);
        }
        
        // Возвращаем исходный номер, если не удалось отформатировать
        return $this->phone;
    }

    /**
     * Получить короткое имя (первое слово)
     */
    public function getShortName(): string
    {
        return explode(' ', trim($this->name))[0];
    }

    /**
     * Проверить, есть ли полная контактная информация
     */
    public function hasFullContactInfo(): bool
    {
        return !empty($this->name) && !empty($this->phone) && !empty($this->email);
    }

    /**
     * Получить информацию о местоположении
     */
    public function getLocationInfo(): ?string
    {
        $location = [];
        
        if ($this->city) {
            $location[] = $this->city;
        }
        
        if ($this->timezone) {
            $location[] = $this->timezone;
        }
        
        return !empty($location) ? implode(', ', $location) : null;
    }

    /**
     * Валидация данных
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->name)) {
            $errors['name'] = 'Имя клиента обязательно';
        }

        if (empty($this->phone)) {
            $errors['phone'] = 'Телефон клиента обязателен';
        } elseif (!$this->isValidPhone($this->phone)) {
            $errors['phone'] = 'Некорректный формат телефона';
        }

        if ($this->email && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат email';
        }

        return $errors;
    }

    /**
     * Проверка корректности номера телефона
     */
    private function isValidPhone(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        
        // Российский номер должен содержать 10-11 цифр
        return strlen($cleaned) >= 10 && strlen($cleaned) <= 11;
    }
}

