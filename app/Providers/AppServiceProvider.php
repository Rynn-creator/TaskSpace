<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('partials.sidebar', function ($view): void {
            $unansweredFeedbackCount = Auth::check() && Auth::user()->isAdmin()
                ? Feedback::whereNull('admin_reply')->count()
                : 0;
            $pendingPasswordResetCount = Auth::check() && Auth::user()->isAdmin()
                ? PasswordResetRequest::where('status', 'pending')->count()
                : 0;

            $view->with([
                'unansweredFeedbackCount' => $unansweredFeedbackCount,
                'pendingPasswordResetCount' => $pendingPasswordResetCount,
            ]);
        });
    }
}
