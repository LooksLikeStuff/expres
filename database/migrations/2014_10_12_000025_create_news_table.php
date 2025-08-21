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
        Schema::create('news', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->date('time')->nullable();
            $table->string('user_img', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->integer('liks');
            $table->string('username', 255)->nullable();
            $table->text('content_txt')->nullable();
            $table->text('content_big_txt'); // NOT NULL
            $table->string('content_url', 255)->nullable();
            $table->string('type', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
