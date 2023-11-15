<x-mail::message>
# Hello {{$squadMove->member->user->Forename}},

A previously scheduled squad move for {{ $squadMove->member->name }} has been cancelled. The details of the cancelled squad move are as follows.

@if($squadMove->newSquad && $squadMove->oldSquad)
{{ $squadMove->member->name }} was moving to {{ $squadMove->newSquad->SquadName }} from {{ $squadMove->oldSquad->SquadName }} on {{ $squadMove->Date->toFormattedDayDateString() }}.
@elseif($squadMove->oldSquad)
{{ $squadMove->member->name }} was leaving {{ $squadMove->oldSquad->SquadName }} on {{ $squadMove->Date->toFormattedDayDateString() }}.
@elseif($squadMove->newSquad)
{{ $squadMove->member->name }} was joining {{ $squadMove->newSquad->SquadName }} on {{ $squadMove->Date->toFormattedDayDateString() }}.
@endif

@if($squadMove->newSquad)
@if($squadMove->Paying && $squadMove->newSquad->fee > 0)
The fee payable for {{ $squadMove->newSquad->SquadName }} would have been {{\App\Business\Helpers\Money::formatCurrency($squadMove->newSquad->fee)}} per month.
@else
{{ $squadMove->member->name }} was not going to be charged for their place in {{ $squadMove->newSquad->SquadName }}.
@endif
@endif

Again, for clarity, the above squad move for {{ $squadMove->member->name }} has been cancelled.

Please contact your coach or a member of club staff if you have questions about this change.

Kind regards,<br>
The {{ tenant()->getOption("CLUB_NAME") }} Team
</x-mail::message>
