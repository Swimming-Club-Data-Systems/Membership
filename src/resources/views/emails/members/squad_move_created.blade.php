<x-mail::message>
# Hello {{$squadMove->member->user->Forename}},

@if($squadMove->newSquad && $squadMove->oldSquad)
{{ $squadMove->member->name }} will be moving to {{ $squadMove->newSquad->SquadName }} and leaving {{ $squadMove->oldSquad->SquadName }} on {{ $squadMove->Date->toFormattedDayDateString() }}.
@elseif($squadMove->oldSquad)
{{ $squadMove->member->name }} will be leaving {{ $squadMove->oldSquad->SquadName }} on {{ $squadMove->Date->toFormattedDayDateString() }}.
@elseif($squadMove->newSquad)
{{ $squadMove->member->name }} will be joining {{ $squadMove->newSquad->SquadName }} on {{ $squadMove->Date->toFormattedDayDateString() }}.
@endif

@if($squadMove->newSquad)
@if($squadMove->Paying && $squadMove->newSquad->fee > 0)
The fee payable for {{ $squadMove->newSquad->SquadName }} is {{\App\Business\Helpers\Money::formatCurrency($squadMove->newSquad->fee)}} per month.
@else
{{ $squadMove->member->name }} will not be charged for their place in {{ $squadMove->newSquad->SquadName }}.
@endif
@endif

If you have any questions, please contact your coach or a member of club staff.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
</x-mail::message>
