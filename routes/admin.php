<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BriefController;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Маршруты Администратора
|--------------------------------------------------------------------------
|
| Здесь определены все маршруты для административной панели.
| Все маршруты загружаются через RouteServiceProvider.
|
*/

// Группа маршрутов админки с проверкой роли admin
Route::middleware(['auth', 'status:admin'])->group(function () {
    // Дашборд
    Route::get('/', [DashboardController::class, 'index'])->name('admin');
    Route::get('/analytics/data', [DashboardController::class, 'getAnalyticsData'])->name('admin.analytics.data');
    Route::get('/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('admin.dashboard-data');
    
    // Управление сделками
    Route::get('/deals', [DashboardController::class, 'dealsList'])->name('admin.deals');
    Route::get('/deals/stats', [DashboardController::class, 'dealsStats'])->name('admin.deals.stats');
    
    // Управление сметами
    Route::get('/estimates', [DashboardController::class, 'estimatesList'])->name('admin.estimates');
    
    // Управление пользователями
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    
    // Изменяем имя маршрута с user.briefs на admin.user.briefs для согласованности с шаблоном
    Route::get('/users/{id}/briefs', [UserController::class, 'userBriefs'])->name('admin.user.briefs');
    
    // Добавляем новые маршруты для удаленных пользователей
    Route::get('/users/trashed', [UserController::class, 'trashed'])->name('admin.users.trashed');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('admin.users.force-delete');
    
    // Добавьте маршрут для редактирования пользователя, если его не существует:
    Route::get('/user/{user}/edit', 'UserController@edit')->name('user.edit');
    
    // Маршрут для просмотра брифов пользователя
    Route::get('/users/{user}/briefs', 'BriefController@userBriefs')->name('user.briefs');
    
    // Управление брифами - группировка для лучшей организации
    Route::prefix('brief')->name('admin.brief.')->group(function() {
        // Общие брифы
        Route::get('/common/{id}/edit', [BriefController::class, 'editCommon'])->name('common.edit');
        Route::put('/common/{id}', [BriefController::class, 'updateCommon'])->name('common.update');
        Route::delete('/common/{id}', [BriefController::class, 'destroyCommon'])->name('common.destroy');
        
        // Коммерческие брифы
        Route::get('/commercial/{id}/edit', [BriefController::class, 'editCommercial'])->name('commercial.edit');
        Route::put('/commercial/{id}', [BriefController::class, 'updateCommercial'])->name('commercial.update');
        Route::delete('/commercial/{id}', [BriefController::class, 'destroyCommercial'])->name('commercial.destroy');
    });
    
    // Добавляем явные маршруты для обратной совместимости с существующими представлениями
    Route::get('/brief/common/edit/{id}', [BriefController::class, 'editCommon'])->name('admin.brief.editCommon');
    Route::get('/brief/commercial/edit/{id}', [BriefController::class, 'editCommercial'])->name('admin.brief.editCommercial');
    
    // Добавляем явные маршруты для форм обновления
    Route::put('/brief/commercial/update/{id}', [BriefController::class, 'updateCommercial'])->name('admin.brief.updateCommercial');
    Route::put('/brief/common/update/{id}', [BriefController::class, 'updateCommon'])->name('admin.brief.updateCommon');
    
    // Добавляем конкретный маршрут в соответствии с адресом из формы
    Route::put('/brief/commercial/{id}', [BriefController::class, 'updateCommercial'])->name('admin.brief.commercial.update');
    Route::put('/brief/common/{id}', [BriefController::class, 'updateCommon'])->name('admin.brief.common.update');
    
    // Управление наградами
    Route::prefix('awards')->name('admin.awards.')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\AwardController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\AwardController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\AwardController::class, 'store'])->name('store');
        Route::get('/{award}/edit', [App\Http\Controllers\Admin\AwardController::class, 'edit'])->name('edit');
        Route::put('/{award}', [App\Http\Controllers\Admin\AwardController::class, 'update'])->name('update');
        Route::delete('/{award}', [App\Http\Controllers\Admin\AwardController::class, 'destroy'])->name('destroy');
        
        // Выдача/отзыв наград у пользователей
        Route::get('/user/{user}', [App\Http\Controllers\Admin\AwardController::class, 'showAwardForm'])->name('user.form');
        Route::post('/user/{user}', [App\Http\Controllers\Admin\AwardController::class, 'awardUser'])->name('user.give');
        Route::delete('/user/{user}/{award}', [App\Http\Controllers\Admin\AwardController::class, 'revokeAward'])->name('user.revoke');
    });
    
    // Настройки системы
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
});
