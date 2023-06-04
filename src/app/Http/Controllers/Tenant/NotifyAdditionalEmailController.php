<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\NotifyAdditionalEmail;
use App\Models\Tenant\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class NotifyAdditionalEmailController extends Controller
{
    public function show(Request $request, $data)
    {
        $data = json_decode(urldecode($data));
        /** @var User $user */
        $user = User::find($data->user);

        // Has the email already been added?
        $already = $user->notifyAdditionalEmails()->where('EmailAddress', $data->email)->first();

        $encryptedData = Crypt::encryptString(json_encode($data));

        return Inertia::render('NotifyAdditionalEmails/Confirm', [
            'already' => $already != null,
            'user' => $user->name,
            'name' => $data->name,
            'email' => $data->email,
            'form_initial_values' => ['data' => $encryptedData],
        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'data' => [
                'required',
            ],
        ]);

        $data = json_decode(Crypt::decryptString($request->input('data')));
        /** @var User $user */
        $user = User::find($data->user);

        // Has the email already been added?
        $already = $user->notifyAdditionalEmails()->where('EmailAddress', $data->email)->first();

        if (! $already) {
            $additionalEmail = new NotifyAdditionalEmail();
            $additionalEmail->Name = $data->name;
            $additionalEmail->EmailAddress = $data->email;
            $additionalEmail->Verified = true;
            $user->notifyAdditionalEmails()->save($additionalEmail);
            $request->session()->flash('success', 'Your email address has been verified. You will now receive copies of squad update emails sent to '.$user->name.'.');
        }

        return Redirect::back();
    }

    /**
     * @throws AuthorizationException
     */
    public function delete(Request $request, NotifyAdditionalEmail $additionalEmail)
    {
        $this->authorize('delete', $additionalEmail);

        $additionalEmail->delete();

        $request->session()->flash('flash_bag.delete_additional_emails.success', 'We have removed '.$additionalEmail->Name.' from your list of additional recipients.');

        return Redirect::route('my_account.email');
    }
}
