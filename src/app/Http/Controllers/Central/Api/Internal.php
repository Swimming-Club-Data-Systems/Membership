<?php

namespace App\Http\Controllers\Central\Api;

use App\Business\Helpers\AppMenu;
use App\Business\Helpers\Recipient;
use App\Http\Controllers\Controller;
use App\Mail\NotifyMail;
use App\Models\Tenant\NotifyHistory;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class Internal extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMenu(Request $request, $id)
    {
        $user = User::find($id);

        $menu = tenancy()->find($user->Tenant)->run(function () use ($user) {
            return AppMenu::asArray($user);
        });

        return response()->json($menu);
    }

    public function triggerEmailSend(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer'],
            'recipients.*.email' => ['email:rfc', 'required'],
            'recipients.*.name' => ['required'],
        ]);

        /** @var NotifyHistory $notifyEmail */
        $notifyEmail = NotifyHistory::find($request->integer('id'));

        $notifyEmail->tenant->run(function () use ($notifyEmail, $request) {

            // Enqueue email for those in the recipients array
            // Legacy string replacements will be ignored

            foreach ($request->input('recipients') as $recipient) {
                $recipientObject = new Recipient($recipient['name'], $recipient['email']);

                if (isset($recipient['unsubscribe_link'])) {
                    $recipientObject->unsubscribe_link = $recipient['unsubscribe_link'];
                }

                Mail::to($recipientObject)->queue(new NotifyMail($notifyEmail, $recipientObject));
            }
        });

        return response()->json([
            'success' => true,
        ]);
    }
}
