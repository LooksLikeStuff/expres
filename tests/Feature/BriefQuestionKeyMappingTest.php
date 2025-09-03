<?php

namespace Tests\Feature;

use App\Enums\Briefs\BriefStatus;
use App\Enums\Briefs\BriefType;
use App\Models\Brief;
use App\Models\BriefAnswer;
use App\Models\BriefQuestion;
use App\Services\BriefQuestionKeyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BriefQuestionKeyMappingTest extends TestCase
{
    use RefreshDatabase;

    private BriefQuestionKeyService $keyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keyService = new BriefQuestionKeyService();
    }

    /** @test */
    public function it_finds_actual_key_for_existing_question()
    {
        // Создаем вопрос
        $question = BriefQuestion::create([
            'key' => 'question_1_1',
            'brief_type' => BriefType::COMMON,
            'title' => 'Test Question',
            'subtitle' => 'Test Subtitle',
            'input_type' => 'text',
            'page' => 1,
            'order' => 1,
            'is_active' => true,
        ]);

        $actualKey = $this->keyService->getActualQuestionKey('question_1_1', BriefType::COMMON);

        $this->assertEquals('question_1_1', $actualKey);
    }

    /** @test */
    public function it_finds_actual_key_by_page_and_order_when_key_changed()
    {
        // Создаем вопрос с новым ключом
        $question = BriefQuestion::create([
            'key' => 'new_question_key_1_1',
            'brief_type' => BriefType::COMMON,
            'title' => 'Test Question',
            'subtitle' => 'Test Subtitle',
            'input_type' => 'text',
            'page' => 1,
            'order' => 1,
            'is_active' => true,
        ]);

        // Пытаемся найти по старому ключу
        $actualKey = $this->keyService->getActualQuestionKey('question_1_1', BriefType::COMMON);

        $this->assertEquals('new_question_key_1_1', $actualKey);
    }

    /** @test */
    public function it_handles_commercial_brief_keys()
    {
        // Создаем коммерческий вопрос
        $question = BriefQuestion::create([
            'key' => 'commercial_zone_question_1',
            'brief_type' => BriefType::COMMERCIAL,
            'title' => 'Commercial Question',
            'subtitle' => 'Test Subtitle',
            'input_type' => 'text',
            'page' => 1,
            'order' => 1,
            'is_active' => true,
        ]);

        // Пытаемся найти по старому формату
        $actualKey = $this->keyService->getActualQuestionKey('question_1', BriefType::COMMERCIAL);

        $this->assertEquals('commercial_zone_question_1', $actualKey);
    }

    /** @test */
    public function brief_answer_automatically_updates_question_key()
    {
        // Создаем бриф
        $brief = Brief::create([
            'user_id' => 1,
            'type' => BriefType::COMMON,
            'title' => 'Test Brief',
            'status' => BriefStatus::IN_PROGRESS,
        ]);

        // Создаем вопрос с новым ключом
        $question = BriefQuestion::create([
            'key' => 'updated_question_2_3',
            'brief_type' => BriefType::COMMON,
            'title' => 'Test Question',
            'subtitle' => 'Test Subtitle',
            'input_type' => 'text',
            'page' => 2,
            'order' => 3,
            'is_active' => true,
        ]);

        // Создаем ответ со старым ключом
        $answer = BriefAnswer::create([
            'brief_id' => $brief->id,
            'question_key' => 'question_2_3', // Старый формат
            'answer_text' => 'Test Answer',
        ]);

        // Проверяем, что ключ автоматически обновился
        $this->assertEquals('updated_question_2_3', $answer->fresh()->question_key);
    }

    /** @test */
    public function brief_save_answer_method_validates_key()
    {
        // Создаем бриф
        $brief = Brief::create([
            'user_id' => 1,
            'type' => BriefType::COMMON,
            'title' => 'Test Brief',
            'status' => BriefStatus::IN_PROGRESS,
        ]);

        // Создаем вопрос с новым ключом
        $question = BriefQuestion::create([
            'key' => 'modern_question_1_1',
            'brief_type' => BriefType::COMMON,
            'title' => 'Test Question',
            'subtitle' => 'Test Subtitle',
            'input_type' => 'text',
            'page' => 1,
            'order' => 1,
            'is_active' => true,
        ]);

        // Сохраняем ответ через метод модели со старым ключом
        $answer = $brief->saveAnswer('question_1_1', 'Test Answer');

        // Проверяем, что ключ автоматически обновился
        $this->assertEquals('modern_question_1_1', $answer->question_key);
        $this->assertEquals('Test Answer', $answer->answer_text);
    }

    /** @test */
    public function it_returns_original_key_when_no_mapping_found()
    {
        // Пытаемся найти несуществующий вопрос
        $actualKey = $this->keyService->getActualQuestionKey('nonexistent_question', BriefType::COMMON);

        // Должен вернуть исходный ключ
        $this->assertEquals('nonexistent_question', $actualKey);
    }
}