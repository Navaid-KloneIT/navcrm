<?php

use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactWebController;
use App\Http\Controllers\AccountWebController;
use App\Http\Controllers\LeadWebController;
use App\Http\Controllers\OpportunityWebController;
use App\Http\Controllers\ProductWebController;
use App\Http\Controllers\PriceBookWebController;
use App\Http\Controllers\QuoteWebController;
use App\Http\Controllers\ForecastWebController;
use App\Http\Controllers\ActivityWebController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CampaignWebController;
use App\Http\Controllers\EmailTemplateWebController;
use App\Http\Controllers\EmailCampaignWebController;
use App\Http\Controllers\LandingPageWebController;
use App\Http\Controllers\WebFormWebController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ─── Root redirect ─────────────────────────────────────────────────────────
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('auth.login');
});

// ─── Guest Routes ──────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    Route::get('/login',           [WebAuthController::class, 'showLogin'])->name('login');
    Route::get('/register',        [WebAuthController::class, 'showRegister'])->name('register');
    Route::get('/auth/login',      [WebAuthController::class, 'showLogin'])->name('auth.login.form');
    Route::get('/auth/register',   [WebAuthController::class, 'showRegister'])->name('auth.register.form');
    Route::get('/auth/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('auth.forgot-password');

    Route::post('/auth/login',     [WebAuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/register',  [WebAuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/forgot-password', [WebAuthController::class, 'sendResetLink'])->name('auth.forgot-password.send');
});

// ─── Authenticated Routes ───────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/auth/logout', [WebAuthController::class, 'logout'])->name('auth.logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Contacts ──────────────────────────────────────────────────────────
    Route::resource('contacts', ContactWebController::class)->names([
        'index'   => 'contacts.index',
        'create'  => 'contacts.create',
        'store'   => 'contacts.store',
        'show'    => 'contacts.show',
        'edit'    => 'contacts.edit',
        'update'  => 'contacts.update',
        'destroy' => 'contacts.destroy',
    ]);

    // ── Accounts ──────────────────────────────────────────────────────────
    Route::resource('accounts', AccountWebController::class)->names([
        'index'   => 'accounts.index',
        'create'  => 'accounts.create',
        'store'   => 'accounts.store',
        'show'    => 'accounts.show',
        'edit'    => 'accounts.edit',
        'update'  => 'accounts.update',
        'destroy' => 'accounts.destroy',
    ]);

    // ── Leads ─────────────────────────────────────────────────────────────
    Route::resource('leads', LeadWebController::class)->names([
        'index'   => 'leads.index',
        'create'  => 'leads.create',
        'store'   => 'leads.store',
        'show'    => 'leads.show',
        'edit'    => 'leads.edit',
        'update'  => 'leads.update',
        'destroy' => 'leads.destroy',
    ]);
    Route::post('/leads/{lead}/convert', [LeadWebController::class, 'convert'])->name('leads.convert');

    // ── Activities ────────────────────────────────────────────────────────
    Route::get('/activities', [ActivityWebController::class, 'index'])->name('activities.index');

    // ── Opportunities ─────────────────────────────────────────────────────
    Route::resource('opportunities', OpportunityWebController::class)->names([
        'index'   => 'opportunities.index',
        'create'  => 'opportunities.create',
        'store'   => 'opportunities.store',
        'show'    => 'opportunities.show',
        'edit'    => 'opportunities.edit',
        'update'  => 'opportunities.update',
        'destroy' => 'opportunities.destroy',
    ]);

    // ── Products ──────────────────────────────────────────────────────────
    Route::resource('products', ProductWebController::class)->names([
        'index'   => 'products.index',
        'create'  => 'products.create',
        'store'   => 'products.store',
        'show'    => 'products.show',
        'edit'    => 'products.edit',
        'update'  => 'products.update',
        'destroy' => 'products.destroy',
    ]);

    // ── Price Books ───────────────────────────────────────────────────────
    Route::get('/price-books',          [PriceBookWebController::class, 'index'])->name('price-books.index');
    Route::post('/price-books',         [PriceBookWebController::class, 'store'])->name('price-books.store');
    Route::get('/price-books/create',   [PriceBookWebController::class, 'create'])->name('price-books.create');
    Route::get('/price-books/{priceBook}',        [PriceBookWebController::class, 'show'])->name('price-books.show');
    Route::put('/price-books/{priceBook}',        [PriceBookWebController::class, 'update'])->name('price-books.update');
    Route::delete('/price-books/{priceBook}',     [PriceBookWebController::class, 'destroy'])->name('price-books.destroy');

    // ── Quotes ────────────────────────────────────────────────────────────
    Route::resource('quotes', QuoteWebController::class)->names([
        'index'   => 'quotes.index',
        'create'  => 'quotes.create',
        'store'   => 'quotes.store',
        'show'    => 'quotes.show',
        'edit'    => 'quotes.edit',
        'update'  => 'quotes.update',
        'destroy' => 'quotes.destroy',
    ]);
    Route::get('/quotes/{quote}/pdf', [QuoteWebController::class, 'pdf'])->name('quotes.pdf');

    // ── Forecasts ─────────────────────────────────────────────────────────
    Route::get('/forecasts', [ForecastWebController::class, 'index'])->name('forecasts.index');

    // ── Marketing Automation ───────────────────────────────────────────────
    Route::prefix('marketing')->name('marketing.')->group(function () {

        // Campaigns
        Route::resource('campaigns', CampaignWebController::class)->names([
            'index'   => 'campaigns.index',
            'create'  => 'campaigns.create',
            'store'   => 'campaigns.store',
            'show'    => 'campaigns.show',
            'edit'    => 'campaigns.edit',
            'update'  => 'campaigns.update',
            'destroy' => 'campaigns.destroy',
        ]);

        // Email Templates
        Route::resource('email-templates', EmailTemplateWebController::class)->names([
            'index'   => 'email-templates.index',
            'create'  => 'email-templates.create',
            'store'   => 'email-templates.store',
            'edit'    => 'email-templates.edit',
            'update'  => 'email-templates.update',
            'destroy' => 'email-templates.destroy',
        ])->except(['show']);

        // Email Campaigns
        Route::resource('email-campaigns', EmailCampaignWebController::class)->names([
            'index'   => 'email-campaigns.index',
            'create'  => 'email-campaigns.create',
            'store'   => 'email-campaigns.store',
            'show'    => 'email-campaigns.show',
            'edit'    => 'email-campaigns.edit',
            'update'  => 'email-campaigns.update',
            'destroy' => 'email-campaigns.destroy',
        ]);

        // Landing Pages
        Route::resource('landing-pages', LandingPageWebController::class)->names([
            'index'   => 'landing-pages.index',
            'create'  => 'landing-pages.create',
            'store'   => 'landing-pages.store',
            'show'    => 'landing-pages.show',
            'edit'    => 'landing-pages.edit',
            'update'  => 'landing-pages.update',
            'destroy' => 'landing-pages.destroy',
        ]);

        // Web Forms
        Route::resource('web-forms', WebFormWebController::class)->names([
            'index'   => 'web-forms.index',
            'create'  => 'web-forms.create',
            'store'   => 'web-forms.store',
            'show'    => 'web-forms.show',
            'edit'    => 'web-forms.edit',
            'update'  => 'web-forms.update',
            'destroy' => 'web-forms.destroy',
        ]);
        Route::post('/web-forms/submissions/{submission}/convert',
            [WebFormWebController::class, 'convertSubmission']
        )->name('web-forms.submissions.convert');
    });

    // ── Settings ──────────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/',        [SettingsController::class, 'index'])->name('index');
        Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
        Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password',[SettingsController::class, 'updatePassword'])->name('password.update');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',              [SettingsController::class, 'users'])->name('index');
            Route::post('/',             [SettingsController::class, 'storeUser'])->name('store');
            Route::put('/{user}',        [SettingsController::class, 'updateUser'])->name('update');
            Route::delete('/{user}',     [SettingsController::class, 'destroyUser'])->name('destroy');
        });

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',              [SettingsController::class, 'roles'])->name('index');
        });
    });
});
