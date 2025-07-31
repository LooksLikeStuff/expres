<?php
namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealFeed;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DealFeedController extends Controller
{
    public function store(Request $request, $dealId)
    {
        $request->validate([
            'content' => 'required|string|max:1990',
        ]);

        $deal = Deal::findOrFail($dealId);
        $feed = DealFeed::create([
            'deal_id' => $dealId,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        // Проверяем статус сделки и необходимость оценки
        $needRating = false;
        $currentUser = Auth::user();
        
        if ($deal->status === 'Проект завершен' && in_array($currentUser->status, ['user', 'client', 'partner'])) {
            // Проверяем, есть ли неоцененные специалисты
            $specialistIds = [
                'architect' => $deal->architect_id,
                'designer' => $deal->designer_id,
                'visualizer' => $deal->visualizer_id,
                'coordinator' => $deal->coordinator_id
            ];
            
            foreach ($specialistIds as $role => $id) {
                if (!$id) continue;
                
                // Проверяем, есть ли уже оценка для этого специалиста от текущего пользователя
                $hasRating = Rating::where('deal_id', $dealId)
                    ->where('rated_user_id', $id)
                    ->where('rater_user_id', $currentUser->id)
                    ->where('role', $role)
                    ->exists();
                
                if (!$hasRating) {
                    $needRating = true;
                    break;
                }
            }

            // Логирование состояния проверки рейтингов
            Log::info('Проверка рейтингов при комментарии', [
                'deal_id' => $dealId,
                'user_id' => $currentUser->id,
                'need_rating' => $needRating,
                'status' => $deal->status
            ]);
        }

        return response()->json([
            'user_name'  => $feed->user->name,
            'content'    => $feed->content,
            'date'       => $feed->created_at->format('d.m.Y H:i'),
            'avatar_url' => $feed->user->avatar_url,
            'need_rating' => $needRating,
            'deal_id' => $deal->id
        ]);
    }
}
