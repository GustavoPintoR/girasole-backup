<?php

use App\Http\Controllers\Api\CadastralGroupController;
use App\Http\Controllers\Api\ExternalApi\CadastralGroupController as ExternalCadastralGroupController;
use App\Http\Controllers\Api\CadastralUnitController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\ExternalApi\EventController;
use App\Http\Controllers\Api\ExternalApi\PlanController;
use App\Http\Controllers\Api\ExternalApi\UserController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\ForecastLogController;
use App\Http\Controllers\Api\ExternalApi\RegionController as ExternalRegionController;
use App\Http\Controllers\Api\ExternalApi\SensorController as ExternalSensorController;
use App\Http\Controllers\Api\ExternalApi\ProvinceController as ExternalProvinceController;
use App\Http\Controllers\Api\ExternalApi\CityController as ExternalCityController;
use App\Http\Controllers\Api\ExternalApi\PostalCodeController as ExternalPostalCodeController;
use App\Http\Controllers\Api\SensorController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Middleware\ApiLocale;
use App\Http\Middleware\ApiAccess;
use App\Http\Middleware\PreventTechnician;
use App\Http\Middleware\Subscribed;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    Route::name('internal.')->group(function () {
        Route::get('/regions/{region}/provinces', [RegionController::class, 'getProvinces'])->name('regions.get.provinces')->withoutMiddleware('auth:api');
        Route::get('/provinces/{province}/cities', [ProvinceController::class, 'getCities'])->name('provinces.get.cities')->withoutMiddleware('auth:api');
        Route::get('/cities/{city}/postal-codes', [CityController::class, 'getPostalCodes'])->name('cities.get.postal-codes')->withoutMiddleware('auth:api');
        Route::get('/ops', [SensorController::class, 'getOps'])->name('ops');
        Route::get('/sensor-fields', [SensorController::class, 'getFields'])->name('fields');
        Route::get('/internal/regions', [RegionController::class, 'index'])->name('regions.index');

        Route::controller(CadastralGroupController::class)->prefix('cadastral-groups')->name('cadastral-groups.')->group(function () {
            Route::post('check-adjacency', 'checkAdjacency')->name('check-adjacency');
            Route::post('calculate-area', 'calculateArea')
                ->name('calculate-area');
            Route::get('/map-data/{user}', 'mapData')->name('mapData');
            Route::get('/get-coordinates', 'getGroupCoordinates')->name('get-coordinates');
            Route::get('/', 'index')->name('index');
        });

        // Companies for a user
        Route::get('/companies', [\App\Http\Controllers\Api\CompanyController::class, 'index'])->name('companies.index');

        Route::controller(CadastralUnitController::class)->prefix('cadastral-units')->name('cadastral-units.')->group(function () {
            Route::get('/get-available-units', 'getAvailableUnits')->name('get-available-units');
            Route::get('/', 'index')->name('index');
        });

        Route::controller(WeatherController::class)->prefix('weather')->name('weather.')->group(function () {
            Route::post('get-current-weather', 'getCurrentWeather')->name('get-current-weather');
        });

        Route::controller(ForecastLogController::class)
            ->prefix('forecast-logs')
            ->name('forecast-logs.')
            ->group(function () {
                Route::get('get-data', 'getData')->name('get-data');
            }
        );
    });


    // External API's start's here
    Route::middleware([ApiLocale::class, ApiAccess::class])->group(function () {
        Route::post('login', [UserController::class, 'login'])->name('login');
        Route::post('register', [UserController::class, 'register']);
        Route::post('password/reset', [UserController::class, 'sendPasswordResetLink']);

        Route::get('plans', [PlanController::class, 'index']);
        Route::post('subscribe/{plan}', [PlanController::class, 'subscribe'])->middleware(['auth:sanctum']);

        Route::middleware(['auth:sanctum', Subscribed::class])->group(function () {

            Route::middleware([PreventTechnician::class])->group(function () {
                Route::resource('sensors', ExternalSensorController::class)->only(['index', 'show']);
                Route::get('/me', [UserController::class, 'me']);
                Route::patch('update/profile', [UserController::class, 'updateProfile']);
                Route::get('sensor/influx', [ExternalSensorController::class, 'getInfluxData']);
                Route::resource('regions', ExternalRegionController::class)->only(['index', 'show']);
                Route::resource('provinces', ExternalProvinceController::class)->only(['index', 'show']);
                Route::resource('cities', ExternalCityController::class)->only(['index', 'show']);
                Route::resource('postal-codes', ExternalPostalCodeController::class)->only(['index', 'show']);
            });

            Route::get('calendar', [EventController::class, 'calendar']);
            Route::resource('fields', ExternalCadastralGroupController::class)->only(['index', 'show']);
            Route::get('all/fields', [ExternalCadastralGroupController::class,  'allFields']);
            Route::get('fields/{field}/forecast-logs', [ExternalCadastralGroupController::class, 'forecastLogs']);
        });
    });
});
