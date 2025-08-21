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
        Schema::create('estimates', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->text('about')->nullable();
            $table->text('info')->nullable();
            $table->string('excel_info', 255)->nullable();
            $table->string('price', 255)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('coefficient', 10, 2)->nullable();
            $table->decimal('extra_charge', 10, 2)->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
