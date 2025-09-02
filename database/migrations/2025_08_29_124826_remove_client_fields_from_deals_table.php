<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * ВНИМАНИЕ: Эта миграция должна запускаться только ПОСЛЕ полного перехода на новую систему
     * и проверки что все клиентские данные корректно работают через таблицу deal_clients
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Удаляем все поля клиента из таблицы deals
            $table->dropColumn([
                'client_name',
                'client_phone',
                'client_email',
                'client_city',
                'client_timezone',
                'client_info',
                'client_account_link',
            ]);
        });
        
        echo "Удалены поля клиента из таблицы deals\n";
        echo "ВАЖНО: Убедитесь что все клиентские данные работают через новую таблицу deal_clients\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Восстанавливаем поля клиента в таблице deals
            $table->string('client_name', 255)->nullable();
            $table->string('client_phone', 255)->nullable();
            $table->string('client_email', 100)->nullable();
            $table->string('client_city', 100)->nullable();
            $table->string('client_timezone', 100)->nullable();
            $table->text('client_info')->nullable();
            $table->string('client_account_link', 255)->nullable();
        });

        echo "Восстановлены поля клиента в таблице deals\n";
        echo "ВНИМАНИЕ: Данные в этих полях будут пустыми, требуется обратная миграция данных\n";
    }
};