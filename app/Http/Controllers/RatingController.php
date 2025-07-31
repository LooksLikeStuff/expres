<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RatingController extends Controller
{
    /**
     * Сохранение оценки исполнителя
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'rated_user_id' => 'required|exists:users,id',
            'score' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'role' => 'required|string|in:architect,designer,visualizer,coordinator,partner'
        ]);

        $deal = Deal::findOrFail($validated['deal_id']);
        $ratedUser = User::findOrFail($validated['rated_user_id']);

        // Проверяем, что пользователь еще не оставлял оценку этому исполнителю в этой сделке
        $existingRating = Rating::where('deal_id', $deal->id)
            ->where('rated_user_id', $ratedUser->id)
            ->where('rater_user_id', Auth::id())
            ->first();

        if ($existingRating) {
            return response()->json([
                'success' => false,
                'message' => 'Вы уже оценили этого исполнителя в этой сделке'
            ]);
        }

        try {
            $rating = Rating::create([
                'deal_id' => $deal->id,
                'rated_user_id' => $ratedUser->id,
                'rater_user_id' => Auth::id(),
                'score' => $validated['score'],
                'comment' => $validated['comment'],
                'role' => $validated['role']
            ]);

            return response()->json([
                'success' => true,
                'rating' => $rating,
                'message' => 'Оценка успешно сохранена'
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения оценки: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Произошла ошибка при сохранении оценки'
            ], 500);
        }
    }

    /**
     * Проверка необходимости выставить оценки в сделке
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkPendingRatings(Request $request)
    {
        $dealId = $request->input('deal_id');
        $currentUser = Auth::user();
        
        Log::info('Запрос проверки оценок для сделки', [
            'deal_id' => $dealId,
            'user_id' => $currentUser->id,
            'user_status' => $currentUser->status,
            'user_name' => $currentUser->name,
            'request_headers' => $request->headers->all() // Добавляем логирование заголовков
        ]);
        
        try {
            if (!$dealId) {
                Log::error('Отсутствует ID сделки в запросе');
                return response()->json([
                    'error' => 'Отсутствует ID сделки', 
                    'pending_ratings' => []
                ], 400);
            }
            
            $deal = Deal::find($dealId);
            
            if (!$deal) {
                Log::error('Сделка не найдена', ['deal_id' => $dealId]);
                return response()->json([
                    'error' => 'Сделка не найдена',
                    'pending_ratings' => []
                ], 404);
            }
            
            // Добавляем подробную информацию о сделке
            Log::info('Информация о сделке', [
                'deal_id' => $dealId, 
                'status' => $deal->status,
                'coordinator_id' => $deal->coordinator_id,
                'office_partner_id' => $deal->office_partner_id,
                'architect_id' => $deal->architect_id,
                'designer_id' => $deal->designer_id,
                'visualizer_id' => $deal->visualizer_id
            ]);
            
            // Если сделка не завершена, то оценки не требуются
            if ($deal->status !== 'Проект завершен') {
                Log::info('Сделка не завершена, оценки не требуются', [
                    'deal_id' => $dealId, 
                    'status' => $deal->status
                ]);
                return response()->json(['pending_ratings' => []]);
            }
            
        // Проверяем, может ли текущий пользователь оценивать исполнителей в этой сделке
        if (!in_array($currentUser->status, ['coordinator', 'partner', 'client', 'user'])) {
            Log::info('Пользователь не может оценивать других в сделках', [
                'user_id' => $currentUser->id,
                'status' => $currentUser->status
            ]);
            return response()->json(['pending_ratings' => []]);
        }
        
        // Проверяем связь пользователя со сделкой
        $userConnectedToDeal = false;
        
        // Проверяем через pivot-таблицу
        $pivotConnection = $deal->users()->where('user_id', $currentUser->id)->exists();
        
        // Проверяем прямые связи
        $directConnection = (
            $deal->office_partner_id == $currentUser->id ||
            $deal->coordinator_id == $currentUser->id ||
            $deal->architect_id == $currentUser->id ||
            $deal->designer_id == $currentUser->id ||
            $deal->visualizer_id == $currentUser->id
        );
        
        $userConnectedToDeal = $pivotConnection || $directConnection;
        
        Log::info('Проверка связи пользователя со сделкой', [
            'user_id' => $currentUser->id,
            'deal_id' => $dealId,
            'pivot_connection' => $pivotConnection,
            'direct_connection' => $directConnection,
            'total_connected' => $userConnectedToDeal,
            'office_partner_id' => $deal->office_partner_id,
            'coordinator_id' => $deal->coordinator_id
        ]);
        
        if (!$userConnectedToDeal) {
            Log::warning('Пользователь не связан со сделкой', [
                'user_id' => $currentUser->id,
                'deal_id' => $dealId
            ]);
            return response()->json(['pending_ratings' => []]);
        }            // Получаем всех пользователей, связанных со сделкой
            $dealUsers = $deal->users()->get();
            
            // Также проверяем coordinator_id и office_partner_id
            // для случаев, если они не были добавлены в pivot-таблицу
            if ($deal->coordinator_id) {
                $coordinator = User::find($deal->coordinator_id);
                if ($coordinator && !$dealUsers->contains('id', $coordinator->id)) {
                    Log::info('Добавляем координатора к списку пользователей для оценки', [
                        'coordinator_id' => $coordinator->id,
                        'name' => $coordinator->name
                    ]);
                    $dealUsers->push($coordinator);
                }
            }
            
            if ($deal->office_partner_id) {
                $partner = User::find($deal->office_partner_id);
                if ($partner && !$dealUsers->contains('id', $partner->id)) {
                    Log::info('Добавляем партнера к списку пользователей для оценки', [
                        'partner_id' => $partner->id,
                        'name' => $partner->name
                    ]);
                    $dealUsers->push($partner);
                }
            }
            
            // Проверяем architect_id, designer_id и visualizer_id
            $executorIds = [$deal->architect_id, $deal->designer_id, $deal->visualizer_id];
            $executorIds = array_filter($executorIds); // Убираем null значения
            
            if (!empty($executorIds)) {
                Log::info('IDs исполнителей найдены в сделке', [
                    'executor_ids' => $executorIds
                ]);
                
                $executors = User::whereIn('id', $executorIds)->get();
                foreach ($executors as $executor) {
                    if (!$dealUsers->contains('id', $executor->id)) {
                        Log::info('Добавляем исполнителя к списку пользователей для оценки', [
                            'executor_id' => $executor->id,
                            'name' => $executor->name,
                            'status' => $executor->status
                        ]);
                        $dealUsers->push($executor);
                    }
                }
            } else {
                Log::warning('Не найдено ни одного исполнителя в сделке', [
                    'deal_id' => $dealId
                ]);
            }
            
            Log::info('Все пользователи сделки', [
                'deal_id' => $dealId,
                'users_count' => $dealUsers->count(),
                'users' => $dealUsers->map(function ($user) {
                    return ['id' => $user->id, 'status' => $user->status, 'name' => $user->name];
                })
            ]);
            
            $pendingRatings = [];
            
            // Проверяем для каждого пользователя, нужно ли его оценить
            foreach ($dealUsers as $user) {
                // Пропускаем самого себя
                if ($user->id == $currentUser->id) {
                    continue;
                }
                
                // Проверяем, оценивал ли уже текущий пользователь этого пользователя
                $hasRating = $deal->hasRatingFrom($currentUser->id, $user->id);
                
                $shouldRate = false;
                $shouldRateReason = 'Не определено';
                
                // Определяем, кого должен оценивать текущий пользователь
                if ($currentUser->status === 'coordinator') {
                    // Координатор оценивает всех исполнителей (дизайнеров, архитекторов, визуализаторов)
                    $shouldRate = in_array($user->status, ['architect', 'designer', 'visualizer']);
                    $shouldRateReason = 'Координатор → Исполнитель';
                } 
                elseif (in_array($currentUser->status, ['partner', 'client', 'user'])) {
                    // Партнер, клиент и user оценивают исполнителей и координатора
                    $shouldRate = in_array($user->status, ['architect', 'designer', 'visualizer', 'coordinator']);
                    $shouldRateReason = 'Партнер/Клиент/User → Исполнитель/Координатор';
                }
                
                // Логируем информацию для отладки
                Log::info('Проверка необходимости оценки', [
                    'rater_id' => $currentUser->id,
                    'rater_status' => $currentUser->status,
                    'rated_id' => $user->id,
                    'rated_status' => $user->status,
                    'has_rating' => $hasRating,
                    'should_rate' => $shouldRate,
                    'reason' => $shouldRateReason
                ]);
                
                if ($shouldRate && !$hasRating) {
                    // Устанавливаем приоритет оценок - сначала исполнители, потом координаторы
                    $priority = 1; // По умолчанию низкий приоритет
                    if (in_array($user->status, ['architect', 'designer', 'visualizer'])) {
                        $priority = 0; // Высокий приоритет для исполнителей
                    }
                    
                    $pendingRatings[] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'role' => $user->status,
                        'avatar_url' => $user->avatar_url ?? asset('storage/icon/profile.svg'),
                        'priority' => $priority
                    ];
                }
            }
            
            // Сортируем по приоритету - сначала основные исполнители
            usort($pendingRatings, function($a, $b) {
                return $a['priority'] - $b['priority'];
            });
            
            Log::info('Результат проверки оценок', [
                'deal_id' => $dealId,
                'user_id' => $currentUser->id,
                'pending_ratings_count' => count($pendingRatings),
                'user_status' => $currentUser->status,
                'pending_users' => array_column($pendingRatings, 'name')
            ]);
            
            return response()->json([
                'pending_ratings' => $pendingRatings,
                'force_rating' => true,
                'user_status' => $currentUser->status
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при проверке оценок', [
                'deal_id' => $dealId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Внутренняя ошибка сервера при проверке оценок',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Проверка, все ли оценки для сделки выставлены
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAllRatingsComplete(Request $request)
    {
        $dealId = $request->input('deal_id');
        $deal = Deal::findOrFail($dealId);
        $currentUser = Auth::user();
        
        // Определяем, кого текущий пользователь должен оценивать
        $usersToRate = $deal->users();
        
        // Для клиентов и партнеров - исполнители и координаторы
        if (in_array($currentUser->status, ['client', 'partner'])) {
            $usersToRate->whereIn('status', ['architect', 'designer', 'visualizer', 'coordinator']);
        }
        // Для координаторов - только исполнители
        elseif ($currentUser->status === 'coordinator') {
            $usersToRate->whereIn('status', ['architect', 'designer', 'visualizer']);
        }
        // Для исполнителей - другие исполнители
        else {
            $usersToRate->whereIn('status', ['architect', 'designer', 'visualizer']);
        }
        
        // Исключаем самого себя
        $usersToRate = $usersToRate->where('user_id', '!=', $currentUser->id)->get();
        
        $allRated = true;
        foreach ($usersToRate as $user) {
            if (!$deal->hasRatingFrom($currentUser->id, $user->id)) {
                $allRated = false;
                break;
            }
        }
        
        return response()->json([
            'all_complete' => $allRated,
            'deal_id' => $dealId,
            'users_to_rate_count' => $usersToRate->count()
        ]);
    }

    /**
     * Получить список всех завершенных сделок пользователя, требующих оценки
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function findCompletedDealsNeedingRatings(Request $request)
    {
        try {
            // Проверяем, авторизован ли пользователь
            $currentUser = Auth::user();
            if (!$currentUser) {
                Log::warning('Попытка поиска сделок для неавторизованного пользователя');
                return response()->json(['deals' => []]);
            }
            
            Log::info('Поиск завершенных сделок для оценки', [
                'user_id' => $currentUser->id,
                'user_status' => $currentUser->status,
            ]);
            
            // Проверяем, может ли пользователь оценивать исполнителей
            if (!in_array($currentUser->status, ['coordinator', 'partner', 'client', 'user'])) {
                Log::info('Пользователь не может оценивать исполнителей', [
                    'user_id' => $currentUser->id,
                    'status' => $currentUser->status
                ]);
                return response()->json(['deals' => []]);
            }
            
            // Находим все сделки пользователя со статусом "Проект завершен"
            $deals = Deal::where('status', 'Проект завершен')
                ->where(function($query) use ($currentUser) {
                    $query->whereHas('users', function($q) use ($currentUser) {
                        $q->where('user_id', $currentUser->id);
                    })
                    // Также проверяем прямые связи для партнеров и координаторов
                    ->orWhere('office_partner_id', $currentUser->id)
                    ->orWhere('coordinator_id', $currentUser->id)
                    ->orWhere('architect_id', $currentUser->id)
                    ->orWhere('designer_id', $currentUser->id)
                    ->orWhere('visualizer_id', $currentUser->id);
                })
                ->get();
            
            $dealsNeedingRatings = [];
            
            foreach ($deals as $deal) {
                $needsRating = false;
                
                // Получаем пользователей, которые могут быть оценены текущим пользователем
                $usersToRate = [];
                
                if ($currentUser->status === 'coordinator') {
                    // Координатор оценивает исполнителей
                    if ($deal->architect_id) $usersToRate[] = $deal->architect_id;
                    if ($deal->designer_id) $usersToRate[] = $deal->designer_id;
                    if ($deal->visualizer_id) $usersToRate[] = $deal->visualizer_id;
                } 
                elseif (in_array($currentUser->status, ['partner', 'client', 'user'])) {
                    // Партнер, клиент и user оценивают исполнителей и координатора
                    if ($deal->architect_id) $usersToRate[] = $deal->architect_id;
                    if ($deal->designer_id) $usersToRate[] = $deal->designer_id;
                    if ($deal->visualizer_id) $usersToRate[] = $deal->visualizer_id;
                    if ($deal->coordinator_id) $usersToRate[] = $deal->coordinator_id;
                }
                
                try {
                    // Проверяем каждого пользователя на наличие оценки
                    foreach ($usersToRate as $userId) {
                        if (!$userId) continue; // Пропускаем, если ID не задан
                        
                        if (!$deal->hasRatingFrom($currentUser->id, $userId)) {
                            $needsRating = true;
                            break;
                        }
                    }
                    
                    // Если нужна оценка хотя бы для одного пользователя, добавляем сделку в список
                    if ($needsRating) {
                        $dealsNeedingRatings[] = $deal->id;
                    }
                } catch (\Exception $inner) {
                    Log::warning('Ошибка при обработке сделки для рейтинга', [
                        'deal_id' => $deal->id,
                        'exception' => $inner->getMessage()
                    ]);
                    // Пропускаем проблемную сделку, продолжаем проверку других
                    continue;
                }
            }
            
            Log::info('Найдены сделки, требующие оценки', [
                'deals_count' => count($dealsNeedingRatings),
                'deals' => $dealsNeedingRatings
            ]);
            
            return response()->json([
                'deals' => $dealsNeedingRatings
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при поиске сделок, требующих оценки', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Внутренняя ошибка сервера при поиске сделок',
                'message' => $e->getMessage(),
                'deals' => []
            ], 500);
        }
    }
}
