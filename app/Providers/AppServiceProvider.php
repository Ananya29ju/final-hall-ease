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
            $bookingsTable = (new Booking())->getTable();

            if (Schema::hasTable($bookingsTable) && Auth::check()) {
                $newBookingsQuery = Booking::query();
                $cancellationCount = 0;

                 if (Schema::hasColumn($bookingsTable, 'booking_status')) {
                    $newBookingsQuery->where('booking_status', 'pending');
                }

                if (Schema::hasColumn($bookingsTable, 'cancellation_reason')) {
                    $newBookingsQuery->where(function ($query) {
                        $query->whereNull('cancellation_reason')
                            ->orWhere('cancellation_reason', '');
                    });

                    $cancellationCount = Booking::query()
                        ->when(Schema::hasColumn($bookingsTable, 'booking_status'), function ($query) {
                            $query->where('booking_status', 'cancelled');
                        })
                        ->whereNotNull('cancellation_reason')
                        ->where('cancellation_reason', '!=', '')
                        ->count();
                }

                $newBookingsCount = $newBookingsQuery->count();
                $notificationCount = $newBookingsCount + $cancellationCount;
            }

            $view->with('admin_notification_count', $notificationCount);
        });

        View::composer('layouts.sections.menu.mediaVerticalMenu', function ($view) {
            $notificationCount = 0;
            $bookingsTable = (new Booking())->getTable();

            if (Schema::hasTable($bookingsTable) && Auth::check()) {
                $newBookingsQuery = Booking::query();

                if (Schema::hasColumn($bookingsTable, 'booking_status')) {
                    $newBookingsQuery->where('booking_status', 'confirmed');
                }

                if (Schema::hasColumn($bookingsTable, 'cancellation_reason')) {
                    $newBookingsQuery->where(function ($query) {
                        $query->whereNull('cancellation_reason')
                            ->orWhere('cancellation_reason', '');
                    });
                }

                $notificationCount = $newBookingsQuery->count();
            }

            $view->with('media_notification_count', $notificationCount);
        });
    }
}
