<?php

namespace App\Exports\Tenant;

use App\Exports\Tenant\Sheets\CompetitionGuestEntrySheet;
use App\Models\Tenant\Competition;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProperties;

class CompetitionGuestEntryExport implements WithMultipleSheets, WithProperties
{
    private Competition $competition;

    public function __construct(Competition $competition)
    {
        $this->competition = $competition;
    }

    public function sheets(): array
    {
        return [
            new CompetitionGuestEntrySheet($this->competition, 'Male'),
            new CompetitionGuestEntrySheet($this->competition, 'Female'),
        ];
    }

    public function properties(): array
    {
        return [
            'title' => $this->competition->name.' Guest Entry Export',
            'description' => 'Guest entries for '.$this->competition->name,
            'subject' => 'Entries',
            'company' => tenant('Name'),
        ];
    }
}
