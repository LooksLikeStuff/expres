<?php

namespace App\Services\Briefs;

use App\DTO\Briefs\BriefDTO;
use App\Models\Brief;
use App\Models\Deal;
use App\Enums\Briefs\BriefStatus;
use App\Enums\Briefs\BriefType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BriefService
{
    public function updateOrCreate(BriefDTO $briefDTO)
    {
        return Brief::updateOrCreate(
            ['article' => $briefDTO->article],
            $briefDTO->toArray());
    }

    public function getUserBriefs(int $userId)
    {

        return Brief::where('user_id', $userId)
            ->orderBy('created_at')
            ->get();
    }

    public function createEmptyBrief(BriefDTO $briefDTO)
    {
        return Brief::create($briefDTO->toArray());
    }

    public function linkToAvailableDeal(Brief $brief): void
    {
        //Ищем активную сделку для привязки брифа
        $availableDeal = Deal::whereNot('status', 'Проект завершен')
            ->whereHas('dealClient', fn($q) => $q->where('phone', $brief->user->phone))
            ->first();

        //Если сделка нашлась привязываем ее к брифу
        if ($availableDeal) {
            $brief->deal_id = $availableDeal->id;
            $brief->save();
        }
    }

    public function saveDocuments(Brief $brief, array $documents): void
    {
        foreach ($documents as $document) {
            $path = Storage::disk('yandex')->putFile('brief-documents', $document);

            $brief->documents()->create([
                'filepath' => $path,
                'original_name' => $document->getClientOriginalName(),
                'file_size' => $document->getSize(),
                'mime_type' => $document->getMimeType(),
            ]);
        }
    }

    /**
     * Получить бриф с загруженными связанными данными для отображения
     */
    public function getBriefForShow(Brief $brief): Brief
    {
        return $brief->load([
            'rooms',
            'answers.question',
            'documents',
            'user'
        ]);
    }

    /**
     * Получить структурированные данные для отображения брифа
     */
    public function getStructuredDataForShow(Brief $brief): array
    {
        // Загружаем данные
        $this->getBriefForShow($brief);

        // Получаем все вопросы для данного типа брифа
        $allQuestions = $brief->questions()
            ->where('is_active', true)
            ->orderBy('page')
            ->orderBy('order')
            ->get();

        // Группируем вопросы по страницам
        $questionsByPage = $allQuestions->groupBy('page');

        // Получаем все ответы с индексированием по ключу вопроса
        $answersMap = $brief->answers->keyBy('question_key');

        // Получаем заголовки страниц из модели
        $pageTitles = $brief->getPageTitles();

        return [
            'brief' => $brief,
            'questionsByPage' => $questionsByPage,
            'answersMap' => $answersMap,
            'pageTitles' => $pageTitles
        ];
    }

    /**
     * Получить брифы без сделок для админ дашборда
     */
    public function getBriefsWithoutDeals(int $limit = 10): Collection
    {
        $briefsWithoutDeals = Brief::whereNull('deal_id')
            ->where('status', BriefStatus::COMPLETED)
            ->with('user:id,name,phone')
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($brief) {
                return [
                    'id' => $brief->id,
                    'type' => $brief->type->label(),
                    'title' => $brief->title,
                    'user' => $brief->user?->name ?? 'Неизвестно',
                    'user_id' => $brief->user_id,
                    'phone' => $brief->user?->phone ?? 'Не указан',
                    'price' => $brief->price ?? 0,
                    'created_at' => $brief->created_at,
                    'status' => $brief->status->value,
                    'edit_route' => route('briefs.show', $brief->id),
                    'brief_type' => $brief->type->value
                ];
            });

        return $briefsWithoutDeals;
    }

    /**
     * Получить статистику брифов
     */
    public function getBriefStats(): array
    {
        $commonCount = Brief::where('type', BriefType::COMMON)->count();
        $commercialCount = Brief::where('type', BriefType::COMMERCIAL)->count();
        
        return [
            'common_count' => $commonCount,
            'commercial_count' => $commercialCount,
            'total_count' => $commonCount + $commercialCount
        ];
    }

    /**
     * Получить новые брифы за последние N дней
     */
    public function getNewBriefsLast30Days(): int
    {
        return Brief::where('created_at', '>=', Carbon::now()->subDays(30))->count();
    }

    /**
     * Получить данные роста брифов по месяцам
     */
    public function getBriefGrowthData(int $months = 6): array
    {
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        
        $briefsByMonth = Brief::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthData = $briefsByMonth->where('year', $date->year)
                                     ->where('month', $date->month)
                                     ->first();
            
            $labels[] = $date->format('M Y');
            $data[] = $monthData ? $monthData->count : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Получить брифы по статусам
     */
    public function getBriefsByStatus(): Collection
    {
        return Brief::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    /**
     * Получить конверсию брифов в сделки
     */
    public function getBriefConversionRate(): array
    {
        $totalBriefs = Brief::count();
        $briefsWithDeals = Brief::whereNotNull('deal_id')->count();
        
        $conversionRate = $totalBriefs > 0 ? round(($briefsWithDeals / $totalBriefs) * 100) : 0;
        
        return [
            'total_briefs' => $totalBriefs,
            'converted_briefs' => $briefsWithDeals,
            'conversion_rate' => $conversionRate
        ];
    }

    /**
     * Получить активные брифы пользователя
     */
    public function getActiveBriefs(int $userId): Collection
    {
        return Brief::where('user_id', $userId)
            ->where('status', '!=', BriefStatus::COMPLETED)
            ->with('rooms')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить завершенные брифы пользователя
     */
    public function getCompletedBriefs(int $userId): Collection
    {
        return Brief::where('user_id', $userId)
            ->where('status', BriefStatus::COMPLETED)
            ->with('rooms')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Получить статистику по брифам для периода
     */
    public function getBriefStatsForPeriod(string $period): array
    {
        $startDate = $this->getStartDateForPeriod($period);
        
        $query = Brief::where('created_at', '>=', $startDate);
        
        return [
            'total' => $query->count(),
            'common' => (clone $query)->where('type', BriefType::COMMON)->count(),
            'commercial' => (clone $query)->where('type', BriefType::COMMERCIAL)->count(),
            'completed' => (clone $query)->where('status', BriefStatus::COMPLETED)->count(),
            'active' => (clone $query)->where('status', '!=', BriefStatus::COMPLETED)->count(),
        ];
    }

    /**
     * Получить начальную дату для периода
     */
    private function getStartDateForPeriod(string $period): Carbon
    {
        return match ($period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '90days' => Carbon::now()->subDays(90),
            'year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
    }
}
