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
        Schema::table('brief_answers', function (Blueprint $table) {
            $table->unique(['brief_id', 'question_key', 'room_id'], 'brief_answers_unique_brief_question_room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brief_answers', function (Blueprint $table) {
           
        });
    }
};
