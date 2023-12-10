<?php

namespace App\Exports\Tenant\Sheets;

use App\Business\Helpers\EntryTimeHelper;
use App\Models\Tenant\Competition;
use App\Models\Tenant\CompetitionEntry;
use App\Models\Tenant\CompetitionEventEntry;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompetitionGuestEntrySheet implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    private Competition $competition;

    private string $sex;

    public function __construct(Competition $competition, string $sex)
    {
        $this->competition = $competition;
        $this->sex = $sex;
    }

    public function collection()
    {
        return CompetitionEntry::where('competition_id', $this->competition->id)
            ->whereRelation('competitionGuestEntrant', 'sex', '=', $this->sex)
            ->with([
                'competitionGuestEntrant',
                'competitionEventEntries',
                'competitionEventEntries.competitionEvent',
            ])
            ->get();
    }

    public function map($row): array
    {
        /** @var CompetitionEntry $row */
        $entrantData = [
            $row->id,
            $row->competitionGuestEntrant->name,
            $row->competitionGuestEntrant->date_of_birth->format('d/m/Y'),
            $row->competitionGuestEntrant->ageAt($this->competition->age_at_date),
            null,
            null,
            $row->amount_string,
            $row->paid,
            $row->processed,
        ];

        $customFields = collect($row->competitionGuestEntrant->getCustomFieldData($this->competition));

        $customFields->each(function ($field) use (&$entrantData) {
            $entrantData[] = $field['friendly_value'];
        });

        $events = $row->competitionEventEntries->map(function (CompetitionEventEntry $entry) {
            return [
                null,
                null,
                null,
                null,
                $entry->competitionEvent->name,
                EntryTimeHelper::formatted($entry->entry_time),
                $entry->amount_string,
                null,
                null,
            ];
        });

        return [
            $entrantData,
            ...$events,
        ];
    }

    public function headings(): array
    {
        $headings = [
            'Entry ID',
            'Name',
            'Date of birth',
            'Age on day',
            'Event',
            'Time',
            'Amount',
            'Paid',
            'Processed',
        ];

        $fields = collect(Arr::get($this->competition->custom_fields, 'guest_entrant_fields', []));

        $fields->each(function ($field) use (&$headings) {
            $headings[] = $field['label'];
        });

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return $this->sex == 'Male' ? 'Open' : 'Female';
    }

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
