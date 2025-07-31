<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

/**
 * API контроллер для AJAX обновления сделки
 */
class AjaxDealController extends Controller
{
    public function updateDeal($id, Request $request)
    {
        // Делегируем обработку запроса стандартному контроллеру
        return app(DealsController::class)->updateDeal($request, $id);
    }
}
