<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HallController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Media\DashboardController as MediaDashboardController;
use App\Http\Controllers\Media\NotificationController as MediaNotificationController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\HallController as UserHallController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use App\Http\Controllers\User\NotificationController as UserNotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;

// ============================================
// PUBLIC AUTHENTICATION ROUTES
// ============================================
Route::get('login', [LoginBasic::class, 'index'])->name('login');
Route::get('register', [RegisterBasic::class, 'index'])->name('register');
Route::post('login', [LoginBasic::class, 'store'])->name('login-store');
Route::post('logout', [LoginBasic::class, 'logout'])->name('logout');
Route::post('register', [RegisterBasic::class, 'store'])->name('register-store');
Route::middleware('guest')->group(function () {
    Route::get('forgot-password', [ForgotPasswordBasic::class, 'index'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordBasic::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [ForgotPasswordBasic::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ForgotPasswordBasic::class, 'reset'])->name('password.update');
});

// ============================================
// PROTECTED ADMIN ROUTES
// ============================================
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Hall Management Routes
        Route::delete('halls/{hall}/images/{image}', [HallController::class, 'destroyImage'])->name('halls.images.destroy');
        Route::resource('halls', HallController::class);

        // Booking Management Routes
        Route::get('bookings/cancel', [BookingController::class, 'showCancellationForm'])->name('bookings.cancel.form');
        Route::post('bookings/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel.submit');
        Route::resource('bookings', BookingController::class);
        Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

        // Customer Management Routes
        Route::resource('customers', CustomerController::class, ['only' => ['index', 'show', 'destroy']]);

        // Event Management Routes (booking events)
        Route::resource('events', EventController::class, ['only' => ['index', 'show', 'update']]);

        // Staff Management Routes
        Route::get('staff/create-user', [StaffController::class, 'createUser'])->name('staff.create-user');
        Route::post('staff/store-user', [StaffController::class, 'storeUser'])->name('staff.store-user');
        Route::resource('staff', StaffController::class);

        // Reports Routes
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
        Route::get('reports/halls', [ReportController::class, 'halls'])->name('reports.halls');

        // Settings Routes
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('settings/email', [SettingsController::class, 'email'])->name('settings.email');
        Route::get('settings/general', [SettingsController::class, 'general'])->name('settings.general');
        Route::get('settings/security', [SettingsController::class, 'security'])->name('settings.security');

        // Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    });

// ============================================
// PROTECTED USER (STAFF) ROUTES
// ============================================
Route::middleware(['auth', 'staff'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('halls', [UserHallController::class, 'index'])->name('halls.index');
        Route::get('campus/{campus}/block/{block}', [UserHallController::class, 'block'])->name('halls.block');
        Route::get('bookings', [UserBookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/create', [UserBookingController::class, 'create'])->name('bookings.create');
        Route::post('bookings', [UserBookingController::class, 'store'])->name('bookings.store');
        Route::get('bookings/cancel', [UserBookingController::class, 'showCancellationForm'])->name('bookings.cancel.form');
        Route::post('bookings/cancel', [UserBookingController::class, 'cancel'])->name('bookings.cancel.submit');
        Route::get('notifications', [UserNotificationController::class, 'index'])->name('notifications.index');
    });

// ============================================
// PROTECTED MEDIA ROUTES
// ============================================
Route::middleware(['auth', 'media'])
    ->prefix('media')
    ->name('media.')
    ->group(function () {
        Route::get('dashboard', [MediaDashboardController::class, 'index'])->name('dashboard');
        Route::get('notifications', [MediaNotificationController::class, 'index'])->name('notifications.index');
    });

// ============================================
// MAIN PAGE ROUTE
// ============================================
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================
// LAYOUT ROUTES
// ============================================
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// ============================================
// PAGES ROUTES
// ============================================
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// ============================================
// CARDS ROUTES
// ============================================
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// ============================================
// USER INTERFACE ROUTES
// ============================================
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// ============================================
// EXTENDED UI ROUTES
// ============================================
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// ============================================
// ICONS ROUTES
// ============================================
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// ============================================
// FORM ELEMENTS ROUTES
// ============================================
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// ============================================
// FORM LAYOUTS ROUTES
// ============================================
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// ============================================
// TABLES ROUTES
// ============================================
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
