<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant\NotifyHistory;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class NotifyHistoryController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage');

        $emails = null;

        if ($request->query('query')) {
            // Enable search once ready
            // $emails = NotifyHistory::search($request->query('query'))->paginate(config('app.per_page'));
        } else {
            $emails = NotifyHistory::orderBy('Date', 'desc')->paginate(config('app.per_page'));
        }
        return Inertia::render('Central/Notify/Index', [
            'emails' => $emails->onEachSide(3),
        ]);
    }

    public function show(NotifyHistory $email) {
        Gate::authorize('manage');
        return response()->json($email->jsonSerialize());
    }
}
