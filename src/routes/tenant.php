<?php

use App\Http\Controllers\Tenant\Auth\V1LoginController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\MemberController;
use App\Http\Controllers\Tenant\MyAccountController;
use App\Http\Controllers\Tenant\NotifyAdditionalEmailController;
use App\Http\Controllers\Tenant\ReportAnErrorController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\VerifyEmailChangeController;
use App\Http\Controllers\Tenant\WebauthnRegistrationController;
use App\Models\Tenant\Session;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

    Route::get('/', [DashboardController::class, 'index']);

    Route::get('/about', function () {
        return Inertia::render('About');
    });

    Route::get('/about-the-changes', function () {
        return Inertia::location("https://docs.myswimmingclub.uk/docs/technical-and-tenant/scds-next");
    })->name('about_changes');

    Route::get('/dev', function () {
        return Inertia::render('Dev');
    });

    Route::prefix('report-an-error')->controller(ReportAnErrorController::class)->group(function () {
        Route::get('/', 'create')->name('report_an_error');
        Route::post('/', 'store');
    });

    Route::get('/dev/require-confirm', function (Request $request) {
        $request->session()->put('auth.password_confirmed_at', 0);
        return redirect('/dev');
    });

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

            // Route::get('/advanced-options', [MyAccountController::class, 'advanced'])->name('advanced');
            // Route::post('/advanced-options', [MyAccountController::class, 'saveAdvanced']);

            Route::get('/password-and-security', [MyAccountController::class, 'password'])->name('security');
            Route::put('/password-and-security', [MyAccountController::class, 'savePassword']);

            Route::get('/totp', [MyAccountController::class, 'createTOTP'])->name('create_totp')->block();
            Route::post('/totp', [MyAccountController::class, 'saveTOTP'])->name('save_totp')->block();
            Route::delete('/totp', [MyAccountController::class, 'deleteTOTP'])->name('delete_totp')->block();

            Route::post('/webauthn/register', [WebauthnRegistrationController::class, 'verify'])->name('webauthn_verify')->block();
            Route::post('/webauthn/options', [WebauthnRegistrationController::class, 'challenge'])->name('webauthn_challenge')->block();
            Route::delete('/webauthn/{credential}', [WebauthnRegistrationController::class, 'delete'])->name('webauthn_delete')->block();
        });
    });

    Route::prefix('/members')->group(function () {
        Route::get('/', [MemberController::class, 'index']);
        Route::get('/{member}', [MemberController::class, 'show'])->name('members.show');
        Route::any('/{path}', function ($path) {
            return Inertia::location('/v1/members/' . $path);
        })->where('path', '.*');
    });

    Route::prefix('/users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{user}', [UserController::class, 'show'])->name('users.show');
        Route::any('/{path}', function ($path) {
            return Inertia::location('/v1/users/' . $path);
        })->where('path', '.*');
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
        return Inertia::location('/v1/' . $path);
    })->where('path', '.*');
});
