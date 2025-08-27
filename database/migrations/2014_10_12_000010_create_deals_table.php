<?php

use App\Enums\DealStatus;
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
        Schema::create('deals', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('common_id')->nullable();
            $table->unsignedBigInteger('commercial_id')->nullable();

            $table->foreignId('user_id')
                ->index()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('coordinator_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('office_partner_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();


            $table->foreignId('architect_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('designer_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('visualizer_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnUpdate()
                ->restrictOnDelete();

            $table->string('client_name', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('client_phone', 255)->nullable();
            $table->decimal('total_sum', 25, 0)->nullable();
            $table->decimal('measuring_cost', 15, 2)->nullable();
            $table->decimal('project_budget', 15, 2)->nullable();
            $table->string('status')->default(DealStatus::IN_PROGRESS->value);
            $table->string('registration_link', 100)->nullable();
            $table->timestamp('registration_link_expiry')->nullable();



            $table->string('registration_token', 32)->nullable();
            $table->timestamp('registration_token_expiry')->nullable();
            $table->string('avatar_path', 100)->default('icon/deal_default.jpg')->nullable();
            $table->string('link', 100)->nullable();

            $table->date('created_date')->nullable();

            $table->string('client_city', 100)->nullable();
            $table->string('client_email', 100)->nullable();
            $table->text('client_info')->nullable();
            $table->text('execution_comment')->nullable();
            $table->text('comment')->nullable();
            $table->json('documents')->nullable();
            $table->string('project_number', 100)->nullable();
            $table->string('order_stage', 100)->nullable();
            $table->string('price_service_option', 100)->nullable();
            $table->string('rooms_count_pricing', 100)->nullable();
            $table->text('execution_order_comment')->nullable();
            $table->string('execution_order_file', 100)->nullable();
            $table->string('client_timezone', 100)->nullable();
            $table->string('client_account_link', 100)->nullable();
            $table->string('chat_link', 100)->nullable();
            $table->text('measurement_comments')->nullable();
            $table->string('measurements_file', 100)->nullable();
            $table->text('brief')->nullable();
            $table->date('start_date')->nullable();
            $table->string('project_duration', 100)->nullable();
            $table->date('project_end_date')->nullable();

            $table->string('final_floorplan', 100)->nullable();


            $table->string('final_collage', 100)->nullable();
            $table->text('visualization_link')->nullable();
            $table->string('final_project_file', 100)->nullable();
            $table->string('work_act', 100)->nullable();

            $table->decimal('client_project_rating', 3, 1)->nullable();
            $table->decimal('architect_rating_client', 3, 1)->nullable();
            $table->decimal('architect_rating_partner', 3, 1)->nullable();
            $table->decimal('architect_rating_coordinator', 3, 1)->nullable();
            $table->decimal('designer_rating_client', 3, 1)->nullable();
            $table->decimal('designer_rating_partner', 3, 1)->nullable();
            $table->decimal('designer_rating_coordinator', 3, 1)->nullable();
            $table->decimal('visualizer_rating_client', 3, 1)->nullable();
            $table->decimal('visualizer_rating_partner', 3, 1)->nullable();
            $table->decimal('visualizer_rating_coordinator', 3, 1)->nullable();
            $table->decimal('coordinator_rating_client', 3, 1)->nullable();
            $table->decimal('coordinator_rating_partner', 3, 1)->nullable();

            $table->string('chat_screenshot', 100)->nullable();
            $table->text('coordinator_comment')->nullable();
            $table->string('archicad_file', 255)->nullable();
            $table->string('contract_number', 255)->nullable();
            $table->string('contract_attachment', 255)->nullable();
            $table->text('deal_note')->nullable();
            $table->string('object_type', 255)->nullable();
            $table->string('package', 255)->nullable();
            $table->string('completion_responsible', 255)->nullable();
            $table->tinyInteger('office_equipment')->default(0);
            $table->string('stage', 255)->nullable();
            $table->string('coordinator_score', 255)->nullable();
            $table->tinyInteger('has_animals')->nullable();
            $table->tinyInteger('has_plants')->nullable();
            $table->string('object_style', 255)->nullable();
            $table->string('measurements', 255)->nullable();
            $table->integer('rooms_count')->nullable();
            $table->date('deal_end_date')->nullable();
            $table->date('payment_date')->nullable();

            // Длинные блоки URL и пути
            $table->string('yandex_url_execution_order_file', 255)->nullable();
            $table->string('yandex_url_measurements_file', 255)->nullable();
            $table->string('yandex_url_final_floorplan', 255)->nullable();
            $table->string('yandex_url_final_collage', 255)->nullable();
            $table->string('yandex_url_final_project_file', 255)->nullable();
            $table->string('yandex_url_work_act', 255)->nullable();
            $table->string('yandex_url_archicad_file', 255)->nullable();
            $table->string('yandex_url_contract_attachment', 255)->nullable();

            $table->string('yandex_disk_path_execution_order_file', 255)->nullable();
            $table->string('yandex_disk_path_measurements_file', 255)->nullable();
            $table->string('yandex_disk_path_final_floorplan', 255)->nullable();
            $table->string('yandex_disk_path_final_collage', 255)->nullable();
            $table->string('yandex_disk_path_final_project_file', 255)->nullable();
            $table->string('yandex_disk_path_work_act', 255)->nullable();
            $table->string('yandex_disk_path_archicad_file', 255)->nullable();
            $table->string('yandex_disk_path_contract_attachment', 255)->nullable();

            $table->string('original_name_execution_order_file', 255)->nullable();
            $table->string('original_name_measurements_file', 255)->nullable();
            $table->string('original_name_final_floorplan', 255)->nullable();
            $table->string('original_name_final_collage', 255)->nullable();
            $table->string('original_name_final_project_file', 255)->nullable();
            $table->string('original_name_work_act', 255)->nullable();
            $table->string('original_name_archicad_file', 255)->nullable();
            $table->string('original_name_contract_attachment', 255)->nullable();

            // План финальный
            $table->string('plan_final', 255)->nullable();
            $table->string('yandex_disk_path_plan_final', 255)->nullable();
            $table->string('yandex_url_plan_final', 255)->nullable();
            $table->string('original_name_plan_final', 255)->nullable();

            // Скриншоты
            $table->string('yandex_url_chat_screenshot', 500)->nullable();
            $table->string('yandex_disk_path_chat_screenshot', 500)->nullable();
            $table->string('original_name_chat_screenshot', 255)->nullable();


            $table->foreignId('deleted_coordinator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('deleted_architect_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('deleted_designer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('deleted_visualizer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->foreignId('deleted_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->restrictOnUpdate();

            $table->string('deleted_user_name', 255)->nullable();
            $table->string('deleted_user_email', 255)->nullable();
            $table->string('deleted_user_phone', 255)->nullable();


            $table->boolean('has_brief')->default(0);
            $table->timestamp('brief_attached_at')->nullable();

            $table->text('screenshot_work_1')->nullable()->comment('Первый скриншот работы над проектом');
            $table->text('yandex_url_screenshot_work_1')->nullable()->comment('URL первого скриншота работы на Яндекс.Диске');
            $table->string('original_name_screenshot_work_1')->nullable()->comment('Оригинальное имя первого скриншота работы');
            $table->text('yandex_disk_path_screenshot_work_1')->nullable()->comment('Путь первого скриншота работы на Яндекс.Диске');

            $table->text('screenshot_work_2')->nullable()->comment('Второй скриншот работы над проектом');
            $table->text('yandex_url_screenshot_work_2')->nullable()->comment('URL второго скриншота работы на Яндекс.Диске');
            $table->string('original_name_screenshot_work_2')->nullable()->comment('Оригинальное имя второго скриншота работы');
            $table->text('yandex_disk_path_screenshot_work_2')->nullable()->comment('Путь второго скриншота работы на Яндекс.Диске');

            $table->text('screenshot_work_3')->nullable()->comment('Третий скриншот работы над проектом');
            $table->text('yandex_url_screenshot_work_3')->nullable()->comment('URL третьего скриншота работы на Яндекс.Диске');
            $table->string('original_name_screenshot_work_3')->nullable()->comment('Оригинальное имя третьего скриншота работы');
            $table->text('yandex_disk_path_screenshot_work_3')->nullable()->comment('Путь третьего скриншота работы на Яндекс.Диске');

            // Добавляем поле для скриншота в разделе "Финал проекта" (1 поле)
            $table->text('screenshot_final')->nullable()->comment('Скриншот финального этапа проекта');
            $table->text('yandex_url_screenshot_final')->nullable()->comment('URL скриншота финального этапа на Яндекс.Диске');
            $table->string('original_name_screenshot_final')->nullable()->comment('Оригинальное имя скриншота финального этапа');
            $table->text('yandex_disk_path_screenshot_final')->nullable()->comment('Путь скриншота финального этапа на Яндекс.Диске');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
