<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.navigation', function ($view) {
            $count = auth()->check()
                ? auth()->user()
                    ->unreadNotifications()
                    ->count()
                : 0;

            $view->with('unreadNotificationCount', $count);
        });
    }
}
