<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequiredDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $requiredDocuments = [
            [
                'document_type' => 'CV',
                'description' => 'Curriculum Vitae complet et à jour',
                'is_active' => true,
            ],
            [
                'document_type' => 'Lettre de motivation',
                'description' => 'Lettre de motivation personnalisée pour le programme',
                'is_active' => true,
            ],
            [
                'document_type' => 'Diplômes',
                'description' => 'Copies certifiées conformes de tous les diplômes obtenus',
                'is_active' => true,
            ],
            [
                'document_type' => 'Relevés de notes',
                'description' => 'Relevés de notes officiels des études supérieures',
                'is_active' => true,
            ],
            [
                'document_type' => 'Certificat de langue',
                'description' => 'Certificat attestant du niveau de langue (TOEFL, IELTS, etc.)',
                'is_active' => true,
            ],
            [
                'document_type' => 'Passeport',
                'description' => 'Copie de la page d\'identité du passeport en cours de validité',
                'is_active' => true,
            ],
        ];

        foreach ($requiredDocuments as $document) {
            \App\Models\RequiredDocument::create($document);
        }
    }
}
