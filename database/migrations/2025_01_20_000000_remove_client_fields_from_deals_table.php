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
        Schema::table('deals', function (Blueprint $table) {
            // Проверяем существование полей перед удалением для безопасности
            if (Schema::hasColumn('deals', 'client_name')) {
                $table->dropColumn('client_name');
            }
            
            if (Schema::hasColumn('deals', 'client_phone')) {
                $table->dropColumn('client_phone');
            }
            
            if (Schema::hasColumn('deals', 'client_email')) {
                $table->dropColumn('client_email');
            }
            
            if (Schema::hasColumn('deals', 'client_city')) {
                $table->dropColumn('client_city');
            }
            
            if (Schema::hasColumn('deals', 'client_timezone')) {
                $table->dropColumn('client_timezone');
            }
            
            if (Schema::hasColumn('deals', 'client_info')) {
                $table->dropColumn('client_info');
            }
            
            if (Schema::hasColumn('deals', 'client_account_link')) {
                $table->dropColumn('client_account_link');
            }
        });
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
