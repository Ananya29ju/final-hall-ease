<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Hall;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        Paginator::useBootstrapFive();

        View::composer('layouts.sections.navbar.navbar-partial', function ($view) {
            $campusGroups = collect();
            $hallsTable = (new Hall())->getTable();

            if (Schema::hasTable($hallsTable)) {
                $campusGroups = Hall::query()
                    ->select('campus_name', 'location', 'name')
                    ->whereNotNull('campus_name')
                    ->where('campus_name', '!=', '')
                    ->orderBy('campus_name')
                    ->orderBy('location')
                    ->orderBy('name')
                    ->get()
                    ->groupBy('campus_name')
                    ->map(function ($campusRows) {
                        return $campusRows
                            ->groupBy(function ($hall) {
                                return filled($hall->location) ? $hall->location : 'Unassigned Block';
                            })
                            ->map(function ($blockRows) {
                                return $blockRows
                                    ->pluck('name')
                                    ->filter(fn ($name) => filled($name))
                                    ->unique()
                                    ->values();
                            })
                            ->sortKeys();
                    });
            }

            $view->with('campus_groups', $campusGroups);
        });

        View::composer('layouts.sections.menu.verticalMenu', function ($view) {
            $notificationCount = 0;
            $pendingVerifications = 0;
            if (Auth::check()) {
                $notificationCount = Auth::user()->unreadNotifications->count();
                $pendingVerifications = \App\Models\User::where('role', 'media')->where('status', 'pending')->count();
            }
            $view->with('admin_notification_count', $notificationCount);
            $view->with('pending_verification_count', $pendingVerifications);
        });

        View::composer('layouts.sections.menu.userVerticalMenu', function ($view) {
            $notificationCount = 0;
            if (Auth::check()) {
                $notificationCount = Auth::user()->unreadNotifications->count();
            }
            $view->with('user_notification_count', $notificationCount);
        });

        View::composer('layouts.sections.menu.mediaVerticalMenu', function ($view) {
            $notificationCount = 0;
            if (Auth::check()) {
                $notificationCount = Auth::user()->unreadNotifications->count();
            }
            $view->with('media_notification_count', $notificationCount);
        });
    }
}
