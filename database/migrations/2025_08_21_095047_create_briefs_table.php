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
        Schema::create('briefs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('deal_id')
                ->nullable()
                ->constrained('deals')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('type', 128);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('article', 128)->nullable();
            $table->text('zones')->nullable();
            $table->decimal('total_area', 6, 2)->nullable();
            $table->integer('price')->nullable();
            $table->json('preferences');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('briefs');
    }
};
