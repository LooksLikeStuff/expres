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
        Schema::create('deal_users', function (Blueprint $table) {
            $table->foreignId('deal_id')
                ->nullable()
                ->comment('ID сделки')
                ->constrained('deals')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->comment('ID пользователя')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('role')
                ->comment('Роль пользователя в сделке');

            $table->json('deleted_user_data')
                ->nullable()
                ->comment('Данные пользователя на случай удаления аккаунта');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_users');
    }
};
