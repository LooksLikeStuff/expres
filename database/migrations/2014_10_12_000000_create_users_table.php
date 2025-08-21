<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('status', 255)->nullable();
            $table->string('email', 255)->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('phone', 255)->nullable()->index();
            $table->string('temp_phone', 191)->nullable();
            $table->string('cod', 255)->nullable();
            $table->string('avatar_url', 1000)->nullable();
            $table->string('avatar_yandex_path', 191)->nullable();

            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('verification_code', 255)->nullable();
            $table->timestamp('verification_code_expires_at')->nullable();

            $table->string('link', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('contract_number', 255)->nullable();
            $table->text('comment')->nullable();
            $table->string('portfolio_link', 255)->nullable();
            $table->string('experience', 255)->nullable();
            $table->string('rating', 255)->nullable();
            $table->integer('active_projects_count')->default(0);


            $table->timestamp('last_seen_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
