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
        Schema::create('deal_files', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('deal_id')
                ->comment('ID сделки')
                ->constrained('deals')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by_id')
                ->comment('ID пользователя, загрузившего файл')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('file_name')
                ->comment('Имя файла на сервере');

            $table->string('original_name')
                ->comment('Оригинальное имя файла при загрузке');

            $table->text('file_path')
                ->comment('Путь к файлу');

            $table->text('description')
                ->nullable()
                ->comment('Описание файла');


            $table->unsignedBigInteger('file_size')
                ->nullable()
                ->default(0)
                ->comment('Размер файла в байтах');

            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_files');
    }
};
