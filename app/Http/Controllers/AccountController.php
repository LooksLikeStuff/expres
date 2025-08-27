<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    /**
     * Показывает страницу удаления аккаунта (с объединенной информацией)
     */
    public function showDeleteAccountForm()
    {
        $title_site = "Удаление аккаунта | Экспресс-дизайн";
        return view('account.delete', compact('title_site'));
    }

    /**
     * Отправляет код подтверждения на телефон для удаления аккаунта
     * @deprecated Используйте AuthController::sendAccountDeleteCode()
     */
    public function sendDeleteAccountCode(Request $request)
    {
        // Перенаправляем на новый метод в AuthController
        return app(AuthController::class)->sendAccountDeleteCode($request);
    }

    /**
     * Проверяет код подтверждения и "удаляет" аккаунт
     * @deprecated Используйте AuthController::verifyAccountDeleteCode()
     */
    public function verifyDeleteAccountCode(Request $request)
    {
        // Перенаправляем на новый метод в AuthController
        return app(AuthController::class)->verifyAccountDeleteCode($request);
    }
}
       