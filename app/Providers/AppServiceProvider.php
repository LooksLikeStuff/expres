<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Добавьте этот импорт
use Illuminate\Support\Facades\Schema;
use App\Services\ChatService;
use App\Services\MessageService;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
     
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Устанавливаем длину строки по умолчанию для совместимости с MySQL < 5.7.7
        Schema::defaultStringLength(191);

        // Проверим и создадим директории для шаблонов админки, если они не существуют
        $viewPaths = [
            resource_path('views/admin'),
            resource_path('views/layouts'),
        ];
        
        foreach ($viewPaths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }
    }
}
