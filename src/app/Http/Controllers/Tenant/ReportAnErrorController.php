<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Mail\IssueReport;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ReportAnErrorController extends Controller
{
    public function create(Request $request)
    {
        $userData = [
            'id' => "",
            'name' => "",
            'email' => "",
        ];

        if ($request->user()) {
            /** @var User $user */
            $user = $request->user();
            $userData = [
                'id' => $user->UserID,
                'name' => $user->name,
                'email' => $user->EmailAddress,
            ];
        }

        return Inertia::render('ReportAnError', [
            'form_initial_values' => [
                'app' => 'tenant',
                'user' => $userData,
                'url' => $request->input('url', ''),
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user.name' => ['required', 'max:255'],
            'user.email' => ['required', 'email:rfc,dns', 'max:255'],
            'url' => ['required', 'url', 'max:1000'],
            'description' => ['required', 'max:1000'],
            'user_agent' => ['required', 'max:255'],
            'user_agent_brands' => ['json'],
            'data_sharing_agreement' => ['accepted']
        ]);

        Mail::to(\App\Models\Central\User::find(1))->send(new IssueReport(
            $request->input('user'),
            $request->input('url'),
            $request->input('description'),
            $request->input('user_agent'),
            $request->input('user_agent_brands'),
            $request->input('user_agent_platform'),
            $request->input('user_agent_mobile'),
        ));

        if (config('app.zoho_desk.auth')) {
            // TODO add support for sending data to Zoho Desk
            Http::withHeaders([
                'Authorization' => config('app.zoho_desk.auth'),
                'orgId' => config('app.zoho_desk.org_id'),
            ])->post('https://desk.zoho.com/api/v1/', [

            ]);
        }

        $request->session()->flash('success', "We've sent your error report. It will be reviewed as soon as possible.");

//        return Inertia::location(route('report_an_error'));
        return Redirect::route('report_an_error');
    }
}
