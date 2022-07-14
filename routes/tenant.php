<?php

use Illuminate\Support\Facades\Route;

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

define("LEGACY_PATH", "");

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
        include LEGACY_PATH . 'controllers/public/emergency-message.json.php';
    });
});

Route::get('/emergency-message.json', function () {
    // Renewal stuff
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
};

Route::prefix('sessions')->group($publicTimetable);

Route::prefix('timetable')->group($publicTimetable);

Route::get('/notify/unsubscribe/{userid}/{email}/{list}', function ($userid, $email, $list) {
    include LEGACY_PATH . 'controllers/notify/UnsubscribeHandler.php';
});

Route::prefix('onboarding/go')->group(function () {
    // Onboarding routes
});

Route::prefix('report-an-issue')->group(function () {
    Route::get('/', function () {
        // Report
    });

    Route::post('/', function () {
        // Report
    });
});

Route::get('/privacy', function () {
    // Show privacy page
});

Route::get('/cc/{id}/{hash}/unsubscribe', function ($id, $hash) {
    // Unsub
});

Route::prefix('payments/checkout')->group(function () {
    Route::get('/', function () {
        // Report
    });
});

Route::prefix('payments/webhooks')->group(function () {
    Route::get('/', function () {
        // Report
    });
});

Route::prefix('payments/stripe/webhooks')->group(function () {
    Route::get('/', function () {
        // Report
    });
});

Route::prefix('webhooks')->group(function () {
    Route::get('/', function () {
        // Report
    });
});

Route::get('/notify', function () {
    // Report
});

Route::prefix('log-books')->group(function () {
    Route::get('/', function () {
        // Report
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        // Report
    });

    Route::prefix('renewal')->group(function () {
        Route::get('/', function () {
            // Report
        });
    });

    Route::get('/account-switch', function () {
        // Report
    });

    Route::prefix('registration')->group(function () {
        Route::get('/', function () {
            // Report
        });
    });

    Route::prefix('users')->group(function () {
        Route::get('/', function () {
            // Report
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
            // Report
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
