<?php

namespace App\Providers;

use App\Views\Composers\BriefTypesComposer;
use App\Views\Composers\UsersComposer;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        view()->composer(['chats.index'], UsersComposer::class);
        view()->composer(['briefs.index'], BriefTypesComposer::class);
    }
}
