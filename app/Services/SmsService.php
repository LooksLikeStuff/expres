<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $apiUrl;
    
    public function __construct()
    {
        // Получаем API-ключ из конфигурации
        $this->apiKey = config('services.smsru.api_key');
        $this->apiUrl = 'https://sms.ru/sms/send';
    }
    
    /**
     * Отправка SMS на указанный номер
     *
     * @param string $phone Номер телефона в формате 79XXXXXXXXX
     * @param string $message Текст сообщения
     * @return bool Результат отправки
     */
    public function sendSms(string $phone, string $message): bool
    {
        // Проверяем наличие API-ключа
        if (empty($this->apiKey)) {
            Log::error('SMS.RU API key is not configured.');
            return false;
        }
        
        // Форматируем номер телефона (убираем все нецифровые символы)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Проверяем формат номера
        if (!preg_match('/^7[0-9]{10}$/', $phone)) {
            Log::error("Invalid phone number format: {$phone}");
            return false;
        }
        
        try {
            // Отправка запроса к API SMS.RU
            $response = Http::get($this->apiUrl, [
                'api_id' => $this->apiKey,
                'to' => $phone,
                'msg' => $message,
                'json' => 1
            ]);
            
            $result = $response->json();
            
            // Проверяем результат отправки
            if ($response->successful() && isset($result['status']) && $result['status'] == 'OK') {
                Log::info("SMS successfully sent to {$phone}");
                return true;
            } else {
                Log::error("Failed to send SMS to {$phone}: " . json_encode($result));
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending SMS to {$phone}: " . $e->getMessage());
            return false;
        }
    }
}
