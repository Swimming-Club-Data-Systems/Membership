<?php

use App\Http\Controllers\Tenant\Auth\V1LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Inertia\Inertia;
use Illuminate\Foundation\Application;
use Laravel\Passport\Passport;

/*
|--------------------------------------------------------------------------
| Tenant Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the 'web' middleware group. Now create something great!
|
*/

Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Passport::routes();
});

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    });

    Route::get('/dev', function () {
        return Inertia::render('Dev');
    });

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    require __DIR__ . '/auth.php';

    Route::middleware('auth')->group(function () {
        Route::get('/login-to-v1', V1LoginController::class)->name('login.v1');
    });

    // V1 Fallback routes in case you get served something by this app
    Route::any('/v1', function () {
        return Inertia::render('Errors/V1');
    });

    Route::any('/v1/{path}', function ($path) {
        return Inertia::render('Errors/V1');
    })->where('path', '.*');

    // Fallback route
    // If not defined here, it's probs in the V1 app so lets send the user there
    // Until V1 App is removed, it will handle 404 cases
    Route::any('/{path}', function ($path) {
        return redirect(url('/v1/' . $path));
    })->where('path', '.*');
});
