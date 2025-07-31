<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Deal;
use Illuminate\Support\Facades\Log;

class ClientDealsController extends Controller
{
    /**
     * Отображение сделок для пользователя.
     */
    public function dealUser(Request $request)
    {
        $title_site = "Мои сделки | Личный кабинет Экспресс-дизайн";
        $user = Auth::user();
        
        // Прямой поиск сделок по ID пользователя и номеру телефона клиента
        $userDeals = Deal::where('user_id', $user->id)
                        ->orWhere('client_phone', $user->phone)
                        ->orderBy('created_at', 'desc')
                        ->get();
        
        Log::info("Найдено сделок по ID пользователя {$user->id}: {$userDeals->count()}");
        
        return view('user_deal_view', compact('userDeals', 'title_site'));
    }

    /**
     * Показывает детальную информацию о сделке для пользователя.
     *
     * @param  \App\Models\Deal  $deal
     * @return \Illuminate\View\View
     */
    public function viewDeal(Deal $deal)
    {
        // Получаем текущего пользователя
        $user = auth()->user();
        
        // Проверка доступа: пользователь должен быть владельцем сделки 
        // ИЛИ номер телефона пользователя должен совпадать с номером клиента в сделке
        if (auth()->id() !== $deal->user_id && $user->phone !== $deal->client_phone) {
            abort(403, 'Доступ запрещен.');
        }

        $title_site = "Детали сделки #" . $deal->id . " | Личный кабинет Экспресс-дизайн";
        $userDeals = collect([$deal]); // Создаем коллекцию из одной текущей сделки для совместимости с шаблоном

        return view('user_deal', compact('deal', 'userDeals', 'user', 'title_site'));
    }
}
