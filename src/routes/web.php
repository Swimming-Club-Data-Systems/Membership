<?php

use App\Http\Controllers\Central\ClubController;
use App\Http\Controllers\Central\InvoiceController;
use App\Http\Controllers\Central\MyAccountController;
use App\Http\Controllers\Central\NotifyHistoryController;
use App\Http\Controllers\Central\PaymentMethodController;
use App\Http\Controllers\Central\TenantAdministratorsController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantUserController;
use App\Http\Controllers\Central\WebauthnRegistrationController;
use App\Http\Controllers\Tenant\ReportAnErrorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
Route::stripeWebhooks('stripe/connect-webhook');

Route::get('/', function () {
    return Inertia::render('Central/Index', [
        'canLogin' => Route::has('central.login') && !Auth::guard('central')->check(),
        'canRegister' => false,
    ]);
})->name('central.home');

// Route::get('/new-user/{user}', [TenantAdministratorsController::class, 'index'])->name('central.admin_signup');

Route::prefix('report-an-issue')->controller(ReportAnErrorController::class)->group(function () {
    Route::get('/', 'create')->name('central.report_an_error');
    Route::post('/', 'store');
});

Route::get('/dev/require-confirm', function (Request $request) {
    $request->session()->put('auth.password_confirmed_at', 0);
    return redirect('/');
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

        Route::middleware('auth:central')->prefix('/notify')->group(function () {
            Route::get('/', [NotifyHistoryController::class, 'index'])->name('notify');
            Route::get('/{notify}', [NotifyHistoryController::class, 'show'])->name('notify.show');
        });

        Route::middleware('auth:central')->prefix('/invoices')->group(function () {
            Route::get('/{invoiceId}', [InvoiceController::class, 'show'])->name('invoices.show');
        });

        Route::middleware('auth:central')->prefix('/tenants')->group(function () {
            Route::get('/', [TenantController::class, 'index'])->name('tenants');
            Route::get('/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
            Route::put('/{tenant}', [TenantController::class, 'save']);

            Route::get('/{tenant}/administrators', [TenantAdministratorsController::class, 'index'])->name('tenants.administrators');
            Route::post('/{tenant}/administrators', [TenantAdministratorsController::class, 'create']);
            Route::delete('/{tenant}/administrators/{user}', [TenantAdministratorsController::class, 'delete'])->name('tenants.administrators.delete');

            // Route::get('/{tenant}/statistics', [TenantController::class, 'show'])->name('tenants.statistics');
            // Route::put('/{tenant}/statistics', [TenantController::class, 'save']);

            Route::get('/{tenant}/stripe', [TenantController::class, 'stripe'])->name('tenants.stripe');
            Route::put('/{tenant}/stripe', [TenantController::class, 'save']);

            Route::get('/{tenant}/billing', [TenantController::class, 'billing'])->name('tenants.billing');
            Route::put('/{tenant}/billing', [TenantController::class, 'save']);
            Route::get('/{tenant}/billing/payment-methods/new', [TenantController::class, 'addPaymentMethod'])->name('tenants.billing.add_payment_method');
            Route::get('/{tenant}/billing/payment-methods/success', [TenantController::class, 'addPaymentMethodSuccess'])->name('tenants.billing.add_payment_method_success');
            Route::put('/{tenant}/billing/payment-methods/{id}', [PaymentMethodController::class, 'setDefault'])->name('tenants.billing.update_payment_method');
            Route::delete('/{tenant}/billing/payment-methods/{id}', [PaymentMethodController::class, 'delete'])->name('tenants.billing.delete_payment_method');
            // Route::get('/{tenant}/billing/payment-methods/new', [PaymentMethodController::class, 'create'])->name('tenants.billing.add_payment_method');
            Route::get('/{tenant}/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

            Route::get('/{tenant}/billing/portal', [TenantController::class, 'stripeBillingPortal'])->name('tenants.billing.portal');

            Route::get('/{tenant}/stripe/setup', [TenantController::class, 'stripeOAuthStart'])->name('tenants.setup_stripe');
            Route::get('/stripe/setup', [TenantController::class, 'stripeOAuthRedirect'])->name('tenants.setup_stripe_redirect');

            Route::get('/{tenant}/pay-as-you-go', [TenantController::class, 'payAsYouGo'])->name('tenants.pay_as_you_go');
            Route::put('/{tenant}/pay-as-you-go', [TenantController::class, 'topUp']);
            Route::get('/{tenant}/pay-as-you-go/top-up', [TenantController::class, 'topUp'])->name('tenants.top_up');
            Route::get('/{tenant}/pay-as-you-go/top-up-success', [TenantController::class, 'topUpSuccess'])->name('tenants.top_up_success');

            // Route::get('/{tenant}/config-keys', [TenantController::class, 'show'])->name('tenants.config_keys');
            // Route::put('/{tenant}/config-keys', [TenantController::class, 'save']);
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
