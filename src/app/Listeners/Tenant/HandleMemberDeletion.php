<?php

namespace App\Listeners\Tenant;

use App\Events\Tenant\MemberDeletionCompleted;
use App\Events\Tenant\MemberDeletionFailed;
use App\Events\Tenant\MemberDeletionRequested;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandleMemberDeletion implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MemberDeletionRequested $event): void
    {
        $member = $event->member;

        DB::beginTransaction();

        try {

            // Delete extra fees
            $member->extraFees()->detach();

            // Delete meet results
            DB::table('meetResults')->where('Member', $member->MemberID)->delete();

            // Delete member email addresses
            DB::table('memberEmailAddresses')->where('Member', $member->MemberID)->delete();

            // Delete medical notes
            $member->memberMedical()->delete();

            // Delete photography details
            $member->photographyPermissions()->delete();

            // Delete pending squad moves
            $member->squadMoves()->delete();

            // Delete squad memberships
            $member->squads()->detach();

            // Delete targeted list memberships
            DB::table('targetedListMembers')
                ->where('ReferenceID', $member->MemberID)
                ->where('ReferenceType', 'Member')
                ->delete();

            if (DB::getSchemaBuilder()->hasTable('times')) {
                DB::table('times')->where('MemberID', $member->MemberID)->delete();
            }

            if (DB::getSchemaBuilder()->hasTable('timesIndividual')) {
                DB::table('timesIndividual')->where('MemberID', $member->MemberID)->delete();
            }

            // Deactivate the member
            $member->Active = false;
            //            $member->MForename = Str::padLeft('', Str::length($member->MForename), 'X');
            //            $member->MMiddleNames = Str::padLeft('', Str::length($member->MMiddleNames), 'X');
            //            $member->MSurname = Str::padLeft('', Str::length($member->MSurname), 'X');
            $member->OtherNotes = '';
            $member->AccessKey = Str::random();
            $member->ASAMember = false;
            $member->ASANumber = 'FORMER-MEMBER-'.$member->MemberID;
            $member->ASAPrimary = false;
            $member->ASAPaid = false;
            $member->ClubMember = false;
            $member->ClubPaid = false;
            $member->Country = 'GB-ENG'; // Default for all members
            $member->GenderPronouns = null;
            $member->GenderIdentity = null;
            $member->GenderDisplay = false;
            $member->PWHash = null;
            $member->PWWrong = 0;
            $member->user()->dissociate();
            $member->save();

            DB::commit();

            // On success fire the MemberDeletionCompleted event
            MemberDeletionCompleted::dispatch($event->member->name, $event->deletingFor, $event->member->user);

        } catch (\Exception $e) {
            DB::rollBack();

            report($e);

            // On failure fire the MemberDeletionFailed event
            MemberDeletionFailed::dispatch($member, $event->deletingFor, $e->getMessage());
        }
    }
}
