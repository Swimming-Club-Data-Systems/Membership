<?php

use App\Http\Controllers\Tenant\Auth\V1LoginController;
use App\Http\Controllers\Tenant\MyAccountController;
use App\Http\Controllers\Tenant\NotifyAdditionalEmailController;
use App\Http\Controllers\Tenant\VerifyEmailChangeController;
use App\Http\Controllers\Tenant\WebauthnRegistrationController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Passport\Passport;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

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

    Route::get('/dev/require-confirm', function (Request $request) {
        $request->session()->put('auth.password_confirmed_at', 0);
        return redirect('/dev');
    });

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    require __DIR__ . '/auth.php';

    Route::get('verify-email-update/{user}/{email}', [VerifyEmailChangeController::class, '__invoke'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify_change');

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

    Route::prefix('my-account')->group(function () {
        Route::name('my_account.')->group(function () {
            Route::get('/', [MyAccountController::class, 'index'])->name('index');

            Route::get('/profile', [MyAccountController::class, 'profile'])->name('profile');
            Route::put('/profile', [MyAccountController::class, 'saveProfile']);

            Route::get('/email-options', [MyAccountController::class, 'email'])->name('email');
            Route::put('/email-options', [MyAccountController::class, 'saveEmail']);
            Route::post('/additional-email', [MyAccountController::class, 'saveAdditionalEmail'])->name('additional_email');
            Route::delete('/additional-email', [MyAccountController::class, 'email']);

            Route::get('/advanced-options', [MyAccountController::class, 'advanced'])->name('advanced');
            Route::post('/advanced-options', [MyAccountController::class, 'saveAdvanced']);

            Route::get('/password-and-security', [MyAccountController::class, 'password'])->name('security');
            Route::put('/password-and-security', [MyAccountController::class, 'savePassword']);

            Route::get('/totp', [MyAccountController::class, 'createTOTP'])->name('create_totp');
            Route::post('/totp', [MyAccountController::class, 'saveTOTP'])->name('save_totp');
            Route::delete('/totp', [MyAccountController::class, 'deleteTOTP'])->name('delete_totp');

            Route::post('/webauthn/register', [WebauthnRegistrationController::class, 'verify'])->name('webauthn_verify');
            Route::post('/webauthn/options', [WebauthnRegistrationController::class, 'challenge'])->name('webauthn_challenge');
            Route::delete('/webauthn/{credential}', [WebauthnRegistrationController::class, 'delete'])->name('webauthn_delete');
        });
    });

    Route::get('/notify-additional-emails/{data}', [NotifyAdditionalEmailController::class, 'show'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('notify_additional_emails.view');

    Route::post('/notify-additional-emails', [NotifyAdditionalEmailController::class, 'create'])
        ->name('notify_additional_emails.confirm');

    Route::delete('/notify-additional-emails/{additionalEmail}', [NotifyAdditionalEmailController::class, 'delete'])
        ->middleware('auth')
        ->name('notify_additional_emails.delete');


    // Fallback route
    // If not defined here, it's probs in the V1 app so lets send the user there
    // Until V1 App is removed, it will handle 404 cases
    Route::any('/{path}', function ($path) {
        return redirect(url('/v1/' . $path));
    })->where('path', '.*');
});
