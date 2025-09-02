<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


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
    
    // API для работы с клиентскими данными
    Route::prefix('deal-clients')->group(function () {
        Route::get('/{dealId}', 'App\Http\Controllers\Api\DealClientController@show');
        Route::post('/', 'App\Http\Controllers\Api\DealClientController@createOrUpdate');
        Route::delete('/{dealId}', 'App\Http\Controllers\Api\DealClientController@destroy');
        Route::get('/search/clients', 'App\Http\Controllers\Api\DealClientController@search');
        Route::get('/statistics/all', 'App\Http\Controllers\Api\DealClientController@statistics');
    });
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
