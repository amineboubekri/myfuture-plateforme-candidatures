<?php

namespace App\Exports;

use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class ApplicationsExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with('user');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom complet',
            'Email',
            'Pays',
            'Statut',
            'Date de candidature',
            'Téléphone',
            'Âge',
            'Genre',
            'Niveau d\'éducation',
            'Expérience professionnelle',
            'Spécialisation',
        ];
    }

    public function map($application): array
    {
        return [
            $application->id,
            $application->user->name ?? 'N/A',
            $application->user->email ?? 'N/A',
            $application->country,
            $this->getStatusLabel($application->status),
            $application->created_at->format('Y-m-d H:i:s'),
            $application->phone ?? 'N/A',
            $application->age ?? 'N/A',
            $application->gender ?? 'N/A',
            $application->education_level ?? 'N/A',
            $application->work_experience ?? 'N/A',
            $application->specialization ?? 'N/A',
        ];
    }

    private function getStatusLabel($status)
    {
        $statuses = [
            'pending' => 'En attente',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
        ];

        return $statuses[$status] ?? $status;
    }
}
