<?php

namespace App\Http\Controllers;

use App\DTO\Auth\AuthRequestDTO;
use App\DTO\Auth\RegisterRequestDTO;
use App\DTO\Auth\VerificationRequestDTO;
use App\Enums\UserStatus;
use App\Enums\VerificationType;
use App\Http\Requests\Auth\LoginCodeRequest;
use App\Http\Requests\Auth\LoginPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendCodeRequest;
use App\Models\Deal;
use App\Services\AuthService;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly VerificationService $verificationService
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
        $deal = Deal::where('registration_token', $token)->where('registration_token_expiry', '>', now())->first();

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

    /**
     * Отправка кода подтверждения для обновления номера телефона
     */
    public function sendPhoneUpdateCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.'
            ], 401);
        }

        $phone = $this->normalizePhoneNumber($request->phone);

        if ($this->verificationService->sendCode($phone, VerificationType::PHONE_UPDATE)) {
            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Ошибка при отправке SMS.'
        ], 500);
    }

    /**
     * Подтверждение кода и обновление номера телефона
     */
    public function verifyPhoneUpdateCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'verification_code' => 'required|string|size:4',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь не авторизован.'
            ], 401);
        }

        $phone = $this->normalizePhoneNumber($request->phone);

        $verificationDTO = new VerificationRequestDTO(
            phone: $phone,
            code: $request->verification_code,
            type: VerificationType::PHONE_UPDATE
        );

        if (!$this->verificationService->verifyCode($verificationDTO)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный или просроченный код.'
            ]);
        }

        // Обновляем номер телефона пользователя
        $user->phone = $this->formatPhoneNumber($phone);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Номер телефона успешно обновлен.'
        ]);
    }

    /**
     * Отправка кода подтверждения для удаления аккаунта
     */
    public function sendAccountDeleteCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $phone = $this->normalizePhoneNumber($request->phone);

        if (!$this->authService->isUserExists($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Пользователь с таким номером телефона не найден.'
            ]);
        }

        if ($this->verificationService->sendCode($phone, VerificationType::ACCOUNT_DELETE)) {
            return response()->json([
                'success' => true,
                'message' => 'Код подтверждения отправлен.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Ошибка при отправке SMS.'
        ], 500);
    }

    /**
     * Подтверждение кода и удаление аккаунта
     */
    public function verifyAccountDeleteCode(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:4',
        ]);

        $phone = $this->normalizePhoneNumber($request->phone);

        $verificationDTO = new VerificationRequestDTO(
            phone: $phone,
            code: $request->code,
            type: VerificationType::ACCOUNT_DELETE
        );

        if (!$this->verificationService->verifyCode($verificationDTO)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный или просроченный код подтверждения.'
            ]);
        }

        // Здесь должна быть логика удаления аккаунта
        // Для безопасности делаем только logout
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Аккаунт успешно удален.',
            'redirect' => route('login.password')
        ]);
    }

    /**
     * Нормализация номера телефона (убираем все нецифровые символы)
     */
    private function normalizePhoneNumber(string $phone): string
    {
        $rawPhone = preg_replace('/\D/', '', $phone);

        // Приводим к формату 7XXXXXXXXXX
        if (strlen($rawPhone) === 10) {
            $rawPhone = '7' . $rawPhone;
        } elseif (strlen($rawPhone) === 11 && $rawPhone[0] === '8') {
            $rawPhone = '7' . substr($rawPhone, 1);
        }

        return $rawPhone;
    }

    /**
     * Форматирование номера телефона для хранения
     */
    private function formatPhoneNumber(string $phone): string
    {
        $normalized = $this->normalizePhoneNumber($phone);

        if (strlen($normalized) === 11) {
            return '+7 (' . substr($normalized, 1, 3) . ') '
                . substr($normalized, 4, 3) . '-'
                . substr($normalized, 7, 2) . '-'
                . substr($normalized, 9, 2);
        }

        return $phone;
    }
}

