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
        Schema::create('commercials', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->text('description');
            $table->string('status')->default(\App\Enums\Briefs\BriefStatus::DRAFT->value);

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id_before_deletion')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            $table->string('article', 15)->nullable();
            $table->text('zones')->nullable();
            $table->string('total_area')->nullable();
            $table->string('projected_area')->nullable();
            $table->text('question_1_2')->nullable();

            $table->integer('current_page')->default(1);

            $table->json('documents')->nullable();
            $table->json('preferences')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('zone_budgets', 1000)->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercials');
    }
};
