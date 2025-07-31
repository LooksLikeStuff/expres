<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rating;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RatingViewController extends Controller
{
    /**
     * Конструктор с проверкой доступа
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->status, ['admin', 'coordinator', 'partner'])) {
                abort(403, 'Доступ запрещен. Страница доступна только для администраторов, координаторов и партнеров.');
            }
            return $next($request);
        });
    }

    /**
     * Отображение страницы с рейтингами специалистов
     */
    public function index(Request $request)
    {
        // Параметры фильтрации
        $role = $request->input('role');
        $minRating = $request->input('min_rating');
        $maxRating = $request->input('max_rating');
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'rating_desc'); // По умолчанию сортировка по рейтингу (убыванию)
        $viewType = $request->input('view_type', 'blocks');
        $perPage = $request->input('per_page', 12);
        
        // Создаем уникальный ключ кэша на основе всех параметров запроса
        $cacheKey = 'specialists_' . md5(json_encode($request->all()) . $perPage);
        $cacheTtl = 15; // Время жизни кэша в минутах
        
        // Получаем специалистов из кэша или базы данных
        $specialists = Cache::remember($cacheKey, $cacheTtl * 60, function () use ($role, $minRating, $maxRating, $search, $sortBy, $perPage) {
            // Извлекаем направление сортировки из параметра sort_by
            $sortParts = explode('_', $sortBy);
            $sortField = $sortParts[0] ?? 'rating'; // Поле сортировки по умолчанию
            $sortDir = $sortParts[1] ?? 'desc';    // Направление сортировки по умолчанию
            
            // Базовый запрос для специалистов, у которых есть рейтинги
            $query = User::whereIn('status', ['architect', 'designer', 'visualizer'])
                ->select(['id', 'name', 'status', 'avatar_url', 'last_seen_at']) // Выбираем только нужные поля
                ->withCount('receivedRatings')
                ->withAvg('receivedRatings', 'score');
                
            // Применение фильтра по роли
            if ($role && $role !== 'all') {
                $query->where('status', $role);
            }
            
            // Поиск по имени
            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }
            
            // Фильтрация по минимальному рейтингу
            if ($minRating) {
                $query->havingRaw('IFNULL(received_ratings_avg_score, 0) >= ?', [$minRating]);
            }
            
            // Фильтрация по максимальному рейтингу
            if ($maxRating) {
                $query->havingRaw('IFNULL(received_ratings_avg_score, 0) <= ?', [$maxRating]);
            }
            
            // Применение сортировки
            switch ($sortBy) {
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                case 'rating_asc':
                    $query->orderByRaw('IFNULL(received_ratings_avg_score, 0) ASC');
                    break;
                case 'rating_desc':
                    $query->orderByRaw('IFNULL(received_ratings_avg_score, 0) DESC');
                    break;
                case 'reviews_count_asc':
                    $query->orderBy('received_ratings_count', 'asc');
                    break;
                case 'reviews_count_desc':
                    $query->orderBy('received_ratings_count', 'desc');
                    break;
            }
            
            // Получаем пользователей с пагинацией
            return $query->paginate($perPage);
        });
        
        // Сохраняем запрос для пагинации
        $specialists->appends($request->query());
        
        // Получение всех ролей для фильтра
        $roles = [
            'all' => 'Все специалисты',
            'architect' => 'Архитекторы',
            'designer' => 'Дизайнеры',
            'visualizer' => 'Визуализаторы'
        ];
        
        // Извлекаем направление сортировки для передачи в представление
        $sortParts = explode('_', $sortBy);
        $sortField = $sortParts[0] ?? 'rating';
        $sortDir = $sortParts[1] ?? 'desc';
        
        // Заголовок страницы
        $title_site = "Рейтинги специалистов | Личный кабинет Экспресс-дизайн";
        
        return view('ratings.specialists', compact(
            'specialists', 
            'roles', 
            'role', 
            'minRating', 
            'maxRating', 
            'search', 
            'sortBy',
            'sortField',
            'sortDir',
            'viewType',
            'title_site'
        ));
    }

    /**
     * Получить последние отзывы для конкретного специалиста через AJAX
     * 
     * @param int $id ID специалиста
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecialistRatings($id)
    {
        try {
            // Используем кэширование для ускорения получения отзывов
            $cacheKey = 'specialist_ratings_' . $id;
            $cacheTtl = 10; // Время жизни кэша в минутах
            
            $formattedRatings = Cache::remember($cacheKey, $cacheTtl * 60, function () use ($id) {
                $specialist = User::findOrFail($id);
                $latestRatings = $specialist->receivedRatings()
                    ->with(['raterUser:id,name,avatar_url', 'deal:id,project_number'])
                    ->latest()
                    ->take(3)
                    ->get();
                
                $formattedRatings = [];
                foreach ($latestRatings as $rating) {
                    $formattedRatings[] = [
                        'id' => $rating->id,
                        'score' => $rating->score,
                        'comment' => $rating->comment,
                        'created_at' => $rating->created_at->format('d.m.Y'),
                        'rater' => [
                            'name' => $rating->raterUser->name,
                            'avatar_url' => $rating->raterUser->avatar_url // Теперь аксессор сам подставит дефолтное изображение если нужно
                        ],
                        'deal' => $rating->deal ? [
                            'project_number' => $rating->deal->project_number ?? 'Без номера'
                        ] : null
                    ];
                }
                
                return $formattedRatings;
            });
            
            return response()->json([
                'success' => true,
                'ratings' => $formattedRatings,
                'has_ratings' => count($formattedRatings) > 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при загрузке отзывов: ' . $e->getMessage()
            ], 500);
        }
    }
}
