<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\ManualPaymentEntryLineType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ManualPaymentEntryLinePostRequest;
use App\Http\Requests\Tenant\ManualPaymentEntryUserPostRequest;
use App\Models\Accounting\Journal;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\ManualPaymentEntry;
use App\Models\Tenant\ManualPaymentEntryLine;
use App\Models\Tenant\User;
use App\Services\Accounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Money\Money;

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

    public function post(ManualPaymentEntry $entry, Request $request)
    {
        $this->authoriseAmendment($entry);

        if (!($entry->lines()->exists() && $entry->users()->exists())) {
            // Either no lines or users, abort
            abort(400, 'You can not post a Manual Payment Entry unless you have at least one valid user and at least one valid line.');
        }

        try {
            DB::beginTransaction();

            foreach ($entry->users()->get() as $user) {
                /** @var User $user */

                // Get the user journal
                /** @var Journal $userJournal */
                $userJournal = $user->getJournal();

                foreach ($entry->lines()->get() as $line) {
                    /** @var ManualPaymentEntryLine $line */
                    $doubleEntryGroup = Accounting::newDoubleEntryTransactionGroup();
                    $amount = Money::GBP($line->getAttribute($line->line_type));
                    $doubleEntryGroup->addTransaction($userJournal, $line->line_type, $amount, $line->description);
                    $doubleEntryGroup->addTransaction($line->accountingJournal, $line->line_opposite_type, $amount, $line->description);
                    $doubleEntryGroup->commit();
                }
            }

            DB::commit();

            $request->session()->flash('flash_bag.post.success', 'The Manual Payment Entry has been successfully posted.');

            return Redirect::route('payments.entries.amend', $entry);
        } catch (\Exception $e) {
            DB::rollBack();

            $request->session()->flash('flash_bag.post.danger', 'The Manual Payment Entry could not be posted posted.');

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
