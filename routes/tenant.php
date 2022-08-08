<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/laravel-welcome', function () {
        return view('welcome');
    });

    Route::prefix('dev')->group(function () {
        Route::get('/', function () {
            // Dev info routes for internal use
        });
    });

    Route::prefix('renewal')->group(function () {
        Route::get('/', function () {
            // Renewal stuff
        });
    });

    Route::get('/emergency-message.json', function (Request $request) {
        return include LEGACY_PATH . 'controllers/public/emergency-message.json.php';
    });

    Route::get('/public/{path}', function ($path) {
        // Renewal stuff
        $filename = 'public/' . $path;
        require LEGACY_PATH . 'controllers/PublicFileLoader.php';
    })->where('search', '.*');

    Route::get('/uploads/{path}', function ($path) {
        // Renewal stuff
        $filename = 'public/' . $path;
        require LEGACY_PATH . 'controllers/FileLoader.php';
    })->where('search', '.*');

    $publicTimetable = function () {
        // Public timetable pages
        Route::get('/', function () {
            include LEGACY_PATH . 'controllers/attendance/public_sessions/sessions.php';
        });

        Route::post('/jump-to-week', function () {
            include LEGACY_PATH . 'controllers/attendance/public_sessions/jump-to-week.php';
        });

        Route::prefix('/booking')->group(function () {
            Route::get('/', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/home.php';
            });

            Route::get('/my-bookings', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/my-bookings/my-bookings.php';
            });

            Route::get('/book', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/new-or-existing-handler.php';
            });

            Route::post('/book', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/book-session/book-session-post.php';
            });

            Route::get('/my-booking-info', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/book-session/my-members/ajax-my-members.php';
            });

            Route::get('/all-booking-info', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/book-session/my-members/ajax-all-members.php';
            });

            Route::post('/cancel', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/book-session/cancel-booking-post.php';
            });

            Route::post('/require-booking', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/require-booking/require-booking-post.php';
            });

            Route::get('/edit', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/require-booking/edit-require-booking.php';
            });

            Route::post('/edit', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/require-booking/edit-require-booking-post.php';
            });

            Route::get('/book-on-behalf-of', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/book-session/book-on-behalf-of.php';
            });

            Route::post('/book-on-behalf-of', function () {
                include LEGACY_PATH . 'controllers/attendance/booking/book-session/book-on-behalf-of-post.php';
            });
        });
    };

    Route::prefix('sessions')->group($publicTimetable);

    Route::prefix('timetable')->group($publicTimetable);

    Route::get('/notify/unsubscribe/{userid}/{email}/{list}', function ($userid, $email, $list) {
        include LEGACY_PATH . 'controllers/notify/UnsubscribeHandler.php';
    });

    Route::prefix('onboarding/go')->group(function () {
        // Onboarding routes
        Route::get('/', function () {
            if (!isset($_SESSION['OnboardingSessionId'])) {
                include LEGACY_PATH . 'controllers/onboarding/public/init.php';
            } else {
                include LEGACY_PATH . 'controllers/onboarding/public/start.php';
            }
        });

        Route::get('/error', function () {
            include LEGACY_PATH . 'controllers/onboarding/public/error.php';
        });

        Route::get('/wrong-account', function () {
            include LEGACY_PATH . 'controllers/onboarding/public/logged-in.php';
        });

        Route::get('/start-task', function () {
            include LEGACY_PATH . 'controllers/onboarding/public/task-handler.php';
        });

        Route::post('/start-task', function () {
            include LEGACY_PATH . 'controllers/onboarding/public/post-task-handler.php';
        });

        Route::prefix('/emergency-contacts')->group(function () {
            Route::get('/list', function () {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/emergency_contacts/list.php';
            });

            Route::post('/new', function () {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/emergency_contacts/new.php';
            });

            Route::post('/edit', function () {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/emergency_contacts/edit.php';
            });

            Route::post('/delete', function () {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/emergency_contacts/delete.php';
            });
        });

        Route::prefix('/direct-debit')->group(function () {
            Route::prefix('/stripe')->group(function () {
                Route::get('/set-up', function () {
                    include LEGACY_PATH . 'controllers/onboarding/public/tasks/direct_debit_mandate/stripe/set-up.php';
                });

                Route::get('/success', function () {
                    include LEGACY_PATH . 'controllers/onboarding/public/tasks/direct_debit_mandate/stripe/set-up.php';
                });
            });

            Route::prefix('/go-cardless')->group(function () {
                Route::get('/set-up', function () {
                    include LEGACY_PATH . 'controllers/onboarding/public/tasks/direct_debit_mandate/go-cardless/set-up.php';
                });

                Route::get('/success', function () {
                    include LEGACY_PATH . 'controllers/onboarding/public/tasks/direct_debit_mandate/go-cardless/set-up.php';
                });
            });
        });

        Route::prefix('/member-forms')->group(function () {
            Route::get('/{id}/start-task', function ($id) {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/member_forms/task-handler.php';
            })->whereUuid('id');

            Route::post('/{id}/start-task', function ($id) {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/member_forms/task-handler.php';
            })->whereUuid('id');
        });

        Route::prefix('/fees')->group(function () {
            Route::get('/success', function () {
                include LEGACY_PATH . 'controllers/onboarding/public/tasks/fees/success.php';
            });
        });

        Route::get('/sign-in', function () {
            include LEGACY_PATH . 'controllers/onboarding/public/sign-in.php';
        });

        Route::fallback(function () {
            include LEGACY_PATH . 'controllers/onboarding/public/init.php';
        });
    });

    Route::prefix('report-an-issue')->group(function () {
        Route::get('/', function () {
            include LEGACY_PATH . 'controllers/help/ReportIssueHandler.php';
        });

        Route::post('/', function () {
            include LEGACY_PATH . 'controllers/help/ReportIssuePost.php';
        });
    });

    Route::get('/privacy', function () {
        // Show privacy page
    });

    Route::get('/cc/{id}/{hash}/unsubscribe', function ($id, $hash) {
        // Unsub
        include LEGACY_PATH . 'controllers/notify/CCUnsubscribe.php';
    });

    Route::prefix('payments/checkout')->group(function () {
        Route::get('/', function () {
            include LEGACY_PATH . 'controllers/checkout/home.php';
        });

        Route::get('/v1/{id}', function ($id) {
            include LEGACY_PATH . 'controllers/checkout/v1/checkout-decide.php';
        })->whereUuid('id');
    });

    Route::get('/login/oauth', function () {
        include LEGACY_PATH . 'controllers/oauth-login.php';
    });

    Route::prefix('payments/webhooks')->group(function () {
        // include LEGACY_PATH . 'controllers/payments/webhooks/handler.php';
    });

    Route::prefix('payments/stripe/webhooks')->group(function () {
        // include LEGACY_PATH . 'controllers/payments/stripe/webhooks.php';
    });

    Route::prefix('webhooks')->group(function () {
        Route::any('/sumpayments', function () {
            include LEGACY_PATH . 'controllers/webhooks/sumpayments.php';
        });

        Route::any('/chargeusers', function () {
            // TODO Choose based on Stripe setting
            // include LEGACY_PATH . 'controllers/webhooks/charge-users-stripe.php';
            // include LEGACY_PATH . 'controllers/webhooks/charge-users-gc-legacy.php';
        });

        Route::any('/retrypayments', function () {
            include LEGACY_PATH . 'controllers/webhooks/retry-payments.php';
        });

        Route::any('/notifysend', function () {
            include LEGACY_PATH . 'controllers/webhooks/SingleEmailHandler.php';
        });

        Route::any('/newnotifysend', function () {
            include LEGACY_PATH . 'controllers/webhooks/notifyhandler.php';
        });

        Route::any('/handle-legacy-renewal-period-creation', function () {
            include LEGACY_PATH . 'controllers/webhooks/squadmemberupdate.php';
        });

        Route::any('/updateregisterweeks', function () {
            include LEGACY_PATH . 'controllers/webhooks/newWeek.php';
        });

        Route::any('/timeupdate', function () {
            include LEGACY_PATH . 'controllers/webhooks/getTimesNew.php';
        });

        Route::post('/checkout_v1', function () {
            include LEGACY_PATH . 'controllers/webhooks/checkout_v1.php';
        });

        Route::any('/checkout_v2', function () {
            include LEGACY_PATH . 'controllers/webhooks/checkout_v2.php';
        });
    });

    Route::get('/notify', function () {
        // Report
        // TODO keep info page or not?
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/', function () {
            // Report
        });

        Route::prefix('log-books')->group(function () {
            Route::get('/', function () {
                include LEGACY_PATH . 'controllers/log-books/members.php';
            });

            Route::get('/members/{member}', function ($member) {
                include LEGACY_PATH . 'controllers/log-books/member-logs.php';
            })->whereNumber('member');

            Route::get('/logs/{id}', function ($id) {
                include LEGACY_PATH . 'controllers/log-books/log.php';
            })->whereNumber('id');

            Route::get('/members/{member}/new', function ($member) {
                include LEGACY_PATH . 'controllers/log-books/new-log.php';
            })->whereNumber('member');

            Route::post('/members/{member}/new', function ($member) {
                include LEGACY_PATH . 'controllers/log-books/new-log-post.php';
            })->whereNumber('member');

            Route::get('/logs/{id}/edit', function ($id) {
                include LEGACY_PATH . 'controllers/log-books/edit-log.php';
            })->whereNumber('id');

            Route::post('/logs/{id}/edit', function ($id) {
                include LEGACY_PATH . 'controllers/log-books/edit-log-post.php';
            })->whereNumber('id');

            Route::get('/squads', function () {
                include LEGACY_PATH . 'controllers/log-books/squads.php';
            });

            Route::get('/squads/{squad}', function ($squad) {
                include LEGACY_PATH . 'controllers/log-books/squad-members.php';
            })->whereNumber('squad');

            Route::get('/squads/{squad}/recent', function ($squad) {
                include LEGACY_PATH . 'controllers/log-books/squad-most-recent-logs.php';
            })->whereNumber('squad');
        });

        Route::prefix('renewal')->group(function () {
            Route::get('/', function () {
                include LEGACY_PATH . 'controllers/renewal/admin/home.php';
            });

            Route::get('/{id}', function ($id) {
                include LEGACY_PATH . 'controllers/renewal/admin/list.php';
            })->whereNumber('id');
        });

        Route::get('/account-switch', function () {
            include LEGACY_PATH . 'controllers/account-switch.php';
        });

        Route::prefix('registration')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('users')->group(function () {
            Route::get('/simulate/exit', function () {
                include LEGACY_PATH . 'controllers/users/ExitSimulation.php';
            });

            Route::get('/', function () {
                include LEGACY_PATH . 'controllers/users/userDirectory.php';
            });

            Route::post('/ajax/userList', function () {
                include LEGACY_PATH . 'controllers/ajax/userList.php';
            });

            Route::post('/ajax/resend-registration-email', function () {
                include LEGACY_PATH . 'controllers/users/ResendRegEmail.php';
            });

            Route::get('/{id}', function ($id) {
                include LEGACY_PATH . 'controllers/users/user.php';
            })->whereNumber('id');

            Route::get('/{id}/current-memberships', function ($id) {
                include LEGACY_PATH . 'controllers/users/current-memberships.php';
            })->whereNumber('id');

            Route::get('/{id}/new-membership-batch', function ($id) {
                include LEGACY_PATH . 'controllers/users/new-membership-batch.php';
            })->whereNumber('id');

            Route::get('/{id}/new-membership-batch-select', function ($id) {
                include LEGACY_PATH . 'controllers/users/new-membership-batch-select.php';
            })->whereNumber('id');

            Route::get('/{id}/edit', function ($id) {
                include LEGACY_PATH . 'controllers/users/Edit.php';
            })->whereNumber('id');

            Route::post('/{id}/edit', function ($id) {
                include LEGACY_PATH . 'controllers/users/EditPost.php';
            })->whereNumber('id');

            Route::post('/{id}/email', function ($id) {
                include LEGACY_PATH . 'controllers/users/EditEmailAjax.php';
            })->whereNumber('id');

            Route::get('/{id}/mandates', function ($id) {
                include LEGACY_PATH . 'controllers/payments/admin/user-mandates/stripe-user-mandates.php';
            })->whereNumber('id');

            Route::get('/{id}/direct-debit', function ($id) {
                include LEGACY_PATH . 'controllers/payments/admin/user-mandates/stripe-user-mandates.php';
            })->whereNumber('id');

            Route::post('/{id}/force-run-info', function ($id) {
                include LEGACY_PATH . 'controllers/users/direct-debit/trigger-direct-debit-payment.php';
            })->whereNumber('id');

            Route::post('/{id}/force-run-submission', function ($id) {
                include LEGACY_PATH . 'controllers/users/direct-debit/trigger-direct-debit-payment-post.php';
            })->whereNumber('id');

            Route::get('/{person}/qualifications', function ($person) {
                include LEGACY_PATH . 'controllers/qualifications/MyQualifications.php';
            })->whereNumber('person');

            Route::get('/{person}/qualifications/new', function ($person) {
                include LEGACY_PATH . 'controllers/qualifications/admin/NewQualification.php';
            })->whereNumber('person');

            Route::post('/{person}/qualifications/new', function ($person) {
                include LEGACY_PATH . 'controllers/qualifications/admin/NewQualificationPost.php';
            })->whereNumber('person');

            Route::get('/{id}/qualifications/new', function ($id) {
                include LEGACY_PATH . 'controllers/payments/parent/MembershipFees.php';
            })->whereNumber('id');

            Route::get('/simulate/{id}', function ($id) {
                include LEGACY_PATH . 'controllers/users/EnterSimulation.php';
            })->whereNumber('id');

            Route::post('/ajax/username', function () {
                include LEGACY_PATH . 'controllers/users/usernameAjax.php';
            });

            Route::post('/delete-user', function () {
                include LEGACY_PATH . 'controllers/users/delete.php';
            });

            Route::post('/{id}/update-email-address', function ($id) {
                include LEGACY_PATH . 'controllers/users/EditEmailPost.php';
            })->whereNumber('id');

            Route::get('/assign-revoke-squad', function () {
                include LEGACY_PATH . 'controllers/users/coaches/assign-revoke-squad-post.php';
            })->whereNumber('id');

            Route::get('/{id}/rep', function ($id) {
                include LEGACY_PATH . 'controllers/users/squad-reps/list.php';
            })->whereNumber('id');

            Route::get('/{id}/rep/add', function ($id) {
                include LEGACY_PATH . 'controllers/users/squad-reps/add.php';
            })->whereNumber('id');

            Route::post('/{id}/rep/add', function ($id) {
                include LEGACY_PATH . 'controllers/users/squad-reps/add-post.php';
            })->whereNumber('id');

            Route::get('/{id}/rep/remove', function ($id) {
                include LEGACY_PATH . 'controllers/users/squad-reps/remove.php';
            })->whereNumber('id');

            Route::get('/{id}/team-manager', function ($id) {
                include LEGACY_PATH . 'controllers/users/team-managers/list.php';
            })->whereNumber('id');

            Route::get('/{id}/team-manager/add', function ($id) {
                include LEGACY_PATH . 'controllers/users/team-managers/add.php';
            })->whereNumber('id');

            Route::post('/{id}/team-manager/add', function ($id) {
                include LEGACY_PATH . 'controllers/users/team-managers/add-post.php';
            })->whereNumber('id');

            Route::get('/{id}/team-manager/remove', function ($id) {
                include LEGACY_PATH . 'controllers/users/team-managers/remove.php';
            })->whereNumber('id');

            Route::get('/{id}/targeted-lists', function ($id) {
                include LEGACY_PATH . 'controllers/users/notify-lists/list.php';
            })->whereNumber('id');

            Route::get('/{id}/targeted-lists/add', function ($id) {
                include LEGACY_PATH . 'controllers/users/notify-lists/add.php';
            })->whereNumber('id');

            Route::post('/{id}/targeted-lists/add', function ($id) {
                include LEGACY_PATH . 'controllers/users/notify-lists/add-post.php';
            })->whereNumber('id');

            Route::get('/{id}/targeted-lists/remove', function ($id) {
                include LEGACY_PATH . 'controllers/users/notify-lists/remove.php';
            })->whereNumber('id');

            Route::get('/{id}/pending-fees', function ($id) {
                include LEGACY_PATH . 'controllers/users/CurrentFees.php';
            })->whereNumber('id');

            Route::get('/{user}/email', function ($user) {
                $userOnly = true;
                include LEGACY_PATH . 'controllers/notify/EmailIndividual.php';
            })->whereNumber('user');

            Route::post('/{user}/email', function ($user) {
                $userOnly = true;
                include LEGACY_PATH . 'controllers/notify/EmailQueuerIndividual.php';
            })->whereNumber('user');

            Route::post('/squads/list', function () {
                include LEGACY_PATH . 'controllers/users/coaches/squad-list.php';
            });

            Route::post('/squads/assign-delete', function () {
                include LEGACY_PATH . 'controllers/users/coaches/assign-revoke-squad-post.php';
            });

            Route::get('/add', function () {
                include LEGACY_PATH . 'controllers/users/new/home.php';
            });

            Route::post('/add', function () {
                include LEGACY_PATH . 'controllers/users/new/new-post.php';
            });
        });

        Route::redirect('/login', '');

        Route::prefix('my-account')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('members')->group(function () {
            Route::get('/', function () {
                include LEGACY_PATH . 'controllers/swimmers/swimmerDirectory.php';
            });
        });

        Route::prefix('squads')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('squad-reps')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::redirect('/emergency-message', '/settings/variables#emergency-message');

        Route::prefix('team-managers')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('onboarding')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        $postsAndPages = function () {
            Route::get('/', function () {
                // Report
            });
        };

        Route::prefix('posts')->group($postsAndPages);

        Route::prefix('pages')->group($postsAndPages);

        Route::prefix('registration')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('memberships')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        $attendanceAndRegisters = function () {
            Route::get('/', function () {
                // Report
            });
        };

        Route::prefix('attendance')->group($attendanceAndRegisters);

        Route::prefix('registers')->group($attendanceAndRegisters);

        Route::prefix('users')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('admin')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('tick-sheets')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('galas')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('renewal')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('notify')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('log-books')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('emergency-contacts')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('payments')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('memberships')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('qualifications')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('resources')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::prefix('settings')->group(function () {
            Route::get('/', function () {
                // Report
            });
        });

        Route::get('/files/{path}', function ($path) {
            // Renewal stuff
            $filename = 'public/' . $path;
            require LEGACY_PATH . 'controllers/FileLoader.php';
        })->where('search', '.*');
    });
});
