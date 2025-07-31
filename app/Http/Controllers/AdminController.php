<?php

namespace App\Http\Controllers;

use App\Models\Common;
use App\Models\Commercial;
use App\Models\Deal;
use App\Models\Estimate;
use App\Models\Message;
use App\Models\User;
use App\Models\Rating;
use App\Models\Attachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Показать панель управления администратора
     */
    public function index()
    {
        $title_site = "Панель администратора | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();

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
                         ->get(['id', 'name', 'email', 'status', 'created_at']);
                         
        // Недавние сделки
        $recentDeals = Deal::orderBy('created_at', 'desc')
                        ->take(5)
                        ->get(['id', 'name', 'status', 'total_sum', 'created_at']);
        
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

    // Другие методы контроллера
    // ...existing code...

    public function user_admin()
    {
        $title_site = "Управление пользователями | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
    
        // Получение данных
        $usersCount = User::count();
        $commonsCount = Common::count();
        $commercialsCount = Commercial::count();
        $dealsCount = Deal::count();
        $estimatesCount = Estimate::count();
    
        // Получение списка пользователей
        $users = User::all();
    
        return view('admin.users', compact(
            'user',
            'title_site',
            'usersCount',
            'commonsCount',
            'commercialsCount',
            'dealsCount',
            'estimatesCount',
            'users'
        ));
    }
    
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
    
        // Проверяем, был ли передан новый пароль
        if ($request->has('password') && !empty($request->password)) {
            $user->password = Hash::make($request->password); // Хешируем пароль перед сохранением
        }
    
        // Обновляем остальные данные пользователя
        $user->update($request->only(['name', 'phone', 'status']));
    
        return response()->json(['success' => true]);
    }
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Удаление пользователя
        $user->delete();

        return response()->json(['success' => true]);
    }
    public function userBriefs($id)
    {
        $user = User::findOrFail($id);
        $title_site = "Управление брифами пользователя | Личный кабинет Экспресс-дизайн";
        // Получаем брифы пользователя
        $commonBriefs = Common::where('user_id', $id)->get();
        $commercialBriefs = Commercial::where('user_id', $id)->get();

        return view('admin.user_briefs', compact('user', 'commonBriefs', 'commercialBriefs', 'title_site'));
    }
    public function edit($id)
    {
        $title_site = "Понель администратора | Личный кабинет Экспресс-дизайн";
        $brif = Commercial::findOrFail($id); // или Common::findOrFail($id), если общий бриф
        $zones = $brif->zones ? json_decode($brif->zones, true) : [];
        $preferences = $brif->preferences ? json_decode($brif->preferences, true) : [];
        $user = $brif->user;

        return view('admin.brief_edit', compact('Бриф прикриплен', 'title_site', 'zones', 'preferences', 'user'));
    }

    public function updateCommonBrief(Request $request, $id)
    {
        $brief = Common::findOrFail($id);  // Get the brief by ID
    
        // Validate the incoming request
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'status' => 'required|string|in:Активный,Завершенный,completed',  // Example status validation
            'zones' => 'nullable|array',
            'preferences' => 'nullable|array',
        ]);
    
        // Update the basic details of the brief
        $brief->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'price' => $request->input('price'),
            'status' => $request->input('status'),
        ]);
    
        // If zones or preferences were submitted, update them
        if ($request->has('zones')) {
            $brief->zones = json_encode($request->input('zones'));
        }
    
        if ($request->has('preferences')) {
            $brief->preferences = json_encode($request->input('preferences'));
        }
    
        // Save the changes
        $brief->save();
    
        return response()->json(['success' => true]);
    }
        // Controller for handling Commercial Briefs

// Edit method to load the brief data for editing
public function editCommercialBrief($id)
{
    $title_site = "Редактировать коммерческий бриф | Личный кабинет Экспресс-дизайн";
    $brief = Commercial::findOrFail($id);  // Find the commercial brief by ID

    // Добавляем массив названий вопросов для зон
    $titles = [
        'zone_1' => "Зоны и их функционал",
        'zone_2' => "Метраж зон",
        'zone_3' => "Зоны и их стиль оформления",
        'zone_4' => "Мебилировка зон",
        'zone_5' => "Предпочтения отделочных материалов",
        'zone_6' => "Освещение зон",
        'zone_7' => "Кондиционирование зон",
        'zone_8' => "Напольное покрытие зон",
        'zone_9' => "Отделка стен зон",
        'zone_10' => "Отделка потолков зон",
        'zone_11' => "Категорически неприемлемо или нет",
        'zone_12' => "Бюджет на помещения",
        'zone_13' => "Пожелания и комментарии",
    ];

    // Decode the questions or zones (depending on your structure)
    $questions = json_decode($brief->questions, true) ?? [];
    $preferences = json_decode($brief->preferences, true) ?? [];
    $zones = json_decode($brief->zones, true) ?? [];

    // Инициализация массивов зон и их бюджетов
    $zones = json_decode($brief->zones ?? '[]', true);
    $zoneBudgets = []; // Массив бюджетов для каждой зоны
    $preferences = []; // Массив предпочтений для каждой зоны
    
    // Заполнение массивов бюджетов, если имеются данные
    // Это нужно адаптировать под вашу структуру данных
    if (!empty($zones)) {
        foreach ($zones as $index => $zone) {
            // Пример: получение бюджета из поля budget каждой зоны
            $zoneBudgets[$index] = $zone['budget'] ?? null;
            
            // Пример: получение предпочтений из поля preferences каждой зоны
            $preferences[$index] = $zone['preferences'] ?? [];
        }
    }

    return view('admin.brief_edit_commercial', compact('brief', 'title_site', 'questions', 'preferences', 'zones', 'titles', 'zoneBudgets'));
}


public function updateCommercialBrief(Request $request, $id)
{
    $brief = Commercial::findOrFail($id);  // Find the commercial brief by ID

    // Validate the input data
    $data = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'nullable|numeric',
        'status' => 'required|string|in:Активный,Завершенный,completed',
        'questions' => 'nullable|array',
        'zones' => 'nullable|array',
        'zone_preferences_data' => 'nullable|string',
    ]);

    // Update the commercial brief
    $brief->update([
        'title' => $data['title'],
        'description' => $data['description'],
        'price' => $data['price'],
        'status' => $data['status'],
    ]);

    // If questions were provided, update them
    if ($request->has('questions')) {
        $brief->questions = json_encode($request->input('questions'));
    }

    // If zones were provided, update them
    if ($request->has('zones')) {
        $brief->zones = json_encode($request->input('zones'));
    }

    // Обрабатываем предпочтения для зон
    if ($request->has('zone_preferences_data')) {
        $preferences = json_decode($request->input('zone_preferences_data'), true);
        if (is_array($preferences)) {
            $brief->preferences = json_encode($preferences);
        }
    }

    // Save the updated brief
    $brief->save();

    // Redirect to the user's briefs page
    return redirect()->route('user.briefs', $brief->user_id)
                    ->with('success', 'Бриф успешно обновлен.');
}

// Получить среднюю оценку сделки ТОЛЬКО от клиента по всем исполнителям
public function getClientAverageRatingAttribute()
{
    if ($this->status !== 'Проект завершен') {
        return null;
    }
    
    // Получаем все оценки по сделке, где оценивающий - клиент (теперь user)
    $ratings = \App\Models\Rating::where('deal_id', $this->id)
                                ->whereHas('raterUser', function($query) {
                                    $query->where('status', 'user');
                                })
                                ->get();
    
    if ($ratings->isEmpty()) {
        return null;
    }
    
    return number_format($ratings->avg('score'), 1);
}

// Получить количество оценок сделки от клиента
public function getClientRatingsCountAttribute()
{
    return \App\Models\Rating::where('deal_id', $this->id)
                           ->whereHas('raterUser', function($query) {
                               $query->where('status', 'user');
                           })
                           ->count();
}

// Обновление в статистике пользователей по ролям
private function getUserStatistics()
{
    // ...existing code...
    $userRoles = [
        'user' => User::where('status', 'user')->count(),
        'coordinator' => User::where('status', 'coordinator')->count(),
        'partner' => User::where('status', 'partner')->count(),
        // ...existing code...
    ];
    // ...existing code...
}

    /**
     * Удалить общий бриф
     */
    public function destroyCommonBrief($id)
    {
        try {
            $brief = Common::findOrFail($id);
            
            // Удаляем бриф
            $brief->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении общего брифа: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Удалить коммерческий бриф
     */
    public function destroyCommercialBrief($id)
    {
        try {
            $brief = Commercial::findOrFail($id);
            
            // Удаляем бриф
            $brief->delete();
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error('Ошибка при удалении коммерческого брифа: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Редактирование общего брифа
     */
    public function editCommonBrief($id)
    {
        $title_site = "Редактировать общий бриф | Личный кабинет Экспресс-дизайн";
        $brief = Common::findOrFail($id);  // Get the Common brief by ID
        
        // Декодируем данные JSON полей
        $zones = $brief->rooms ? json_decode($brief->rooms, true) : [];
        
        // Обработка ответов на вопросы - улучшенная логика
        $answers = [];
        
        // 1. Сначала проверяем наличие JSON-поля answers и правильно декодируем его
        if (!empty($brief->answers)) {
            $decodedAnswers = json_decode($brief->answers, true);
            if (is_array($decodedAnswers)) {
                $answers = $decodedAnswers;
                \Log::info('Найдены ответы в JSON-поле answers', [
                    'brief_id' => $id,
                    'answers_count' => count($answers)
                ]);
            } else {
                \Log::warning('JSON-поле answers не содержит валидный массив', [
                    'brief_id' => $id,
                    'answers_raw' => $brief->answers
                ]);
            }
        }
        
        // 2. Если ответов в JSON-поле нет или они неполные, собираем их из индивидуальных полей и дополняем
        
        // Структура ответов по страницам
        if (!isset($answers['page1'])) $answers['page1'] = [];
        if (!isset($answers['page2'])) $answers['page2'] = [];
        if (!isset($answers['page3'])) $answers['page3'] = [];
        if (!isset($answers['page4'])) $answers['page4'] = [];
        if (!isset($answers['page5'])) $answers['page5'] = [];
        
        // Названия вопросов по страницам
        $questionTitles = [
            1 => [
                'question_1_1' => 'Сколько человек будет проживать в квартире',
                'question_1_2' => 'Есть ли домашние животные и растения',
                'question_1_3' => 'Есть ли у членов семьи особые увлечения или хобби',
                'question_1_4' => 'Какие потребности у семьи',
                'question_1_5' => 'Как часто вы встречаете гостей',
                'question_1_6' => 'Адрес'
            ],
            2 => [
                'question_2_1' => 'Какой стиль Вы хотите видеть в своем интерьере',
                'question_2_2' => 'Референсы интерьера',
                'question_2_3' => 'Какую атмосферу вы хотите ощущать',
                'question_2_4' => 'Предметы обстановки для нового интерьера',
                'question_2_5' => 'Что не должно быть в интерьере',
                'question_2_6' => 'Ценовой сегмент ремонта',
                'question_2_7' => 'Предпочтения по цветам и материалам'
            ],
            3 => [
                'question_3_1' => 'Прихожая',
                'question_3_2' => 'Детская',
                'question_3_3' => 'Кладовая',
                'question_3_4' => 'Кухня и гостиная',
                'question_3_5' => 'Гостевой санузел',
                'question_3_6' => 'Гостиная',
                'question_3_7' => 'Рабочее место',
                'question_3_8' => 'Столовая',
                'question_3_9' => 'Ванная комната',
                'question_3_10' => 'Кухня',
                'question_3_11' => 'Кабинет',
                'question_3_12' => 'Спальня',
                'question_3_13' => 'Гардеробная',
                'question_3_14' => 'Другое'
            ],
            4 => [
                'question_4_1' => 'Напольные покрытия',
                'question_4_2' => 'Двери',
                'question_4_3' => 'Отделка стен',
                'question_4_4' => 'Освещение и электрика',
                'question_4_5' => 'Потолки',
                'question_4_6' => 'Дополнительные пожелания по отделке'
            ],
            5 => [
                'question_5_1' => 'Пожелания по звукоизоляции',
                'question_5_2' => 'Теплые полы',
                'question_5_3' => 'Предпочтения по размещению и типу радиаторов',
                'question_5_4' => 'Водоснабжение',
                'question_5_5' => 'Кондиционирование и вентиляция',
                'question_5_6' => 'Сети и коммуникации',
                'question_5_7' => 'Системы безопасности',
                'question_5_8' => 'Умный дом'
            ]
        ];
        
        // Проходимся по всем полям брифа
        foreach ($brief->getAttributes() as $key => $value) {
            // Ищем поля типа question_X_Y
            if (preg_match('/^question_(\d+)_(\d+)$/', $key, $matches) && !empty($value)) {
                $page = $matches[1];
                $question = $matches[2];
                
                // Получаем заголовок вопроса из массива или используем ключ
                $questionTitle = $questionTitles[$page][$key] ?? "Вопрос $question";
                
                // Добавляем ответ в соответствующую страницу, если его еще нет
                if (!isset($answers['page'.$page][$questionTitle])) {
                    $answers['page'.$page][$questionTitle] = $value;
                    \Log::info("Добавлен ответ из поля $key", [
                        'brief_id' => $id,
                        'question' => $questionTitle,
                        'answer' => substr($value, 0, 30) . (strlen($value) > 30 ? '...' : '')
                    ]);
                }
            }
        }
        
        // 3. Проверяем поля для отдельных вопросов (если они есть)
        $specificQuestionFields = [
            'style_preferences',
            'floor_preferences',
            'wall_preferences',
            'ceiling_preferences',
            'door_preferences',
            'lighting_preferences',
            'bathroom_preferences',
            'kitchen_preferences'
            // можно добавить другие поля, если они есть
        ];
        
        foreach ($specificQuestionFields as $field) {
            if (!empty($brief->$field)) {
                // В зависимости от имени поля определяем, к какой странице относится вопрос
                $page = $this->mapFieldToPage($field);
                $questionTitle = $this->getQuestionTitleForField($field);
                
                if ($page && $questionTitle) {
                    $answers['page'.$page][$questionTitle] = $brief->$field;
                    \Log::info("Добавлен ответ из специального поля $field", [
                        'brief_id' => $id,
                        'page' => $page,
                        'question' => $questionTitle
                    ]);
                }
            }
        }
        
        // 4. Если после всех проверок ответов все равно нет, создаём пустую структуру
        $hasAnswers = false;
        foreach ($answers as $page => $pageAnswers) {
            if (!empty($pageAnswers)) {
                $hasAnswers = true;
                break;
            }
        }
        
        if (!$hasAnswers) {
            \Log::warning('Не найдены ответы на вопросы для брифа', [
                'brief_id' => $id
            ]);
        } else {
            \Log::info('Обработаны ответы на вопросы', [
                'brief_id' => $id,
                'pages_with_answers' => array_keys(array_filter($answers, function($page) {
                    return !empty($page);
                }))
            ]);
        }
        
        // Загружаем пользователя с проверкой
        $user = null;
        if ($brief->user_id) {
            $user = User::find($brief->user_id);
        }
        
        return view('admin.brief_edit_common', compact('brief', 'title_site', 'zones', 'answers', 'user'));
    }
    
    /**
     * Определяет, к какой странице относится специальное поле
     */
    private function mapFieldToPage($field)
    {
        $mapping = [
            'style_preferences' => 2,
            'floor_preferences' => 4,
            'wall_preferences' => 4,
            'ceiling_preferences' => 4,
            'door_preferences' => 4,
            'lighting_preferences' => 4,
            'bathroom_preferences' => 3,
            'kitchen_preferences' => 3
        ];
        
        return $mapping[$field] ?? null;
    }
    
    /**
     * Возвращает заголовок вопроса для специального поля
     */
    private function getQuestionTitleForField($field)
    {
        $mapping = [
            'style_preferences' => 'Какой стиль Вы хотите видеть в своем интерьере',
            'floor_preferences' => 'Напольные покрытия',
            'wall_preferences' => 'Отделка стен',
            'ceiling_preferences' => 'Потолки',
            'door_preferences' => 'Двери',
            'lighting_preferences' => 'Освещение и электрика',
            'bathroom_preferences' => 'Ванная комната',
            'kitchen_preferences' => 'Кухня'
        ];
        
        return $mapping[$field] ?? null;
    }
}
