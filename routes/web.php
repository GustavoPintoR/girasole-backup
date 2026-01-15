<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ForecastLogController;
use Inertia\Inertia;
use App\Http\Middleware\Subscribed;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUserActive;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CultivarController;
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IrrigationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\SensorTypeController;
use App\Http\Controllers\CultivationController;
use App\Http\Controllers\SensorFieldController;
use App\Http\Middleware\HasAcceptedLatestTerms;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\Auth\WaitingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PersonalAccessTokenController;
use App\Http\Controllers\PlantDiseaseController;
use App\Http\Controllers\CadastralUnitController;
use App\Http\Controllers\CadastralGroupController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlantingSchemeController;
use App\Http\Controllers\SensorOperationController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Stripe\WebhooksController;
use App\Http\Controllers\TermsAndConditionsController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use App\Http\Controllers\ForecastController;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('home');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

// Route::prefix('hooks')->name('hooks.')->group(function () {
//     Route::post('/{stripe}', [WebhookController::class, 'handleWebhook'])->name('stripe');
// });

Route::get('waiting', WaitingController::class)
    ->middleware(['auth'])
    ->name('waiting');

Route::prefix('settings/subscription')
    ->middleware(['auth', 'verified', HasAcceptedLatestTerms::class, CheckUserActive::class, Subscribed::class])
    ->name('settings.subscription.')
    ->group(function () {
        Route::get('/', [ProfileController::class, 'subscriptionInfo'])->name('index');
        Route::post('/update-own-plan', [ProfileController::class, 'updatePlan'])->name('update-own-plan');
    });

Route::prefix('billing')
    ->middleware(['auth', 'verified'])
    ->name('billing.')
    ->group(function () {
        Route::get('/', [BillingController::class, 'getPlans'])->name('plans');
        Route::post('subscribe/{planId}', [BillingController::class, 'subscribe'])->name('subscribe');
        Route::get('success', [BillingController::class, 'success'])->name('success');
        Route::post('cancel', [BillingController::class, 'cancel'])->name('cancel');
    });

Route::get('/users/{user}/subscription-type', [UserController::class, 'getSubscription']);

Route::controller(TermsAndConditionsController::class)
    ->middleware(['auth', 'verified'])
    ->prefix('terms-and-conditions')
    ->name('terms-and-conditions.')
    ->group(function () {
        Route::get('accept', 'accept')->name('accept');
        Route::post('confirm-accept', 'confirmAccept')->name('confirm-accept');
        Route::patch('activate', 'activate')->name('activate');
    });

Route::middleware(['auth', 'verified', HasAcceptedLatestTerms::class, CheckUserActive::class, Subscribed::class])->group(function () {

    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', [DashboardController::class, 'dashboard2'])->name('dashboard');
    Route::get('/calendar', [EventController::class, 'calendar'])->name('events.calendar');

    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('regions', RegionController::class);
    Route::resource('users', UserController::class)->names('users');
    Route::resource('provinces', ProvinceController::class);
    Route::resource('cities', CityController::class);
    Route::resource('postal-codes', PostalCodeController::class);
    Route::resource('cultivations', CultivationController::class);
    Route::resource('planting-schemes', PlantingSchemeController::class);
    Route::resource('plant-diseases', PlantDiseaseController::class);
    Route::resource('cultivars', CultivarController::class);
    Route::resource('irrigations', IrrigationController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('events', EventController::class);
    Route::resource('notifications', NotificationController::class);
    Route::resource('terms-and-conditions', TermsAndConditionsController::class);
    Route::resource('sensors', SensorController::class);
    Route::resource('sensor-types', SensorTypeController::class);
    Route::resource('sensor-fields', SensorFieldController::class);
    Route::resource('sensor-operations', SensorOperationController::class);
    Route::resource('api-keys', PersonalAccessTokenController::class);
    Route::resource('forecast-logs', ForecastLogController::class)->only(['index', 'show']);
    Route::post('api-keys/{personalAccessToken}/regenerate', [PersonalAccessTokenController::class, 'regenerate'])
        ->name('api-keys.regenerate');
    Route::resource('plans', PlanController::class);

    Route::resource('custom-fields', CustomFieldController::class);

    Route::post('sensors/sync', [SensorController::class, 'sync'])->name('sensors.sync');

    // Impersonation
    Route::impersonate();

    // Route::controller(TermsAndConditionsController::class)
    //     ->prefix('terms-and-conditions')
    //     ->name('terms-and-conditions.')
    //     ->group(function () {
    //         Route::patch('activate', 'activate')->name('activate');
    //     });

    Route::controller(ProvinceController::class)
        ->prefix('provinces/{province}')
        ->name('provinces.')
        ->group(function () {
            Route::get('available-cities', 'available')->name('available-cities');
            Route::post('attach-cities', 'attach')->name('attach-cities');
        });

    Route::controller(ImportController::class)
        ->prefix('imports')
        ->name('imports.')
        ->group(function () {
            Route::get('/', 'imports')->name('index');
            Route::post('/regions', 'regions')->name('regions');
            Route::post('/provinces', 'provinces')->name('provinces');
            Route::post('/cities', 'cities')->name('cities');
            Route::post('/postal_codes', 'postal_codes')->name('postal_codes');
        });

    Route::controller(CadastralUnitController::class)
        ->prefix('cadastral-units')
        ->name('cadastral-units.')
        ->group(function () {
            Route::patch('get-polygon-from-dataset', 'getPolygonFromDataset')->name('get-polygon');
        });
    Route::resource('cadastral-units', CadastralUnitController::class);

    Route::controller(CadastralGroupController::class)
        ->prefix('cadastral-groups')
        ->name('cadastral-groups.')
        ->group(function () {
            Route::get('{cadastralGroup}/export-feature-collection', 'exportFeatureCollection')->name('export-feature-collection');
            Route::get('{cadastralGroup}/export-geojson', 'exportGeojson')->name('export-geojson');
            Route::post('import-geojson', 'importGeojson')->name('import-geojson');
        });
    Route::resource('cadastral-groups', CadastralGroupController::class);

    // Forecast setups
    Route::controller(ForecastController::class)
        ->prefix('forecasts')
        ->name('forecasts.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('update', 'update')->name('update');
        });

    Route::get('notification/markAsRead/{notification}', [NotificationController::class, 'markAsRead'])->name('notification.markAsRead');

    Route::controller(UserController::class)
    ->prefix('users')
    ->name('users.')
    ->group(function () {
        Route::get('{user}/manage-plan', 'managePlan')->name('manage-plan');
        Route::post('{user}/update-plan', 'updatePlan')->name('update-plan');
        Route::get('{user}/view-plan', 'viewPlan')->name('view-plan');
        Route::post('{user}/set-plan-details', 'setCustomPlanDetails')->name('set-plan-details');
        Route::post('{user}/restore', 'restore')->name('restore');
        Route::delete('{user}/force-delete', 'forceDelete')->name('force-delete');
    });

});
