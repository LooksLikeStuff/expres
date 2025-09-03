<?php

namespace Tests\Feature;

use App\Enums\Briefs\BriefStatus;
use App\Enums\Briefs\BriefType;
use App\Models\Brief;
use App\Models\BriefAnswer;
use App\Models\BriefQuestion;
use App\Models\BriefRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BriefPdfGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+7900123456',
        ]);
    }

    /** @test */
    public function can_download_pdf_for_common_brief_via_http()
    {
        $this->actingAs($this->user);

        // Создаем общий бриф
        $brief = Brief::factory()->create([
            'user_id' => $this->user->id,
            'type' => BriefType::COMMON,
            'status' => BriefStatus::COMPLETED,
            'title' => 'Тестовый общий бриф',
            'description' => 'Описание тестового брифа',
            'price' => 100000,
            'article' => 'TEST-001',
        ]);

        // Создаем тестовые вопросы
        BriefQuestion::create([
            'brief_type' => BriefType::COMMON,
            'page' => 1,
            'order' => 1,
            'key' => 'question_1_1',
            'title' => 'Сколько человек будет проживать?',
            'subtitle' => 'Укажите количество жильцов',
            'input_type' => 'textarea',
            'placeholder' => 'Пример ответа',
            'format' => 'default',
            'is_active' => true,
        ]);

        // Создаем комнату
        $room = BriefRoom::create([
            'brief_id' => $brief->id,
            'key' => 'room_gostinaya',
            'title' => 'Гостиная',
        ]);

        // Создаем ответ
        BriefAnswer::create([
            'brief_id' => $brief->id,
            'question_key' => 'question_1_1',
            'answer_text' => 'Семья из 3 человек',
        ]);

        // Создаем ответ для комнаты
        BriefAnswer::create([
            'brief_id' => $brief->id,
            'room_id' => $room->id,
            'question_key' => 'room',
            'answer_text' => 'Просторная гостиная с большим диваном',
        ]);

        // Тестируем HTTP запрос
        $response = $this->get(route('briefs.pdf', $brief));

        // Проверяем ответ
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        
        // Проверяем имя файла
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("common_brief_{$brief->id}.pdf", $contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);

        // Проверяем, что контент не пустой
        $this->assertGreaterThan(1000, strlen($response->getContent()));
        
        // Проверяем, что это действительно PDF (начинается с PDF-заголовка)
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /** @test */
    public function can_download_pdf_for_commercial_brief_via_http()
    {
        $this->actingAs($this->user);

        // Создаем коммерческий бриф
        $brief = Brief::factory()->create([
            'user_id' => $this->user->id,
            'type' => BriefType::COMMERCIAL,
            'status' => BriefStatus::COMPLETED,
            'title' => 'Тестовый коммерческий бриф',
            'description' => 'Описание коммерческого брифа',
            'price' => 500000,
            'article' => 'TEST-COMM-001',
            'zones' => json_encode([
                [
                    'name' => 'Офисная зона',
                    'description' => 'Рабочее пространство',
                    'total_area' => 50,
                    'projected_area' => 45,
                ]
            ]),
        ]);

        // Создаем вопрос для коммерческого брифа
        BriefQuestion::create([
            'brief_type' => BriefType::COMMERCIAL,
            'page' => 1,
            'order' => 1,
            'key' => 'zone_question_1',
            'title' => 'Функционал зоны',
            'subtitle' => 'Опишите назначение зоны',
            'input_type' => 'textarea',
            'placeholder' => 'Пример функционала',
            'format' => 'default',
            'is_active' => true,
        ]);

        // Создаем зону как комнату
        $zone = BriefRoom::create([
            'brief_id' => $brief->id,
            'key' => 'zone_office',
            'title' => 'Офисная зона',
        ]);

        // Создаем ответ
        BriefAnswer::create([
            'brief_id' => $brief->id,
            'question_key' => 'zone_question_1',
            'answer_text' => 'Открытое офисное пространство для совместной работы',
        ]);

        // Создаем ответ для зоны
        BriefAnswer::create([
            'brief_id' => $brief->id,
            'room_id' => $zone->id,
            'question_key' => 'zone_preference',
            'answer_text' => 'Современный стиль с эргономичной мебелью',
        ]);

        // Тестируем HTTP запрос
        $response = $this->get(route('briefs.pdf', $brief));

        // Проверяем ответ
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        
        // Проверяем имя файла
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertStringContainsString("commercial_brief_{$brief->id}.pdf", $contentDisposition);
        $this->assertStringContainsString('attachment', $contentDisposition);

        // Проверяем, что контент не пустой
        $this->assertGreaterThan(1000, strlen($response->getContent()));
        
        // Проверяем, что это действительно PDF
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /** @test */
    public function can_download_pdf_for_empty_brief()
    {
        $this->actingAs($this->user);

        // Создаем пустой бриф без ответов
        $brief = Brief::factory()->create([
            'user_id' => $this->user->id,
            'type' => BriefType::COMMON,
            'status' => BriefStatus::ACTIVE,
            'title' => 'Пустой бриф',
            'article' => 'TEST-EMPTY',
        ]);

        // Тестируем HTTP запрос для пустого брифа
        $response = $this->get(route('briefs.pdf', $brief));

        // Проверяем, что PDF генерируется даже для пустого брифа
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        
        // Проверяем, что контент не пустой (хотя бы основная структура)
        $this->assertGreaterThan(500, strlen($response->getContent()));
        
        // Проверяем, что это действительно PDF
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /** @test */
    public function unauthorized_user_gets_redirected()
    {
        // Создаем бриф для другого пользователя
        $otherUser = User::factory()->create();
        $brief = Brief::factory()->create([
            'user_id' => $otherUser->id,
            'type' => BriefType::COMMON,
        ]);

        $this->actingAs($this->user);

        // Пытаемся получить PDF чужого брифа
        $response = $this->get(route('briefs.pdf', $brief));

        // Должен быть редирект (возможно на главную или страницу брифов)
        $response->assertRedirect();
    }

    /** @test */
    public function guest_gets_redirected()
    {
        $brief = Brief::factory()->create([
            'user_id' => $this->user->id,
            'type' => BriefType::COMMON,
        ]);

        // Пытаемся получить PDF без авторизации
        $response = $this->get(route('briefs.pdf', $brief));

        // Должно быть перенаправление (на главную страницу по умолчанию)
        $response->assertRedirect();
    }
}
