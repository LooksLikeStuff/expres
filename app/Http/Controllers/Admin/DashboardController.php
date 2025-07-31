<?php

namespace App\Http\Controllers\Admin;

use App\Models\Common;
use App\Models\Commercial;
use App\Models\Deal;
use App\Models\Estimate;
use App\Models\Message;
use App\Models\User;
use App\Models\Rating;
use App\Models\Attachment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseAdminController
{
    /**
     * Показать панель управления администратора
     */
    public function index()
    {
        $title_site = "Панель администратора | Личный кабинет Экспресс-дизайн";
        $user = $this->getAdminUser();

        // Базовая статистика
        $usersCount = User::count();
        $commonsCount = Common::count();
        $commercialsCount = Commercial::count();
        $dealsCount = Deal::count();
        $estimatesCount = Estimate::count();
        
        // Статистика пользователей по статусам
        $usersByStatus = User::select('status', DB::raw('count(*) as count'))
                           ->groupBy('status')
                           ->get();
                           
        // Сделки по статусам
        $dealsByStatus = Deal::select('status', DB::raw('count(*) as count'))
                          ->groupBy('status')
                          ->get();
        
        // Недавние пользователи
        $recentUsers = User::orderBy('created_at', 'desc')
                         ->take(5)
                         ->get(['id', 'email', 'status', 'created_at']);
                         
        // Недавние сделки
        $recentDeals = Deal::orderBy('created_at', 'desc')
                        ->take(5)
                        ->get(['id', 'status', 'total_sum', 'created_at']);
        
        // Активность за последние 30 дней
        $startDate = Carbon::now()->subDays(30);
        
        $newUsersLast30Days = User::where('created_at', '>=', $startDate)->count();
        $newDealsLast30Days = Deal::where('created_at', '>=', $startDate)->count();
        $newMessagesLast30Days = Message::where('created_at', '>=', $startDate)->count();
        
        // Средний рейтинг исполнителей
        $avgRating = Rating::avg('score') ?: 0;
        
        // Общая сумма всех сделок
        $totalDealAmount = Deal::sum('total_sum');
        
        // Пользователи по ролям
        $userRoles = [
            'client' => User::where('status', 'client')->count(),
            'coordinator' => User::where('status', 'coordinator')->count(),
            'partner' => User::where('status', 'partner')->count(),
            'architect' => User::where('status', 'architect')->count(),
            'designer' => User::where('status', 'designer')->count(),
            'visualizer' => User::where('status', 'visualizer')->count(),
            'admin' => User::where('status', 'admin')->count(),
        ];
        
        // Данные для графика роста пользователей
        $userGrowthData = $this->getUserGrowthChartData();
        
        // Данные для графика роста сделок
        $dealGrowthData = $this->getDealGrowthChartData();

        return view('admin.dashboard', compact(
            'user',
            'title_site',
            'usersCount',
            'commonsCount',
            'commercialsCount',
            'dealsCount',
            'estimatesCount',
            'usersByStatus',
            'dealsByStatus',
            'recentUsers',
            'recentDeals',
            'newUsersLast30Days',
            'newDealsLast30Days',
            'newMessagesLast30Days',
            'avgRating',
            'totalDealAmount',
            'userRoles',
            'userGrowthData',
            'dealGrowthData'
        ));
    }

    /**
     * Получить данные для графика роста пользователей
     */
    private function getUserGrowthChartData()
    {
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $users = User::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
        
        $labels = [];
        $data = [];
        
        $monthNames = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
            7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];
        
        // Заполняем данные для всех месяцев, включая нулевые значения
        $currentDate = clone $startDate;
        
        while ($currentDate <= $endDate) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            
            $userCount = $users
                ->where('year', $year)
                ->where('month', $month)
                ->first();
            
            $labels[] = $monthNames[$month] . ' ' . $year;
            $data[] = $userCount ? $userCount->count : 0;
            
            $currentDate->addMonth();
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Получить данные для графика роста сделок
     */
    private function getDealGrowthChartData()
    {
        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        
        $deals = Deal::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
        
        $labels = [];
        $data = [];
        
        $monthNames = [
            1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель', 5 => 'Май', 6 => 'Июнь',
            7 => 'Июль', 8 => 'Август', 9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
        ];
        
        // Заполняем данные для всех месяцев, включая нулевые значения
        $currentDate = clone $startDate;
        
        while ($currentDate <= $endDate) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            
            $dealCount = $deals
                ->where('year', $year)
                ->where('month', $month)
                ->first();
            
            $labels[] = $monthNames[$month] . ' ' . $year;
            $data[] = $dealCount ? $dealCount->count : 0;
            
            $currentDate->addMonth();
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }
    
    /**
     * Получить аналитические данные для AJAX запросов
     */
    public function getAnalyticsData(Request $request)
    {
        $period = $request->input('period', '30days');
        
        switch ($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(7);
                $periodText = 'последние 7 дней';
                break;
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                $periodText = 'последние 30 дней';
                break;
            case '90days':
                $startDate = Carbon::now()->subDays(90);
                $periodText = 'последние 90 дней';
                break;
            case 'year':
                $startDate = Carbon::now()->subYear();
                $periodText = 'последний год';
                break;
            default:
                $startDate = Carbon::now()->subDays(30);
                $periodText = 'последние 30 дней';
        }
        
        $endDate = Carbon::now();
        
        // Получаем данные о пользователях за выбранный период
        $usersByDate = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
        
        // Получаем данные о сделках за выбранный период
        $dealsByDate = Deal::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', $startDate)
        ->where('created_at', '<=', $endDate)
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();
        
        // KPI данные для обновления карточек
        $kpiData = $this->getKPIData($startDate, $endDate, $periodText);
        
        // Данные для круговых диаграмм
        $userRolesData = $this->getUserRolesData();
        $dealStatusData = $this->getDealStatusData($startDate, $endDate);
        
        // Данные для графиков роста
        $userGrowthData = $this->getGrowthChartData($usersByDate, $startDate, $endDate);
        $dealGrowthData = $this->getGrowthChartData($dealsByDate, $startDate, $endDate);
        
        // Данные для прогноза
        $forecastData = $this->getForecastData($userGrowthData, $dealGrowthData);
        
        return response()->json([
            'period' => $period,
            'kpi' => $kpiData,
            'userRoles' => $userRolesData,
            'dealStatus' => $dealStatusData,
            'userGrowth' => $userGrowthData,
            'dealGrowth' => $dealGrowthData,
            'forecast' => $forecastData
        ]);
    }

    /**
     * Получить данные для KPI карточек
     */
    private function getKPIData($startDate, $endDate, $periodText)
    {
        // Общее количество пользователей
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        
        // Определение тренда для пользователей
        $previousPeriodStart = (clone $startDate)->subDays($endDate->diffInDays($startDate));
        $previousPeriodUsers = User::where('created_at', '>=', $previousPeriodStart)
                                ->where('created_at', '<', $startDate)
                                ->count();
        $usersTrendDirection = ($newUsers >= $previousPeriodUsers) ? 'up' : 'down';
        
        // Общее количество сделок
        $totalDeals = Deal::count();
        $newDeals = Deal::where('created_at', '>=', $startDate)->count();
        
        // Определение тренда для сделок
        $previousPeriodDeals = Deal::where('created_at', '>=', $previousPeriodStart)
                                ->where('created_at', '<', $startDate)
                                ->count();
        $dealsTrendDirection = ($newDeals >= $previousPeriodDeals) ? 'up' : 'down';
        
        // Общая сумма сделок
        $totalAmount = Deal::sum('total_sum');
        $formattedAmount = number_format($totalAmount, 0, '.', ' ');
        
        // Средний рейтинг
        $avgRating = Rating::avg('score') ?: 0;
        $avgRatingFormatted = number_format($avgRating, 1);
        
        // Тренд рейтинга
        $previousRating = Rating::where('created_at', '>=', $previousPeriodStart)
                          ->where('created_at', '<', $startDate)
                          ->avg('score') ?: 0;
        $ratingDiff = $avgRating - $previousRating;
        $ratingTrendDirection = ($ratingDiff >= 0) ? 'up' : 'down';
        
        return [
            'users' => [
                'total' => $totalUsers,
                'trend_value' => $newUsers,
                'trend_direction' => $usersTrendDirection
            ],
            'deals' => [
                'total' => $totalDeals,
                'trend_value' => $newDeals,
                'trend_direction' => $dealsTrendDirection
            ],
            'amount' => [
                'total' => $totalAmount,
                'formatted' => $formattedAmount,
                'trend_percent' => 12, // Это значение нужно рассчитать на основе реальных данных
                'trend_direction' => 'up' // Это тоже нужно определить из реальных данных
            ],
            'rating' => [
                'value' => $avgRatingFormatted,
                'trend_value' => number_format(abs($ratingDiff), 1),
                'trend_direction' => $ratingTrendDirection
            ],
            'period_text' => $periodText
        ];
    }

    /**
     * Получить данные о ролях пользователей для графика
     */
    private function getUserRolesData()
    {
        $userRoles = [
            'client' => User::where('status', 'client')->orWhere('status', 'user')->count(),
            'coordinator' => User::where('status', 'coordinator')->count(),
            'partner' => User::where('status', 'partner')->count(),
            'architect' => User::where('status', 'architect')->count(),
            'designer' => User::where('status', 'designer')->count(),
            'visualizer' => User::where('status', 'visualizer')->count(),
            'admin' => User::where('status', 'admin')->count(),
        ];
        
        return [
            'labels' => ['Клиенты', 'Координаторы', 'Партнеры', 'Архитекторы', 'Дизайнеры', 'Визуализаторы', 'Администраторы'],
            'data' => array_values($userRoles)
        ];
    }

    /**
     * Получить данные о статусах сделок для графика
     */
    private function getDealStatusData($startDate, $endDate)
    {
        $dealsByStatus = Deal::select('status', DB::raw('count(*) as count'))
                        ->where('created_at', '>=', $startDate)
                        ->where('created_at', '<=', $endDate)
                        ->groupBy('status')
                        ->get();
        
        $labels = [];
        $data = [];
        
        foreach ($dealsByStatus as $status) {
            $labels[] = $status->status;
            $data[] = $status->count;
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Получить данные для графиков роста
     */
    private function getGrowthChartData($dataByDate, $startDate, $endDate)
    {
        // Форматируем данные для построения графика
        $dates = [];
        $counts = [];
        
        // Создаем полный диапазон дат в указанном периоде
        $current = clone $startDate;
        
        // Если данных слишком много (больше 60 дней), группируем по неделям
        $groupByWeek = $startDate->diffInDays($endDate) > 60;
        $groupByMonth = $startDate->diffInDays($endDate) > 180;
        
        if ($groupByMonth) {
            // Группировка по месяцам
            $monthData = [];
            
            while ($current <= $endDate) {
                $monthKey = $current->format('Y-m');
                $monthLabel = $current->format('F Y');
                
                if (!isset($monthData[$monthKey])) {
                    $monthData[$monthKey] = [
                        'label' => $monthLabel,
                        'count' => 0
                    ];
                }
                
                // Ищем данные для текущего дня
                $dateStr = $current->format('Y-m-d');
                $dataForDate = $dataByDate->where('date', $dateStr)->first();
                
                if ($dataForDate) {
                    $monthData[$monthKey]['count'] += $dataForDate->count;
                }
                
                $current->addDay();
            }
            
            foreach ($monthData as $data) {
                $dates[] = $data['label'];
                $counts[] = $data['count'];
            }
        } else if ($groupByWeek) {
            // Группировка по неделям
            $weekData = [];
            
            while ($current <= $endDate) {
                $weekKey = $current->year . '-' . $current->week;
                $weekLabel = 'Неделя ' . $current->week . ' ' . $current->year;
                
                if (!isset($weekData[$weekKey])) {
                    $weekData[$weekKey] = [
                        'label' => $weekLabel,
                        'count' => 0
                    ];
                }
                
                // Ищем данные для текущего дня
                $dateStr = $current->format('Y-m-d');
                $dataForDate = $dataByDate->where('date', $dateStr)->first();
                
                if ($dataForDate) {
                    $weekData[$weekKey]['count'] += $dataForDate->count;
                }
                
                $current->addDay();
            }
            
            foreach ($weekData as $data) {
                $dates[] = $data['label'];
                $counts[] = $data['count'];
            }
        } else {
            // Данные по дням
            while ($current <= $endDate) {
                $dateStr = $current->format('Y-m-d');
                $dateLabel = $current->format('d.m');
                
                $dates[] = $dateLabel;
                
                // Ищем данные для этой даты
                $dataForDate = $dataByDate->where('date', $dateStr)->first();
                $counts[] = $dataForDate ? $dataForDate->count : 0;
                
                $current->addDay();
            }
        }
        
        return [
            'labels' => $dates,
            'data' => $counts
        ];
    }

    /**
     * Получить данные для прогноза
     */
    private function getForecastData($userGrowthData, $dealGrowthData)
    {
        // Используем последние 3 точки данных для построения прогноза
        $userHistorical = $userGrowthData['data'];
        $dealHistorical = $dealGrowthData['data'];
        
        // Простой метод прогнозирования на основе среднего роста
        function predictFuture($data, $forecastPoints = 3) {
            // Если меньше 3 точек, просто дублируем последнее значение
            if (count($data) < 3) {
                $lastValue = end($data) ?: 1;
                return array_fill(0, $forecastPoints, $lastValue);
            }
            
            $last3 = array_slice($data, -3);
            $avgChange = 0;
            
            // Рассчитываем средний прирост
            for ($i = 1; $i < count($last3); $i++) {
                $avgChange += $last3[$i] - $last3[$i-1];
            }
            $avgChange = $avgChange / (count($last3) - 1);
            
            // Если изменение слишком маленькое, делаем минимальный прогноз роста
            if (abs($avgChange) < 0.5) {
                $avgChange = $last3[0] > 0 ? 1 : -1;
            }
            
            // Прогноз на будущие периоды
            $forecast = [];
            $lastValue = end($last3);
            
            for ($i = 0; $i < $forecastPoints; $i++) {
                $nextValue = max(1, round($lastValue + $avgChange * ($i + 1)));
                $forecast[] = $nextValue;
                $lastValue = $nextValue;
            }
            
            return $forecast;
        }
        
        // Генерируем прогноз
        $userForecast = predictFuture($userHistorical);
        $dealForecast = predictFuture($dealHistorical);
        
        // Метки для прогнозных периодов
        $labels = $userGrowthData['labels'];
        
        // Добавляем метки для прогнозных периодов
        $forecastLabels = ['Прогноз 1 мес', 'Прогноз 2 мес', 'Прогноз 3 мес'];
        
        return [
            'labels' => array_merge($labels, $forecastLabels),
            'users' => [
                'historical' => $userHistorical,
                'forecast' => $userForecast
            ],
            'deals' => [
                'historical' => $dealHistorical,
                'forecast' => $dealForecast
            ]
        ];
    }

    /**
     * Получить данные для API маршрутов
     */
    public function getDashboardData()
    {
        // Данные для обобщенного дашборда
        $data = [
            'users' => [
                'total' => User::count(),
                'new_today' => User::whereDate('created_at', today())->count(),
                'by_status' => User::select('status', DB::raw('count(*) as count'))
                                ->groupBy('status')
                                ->get()
            ],
            'deals' => [
                'total' => Deal::count(),
                'new_today' => Deal::whereDate('created_at', today())->count(),
                'by_status' => Deal::select('status', DB::raw('count(*) as count'))
                                ->groupBy('status')
                                ->get(),
                'total_value' => Deal::sum('total_sum')
            ],
            'briefs' => [
                'common' => Common::count(),
                'commercial' => Commercial::count(),
                'new_today' => Common::whereDate('created_at', today())->count() +
                              Commercial::whereDate('created_at', today())->count()
            ],
            'estimates' => [
                'total' => Estimate::count(),
                'new_today' => Estimate::whereDate('created_at', today())->count()
            ],
            'activity' => [
                'messages_today' => Message::whereDate('created_at', today())->count(),
                'attachments_today' => Attachment::whereDate('created_at', today())->count()
            ]
        ];
        
        return response()->json($data);
    }
    
    /**
     * Управление сделками
     */
    public function dealsList()
    {
        // Реализация метода управления сделками
        return view('admin.deals');
    }
    
    /**
     * Статистика по сделкам
     */
    public function dealsStats()
    {
        // Реализация метода статистики сделок
        return view('admin.deals_stats');
    }
    
    /**
     * Управление сметами
     */
    public function estimatesList()
    {
        // Реализация метода управления сметами
        return view('admin.estimates');
    }
}
