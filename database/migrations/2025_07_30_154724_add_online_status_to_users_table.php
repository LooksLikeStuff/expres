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
//            $table->boolean('is_online')->default(false)->after('firebase_token');
//            $table->timestamp('last_seen_at')->nullable()->after('is_online');
//
//            $table->index('is_online');
//            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_online']);
            $table->dropIndex(['last_seen_at']);
            $table->dropColumn(['is_online', 'last_seen_at']);
        });
    }
};
