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
        Schema::create('brief_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('brief_id')
                ->constrained('briefs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('key');
            $table->string('title');
            $table->timestamps();

            $table->unique(['brief_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brief_rooms');
    }
};
