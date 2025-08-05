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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id('id');

            $table->foreignId('message_id')
                ->constrained('messages')
                ->onDelete('cascade');

            $table->string('path');
            $table->string('original_name', 128);
            $table->string('mime_type');

            $table->bigInteger('filesize');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
