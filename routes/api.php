<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AdminController;
use App\Http\Middleware\CheckChatAccess;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Простые утилитарные эндпоинты для оптимизации соединений
Route::match(['GET', 'HEAD'], '/ping', function () {
    return response()->json(['status' => 'ok', 'timestamp' => time()]);
});

Route::match(['GET', 'HEAD'], '/keepalive', function () {
    return response()->json(['status' => 'alive', 'timestamp' => time()]);
});

// Маршруты для чата с защитой аутентификации 
// Проверка аутентификации через web (cookie-сессии)
// Route::middleware(['auth:web', CheckChatAccess::class])->group(function () {
//     // Базовые маршруты чата
//     Route::get('/contacts', [ChatController::class, 'getContacts']);
//     Route::get('/chats/{id}/messages', [ChatController::class, 'getMessages']);
//     Route::get('/chats/{id}/new-messages', [ChatController::class, 'getNewMessages']);
//     Route::post('/chats/{id}/messages', [ChatController::class, 'sendMessage']);
//     Route::get('/unread-count', [ChatController::class, 'getUnreadCount']);
    
//     // Маршруты для групповых чатов
//     Route::get('/chat-groups', [ChatController::class, 'getChatGroups']);
//     Route::post('/chat-groups', [ChatController::class, 'createChatGroup']);
//     Route::get('/chat-groups/{id}', [ChatController::class, 'getChatGroup']);
//     Route::put('/chat-groups/{id}', [ChatController::class, 'updateChatGroup']);
//     Route::delete('/chat-groups/{id}', [ChatController::class, 'deleteChatGroup']);
//     Route::get('/chat-groups/{id}/messages', [ChatController::class, 'getGroupMessages']);
//     Route::get('/chat-groups/{id}/new-messages', [ChatController::class, 'getNewGroupMessages']);
//     Route::post('/chat-groups/{id}/messages', [ChatController::class, 'sendGroupMessage']);
//     Route::post('/chat-groups/{id}/users', [ChatController::class, 'addChatGroupUser']);
//     Route::delete('/chat-groups/{id}/users/{user_id}', [ChatController::class, 'removeChatGroupUser']);
    
//     // Проверка новых сообщений
//     Route::get('/chats/check-new-messages', [ChatController::class, 'checkNewMessagesInChats']);
//     Route::get('/chat-groups/check-new-messages', [ChatController::class, 'checkNewMessagesInGroups']);
    
//     // Поиск сообщений
//     Route::get('/messages/search', [ChatController::class, 'searchMessages']);

// Маршруты для поиска и привязки брифов к сделкам
Route::middleware('auth:web')->group(function () {
    // Поиск брифов по номеру телефона клиента
    Route::post('/deals/{deal}/search-briefs', 'App\Http\Controllers\BriefSearchController@findBriefsByClientPhone');
    // Привязка брифа к сделке
    Route::post('/deals/{deal}/attach-brief', 'App\Http\Controllers\BriefSearchController@linkBriefToDeal');
    // Отвязка брифа от сделки
    Route::post('/deals/{deal}/detach-brief', 'App\Http\Controllers\BriefSearchController@unlinkBriefFromDeal');
    
    // Загрузка документов
    Route::post('/upload-documents', 'App\Http\Controllers\DocumentUploadController@uploadDocuments');
});

// Новая система загрузки файлов на Яндекс.Диск v3.0 (без дополнительной аутентификации)
Route::middleware('auth:web')->group(function () {
    Route::post('/yandex-disk/upload', 'App\Http\Controllers\Api\YandexDiskController@upload');
    Route::post('/yandex-disk/delete', 'App\Http\Controllers\Api\YandexDiskController@delete');
    Route::get('/yandex-disk/info', 'App\Http\Controllers\Api\YandexDiskController@info');
    Route::get('/yandex-disk/health', 'App\Http\Controllers\Api\YandexDiskController@health');
    
    // Специальный маршрут для загрузки файлов сделок
    Route::post('/deals/upload-yandex', 'App\Http\Controllers\DealsController@uploadFileToYandex');
    
    // API для получения данных сделки
    Route::get('/deals/{deal}/data', 'App\Http\Controllers\DealsController@getDealData');
});
// });

// API маршруты для админки
Route::middleware(['auth', 'status:admin'])->prefix('admin')->group(function () {
    // Получение статистики для графиков
    Route::get('/stats/users', [AdminController::class, 'getUsersStats']);
    Route::get('/stats/briefs', [AdminController::class, 'getBriefsStats']);
    Route::get('/stats/deals', [AdminController::class, 'getDealsStats']);
    Route::get('/stats/estimates', [AdminController::class, 'getEstimatesStats']);
    
    // Получение данных для дашборда
    Route::get('/dashboard', [AdminController::class, 'getDashboardData']);
    
    // Экспорт данных
    Route::get('/export/users', [AdminController::class, 'exportUsers']);
    Route::get('/export/deals', [AdminController::class, 'exportDeals']);
});
