<?php

use App\Http\Controllers\Central\ClubController;
use App\Http\Controllers\Central\MyAccountController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantUserController;
use App\Http\Controllers\Central\WebauthnRegistrationController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Central/Index', [
        'canLogin' => Route::has('central.login') && !Auth::guard('central')->check(),
        'canRegister' => false,
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/clubs', [ClubController::class, 'index'])->name('central.clubs');
Route::get('/clubs/{tenant}', [ClubController::class, 'redirect'])->name('central.clubs.redirect');

Route::middleware('auth:central')->group(function () {
    Route::name('central.')->group(function () {
        Route::prefix('system-administration')->group(function () {
            Route::name('admin.')->group(function () {
                Route::get('/', [MyAccountController::class, 'index'])->name('index');
            });
        });
    });

    Route::name('central.')->group(function () {
        Route::prefix('my-account')->group(function () {
            Route::name('my_account.')->group(function () {
                Route::get('/', [MyAccountController::class, 'index'])->name('index');

                Route::get('/profile', [MyAccountController::class, 'profile'])->name('profile');
                Route::put('/profile', [MyAccountController::class, 'saveProfile']);

                Route::get('/password-and-security', [MyAccountController::class, 'password'])->name('security');
                Route::put('/password-and-security', [MyAccountController::class, 'savePassword']);

                Route::post('/webauthn/register', [WebauthnRegistrationController::class, 'verify'])->name('webauthn_verify');
                Route::post('/webauthn/options', [WebauthnRegistrationController::class, 'challenge'])->name('webauthn_challenge');
                Route::delete('/webauthn/{credential}', [WebauthnRegistrationController::class, 'delete'])->name('webauthn_delete');
            });
        });

        Route::prefix('/tenants/users')->group(function () {
            Route::get('/', [TenantUserController::class, 'index'])->name('tenant_users.index');
            Route::get('/{user}', [TenantUserController::class, 'show'])->name('users.show');
        });

        Route::middleware('auth:central')->prefix('/tenants')->group(function () {
            Route::get('/', [TenantController::class, 'index'])->name('tenants');
            Route::get('/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
            Route::put('/{tenant}', [TenantController::class, 'save']);

            Route::get('/{tenant}/statistics', [TenantController::class, 'show'])->name('tenants.statistics');
            Route::put('/{tenant}/statistics', [TenantController::class, 'save']);

            Route::get('/{tenant}/stripe', [TenantController::class, 'stripe'])->name('tenants.stripe');
            Route::put('/{tenant}/stripe', [TenantController::class, 'save']);

            Route::get('/{tenant}/stripe/setup', [TenantController::class, 'stripeOAuthStart'])->name('tenants.setup_stripe');
            Route::get('/stripe/setup', [TenantController::class, 'stripeOAuthRedirect'])->name('tenants.setup_stripe_redirect');

            Route::get('/{tenant}/config-keys', [TenantController::class, 'show'])->name('tenants.config_keys');
            Route::put('/{tenant}/config-keys', [TenantController::class, 'save']);
        });
    });
});

require __DIR__ . '/central-auth.php';

//Route::get('/dev', function () {
//    return Inertia::render('Dev');
//});

// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');
