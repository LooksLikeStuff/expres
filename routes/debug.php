<?php

use Illuminate\Http\Request;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

Route::get('/debug/rating-test/{dealId}', function ($dealId) {
    $deal = Deal::findOrFail($dealId);
    $currentUser = Auth::user();
    
    $debugInfo = [
        'deal_info' => [
            'id' => $deal->id,
            'status' => $deal->status,
            'office_partner_id' => $deal->office_partner_id,
            'coordinator_id' => $deal->coordinator_id,
            'architect_id' => $deal->architect_id,
            'designer_id' => $deal->designer_id,
            'visualizer_id' => $deal->visualizer_id,
        ],
        'current_user' => [
            'id' => $currentUser->id,
            'name' => $currentUser->name,
            'status' => $currentUser->status,
        ],
        'connections' => [
            'pivot_connection' => $deal->users()->where('user_id', $currentUser->id)->exists(),
            'is_office_partner' => $deal->office_partner_id == $currentUser->id,
            'is_coordinator' => $deal->coordinator_id == $currentUser->id,
            'is_architect' => $deal->architect_id == $currentUser->id,
            'is_designer' => $deal->designer_id == $currentUser->id,
            'is_visualizer' => $deal->visualizer_id == $currentUser->id,
        ],
        'deal_users' => $deal->users()->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status
            ];
        })
    ];
    
    return response()->json($debugInfo, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
})->middleware('auth')->name('debug.rating-test');

Route::post('/debug/complete-deal/{dealId}', function ($dealId) {
    $deal = Deal::findOrFail($dealId);
    $deal->status = 'Проект завершен';
    $deal->save();
    
    Log::info('Сделка принудительно завершена для тестирования', [
        'deal_id' => $dealId,
        'user_id' => Auth::id()
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Сделка завершена',
        'deal_id' => $dealId,
        'new_status' => $deal->status
    ]);
})->middleware('auth')->name('debug.complete-deal');

Route::get('/debug/rating-check/{dealId}', function ($dealId) {
    // Имитируем обновление сделки
    return response()->json([
        'success' => true,
        'message' => 'Сделка успешно обновлена',
        'status_changed_to_completed' => true,
        'deal' => [
            'id' => $dealId,
            'status' => 'Проект завершен'
        ],
        'deal_id' => $dealId
    ]);
})->middleware('auth')->name('debug.rating-check');
