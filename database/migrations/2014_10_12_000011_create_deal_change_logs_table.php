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
        Schema::create('deal_change_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('deal_id')
                ->comment('ID сделки')
                ->constrained('deals')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->comment('ID пользователя, совершившего действие')
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('user_name')
                ->nullable()
                ->comment('Имя пользователя (на случай удаления аккаунта)');

            $table->string('action_type', 50)
                ->comment('Тип действия: create, update, delete, status_change')
                ->default('update')
                ->index();

            $table->json('changes')
                ->nullable()
                ->comment('JSON с изменениями');

            $table->text('description')
                ->nullable()
                ->comment('Описание изменения');

            $table->string('ip_address', 45)
                ->nullable()
                ->comment('IP адрес пользователя');

            $table->text('user_agent')
                ->nullable()
                ->comment('User Agent браузера');

            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_change_logs');
    }
};
