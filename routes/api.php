<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ForecastController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\PipelineStageController;
use App\Http\Controllers\Api\PriceBookController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\RolePermissionController;
use App\Http\Controllers\Api\SalesTargetController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\EmailCampaignController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\WebFormController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\KbArticleController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\CallLogController;
use App\Http\Controllers\Api\EmailLogController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TimesheetController;
use App\Models\CampaignTargetList;
use App\Models\WebFormSubmission;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Public auth routes
Route::name('api.')->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes
Route::name('api.')->middleware(['auth:sanctum', App\Http\Middleware\TenantScope::class])->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Profile routes
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::put('/profile/password', [UserController::class, 'updatePassword']);

    // Contacts
    Route::apiResource('contacts', ContactController::class);
    Route::post('/contacts/{contact}/sync-tags', [ContactController::class, 'syncTags']);
    Route::get('/contacts/{contact}/relationships', [ContactController::class, 'relationships']);
    Route::post('/contacts/{contact}/relationships', [ContactController::class, 'addRelationship']);
    Route::delete('/contacts/{contact}/relationships/{related}', [ContactController::class, 'removeRelationship']);

    // Accounts
    Route::apiResource('accounts', AccountController::class);
    Route::get('/accounts/{account}/contacts', [AccountController::class, 'contacts']);
    Route::post('/accounts/{account}/contacts', [AccountController::class, 'attachContact']);
    Route::delete('/accounts/{account}/contacts/{contact}', [AccountController::class, 'detachContact']);
    Route::get('/accounts/{account}/children', [AccountController::class, 'children']);

    // Account addresses (shallow nested resource)
    Route::get('/accounts/{account}/addresses', [AddressController::class, 'index']);
    Route::post('/accounts/{account}/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

    // Leads
    Route::apiResource('leads', LeadController::class);
    Route::post('/leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::post('/leads/{lead}/sync-tags', [LeadController::class, 'syncTags']);

    // Tags
    Route::apiResource('tags', TagController::class)->except(['show']);

    // Activities
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::delete('/activities/{activity}', [ActivityController::class, 'destroy']);

    // Notes
    Route::get('/notes', [NoteController::class, 'index']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::put('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

    // =====================================================
    // Sales Force Automation (SFA)
    // =====================================================

    // Pipeline Stages
    Route::apiResource('pipeline-stages', PipelineStageController::class);
    Route::post('/pipeline-stages/reorder', [PipelineStageController::class, 'reorder']);

    // Opportunities (Deals)
    Route::apiResource('opportunities', OpportunityController::class);
    Route::put('/opportunities/{opportunity}/stage', [OpportunityController::class, 'updateStage']);
    Route::get('/opportunities/{opportunity}/team', [OpportunityController::class, 'team']);
    Route::post('/opportunities/{opportunity}/team', [OpportunityController::class, 'addTeamMember']);
    Route::put('/opportunities/{opportunity}/team/{user}', [OpportunityController::class, 'updateTeamMember']);
    Route::delete('/opportunities/{opportunity}/team/{user}', [OpportunityController::class, 'removeTeamMember']);

    // Products
    Route::apiResource('products', ProductController::class);

    // Price Books
    Route::apiResource('price-books', PriceBookController::class);
    Route::post('/price-books/{priceBook}/entries', [PriceBookController::class, 'addEntry']);
    Route::put('/price-book-entries/{entry}', [PriceBookController::class, 'updateEntry']);
    Route::delete('/price-book-entries/{entry}', [PriceBookController::class, 'removeEntry']);

    // Quotes
    Route::apiResource('quotes', QuoteController::class);
    Route::put('/quotes/{quote}/status', [QuoteController::class, 'updateStatus']);
    Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'generatePdf']);

    // Sales Targets
    Route::apiResource('sales-targets', SalesTargetController::class);

    // Forecasting
    Route::get('/forecasts', [ForecastController::class, 'index']);
    Route::get('/forecasts/summary', [ForecastController::class, 'summary']);

    // =====================================================
    // Marketing Automation
    // =====================================================

    // Campaigns
    Route::apiResource('campaigns', CampaignController::class);
    Route::get('/campaigns/{campaign}/target-lists',          [CampaignController::class, 'targetLists']);
    Route::post('/campaigns/{campaign}/target-lists',         [CampaignController::class, 'storeTargetList']);
    Route::put('/campaign-target-lists/{targetList}',         [CampaignController::class, 'updateTargetList']);
    Route::delete('/campaign-target-lists/{targetList}',      [CampaignController::class, 'destroyTargetList']);
    Route::post('/campaign-target-lists/{targetList}/sync-contacts', [CampaignController::class, 'syncContacts']);
    Route::post('/campaign-target-lists/{targetList}/sync-leads',    [CampaignController::class, 'syncLeads']);

    // Email Templates
    Route::apiResource('email-templates', EmailTemplateController::class);

    // Email Campaigns
    Route::apiResource('email-campaigns', EmailCampaignController::class);
    Route::put('/email-campaigns/{emailCampaign}/status', [EmailCampaignController::class, 'updateStatus']);

    // Landing Pages
    Route::apiResource('landing-pages', LandingPageController::class);

    // Web Forms
    Route::apiResource('web-forms', WebFormController::class);
    Route::get('/web-forms/{webForm}/submissions',            [WebFormController::class, 'submissions']);
    Route::post('/web-form-submissions/{submission}/convert', [WebFormController::class, 'convertSubmission']);

    // =====================================================
    // Customer Service & Support
    // =====================================================

    // Tickets
    Route::apiResource('tickets', TicketController::class);
    Route::get('/tickets/{ticket}/comments',        [TicketController::class, 'comments']);
    Route::post('/tickets/{ticket}/comments',       [TicketController::class, 'addComment']);

    // Knowledge Base
    Route::apiResource('kb-articles', KbArticleController::class);

    // =====================================================
    // Activity & Communication Management
    // =====================================================

    // Tasks
    Route::apiResource('tasks', TaskController::class);

    // Calendar Events
    Route::apiResource('calendar-events', CalendarEventController::class);

    // Call Logs
    Route::apiResource('call-logs', CallLogController::class);

    // Email Logs
    Route::apiResource('email-logs', EmailLogController::class);

    // =====================================================
    // Finance & Billing
    // =====================================================

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);
    Route::patch('/invoices/{invoice}/status', [InvoiceController::class, 'updateStatus']);

    // Expenses
    Route::apiResource('expenses', ExpenseController::class);
    Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
    Route::post('/expenses/{expense}/reject',  [ExpenseController::class, 'reject']);

    // =====================================================
    // Project & Delivery Management
    // =====================================================

    // Projects
    Route::apiResource('projects', ProjectController::class);

    // Timesheets
    Route::apiResource('timesheets', TimesheetController::class);

    // Admin routes
    Route::middleware(['role:admin'])->group(function () {
        // User management
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/sync-roles', [UserController::class, 'syncRoles']);

        // Role & Permission management
        Route::apiResource('roles', RolePermissionController::class);
        Route::get('/permissions', [RolePermissionController::class, 'permissions']);
    });
});
