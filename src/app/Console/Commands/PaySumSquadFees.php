<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\Tenant\Member;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Brick\Math\RoundingMode;
use Illuminate\Console\Command;

class PaySumSquadFees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant_payments:sum_squad_fees';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sums up squad and extra fee payments for today\'s tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $date = today()->day;

        $tenants = Tenant::where('data->use_payments_v2', true)->where('data->squad_fee_calculation_date', $date)->get();

        foreach ($tenants as $tenant) {
            /** @var Tenant $tenant */
            $tenant->run(function () use ($tenant) {
                // Calculate for this tenant
                echo $tenant->Name;

                $users = User::all();
                foreach ($users as $user) {
                    /** @var User $user */

                    $numberOfPayingMembers = 0;
                    $discount = 0;

                    $discountMembers = [];

                    $members = $user->members();
                    foreach ($members as $member) {
                        $paying = false;
                        $memberTotal = 0;

                        /** @var Member $member */
                        $squads = $member->squads();
                        foreach ($squads as $squad) {
                            /** @var Squad $squad */

                            if ($squad->pivot->Paying) {
                                $paying = true;
                            }

                            // Add transaction
                            $user->journal->debit($squad->fee, $member->name . ' - ' . $squad->SquadName . ' Squad Fees');
                            $memberTotal += $squad->fee;

                            // Credit if we're not paying
                            if (!$squad->pivot->Paying) {
                                $user->journal->credit($squad->fee, $member->name . ' - ' . $squad->SquadName . ' Squad Fee Exemption');
                                $memberTotal -= $squad->fee;
                            }
                        }

                        if ($paying) {
                            $numberOfPayingMembers++;
                        }

                        if ($paying && $tenant->Code == "CLSE") {
                            $memberFees = [
                                'fee' => $memberTotal,
                                'member' => $member->name,
                            ];
                            $discountMembers[] = $memberFees;
                        }
                    }

                    // If CLS deal with discounts in code
                    // To be removed when configurable discounts codes
                    if ($tenant->Code == "CLSE") {
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
                                $user->journal->credit($swimmerDiscount, $member['member'] . ' - Multi swimmer squad fee discount (' . $discountPercent . '%)');
                            }
                        }
                    }

                    // Calculate extra fees

                }
            });
        }

        return Command::SUCCESS;
    }
}
