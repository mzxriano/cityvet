<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class VaccinationReportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = DB::table('animal_vaccine')
            ->join('animals', 'animal_vaccine.animal_id', '=', 'animals.id')
            ->join('vaccines', 'animal_vaccine.vaccine_id', '=', 'vaccines.id')
            ->join('users', 'animals.user_id', '=', 'users.id')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->select([
                'animal_vaccine.id',
                'animal_vaccine.dose',
                'animal_vaccine.date_given',
                'animal_vaccine.administrator',
                'animals.name as animal_name',
                'animals.type as animal_type',
                'animals.breed',
                'animals.code as animal_code',
                'vaccines.name as vaccine_name',
                'vaccines.protect_against',
                DB::raw("CONCAT(users.first_name, ' ', users.last_name) as owner_name"),
                'users.id as owner_id',
                'users.barangay_id',
                'barangays.name as barangay_name',
                'animal_vaccine.created_at',
                'animal_vaccine.updated_at'
            ]);

        // Apply filters
        if (isset($this->filters['animal_type']) && !empty($this->filters['animal_type'])) {
            $query->where('animals.type', $this->filters['animal_type']);
        }

        if (isset($this->filters['barangay_id']) && !empty($this->filters['barangay_id'])) {
            $query->where('users.barangay_id', $this->filters['barangay_id']);
        }

        if (isset($this->filters['owner_role']) && !empty($this->filters['owner_role'])) {
            $ownerRole = $this->filters['owner_role'];
            $animalType = str_replace('_owner', '', $ownerRole);
            $query->whereExists(function ($subQuery) use ($animalType) {
                $subQuery->select(DB::raw(1))
                    ->from('animals as a2')
                    ->whereColumn('a2.user_id', 'users.id')
                    ->where('a2.type', $animalType);
            });
        }

        // Apply date range filters
        if (isset($this->filters['date_from']) && !empty($this->filters['date_from'])) {
            $query->whereDate('animal_vaccine.date_given', '>=', $this->filters['date_from']);
        }

        if (isset($this->filters['date_to']) && !empty($this->filters['date_to'])) {
            $query->whereDate('animal_vaccine.date_given', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('animal_vaccine.date_given', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No.',
            'Owner Name',
            'Pet Name',
            'Species',
            'Type',
            'Vaccine',
            'Date Given',
            'Dosage',
            'Administered By',
            'Barangay',
            'Animal Code',
            'Protection Against'
        ];
    }

    public function map($row): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $row->owner_name,
            $row->animal_name,
            $row->breed,
            ucfirst($row->animal_type),
            $row->vaccine_name,
            \Carbon\Carbon::parse($row->date_given)->format('M d, Y'),
            $row->dose,
            $row->administrator,
            $row->barangay_name ?? 'N/A',
            $row->animal_code,
            $row->protect_against
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'], // Green color
                ],
            ],
        ];
    }
}
