<?php

use App\Http\Controllers\Chats\FCMTokenController;
use App\Http\Controllers\Chats\MessageController;
use App\Http\Controllers\Chats\ReadReceiptController;
use App\Http\Controllers\Chats\UserChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\ClientDealsController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SmetsController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Chats\ChatController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BrifsController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\DealFeedController;

use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\BriefSearchController;

// Главная страница
Route::get('/', function () {
    return redirect('login/password');
});
Route::get('/login', function () {
    return redirect('login/password');
});
Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/reply/{ticket}', [SupportController::class, 'reply'])->name('support.reply');
    Route::post('/support/create', [SupportController::class, 'create'])->name('support.create');

    // Убрать дублирование - оставляем один маршрут с именем profile.index
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/view/{id}', [ProfileController::class, 'viewProfile'])->name('profile.view');
    Route::post('/profile/avatar/update', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::post('/profile/send-code', [ProfileController::class, 'sendVerificationCode'])->name('profile.send-code');
    Route::post('/profile/verify-code', [ProfileController::class, 'verifyCode'])->name('profile.verify-code');
    Route::post('/delete-account', [ProfileController::class, 'deleteAccount'])->name('delete_account');
    Route::post('/profile/update-all', [ProfileController::class, 'updateProfileAll'])->name('profile.update_all');
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    Route::get('/brifs', [BrifsController::class, 'index'])->name('brifs.index');
    Route::post('/brifs/store', [BrifsController::class, 'store'])->name('brifs.store');
    Route::delete('/brifs/{brif}', [BrifsController::class, 'destroy'])->name('brifs.destroy');

    Route::get('/common/questions/{id}/{page}', [CommonController::class, 'questions'])->name('common.questions');
    Route::post('/common/questions/{id}/{page}', [CommonController::class, 'saveAnswers'])->name('common.saveAnswers');
    Route::get('/common/create', [BrifsController::class, 'common_create'])->name('common.create');
    Route::post('/common', [BrifsController::class, 'common_store'])->name('common.store');
    Route::get('/common/{id}', [BrifsController::class, 'common_show'])->name('common.show');
    Route::get('/common/{id}/download-pdf', [BrifsController::class, 'common_download_pdf'])->name('common.download.pdf');

    Route::get('/commercial/questions/{id}/{page}', [CommercialController::class, 'questions'])->name('commercial.questions');
    Route::post('/commercial/questions/{id}/{page}', [CommercialController::class, 'saveAnswers'])->name('commercial.saveAnswers');
    Route::get('/commercial/create', [BrifsController::class, 'commercial_create'])->name('commercial.create');
    Route::post('/commercial', [BrifsController::class, 'commercial_store'])->name('commercial.store');
    Route::get('/commercial/{id}', [BrifsController::class, 'commercial_show'])->name('commercial.show');
    Route::get('/commercial/{id}/download-pdf', [BrifsController::class, 'commercial_download_pdf'])->name('commercial.download.pdf');

    // Сделки для клиентов - используем новый контроллер
    Route::get('/deal-user', [ClientDealsController::class, 'dealUser'])->name('user_deal');
    Route::get('/deal/{deal}/view', [ClientDealsController::class, 'viewDeal'])->name('deal.view');

    // Маршрут для удаления файла из брифа
    Route::post('/common/{id}/delete-file', [CommonController::class, 'deleteFile'])->name('common.delete-file');

    // Маршрут для удаления файла из коммерческого брифа
    Route::post('/commercial/{id}/delete-file', [CommercialController::class, 'deleteFile'])->name('commercial.delete-file');

    // Маршрут для пропуска страницы в брифе
    Route::post('/common/{id}/skip/{page}', [CommonController::class, 'skipPage'])->name('common.skipPage');

    // Редактирование общего брифа
    Route::get('/common/{id}/edit/start', [CommonController::class, 'startEdit'])->name('common.startEdit');
    Route::post('/common/{id}/update', [CommonController::class, 'update'])->name('common.update');
    Route::get('/common/{id}/history', [BrifsController::class, 'commonHistory'])->name('common.history');
});

// Маршруты для рейтингов исполнителей - убираем дублирование
Route::prefix('ratings')->middleware(['auth'])->group(function () {
    Route::post('/store', [RatingController::class, 'store'])->name('ratings.store');
    Route::get('/check-pending', [RatingController::class, 'checkPendingRatings'])->name('ratings.check-pending');
    Route::get('/check-completed', [RatingController::class, 'checkAllRatingsComplete'])->name('ratings.check-completed');
    Route::get('/specialists', [App\Http\Controllers\RatingViewController::class, 'index'])->name('ratings.specialists');
    Route::get('/specialists/{id}', [App\Http\Controllers\RatingViewController::class, 'show'])->name('ratings.show');
    Route::get('/specialists/{id}/ratings', [App\Http\Controllers\RatingViewController::class, 'getSpecialistRatings'])->name('ratings.specialist.ratings');
    Route::get('/statistics', [App\Http\Controllers\RatingViewController::class, 'getRatingsStatistics'])->name('ratings.statistics');
    Route::get('/find-completed-deals', [RatingController::class, 'findCompletedDealsNeedingRatings'])->name('ratings.find-completed-deals');

    // Демо страница для тестирования улучшений рейтинга (только для разработки)
    Route::get('/demo', function () {
        return view('demo.rating-test');
    })->name('ratings.demo');
});

Route::middleware(['auth', 'status:partner'])->group(function () {
    Route::get('/estimate', [SmetsController::class, 'estimate'])->name('estimate');
    Route::get('/estimate/service', [SmetsController::class, 'allService'])->name('estimate.service');
    Route::get('/estimate/default', [SmetsController::class, 'defaultValueBD'])->name('estimate.default');
    Route::get('/estimate/create/{id?}', [SmetsController::class, 'createEstimate'])->name('estimate.create');
    Route::post('/estimate/createcoefs', [SmetsController::class, 'addCoefs'])->name('estimate.createcoefs');
    Route::post('/estimate/save/{id?}', [SmetsController::class, 'saveEstimate'])->name('estimate.save');
    Route::post('/estimate/pdf/{id?}', [SmetsController::class, 'savePdf'])->name('estimate.pdf');
    Route::post('/estimate/del/{id}', [SmetsController::class, 'delEstimate'])->name('estimate.del');
    Route::post('/estimate/chenge/{id}/{slot}/{value}/{type}/{stage}', [SmetsController::class, 'changeService'])->name('estimate.change');
    Route::get('/estimate/preview', [SmetsController::class, 'previewEstimate'])->name('estimate.preview');
    Route::get('/estimate/defaultServices', [SmetsController::class, 'defaultServices'])->name('estimate.defaultServices');
    Route::get('/estimate/copy/{id?}', [SmetsController::class, 'copyEstimate'])->name('estimate.copy');
    Route::get('/estimate/change-estimate/{id?}', [SmetsController::class, 'changeEstimate'])->name('estimate.changeEstimate');
});

Route::middleware(['auth', 'status:coordinator,admin,partner,visualizer,architect,designer'])->group(function () {
    Route::get('/deal-cardinator', [DealsController::class, 'dealCardinator'])->name('deal.cardinator');
    Route::get('/deal/{id}/data', [DealsController::class, 'getDealData'])->name('deal.data');
});

// Отдельная страница редактирования сделки - только для coordinator, admin, partner
Route::middleware(['auth', 'status:coordinator,admin,partner'])->group(function () {
    Route::get('/deal/{deal}/edit-page', [DealsController::class, 'editDealPage'])->name('deal.edit-page');
});


Route::middleware(['auth', 'status:coordinator,admin,partner'])->group(function () {
    Route::get('/deals/create', [DealsController::class, 'createDeal'])->name('deals.create');
    Route::post('/deal/store', [DealsController::class, 'storeDeal'])->name('deals.store');

    // Единый маршрут для обновления сделки, поддерживающий и PUT и POST
    Route::match(['put', 'post'], '/deal/update/{id}', [DealsController::class, 'updateDeal'])->name('deal.update');

    Route::post('/deal/create-from-brief', [DealsController::class, 'createDealFromBrief'])->name('deals.create-from-brief');

    // Маршруты для поиска и привязки брифов
    Route::middleware(['auth'])->group(function () {
        // Маршрут для поиска брифов по телефону клиента
        Route::post('/deal/find-brief', [App\Http\Controllers\DealsController::class, 'findBriefByPhone'])->name('deal.find-brief');

        // Маршрут для привязки брифа к сделке
        Route::post('/deal/link-brief', [App\Http\Controllers\DealsController::class, 'linkBriefToDeal'])->name('deal.link-brief');

        // Маршрут для отвязки брифа от сделки
        Route::post('/deal/unlink-brief', [App\Http\Controllers\DealsController::class, 'unlinkBriefFromDeal'])->name('deal.unlink-brief');
    });
});

Route::middleware(['auth', 'status:coordinator,admin'])->group(function () {
    Route::get('/deal/change-logs', [DealsController::class, 'changeLogs'])->name('deal.change_logs');
    Route::get('/deal/{deal}/change-logs', [DealsController::class, 'changeLogsForDeal'])->name('deal.change_logs.deal');
});

Route::post('/deal/{deal}/feed', [DealFeedController::class, 'store'])
    ->name('deal.feed.store');

Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');

Route::prefix('admin')->group(base_path('routes/admin.php'));

Route::get('/register_by_deal/{token}', [AuthController::class, 'registerByDealLink'])->name('register_by_deal');
Route::post('/complete-registration-by-deal/{token}', [AuthController::class, 'completeRegistrationByDeal'])->name('auth.complete_registration_by_deal');
Route::get('', [AuthController::class, 'showLoginFormByPassword'])->name('login.password');
Route::post('login/password', [AuthController::class, 'loginByPassword'])->name('login.password.post');
Route::get('login/code', [AuthController::class, 'showLoginFormByCode'])->name('login.code');
Route::post('login/code', [AuthController::class, 'loginByCode'])->name('login.code.post');
Route::post('/send-code', [AuthController::class, 'sendCode'])->name('send.code');
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

Route::get('/register/executor', [AuthController::class, 'showRegistrationFormForExecutors'])->name('register.executor');
Route::post('/register/executor', [AuthController::class, 'registerExecutor'])->name('register.executor.post');



// Если URL без id, перенаправляем на карточку сделок
Route::get('/deal/update', function () {
    return redirect()->route('deal.cardinator');
});

// Маршрут для обновления профиля пользователя администратором
Route::middleware(['auth', 'status:admin'])->group(function () {
    Route::post('/admin/profile/update/{id}', [ProfileController::class, 'updateUserProfileByAdmin'])
        ->name('profile.admin.update');
});

// Маршрут для поиска пользователей через AJAX
Route::get('/search-users', function (Illuminate\Http\Request $request) {
    $query = $request->input('q');
    $role = $request->input('role');
    $status = $request->input('status');

    $users = \App\Models\User::where('name', 'like', '%' . $query . '%')
        ->when($role, function ($q) use ($role) {
            return $q->where('role', $role);
        })
        ->when($status, function ($q) use ($status) {
            if (is_array($status)) {
                return $q->whereIn('status', $status);
            } else {
                return $q->where('status', $status);
            }
        })
        ->limit(30)
        ->get(['id', 'name', 'email', 'status', 'avatar_url']);

    return response()->json($users);
})->middleware('auth')->name('search.users');

// Маршрут для поиска специалистов через AJAX
Route::get('/search-specialists', function (Illuminate\Http\Request $request) {
    try {
        $query = $request->input('q', '');
        $role = $request->input('role');

        // Определяем соответствие ролей с учетом того, что используется поле status
        $roleMapping = [
            'designer' => 'designer',
            'architect' => 'architect',
            'visualizer' => 'visualizer',
            'constructor' => 'constructor',
            'manager' => 'coordinator',
            'partner' => 'partner'
        ];

        $searchStatus = isset($roleMapping[$role]) ? $roleMapping[$role] : $role;

        // Строим запрос
        $queryBuilder = \App\Models\User::query();

        // Поиск по имени если указан запрос
        if (!empty($query)) {
            $queryBuilder->where('name', 'like', '%' . $query . '%');
        }

        // Фильтр по статусу если указан
        if (!empty($searchStatus)) {
            $queryBuilder->where('status', $searchStatus);
        }

        $specialists = $queryBuilder
            ->orderByRaw('rating IS NULL, rating DESC') // Сначала с рейтингом, потом без
            ->limit(30)
            ->get(['id', 'name', 'email', 'rating', 'status']);

        // Форматируем данные для Select2
        $formattedSpecialists = $specialists->map(function ($specialist) {
            return [
                'id' => $specialist->id,
                'text' => $specialist->name,
                'email' => $specialist->email,
                'rating' => round($specialist->rating ?? 0, 1),
                'role' => $specialist->status // Используем status как role для фронтенда
            ];
        });

        \Illuminate\Support\Facades\Log::info('Поиск специалистов', [
            'query' => $query,
            'role' => $role,
            'searchStatus' => $searchStatus,
            'found' => $specialists->count()
        ]);

        return response()->json($formattedSpecialists);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Ошибка поиска специалистов: ' . $e->getMessage());
        return response()->json(['error' => 'Ошибка поиска специалистов'], 500);
    }
})->middleware('auth')->name('search.specialists');

// Маршрут для получения данных пользователя по ID
Route::get('/get-user/{id}', function ($id) {
    $user = \App\Models\User::find($id, ['id', 'name', 'email', 'status', 'avatar_url']);
    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }
    return response()->json($user);
})->middleware('auth')->name('get.user');

// Маршрут для проверки существования сделки
Route::get('/deal/{id}/exists', function ($id) {
    $exists = \App\Models\Deal::where('id', $id)->exists();
    return response()->json(['exists' => $exists]);
})->middleware('auth')->name('deal.exists');

// Маршрут для удаления сделки (только для администратора)
Route::delete('/deal/{deal}/delete', [DealsController::class, 'deleteDeal'])
    ->middleware(['auth', 'status:admin'])
    ->name('deal.delete');

// Маршрут для загрузки городов в формате JSON для Select2
Route::get('/cities.json', function () {
    // Проверим наличие файла
    $path = public_path('cities.json');
    if (file_exists($path)) {
        return response()->file($path);
    }

    // Если файла нет, вернём пустой массив
    return response()->json([]);
});if (app()->environment('production')) {
    URL::forceScheme('https');
}

// Маршруты для работы с брифами в сделках
Route::post('/find-briefs-by-user-id', [App\Http\Controllers\DealsController::class, 'findBriefsByUserId'])->middleware('auth');
Route::post('/link-brief-to-deal', [App\Http\Controllers\DealsController::class, 'linkBriefToDeal'])->middleware('auth');
Route::post('/unlink-brief-from-deal', [App\Http\Controllers\DealsController::class, 'unlinkBriefFromDeal'])->middleware('auth');

Route::post('/deal/{id}/upload-documents', [App\Http\Controllers\DealsController::class, 'uploadDocuments'])->name('deal.upload.documents')->middleware('auth');

// Роут для быстрой загрузки на Яндекс.Диск
Route::post('/upload/fast-yandex', [App\Http\Controllers\DealsController::class, 'fastYandexUpload'])->name('upload.fast.yandex')->middleware('auth');

// Публичные маршруты для удаления аккаунта (убрана страница delete-info)
Route::get('/account/delete', [AccountController::class, 'showDeleteAccountForm'])->name('account.delete');
Route::post('/account/delete/send-code', [AccountController::class, 'sendDeleteAccountCode'])->name('account.delete.send-code');
Route::post('/account/delete/verify-code', [AccountController::class, 'verifyDeleteAccountCode'])->name('account.delete.verify-code');

// Маршруты для работы с документами сделок
Route::middleware(['auth'])->group(function () {
    Route::get('/deals/{deal}/documents/{filename}/download', [App\Http\Controllers\DealDocumentController::class, 'downloadDocument'])
        ->name('deal.document.download');
    Route::get('/deals/{deal}/documents/{filename}/view', [App\Http\Controllers\DealDocumentController::class, 'viewDocument'])
        ->name('deal.document.view');
    Route::delete('/deals/{deal}/documents/{filename}', [App\Http\Controllers\DealDocumentController::class, 'deleteDocument'])
        ->name('deal.document.delete');
});

// Тестовые страницы для отладки
Route::get('/test/document-system', function () {
    return view('test.document-test');
})->name('test.document.system')->middleware('auth');




//Chats
Route::middleware('auth')->group(function () {
    Route::prefix('chats')->controller(ChatController::class)->group(function () {
        Route::get('/', 'index')->name('chats.index');
        Route::post('{chat}/', 'show')->name('chats.show');
        Route::post('{chatId}/messages', 'getMessages')->name('chats.messages');
        Route::post('/', 'store')->name('chats.store');

        //Admin
        Route::get('/{userId}', 'showUserChats')->middleware('admin')->name('chats.show-user-chats');
    });

    Route::resource('messages', MessageController::class);
    Route::post('messages/search', [MessageController::class, 'search'])->name('messages.search');
    Route::get('messages/{messageId}/page', [MessageController::class, 'getPageOfMessages'])->name('messages.page');

    Route::controller(ReadReceiptController::class)->prefix('readReceipts')->group(function () {
        Route::patch('read', 'readMessage');
    });

    Route::delete('/userChats/{chatId}/users/remove', [UserChatController::class, 'removeUserFromChat']);
    // routes/api.php или web.php
    Route::post('/fcm/register', [FCMTokenController::class, 'store'])->middleware('auth');
});
