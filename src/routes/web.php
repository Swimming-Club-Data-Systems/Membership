<?php

use App\Http\Controllers\Central\MyAccountController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\WebauthnRegistrationController;
use App\Http\Controllers\Central\TenantUserController;
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

Route::get('/clubs', [TenantController::class, 'index'])->name('tenants');
Route::get('/clubs/{tenant}', [TenantController::class, 'redirect'])->name('tenants.redirect');

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

        Route::prefix('/users')->group(function () {
            Route::get('/', [TenantUserController::class, 'index']);
            Route::get('/{user}', [TenantUserController::class, 'show'])->name('users.show');
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
