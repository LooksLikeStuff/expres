<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Для SQLite делаем несколько отдельных операций
        $columnsToRemove = [
            'client_name',
            'client_phone', 
            'client_email',
            'client_city',
            'client_timezone',
            'client_info',
            'client_account_link'
        ];

        foreach ($columnsToRemove as $column) {
            if (Schema::hasColumn('deals', $column)) {
                Schema::table('deals', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Восстанавливаем клиентские поля при откате миграции
            $table->string('client_name')->nullable();
            $table->string('client_phone', 20)->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_city')->nullable();
            $table->string('client_timezone')->nullable();
            $table->text('client_info')->nullable();
            $table->string('client_account_link')->nullable();
        });
    }
};
