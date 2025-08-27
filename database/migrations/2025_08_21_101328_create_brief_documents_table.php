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
        Schema::create('brief_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('brief_id')
                ->constrained('briefs')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('original_name');

            $table->string('filepath');
            $table->string('mime_type');
            $table->integer('file_size');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brief_documents');
    }
};
