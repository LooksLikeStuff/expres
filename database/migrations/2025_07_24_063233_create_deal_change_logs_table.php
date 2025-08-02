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
        // Проверяем, существует ли уже таблица
        if (!Schema::hasTable('deal_change_logs')) {
            Schema::create('deal_change_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('deal_id')->comment('ID сделки');
                $table->unsignedBigInteger('user_id')->nullable()->comment('ID пользователя, совершившего действие');
                $table->string('user_name')->nullable()->comment('Имя пользователя (на случай удаления аккаунта)');
                $table->string('action_type', 50)->default('update')->comment('Тип действия: create, update, delete, status_change');
                $table->json('changes')->nullable()->comment('JSON с изменениями');
                $table->text('description')->nullable()->comment('Описание изменения');
                $table->ipAddress('ip_address')->nullable()->comment('IP адрес пользователя');
                $table->text('user_agent')->nullable()->comment('User Agent браузера');
                $table->timestamps();

                // Индексы для быстрого поиска
                $table->index(['deal_id', 'created_at']);
                $table->index(['user_id', 'created_at']);
                $table->index(['action_type', 'created_at']);
                $table->index('created_at');

                // Внешний ключ на пользователя (с возможностью NULL если пользователь удален)
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_change_logs');
    }
};
