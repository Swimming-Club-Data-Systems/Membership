<?php

use App\Http\Controllers\Tenant\Auth\V1LoginController;
use App\Http\Controllers\Tenant\BalanceTopUpController;
use App\Http\Controllers\Tenant\CheckoutController;
use App\Http\Controllers\Tenant\CompetitionController;
use App\Http\Controllers\Tenant\CompetitionEventController;
use App\Http\Controllers\Tenant\CompetitionFileController;
use App\Http\Controllers\Tenant\CompetitionGuestEntryController;
use App\Http\Controllers\Tenant\CompetitionGuestEntryHeaderController;
use App\Http\Controllers\Tenant\CompetitionGuestEntryPaymentController;
use App\Http\Controllers\Tenant\CompetitionGuestEntryReportController;
use App\Http\Controllers\Tenant\CompetitionMemberAvailableController;
use App\Http\Controllers\Tenant\CompetitionMemberEntryController;
use App\Http\Controllers\Tenant\CompetitionMemberEntryHeaderController;
use App\Http\Controllers\Tenant\CompetitionMemberEntryPaymentController;
use App\Http\Controllers\Tenant\CompetitionSessionController;
use App\Http\Controllers\Tenant\CustomerStatementController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\JournalAccountController;
use App\Http\Controllers\Tenant\JournalController;
use App\Http\Controllers\Tenant\LedgerAccountController;
use App\Http\Controllers\Tenant\MemberController;
use App\Http\Controllers\Tenant\MyAccountController;
use App\Http\Controllers\Tenant\NotifyAdditionalEmailController;
use App\Http\Controllers\Tenant\NotifyHistoryController;
use App\Http\Controllers\Tenant\PaymentEntryController;
use App\Http\Controllers\Tenant\PaymentMethodController;
use App\Http\Controllers\Tenant\PaymentsController;
use App\Http\Controllers\Tenant\PaymentTransactionController;
use App\Http\Controllers\Tenant\ReportAnErrorController;
use App\Http\Controllers\Tenant\SettingsController;
use App\Http\Controllers\Tenant\SMSController;
use App\Http\Controllers\Tenant\SquadController;
use App\Http\Controllers\Tenant\SquadMoveController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\VenueController;
use App\Http\Controllers\Tenant\VerifyEmailChangeController;
use App\Http\Controllers\Tenant\WebauthnRegistrationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
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

//Route::middleware([
//    InitializeTenancyByDomain::class,
//    PreventAccessFromCentralDomains::class,
//])->group(function () {
//    Passport::routes();
//});

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('index');

    Route::get('/about', function () {
        return Inertia::render('About');
    });

    Route::get('/about-the-changes', function () {
        return Inertia::location('https://docs.myswimmingclub.uk/docs/technical-and-tenant/scds-next');
    })->name('about_changes');

    Route::get('/dev', function () {
        return Inertia::render('Dev');
    });

    Route::get('/component-testing', function () {
        return Inertia::render('ComponentTesting');
    });

    Route::prefix('report-an-issue')->controller(ReportAnErrorController::class)->group(function () {
        Route::get('/', 'create')->name('report_an_error');
        Route::post('/', 'store');
    });

    Route::get('/dev/require-confirm', function (Request $request) {
        $request->session()->put('auth.password_confirmed_at', 0);

        return redirect('/dev');
    });

    require __DIR__.'/auth.php';

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
        Route::name('members.')->group(function () {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::get('/combobox', [MemberController::class, 'combobox'])
                ->name('combobox');
            Route::get('/{member}/squads', [MemberController::class, 'squads'])
                ->whereNumber('member')
                ->name('squads');
            Route::get('/{member}', [MemberController::class, 'show'])
                ->whereNumber('member')
                ->name('show');
            Route::any('/{path}', function ($path) {
                return Inertia::location('/v1/members/'.$path);
            })->where('path', '.*');
        });
    });

    Route::prefix('/squads')->group(function () {
        Route::name('squads.')->group(function () {
            Route::post('/', [SquadController::class, 'create']);
            Route::get('/', [SquadController::class, 'index'])->name('index');
            Route::get('/combobox', [SquadController::class, 'combobox'])
                ->name('combobox');
            Route::get('/new', [SquadController::class, 'new'])->name('new');
            Route::get('/{squad}', [SquadController::class, 'show'])->whereNumber('squad')->name('show');
            Route::post('/{squad}/coaches', [SquadController::class, 'addCoach'])->whereNumber('squad')->name('add-coach');
            Route::delete('/{squad}/coaches/{user}', [SquadController::class, 'deleteCoach'])
                ->whereNumber('squad')
                ->whereNumber('user')
                ->name('delete-coach');
            Route::get('/{squad}/edit', [SquadController::class, 'edit'])->whereNumber('squad')->name('edit');
            Route::put('/{squad}', [SquadController::class, 'update'])->whereNumber('squad');
            Route::delete('/{squad}', [SquadController::class, 'delete'])->whereNumber('squad')->name('delete');
            Route::any('/{path}', function ($path) {
                return Inertia::location('/v1/squads/'.$path);
            })->where('path', '.*');
        });
    });

    Route::prefix('/squad-moves')->group(function () {
        Route::name('squad-moves.')->group(function () {
            Route::get('/', [SquadMoveController::class, 'index'])
                ->name('index');
            Route::post('/', [SquadMoveController::class, 'create'])
                ->name('create');
            Route::put('/{squadMove}', [SquadMoveController::class, 'update'])
                ->whereNumber('squadMove')
                ->name('update');
            Route::delete('/{squadMove}', [SquadMoveController::class, 'delete'])
                ->whereNumber('squadMove')
                ->name('delete');
        });
    });

    Route::prefix('/users')->group(function () {
        Route::name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])
                ->name('index');
            Route::get('/combobox', [UserController::class, 'combobox'])
                ->name('combobox');
            Route::get('/{user}', [UserController::class, 'show'])
                ->whereNumber('user')
                ->name('show');
            Route::get('/{user}/statements', [CustomerStatementController::class, 'userStatementIndex'])
                ->whereNumber('user')
                ->name('statements.index');
            Route::get('/{user}/statements/{statement}', [CustomerStatementController::class, 'userShow'])
                ->whereNumber('user')
                ->whereNumber('statement')
                ->name('statements.show');
            Route::get('/{user}/transactions', [PaymentTransactionController::class, 'userIndex'])
                ->whereNumber('user')
                ->name('transactions.index');
            Route::get('/{user}/payments', [PaymentsController::class, 'userIndex'])
                ->whereNumber('user')
                ->name('payments.index');
            Route::get('/{user}/payment-methods', [PaymentMethodController::class, 'userIndex'])
                ->whereNumber('user')
                ->name('payment_methods.index');
            Route::get('/{user}/payments/{payment}', [PaymentsController::class, 'userShow'])
                ->whereNumber('user')
                ->whereNumber('payment')
                ->name('payments.show');
            Route::get('/{user}/balance-top-ups', [BalanceTopUpController::class, 'userIndex'])
                ->whereNumber('user')
                ->name('top_up.index');
            Route::get('/{user}/balance-top-ups/new', [BalanceTopUpController::class, 'new'])
                ->whereNumber('user')
                ->name('top_up.new');
            Route::post('/{user}/balance-top-ups', [BalanceTopUpController::class, 'create'])
                ->whereNumber('user')
                ->name('top_up.create');
            Route::get('/{user}/balance-top-ups/{top_up}', [BalanceTopUpController::class, 'userShow'])
                ->whereNumber('user')
                ->whereNumber('top_up')
                ->name('top_up.show');
            Route::any('/{path}', function ($path) {
                return Inertia::location('/v1/users/'.$path);
            })
                ->where('path', '.*');
        });
    });

    Route::prefix('notify')->group(function () {
        Route::name('notify.')->group(function () {
            Route::get('/email/history', [NotifyHistoryController::class, 'index'])->name('email.history');
            // Route::get('/{notify}', [NotifyHistoryController::class, 'show'])->name('email.show');
            Route::get('/{email}/download-file', [NotifyHistoryController::class, 'downloadFile'])->name('email.download_file');
            Route::get('/sms', [SMSController::class, 'new'])->name('sms.new');
            Route::post('/sms', [SMSController::class, 'store']);
            Route::get('/sms/history', [SMSController::class, 'index'])->name('sms.history');
        });
    });

    Route::prefix('billing')->group(function () {
        Route::name('payments.')->group(function () {

            Route::get('/', function () {
                return Inertia::location('/v1/payments');
            })->name('index');

            Route::prefix('payment-methods')->group(function () {
                Route::name('methods.')->group(function () {
                    Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
                    Route::get('/new', [PaymentMethodController::class, 'addPaymentMethod'])->name('new');
                    Route::get('/new-direct-debit', [PaymentMethodController::class, 'addDirectDebit'])->name('new_direct_debit');
                    Route::get('/new-success', [PaymentMethodController::class, 'addPaymentMethodSuccess'])->name('new_success');
                    Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'delete'])->name('delete');
                    Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])->name('update');
                });
            });

            Route::prefix('ledgers')->group(function () {
                Route::name('ledgers.')->group(function () {
                    Route::get('/journals-combobox', [JournalAccountController::class, 'combobox'])
                        ->name('journals.combobox');
                    Route::get('/', [LedgerAccountController::class, 'index'])
                        ->name('index')
                        ->can('view', App\Models\Tenant\LedgerAccount::class);
                    Route::get('/new', [LedgerAccountController::class, 'new'])
                        ->name('new')
                        ->can('create', App\Models\Tenant\LedgerAccount::class);
                    Route::post('/new', [LedgerAccountController::class, 'create'])
                        ->can('create', App\Models\Tenant\LedgerAccount::class);
                    Route::get('/{ledger}', [LedgerAccountController::class, 'show'])
                        ->whereNumber('ledger')->name('show')
                        ->can('view', 'ledger');
                    // Route::put('/{ledger}', [LedgerAccountController::class, 'addPaymentMethod'])->whereNumber('ledger');
                    Route::get('/{ledger}/journals/new', [JournalAccountController::class, 'new'])
                        ->whereNumber('ledger')
                        ->name('journals.new')
                        ->can('create', App\Models\Tenant\JournalAccount::class);
                    Route::post('/{ledger}/journals/new', [JournalAccountController::class, 'create'])
                        ->whereNumber('ledger')
                        ->can('create', App\Models\Tenant\JournalAccount::class);
                    Route::get('/{ledger}/journals/{journal}', [JournalAccountController::class, 'show'])
                        ->whereNumber(['ledger', 'journal'])
                        ->name('journals.show')
                        ->can('view', 'journal');
                    // Route::put('/{ledger}/journals/{journal}', [JournalAccountController::class, 'addPaymentMethod'])
                    //    ->whereNumber('ledger');
                });
            });

            Route::prefix('statements')->group(function () {
                Route::name('statements.')->group(function () {
                    Route::get('/', [CustomerStatementController::class, 'index'])
                        ->name('index');
                    Route::get('/{statement}', [CustomerStatementController::class, 'show'])
                        ->whereNumber('statement')
                        ->name('show');
                });
            });

            Route::prefix('manual-entries')->group(function () {
                Route::name('entries.')->group(function () {
                    Route::get('/new', [PaymentEntryController::class, 'new'])
                        ->name('new');
                    Route::put('/{entry}', [PaymentEntryController::class, 'post'])
                        ->whereNumber('entry')
                        ->name('post');
                    Route::get('/{entry}', [PaymentEntryController::class, 'view'])
                        ->whereNumber('entry')
                        ->name('view');
                    Route::get('/{entry}/edit', [PaymentEntryController::class, 'amend'])
                        ->whereNumber('entry')
                        ->name('amend');
                    Route::post('/{entry}/users', [PaymentEntryController::class, 'addUser'])
                        ->whereNumber('entry')
                        ->name('add_user');
                    Route::delete('/{entry}/users/{user}', [PaymentEntryController::class, 'deleteUser'])
                        ->whereNumber('entry')
                        ->whereNumber('user')
                        ->name('delete_user')
                        ->scopeBindings();
                    Route::post('/{entry}/lines', [PaymentEntryController::class, 'addLine'])
                        ->whereNumber('entry')
                        ->name('add_line');
                    Route::delete('/{entry}/lines/{line}', [PaymentEntryController::class, 'deleteLine'])
                        ->whereNumber('entry')
                        ->whereNumber('line')
                        ->name('delete_line')
                        ->scopeBindings();
                });
            });

            Route::prefix('transactions')->group(function () {
                Route::name('transactions.')->group(function () {
                    Route::get('/', [PaymentTransactionController::class, 'index'])
                        ->name('index');
                });
            });

            Route::prefix('payments')->group(function () {
                Route::name('payments.')->group(function () {
                    Route::get('/', [PaymentsController::class, 'index'])
                        ->name('index');
                    Route::get('/{payment}', [PaymentsController::class, 'show'])
                        ->name('show');
                    Route::post('/{payment}/refund', [PaymentsController::class, 'refund'])
                        ->name('refund');
                });
            });

            Route::prefix('checkout')->group(function () {
                Route::name('checkout.')->group(function () {
                    //                    Route::get('/', [CheckoutController::class, 'index'])
                    //                        ->name('index');
                    Route::get('/{payment}', [CheckoutController::class, 'show'])
                        ->whereNumber('payment')
                        ->name('show');
                    Route::get('/{payment}/success', [CheckoutController::class, 'success'])
                        ->whereNumber('payment')
                        ->name('success');
                    Route::get('/create-gala-payment', [CheckoutController::class, 'create'])
                        ->name('create');
                });
            });

            Route::prefix('balance-top-ups')->group(function () {
                Route::name('top_ups.')->group(function () {
                    Route::get('/', [BalanceTopUpController::class, 'index'])->name('index');
                    Route::get('/{top_up}', [BalanceTopUpController::class, 'index'])
                        ->whereNumber('top_up')
                        ->name('show');
                });
            });

            if (App::isLocal()) {
                Route::get('journal-report', [JournalController::class, 'view']);
            }
        });
    });

    Route::prefix('renewals')->group(function () {
        Route::name('renewals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Tenant\RenewalController::class, 'index'])
                ->name('index');
            Route::get('/new', [\App\Http\Controllers\Tenant\RenewalController::class, 'new'])
                ->name('new');
            Route::post('/', [\App\Http\Controllers\Tenant\RenewalController::class, 'create'])
                ->name('create');
            Route::get('/{renewal}', [\App\Http\Controllers\Tenant\RenewalController::class, 'show'])
                ->whereUuid('renewal')
                ->name('show');
            Route::get('/{renewal}/edit', [\App\Http\Controllers\Tenant\RenewalController::class, 'edit'])
                ->whereUuid('renewal')
                ->name('edit');
            Route::put('/{renewal}', [\App\Http\Controllers\Tenant\RenewalController::class, 'update'])
                ->whereUuid('renewal')
                ->name('update');
        });
    });

    Route::prefix('venues')->group(function () {
        Route::name('venues.')->group(function () {
            Route::get('/', [VenueController::class, 'index'])->name('index');
            Route::get('/new', [VenueController::class, 'new'])->name('new');
            Route::post('/', [VenueController::class, 'create']);
            Route::get('/{venue}', [VenueController::class, 'show'])
                ->whereNumber('venue')
                ->name('show');
            Route::get('/combobox', [VenueController::class, 'combobox'])
                ->name('combobox');
        });
    });

    Route::prefix('competitions')->group(function () {
        Route::name('competitions.')->group(function () {
            Route::get('/', [CompetitionController::class, 'index'])
                ->name('index');
            Route::get('/new', [CompetitionController::class, 'new'])
                ->name('new');
            Route::post('/', [CompetitionController::class, 'create']);
            Route::get('/pay-for-entries', [CompetitionMemberEntryPaymentController::class, 'viewPayable'])->name('pay');
            Route::post('/pay-for-entries', [CompetitionMemberEntryPaymentController::class, 'createPayment']);
            Route::prefix('{competition}')->group(function () {
                Route::get('/', [CompetitionController::class, 'show'])
                    ->name('show');
                Route::get('/edit', [CompetitionController::class, 'edit'])
                    ->name('edit');
                Route::put('/', [CompetitionController::class, 'update']);
                Route::name('files.')->group(function () {
                    Route::prefix('files')->group(function () {
                        Route::post('/upload', [CompetitionFileController::class, 'upload'])
                            ->name('upload');
                        Route::get('/{file}', [CompetitionFileController::class, 'view'])
                            ->whereUuid('file')
                            ->name('view');
                        Route::put('/{file}', [CompetitionFileController::class, 'update'])
                            ->whereUuid('file')
                            ->name('update');
                        Route::delete('/{file}', [CompetitionFileController::class, 'delete'])
                            ->whereUuid('file')
                            ->name('delete');
                    });
                });
                Route::prefix('entries')->group(function () {
                    Route::get('/', [CompetitionMemberEntryController::class, 'index'])
                        ->name('entries.index');
                    Route::get('/{entry}', [CompetitionMemberEntryController::class, 'show'])
                        ->whereUuid('entry')
                        ->name('entries.show');
                });
                Route::prefix('guest-entries')->group(function () {
                    Route::get('/', [CompetitionGuestEntryController::class, 'index'])
                        ->name('guest_entries.index');
                    Route::get('/report', [CompetitionGuestEntryReportController::class, 'report'])
                        ->name('guest_entries.report');
                    Route::get('/{entry}', [CompetitionGuestEntryController::class, 'show'])
                        ->whereUuid('entry')
                        ->name('guest_entries.show');
                    Route::put('/{entry}', [CompetitionGuestEntryController::class, 'update'])
                        ->whereUuid('entry');
                });
                Route::prefix('sessions')->group(function () {
                    Route::name('sessions.')->group(function () {
                        Route::get('/', [CompetitionSessionController::class, 'show'])
                            ->name('index');
                        Route::post('/', [CompetitionSessionController::class, 'create']);
                        Route::get('/{session}', [CompetitionSessionController::class, 'show'])
                            ->whereNumber('session')
                            ->name('show');
                        Route::put('/{session}', [CompetitionSessionController::class, 'update'])
                            ->whereNumber('session');
                        Route::get('/{session}/edit', [CompetitionSessionController::class, 'edit'])
                            ->whereNumber('session')
                            ->name('edit');
                        Route::post('/{session}/events', [CompetitionEventController::class, 'create'])
                            ->whereNumber('session')
                            ->name('events.create');
                        Route::delete('/{session}/events/{event}', [CompetitionEventController::class, 'delete'])
                            ->whereNumber('session')
                            ->whereNumber('event')
                            ->name('events.delete');
                    });
                });
                Route::prefix('/enter')->group(function () {
                    Route::get('/', [CompetitionMemberEntryHeaderController::class, 'new'])
                        ->name('enter');
                    //                    Route::get('/{header}/pay-now', [CompetitionGuestEntryPaymentController::class, 'start'])
                    //                        ->whereUuid('header')
                    //                        ->name('enter_as_guest.pay');
                    //                    Route::post('/{header}/express-pay', [CompetitionGuestEntryPaymentController::class, 'startJson'])
                    //                        ->whereUuid('header')
                    //                        ->name('enter_as_guest.express_pay');
                    Route::get('/member/{member}', [CompetitionMemberEntryHeaderController::class, 'start'])
                        ->whereNumber(['member'])
                        ->name('enter.start');
                    Route::get('/member/{member}/enter', [CompetitionMemberEntryHeaderController::class, 'editEntry'])
                        ->whereNumber(['member'])
                        ->name('enter.edit_entry');
                    Route::put('/member/{member}/enter', [CompetitionMemberEntryHeaderController::class, 'updateEntry'])
                        ->whereNumber(['member']);
                    Route::get('/member/{member}/edit-session-availability', [CompetitionMemberEntryHeaderController::class, 'editEntrySelectSessions'])
                        ->whereNumber(['member'])
                        ->name('enter.edit-session-availability');
                    Route::put('/member/{member}/edit-session-availability', [CompetitionMemberEntryHeaderController::class, 'updateAvailableSessions'])
                        ->name('enter.edit-session-availability')
                        ->whereNumber('member');
                });
                Route::prefix('/members-available')->group(function () {
                    Route::get('/', [CompetitionMemberAvailableController::class, 'index'])
                        ->name('members-available');
                });
                Route::prefix('/enter-as-guest')->group(function () {
                    Route::get('/', [CompetitionGuestEntryHeaderController::class, 'new'])
                        ->name('enter_as_guest');
                    Route::post('/', [CompetitionGuestEntryHeaderController::class, 'create']);
                    Route::get('/{header}', [CompetitionGuestEntryHeaderController::class, 'show'])
                        ->whereUuid('header')
                        ->name('enter_as_guest.show');
                    Route::get('/{header}/pay-now', [CompetitionGuestEntryPaymentController::class, 'start'])
                        ->whereUuid('header')
                        ->name('enter_as_guest.pay');
                    Route::post('/{header}/express-pay', [CompetitionGuestEntryPaymentController::class, 'startJson'])
                        ->whereUuid('header')
                        ->name('enter_as_guest.express_pay');
                    Route::get('/{header}/entry/{entrant}', [CompetitionGuestEntryHeaderController::class, 'editEntry'])
                        ->whereUuid(['header', 'entrant'])
                        ->name('enter_as_guest.edit_entry');
                    Route::put('/{header}/entry/{entrant}', [CompetitionGuestEntryHeaderController::class, 'updateEntry'])
                        ->whereUuid(['header', 'entrant']);
                });

            })->whereNumber('competition');
            //                    Route::get('/{header}/pay-now', [CompetitionGuestEntryPaymentController::class, 'start'])
            //                        ->whereUuid('header')
            //                        ->name('enter_as_guest.pay');
        });
    });

    Route::prefix('settings')->group(function () {
        Route::name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/payments', [SettingsController::class, 'showPaymentSettings'])->name('payments');
            Route::put('/payments', [SettingsController::class, 'updatePaymentSettings']);
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
    Route::any('/{path}', function (Request $request) {
        return Inertia::location(url('/v1'.$request->getRequestUri()));
    })->where('path', '.*');
});
