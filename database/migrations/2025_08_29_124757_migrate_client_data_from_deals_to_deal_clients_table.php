<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Deal;
use App\Models\DealClient;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Переносим данные клиентов из таблицы deals в таблицу deal_clients
        
        $deals = Deal::select([
            'id',
            'client_name',
            'client_phone', 
            'client_email',
            'client_city',
            'client_timezone',
            'client_info',
            'client_account_link'
        ])->whereNotNull('client_name')
          ->orWhereNotNull('client_phone')
          ->get();

        echo "Найдено {$deals->count()} сделок с данными клиентов для миграции\n";

        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($deals as $deal) {
            // Проверяем, что есть хотя бы имя или телефон
            if (empty($deal->client_name) && empty($deal->client_phone)) {
                $skippedCount++;
                continue;
            }

            // Проверяем, не существует ли уже запись для этой сделки
            $existingClient = DealClient::where('deal_id', $deal->id)->first();
            if ($existingClient) {
                echo "Клиент для сделки {$deal->id} уже существует, пропускаем\n";
                $skippedCount++;
                continue;
            }

            try {
                DealClient::create([
                    'deal_id' => $deal->id,
                    'name' => $deal->client_name ?: 'Клиент',
                    'phone' => $deal->client_phone ?: '',
                    'email' => $deal->client_email,
                    'city' => $deal->client_city,
                    'timezone' => $deal->client_timezone,
                    'info' => $deal->client_info,
                    'account_link' => $deal->client_account_link,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $migratedCount++;
                
                if ($migratedCount % 100 === 0) {
                    echo "Перенесено {$migratedCount} записей...\n";
                }
                
            } catch (\Exception $e) {
                echo "Ошибка при переносе данных для сделки {$deal->id}: {$e->getMessage()}\n";
                $skippedCount++;
            }
        }

        echo "Миграция завершена:\n";
        echo "- Перенесено записей: {$migratedCount}\n";
        echo "- Пропущено записей: {$skippedCount}\n";
        echo "- Всего обработано: " . ($migratedCount + $skippedCount) . "\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // При откате удаляем все данные из таблицы deal_clients
        // Исходные данные в таблице deals остаются нетронутыми
        
        echo "Откат миграции: удаление всех записей из таблицы deal_clients\n";
        
        $deletedCount = DealClient::count();
        DealClient::truncate();
        
        echo "Удалено записей: {$deletedCount}\n";
    }
};