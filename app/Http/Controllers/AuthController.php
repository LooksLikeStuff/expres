<?php

namespace App\Http\Controllers;

use App\DTO\Auth\AuthRequestDTO;
use App\DTO\Auth\RegisterRequestDTO;
use App\Enums\UserStatus;
use App\Http\Requests\Auth\LoginCodeRequest;
use App\Http\Requests\Auth\LoginPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendCodeRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function showLoginFormByPassword()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        return view('auth.login-password');
    }

    public function loginByPassword(LoginPasswordRequest $request): RedirectResponse
    {
        if ($this->authService->loginByPassword(AuthRequestDTO::fromLoginPasswordRequest($request))) {
            return redirect()->route('home');
        }

        return redirect()->back()->withErrors(['phone' => 'Неверный номер телефона или пароль.']);
    }

    public function showLoginFormByCode()
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        return view('auth.login-code');
    }

    public function loginByCode(LoginCodeRequest $request): RedirectResponse
    {
        if ($this->authService->loginByCode(AuthRequestDTO::fromLoginCodeRequest($request))) {
            return redirect()->route('home');
        }

        return redirect()->back()->withErrors(['code' => 'Неверный код.']);
    }

    public function sendCode(SendCodeRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');

        if (!$this->authService->isUserExists($phone)) {
            return response()->json(['error' => 'Пользователь с таким номером не найден.'], 400);
        }

        if ($this->authService->sendVerificationCode($phone)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Ошибка при отправке кода.'], 500);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        //Регистрируем пользователя
        $this->authService->register(RegisterRequestDTO::fromRegisterRequest($request));

        //Редиректим на главную
        return redirect()->route('home');
    }

    public function logout(): RedirectResponse
    {
        $this->authService->logout();
        Session::flush();
        Session::regenerateToken();
        return redirect('/');
    }

    public function registerByDealLink(string $token)
    {
        if (auth()->check()) {
            return redirect()->route('home');
        }

        // TODO: Добавить DealService для проверки токена
        $deal = null; // Deal::where('registration_token', $token)->where('registration_token_expiry', '>', now())->first();

        if (!$deal) {
            return redirect()->route('login.password')->with('error', 'Ссылка на регистрацию устарела или неверна.');
        }

        return view('auth.register_by_deal', compact('deal'));
    }

    public function completeRegistrationByDeal(Request $request, string $token): RedirectResponse
    {
        // TODO: Добавить DealService для работы со сделками
        // Логика будет перенесена в AuthService

        return redirect()->route('home')->with('success', 'Вы успешно зарегистрированы и привязаны к сделке.');
    }

    public function showRegistrationFormForExecutors()
    {
        $roles = UserStatus::executors();

        return view('auth.register_executor', compact('roles'));
    }

    public function registerExecutor(RegisterRequest $request): RedirectResponse
    {
        $user = $this->authService->registerExecutor(RegisterRequestDTO::fromRegisterRequest($request));

        return redirect()->route('home')->with('success', 'Вы успешно зарегистрированы как ' . $user->status . '.');
    }
}

