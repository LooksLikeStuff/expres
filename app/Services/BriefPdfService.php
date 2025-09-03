<?php

namespace App\Services;

use App\Models\Brief;
use App\Models\BriefQuestion;
use App\Models\User;
use App\Services\Briefs\BriefQuestionService;
use App\Services\Briefs\BriefAnswerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDF;

class BriefPdfService
{
    public function __construct(
        private readonly BriefQuestionService $briefQuestionService,
        private readonly BriefAnswerService $briefAnswerService
    ) {}

    /**
     * Генерировать PDF для брифа
     */
    public function generatePdf(Brief $brief): \Illuminate\Http\Response
    {
        try {
            // Получаем владельца брифа
            $user = $this->getBriefOwner($brief);

            // Подготавливаем данные в зависимости от типа брифа
            $data = $this->preparePdfData($brief, $user);

            // Используем унифицированный шаблон
            $template = 'briefs.pdf';

            // Генерируем PDF
            $pdf = PDF::loadView($template, $data);

            // Настраиваем PDF
            $this->configurePdf($pdf);

            // Формируем имя файла
            $filename = $this->generateFilename($brief);

            return $pdf->download($filename);

        } catch (\Exception $e) {
            $this->logError($brief, $e);
            
            throw new \Exception('Не удалось сгенерировать PDF. Ошибка: ' . $e->getMessage());
        }
    }

    /**
     * Получить владельца брифа
     */
    private function getBriefOwner(Brief $brief): User
    {
        $user = User::find($brief->user_id);
        
        if (!$user) {
            $user = Auth::user();
        }

        return $user;
    }

    /**
     * Подготовить данные для PDF в зависимости от типа брифа
     */
    private function preparePdfData(Brief $brief, User $user): array
    {
        $baseData = [
            'brief' => $brief, // Используем новое название переменной
            'brif' => $brief,  // Сохраняем старое название для совместимости с шаблонами
            'user' => $user,
        ];

        if ($brief->isCommon()) {
            return $this->prepareCommonBriefData($brief, $baseData);
        }

        if ($brief->isCommercial()) {
            return $this->prepareCommercialBriefData($brief, $baseData);
        }

        return $baseData;
    }

    /**
     * Подготовить данные для общего брифа
     */
    private function prepareCommonBriefData(Brief $brief, array $baseData): array
    {
        // Получаем заголовки страниц из модели
        $pageData = $brief->getPageTitles();
        $pageTitlesCommon = array_column($pageData, 'title');

        // Получаем все вопросы и ответы из БД
        $questionsWithAnswers = $this->getQuestionsWithAnswersFromDb($brief);

        // Загружаем комнаты для общего брифа
        $brief->load('rooms');

        // Получаем ответы для комнат через сервис
        $roomAnswers = $this->briefAnswerService->getRoomAnswersForCommonBrief($brief);

        // Преобразуем ссылки на документы в полные URL
        $this->convertDocumentUrlsToAbsolute($brief);

        return array_merge($baseData, [
            'pageTitlesCommon' => $pageTitlesCommon,
            'pageTitles' => $pageTitlesCommon, // Дублируем для совместимости
            'questions' => $questionsWithAnswers,
            'roomAnswers' => $roomAnswers,
            'rooms' => $brief->rooms,
        ]);
    }

    /**
     * Подготовить данные для коммерческого брифа
     */
    private function prepareCommercialBriefData(Brief $brief, array $baseData): array
    {
        $zones = $brief->getZonesData();
        
        // Получаем все вопросы и ответы из БД
        $questionsWithAnswers = $this->getQuestionsWithAnswersFromDb($brief);
        
        // Получаем ответы по зонам для коммерческого брифа через сервис
        $zoneAnswers = $this->briefAnswerService->getZoneAnswersForCommercialBrief($brief);

        // Преобразуем ссылки на документы в полные URL
        $this->convertDocumentUrlsToAbsolute($brief);

        return array_merge($baseData, [
            'zones' => $zones,
            'questions' => $questionsWithAnswers,
            'zoneAnswers' => $zoneAnswers,
            'price' => $brief->price ?? 0,
        ]);
    }



    /**
     * Преобразовать ссылки на документы в абсолютные URL
     */
    private function convertDocumentUrlsToAbsolute(Brief $brief): void
    {
        // Загружаем связанные документы
        $brief->load('documents');
        
        // Документы теперь доступны через отношение $brief->documents
        // Каждый документ имеет метод getFullUrlAttribute() для получения полного URL
    }

    /**
     * Настроить параметры PDF
     */
    private function configurePdf($pdf): void
    {
        $pdf->getDomPDF()->set_option('defaultFont', 'DejaVu Sans');
        $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
        $pdf->getDomPDF()->set_option('isPhpEnabled', true);
    }

    /**
     * Сгенерировать имя файла
     */
    private function generateFilename(Brief $brief): string
    {
        $type = $brief->isCommon() ? 'common' : 'commercial';
        return "{$type}_brief_{$brief->id}.pdf";
    }

    /**
     * Логировать ошибку
     */
    private function logError(Brief $brief, \Exception $e): void
    {
        $type = $brief->isCommon() ? 'общего' : 'коммерческого';
        
        Log::error("Ошибка генерации PDF для {$type} брифа: " . $e->getMessage(), [
            'brief_id' => $brief->id,
            'brief_type' => $brief->type->value,
            'trace' => $e->getTraceAsString()
        ]);
    }

    /**
     * Получить вопросы с ответами из БД
     */
    private function getQuestionsWithAnswersFromDb(Brief $brief): array
    {
        $questionsWithAnswers = [];
        
        // Получаем все вопросы для данного типа брифа
        $questions = BriefQuestion::where('brief_type', $brief->type)
            ->where('is_active', true)
            ->orderBy('page')
            ->orderBy('order')
            ->get()
            ->groupBy('page');

        // Получаем все ответы для брифа
        $answers = $brief->getAnswersByQuestionKey();

        // Объединяем вопросы с ответами
        foreach ($questions as $page => $pageQuestions) {
            $questionsWithAnswers[$page] = [];
            
            foreach ($pageQuestions as $question) {
                $questionData = [
                    'key' => $question->key,
                    'title' => $question->title,
                    'subtitle' => $question->subtitle,
                    'type' => $question->input_type,
                    'placeholder' => $question->placeholder,
                    'format' => $question->format,
                    'answer' => $answers[$question->key] ?? null,
                ];
                
                $questionsWithAnswers[$page][] = $questionData;
            }
        }
        
        return $questionsWithAnswers;
    }

    /**
     * Получить ответы для комнат (общий бриф)
     */
    private function getRoomAnswersForCommonBrief(Brief $brief): array
    {
        if (!$brief->isCommon()) {
            return [];
        }
        
        return $brief->getRoomAnswers();
    }

    /**
     * Получить ответы для зон (коммерческий бриф)
     */
    private function getZoneAnswersForCommercialBrief(Brief $brief): array
    {
        if (!$brief->isCommercial()) {
            return [];
        }
        
        $zoneAnswers = [];
        
        // Получаем ответы для зон коммерческого брифа
        $answers = $brief->answers()
            ->whereNotNull('room_id')
            ->with('room')
            ->get();
        
        foreach ($answers as $answer) {
            $zoneId = $answer->room_id;
            $questionKey = $answer->question_key;
            
            if (!isset($zoneAnswers[$zoneId])) {
                $zoneAnswers[$zoneId] = [];
            }
            
            $zoneAnswers[$zoneId][$questionKey] = $answer->answer_text;
        }
        
        return $zoneAnswers;
    }
}
