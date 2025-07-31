<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\User;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingsAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (!in_array($user->status, ['admin', 'coordinator'])) {
                abort(403, 'Доступ запрещен.');
            }
            return $next($request);
        });
    }

    /**
     * Отображение таблицы рейтингов исполнителей
     */
    public function index(Request $request)
    {
        $title_site = "Рейтинги исполнителей | Личный кабинет Экспресс-дизайн";
        $statuses = ['architect', 'designer', 'visualizer']; // Статусы исполнителей

        $query = User::whereIn('status', $statuses)
            ->withCount(['dealsPivot as active_projects' => function($q) {
                $q->whereHas('deal', function($q) {
                    $q->whereNotIn('status', ['Проект готов', 'Проект завершен', 'Возврат']);
                });
            }])
            ->withCount(['receivedRatings as ratings_count'])
            ->withAvg('receivedRatings as average_rating', 'score');
        
        // Фильтрация по статусу исполнителя
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // Фильтрация по имени
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Сортировка
        $sortField = $request->get('sort', 'average_rating');
        $sortDirection = $request->get('direction', 'desc');
        
        $query->orderBy($sortField, $sortDirection);
        
        $executors = $query->paginate(20);

        return view('ratings.admin', compact('executors', 'statuses', 'title_site'));
    }

    /**
     * Отображение детальной информации о рейтингах исполнителя
     */
    public function showExecutorRatings($id)
    {
        $title_site = "Детали рейтинга | Личный кабинет Экспресс-дизайн";
        $executor = User::with(['receivedRatings' => function($q) {
            $q->with(['raterUser', 'deal']);
        }])->findOrFail($id);
        
        // Подсчет активных проектов
        $activeProjects = $executor->dealsPivot()
            ->whereHas('deal', function($q) {
                $q->whereNotIn('status', ['Проект готов', 'Проект завершен', 'Возврат']);
            })
            ->count();
            
        // Подсчет завершенных проектов
        $completedProjects = $executor->dealsPivot()
            ->whereHas('deal', function($q) {
                $q->whereIn('status', ['Проект готов', 'Проект завершен']);
            })
            ->count();
        
        return view('ratings.executor_details', compact('executor', 'activeProjects', 'completedProjects', 'title_site'));
    }
}
