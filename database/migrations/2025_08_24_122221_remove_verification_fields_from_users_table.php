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
        Schema::table('users', function (Blueprint $table) {
            // Удаляем поля верификации
            $table->dropColumn(['verification_code', 'verification_code_expires_at']);

            // Удаляем поле last_seen_at (переносим в отдельную таблицу)
            $table->dropColumn('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Возвращаем поля верификации
            $table->string('verification_code', 10)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();

            // Возвращаем поле last_seen_at
            $table->timestamp('last_seen_at')->nullable();
        });
    }
};
