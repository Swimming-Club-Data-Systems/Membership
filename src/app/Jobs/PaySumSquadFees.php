<?php

namespace App\Jobs;

use App\Models\Accounting\Journal;
use App\Models\Central\Tenant;
use App\Models\Tenant\ExtraFee;
use App\Models\Tenant\Member;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PaySumSquadFees implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Tenant $tenant
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->tenant->run(function () {
            $users = User::where('Active', true)->get();
            foreach ($users as $user) {
                /** @var User $user */

                /** @var Journal $journal */
                $user->getJournal();
                $journal = $user->journal;

                $numberOfPayingMembers = 0;
                $discount = 0;

                $discountMembers = [];

                $members = $user->members()->get();
                foreach ($members as $member) {
                    $paying = false;
                    $memberTotal = 0;

                    /** @var Member $member */
                    $squads = $member->squads()->get();
                    foreach ($squads as $squad) {
                        /** @var Squad $squad */

                        if ($squad->pivot->Paying) {
                            $paying = true;
                        }

                        // Add transaction
                        $journal->debit($squad->fee, $member->name . ' - ' . $squad->SquadName . ' Squad Fees');
                        $memberTotal += $squad->fee;

                        // Credit if we're not paying
                        if (!$squad->pivot->Paying) {
                            $journal->credit($squad->fee, $member->name . ' - ' . $squad->SquadName . ' Squad Fee Exemption');
                            $memberTotal -= $squad->fee;
                        }
                    }

                    if ($paying) {
                        $numberOfPayingMembers++;
                    }

                    if ($paying && $this->tenant->Code == "CLSE") {
                        $memberFees = [
                            'fee' => $memberTotal,
                            'member' => $member->name,
                        ];
                        $discountMembers[] = $memberFees;
                    }
                }

                // If CLS deal with discounts in code
                // To be removed when configurable discounts codes
                if ($this->tenant->Code == "CLSE") {
                    usort($discountMembers, function ($item1, $item2) {
                        return $item2['fee'] <=> $item1['fee'];
                    });

                    $number = 0;
                    foreach ($discountMembers as $member) {
                        $number++;

                        // Calculate discounts if required.
                        // Always round discounted value down - Could save clubs pennies!
                        $swimmerDiscount = 0;
                        $discountPercent = '0';
                        try {
                            $memberTotalDec = $member['fee'];
                            if ($number == 3) {
                                // 20% discount applies
                                $swimmerDiscount = $memberTotalDec->multipliedBy('0.20')->toInt(); // TODO check multiply code results
                                $discountPercent = '20';
                            } else if ($number > 3) {
                                // 40% discount applies
                                $swimmerDiscount = $memberTotalDec->multipliedBy('0.40')->toInt(); // TODO check multiply code results
                                $discountPercent = '40';
                            }
                        } catch (\Exception $e) {
                            // Something went wrong so ensure these stay zero!
                            $swimmerDiscount = 0;
                            $discountPercent = '0';
                        }

                        if ($swimmerDiscount > 0) {
                            // Apply credit to account for discount
                            $journal->credit($swimmerDiscount, $member['member'] . ' - Multi swimmer squad fee discount (' . $discountPercent . '%)');
                        }
                    }
                }

                // Calculate extra fees
                foreach ($user->members()->get() as $member) {
                    /** @var Member $member */
                    foreach ($member->extraFees()->get() as $extra) {
                        /** @var ExtraFee $extra */
                        if ($extra->Type == 'Payment') {
                            $journal->debit($extra->fee, $member->name . ' - ' . $extra->ExtraName);
                        } else if ($extra->Type == 'Refund') {
                            $journal->credit($extra->fee, $member->name . ' - ' . $extra->ExtraName);
                        }
                    }
                }
            }
        });
    }
}
