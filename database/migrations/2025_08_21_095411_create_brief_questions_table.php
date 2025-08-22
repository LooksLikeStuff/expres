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
        Schema::create('brief_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key')->index();
            $table->text('brief_type');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('input_type')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('format')->nullable();
            $table->string('class')->nullable();
            $table->tinyInteger('page');
            $table->tinyInteger('order');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brief_questions');
    }
};
