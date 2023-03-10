<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ManualPaymentEntryLineType;
use App\Exceptions\Accounting\DebitsAndCreditsDoNotEqual;
use App\Exceptions\ManualPaymentEntryNotReady;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ManualPaymentEntryLinePostRequest;
use App\Http\Requests\Tenant\ManualPaymentEntryUserPostRequest;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\ManualPaymentEntry;
use App\Models\Tenant\ManualPaymentEntryLine;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PaymentEntryController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function new(Request $request)
    {
        $this->authorize('create', ManualPaymentEntry::class);

        /** @var User $user */
        $user = $request->user();

        // Create a ManualPaymentEntry record to update in real time
        // Uncomplete manual payment entries are prunable

        $entry = new ManualPaymentEntry();
        $entry->user()->associate($user);
        $entry->save();

        return redirect()->route('payments.entries.amend', $entry);
    }

    public function amend(ManualPaymentEntry $entry)
    {
        $this->authoriseAmendment($entry);

        $users = $entry->users()->orderBy('created_at')->get();
        $users->transform(function (User $user) use ($entry) {
            return [
                'manual_payment_entry_id' => $entry->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        $lines = $entry->lines()->orderBy('created_at')->get();
        $lines->transform(function (ManualPaymentEntryLine $line) use ($entry) {

            $journalAccountName = $line?->accountingJournal?->morphed?->name;

            return [
                'manual_payment_entry_id' => $entry->id,
                'line_id' => $line->id,
                'description' => $line->description,
                'credit' => $line->credit,
                'debit' => $line->debit,
                'credit_formatted' => $line->credit_string,
                'debit_formatted' => $line->debit_string,
                'type' => $line->credit > 0 ? ManualPaymentEntryLineType::CREDIT : ManualPaymentEntryLineType::DEBIT,
                'journal_account_name' => $journalAccountName,
            ];
        });

        return Inertia::render('Payments/Entry', [
            'id' => $entry->id,
            'users' => $users,
            'lines' => $lines,
            'can_post' => $lines->count() > 0 && $users->count() > 0,
        ]);
    }

    private function authoriseAmendment($entry)
    {
        $this->authorize('amend', $entry);

        if ($entry->posted) {
            abort(400, 'The Manual Payment Entry you are trying to amend has already been posted.');
        }
    }

    /**
     * @throws ValidationException
     */
    public function post(ManualPaymentEntry $entry, Request $request)
    {
        $this->authoriseAmendment($entry);

        try {
            $entry->post();

            $request->session()->flash('flash_bag.post_transactions.success', 'The Manual Payment Entry has been successfully posted.');
            return Redirect::route('payments.entries.amend', $entry);
        } catch (ManualPaymentEntryNotReady) {
            $request->session()->flash('flash_bag.post_transactions.error', 'You can not post a Manual Payment Entry unless you have at least one valid user and at least one valid line.');
            return Redirect::route('payments.entries.amend', $entry);
        } catch (ValidationException $e) {
            // Re-throw this exception
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();

            $request->session()->flash('flash_bag.post_transactions.error', 'The Manual Payment Entry could not be posted.');
            return Redirect::route('payments.entries.amend', $entry);
        }
    }

    public function addUser(ManualPaymentEntry $entry, ManualPaymentEntryUserPostRequest $request)
    {
        $this->authoriseAmendment($entry);

        /** @var User $user */
        $user = User::findOrFail($request->integer('user_select'));

        $entry->users()->attach($user);

        $request->session()->flash('flash_bag.manage_users.success', "{$user->name} has been added.");

        return Redirect::route('payments.entries.amend', $entry);
    }

    public function deleteUser(ManualPaymentEntry $entry, User $user, Request $request)
    {
        $this->authoriseAmendment($entry);

        $entry->users()->detach($user);

        $request->session()->flash('flash_bag.manage_users.success', "{$user->name} has been removed.");

        return Redirect::route('payments.entries.amend', $entry);
    }

    public function addLine(ManualPaymentEntry $entry, ManualPaymentEntryLinePostRequest $request)
    {
        $this->authoriseAmendment($entry);

        $line = new ManualPaymentEntryLine();
        $line->description = $request->string('description');
        $type = $request->enum('type', ManualPaymentEntryLineType::class);

        if ($type == ManualPaymentEntryLineType::CREDIT) {
            $line->credit_string = $request->string('amount');
        } else {
            $line->debit_string = $request->string('amount');
        }

        // Set journal to use
        /** @var JournalAccount $journalAccount */
        $journalAccount = JournalAccount::find($request->integer('journal_select'));
        $line->accountingJournal()->associate($journalAccount->journal);

        $entry->lines()->save($line);

        $request->session()->flash('flash_bag.manage_lines.success', "{$line->description} has been added.");

        return Redirect::route('payments.entries.amend', $entry);
    }

    public function deleteLine(ManualPaymentEntry $entry, ManualPaymentEntryLine $line, Request $request)
    {
        $this->authoriseAmendment($entry);

        $line->delete();

        $request->session()->flash('flash_bag.manage_lines.success', "{$line->description} has been removed.");

        return Redirect::route('payments.entries.amend', $entry);
    }
}
