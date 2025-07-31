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
            // Добавляем поля для работы с брифами, если они не существуют
            if (!Schema::hasColumn('deals', 'has_brief')) {
                $table->boolean('has_brief')->default(false)->comment('Флаг наличия привязанного брифа');
            }
            
            if (!Schema::hasColumn('deals', 'brief_attached_at')) {
                $table->timestamp('brief_attached_at')->nullable()->comment('Дата и время привязки брифа');
            }
            
            if (!Schema::hasColumn('deals', 'common_id')) {
                $table->unsignedBigInteger('common_id')->nullable()->comment('ID общего брифа');
            }
            
            if (!Schema::hasColumn('deals', 'commercial_id')) {
                $table->unsignedBigInteger('commercial_id')->nullable()->comment('ID коммерческого брифа');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            // Удаляем добавленные поля
            $table->dropColumn(['has_brief', 'brief_attached_at', 'common_id', 'commercial_id']);
        });
    }
};
