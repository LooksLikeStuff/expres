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
        Schema::table('deal_change_logs', function (Blueprint $table) {
            // Добавляем индекс на action_type для ускорения фильтрации и статистики
           // $table->index('action_type', 'idx_deal_change_logs_action_type');

            // Добавляем составной индекс для лучшей производительности фильтрации по дате и типу действия
           // $table->index(['created_at', 'action_type'], 'idx_deal_change_logs_created_action');

            // Добавляем индекс на deal_id для быстрого поиска логов по сделке
           // $table->index('deal_id', 'idx_deal_change_logs_deal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_change_logs', function (Blueprint $table) {
            $table->dropIndex('idx_deal_change_logs_action_type');
            $table->dropIndex('idx_deal_change_logs_created_action');
            $table->dropIndex('idx_deal_change_logs_deal_id');
        });
    }
};
