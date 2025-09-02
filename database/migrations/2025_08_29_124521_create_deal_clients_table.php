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
        Schema::create('deal_clients', function (Blueprint $table) {
            $table->id();
            
            // Связь с сделкой
            $table->foreignId('deal_id')
                ->unique()
                ->constrained('deals')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            
            // Основные данные клиента
            $table->string('name', 255);
            $table->string('phone', 255);
            $table->string('email', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('timezone', 100)->nullable();
            $table->text('info')->nullable();
            $table->string('account_link', 255)->nullable();
            
            $table->timestamps();
            
            // Индексы для оптимизации поиска
            $table->index('phone');
            $table->index('email');
            $table->index(['name', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_clients');
    }
};