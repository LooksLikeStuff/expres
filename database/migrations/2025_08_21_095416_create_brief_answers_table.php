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

        Schema::create('brief_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('brief_id')
                ->constrained('briefs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained('brief_rooms')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('question_key')->index();
            $table->text('answer_text')->nullable();
            $table->json('answer_json')->nullable();
            $table->timestamps();

            $table->unique(['brief_id', 'question_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brief_answers');
    }
};
