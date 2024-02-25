<?php

namespace App\Exports\Tenant;

use App\Exports\Tenant\Sheets\CompetitionMemberEntrySheet;
use App\Models\Tenant\Competition;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

class CompetitionMemberEntryExport implements WithMultipleSheets, WithProperties
{
    private Competition $competition;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    public function sheets(): array
    {
        return [
            new CompetitionMemberEntrySheet($this->competition, 'Male'),
            new CompetitionMemberEntrySheet($this->competition, 'Female'),
        ];
    }

    public function properties(): array
    {
        return [
            'title' => $this->competition->name.' Member Entry Export',
            'description' => 'Member entries for '.$this->competition->name,
            'subject' => 'Entries',
            'company' => tenant('Name'),
        ];
    }
}
