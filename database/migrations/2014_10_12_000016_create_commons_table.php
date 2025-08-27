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
        Schema::create('commons', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id_before_deletion')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->string('title', 255);
            $table->text('description');
            $table->string('status')->default(\App\Enums\Briefs\BriefStatus::DRAFT);
            $table->string('edit_status', 255)->nullable();

            $table->string('article', 15)->nullable();
            $table->string('price', 10000)->nullable();
            $table->timestamps();
            $table->integer('current_page')->default(1);
            $table->json('documents')->nullable();
            $table->text('rooms')->nullable();
            $table->text('custom_rooms')->nullable();
            $table->text('custom_room_answers')->nullable();
            $table->text('skipped_pages')->nullable();

            // Вопросы с комментариями там, где они есть
            $table->text('question_1_1')->nullable()->comment('Сколько человек будет проживать в квартире');
            $table->text('question_1_2')->nullable()->comment('Есть ли домашние животные и растения');
            $table->text('question_1_3')->nullable()->comment('Есть ли у членов семьи особые увлечения или хобби');
            $table->text('question_1_4')->nullable()->comment('Сколько человек будет проживать в квартире');
            $table->text('question_1_5')->nullable()->comment('Как часто вы встречаете гостей');
            $table->text('question_1_6')->nullable()->comment('Адрес');

            $table->text('question_2_1')->nullable()->comment('Какой стиль Вы хотите видеть в своем интерьере');
            $table->text('question_2_2')->nullable()->comment('Референсы интерьера');
            $table->text('question_2_3')->nullable()->comment('Какую атмосферу вы хотите ощущать');
            $table->text('question_2_4')->nullable()->comment('Предметы обстановки для нового интерьера');
            $table->text('question_2_5')->nullable()->comment('Что не должно быть в интерьере');
            $table->text('question_2_6')->nullable()->comment('Ценовой сегмент ремонта');

            $table->text('question_3_1')->nullable()->comment('Прихожая');
            $table->text('question_3_2')->nullable()->comment('Детская');
            $table->text('question_3_3')->nullable()->comment('Кладовая');
            $table->text('question_3_4')->nullable()->comment('Кухня и гостиная');
            $table->text('question_3_5')->nullable()->comment('Гостевой санузел');
            $table->text('question_3_6')->nullable()->comment('Гостиная');
            $table->text('question_3_7')->nullable()->comment('Рабочее место');
            $table->text('question_3_8')->nullable()->comment('Столовая');
            $table->text('question_3_9')->nullable()->comment('Ванная комната');
            $table->text('question_3_10')->nullable()->comment('Кухня');
            $table->text('question_3_11')->nullable()->comment('Кабинет');
            $table->text('question_3_12')->nullable()->comment('Спальня');
            $table->text('question_3_13')->nullable()->comment('Гардеробная');
            $table->text('question_3_14')->nullable()->comment('Другое');

            $table->text('question_4_1')->nullable()->comment('Напольные покрытия');
            $table->text('question_4_2')->nullable()->comment('Двери');
            $table->text('question_4_3')->nullable()->comment('Отделка стен');
            $table->text('question_4_4')->nullable()->comment('Освещение и электрика');
            $table->text('question_4_5')->nullable()->comment('Потолки');
            $table->text('question_4_6')->nullable()->comment('Дополнительные пожелания');

            $table->text('question_5_1')->nullable()->comment('Пожелания по звукоизоляции');
            $table->text('question_5_2')->nullable()->comment('Теплые полы');
            $table->text('question_5_3')->nullable()->comment('Предпочтения по размещению и типу радиаторов');
            $table->text('question_5_4')->nullable()->comment('Водоснабжение');
            $table->text('question_5_5')->nullable()->comment('Кондиционирование и вентиляция');
            $table->text('question_5_6')->nullable()->comment('Сети');

            $table->json('references')->nullable()->comment('Референсы и документы, загруженные на странице 2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commons');
    }
};
