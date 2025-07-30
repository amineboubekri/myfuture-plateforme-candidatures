<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'une candidature de test pour lier les étapes
        $user = \App\Models\User::where('role', 'admin')->first();
        $applicationId = \DB::table('applications')->insertGetId([
            'user_id' => $user->id,
            'university_name' => 'Université de Test',
            'country' => 'France',
            'program' => 'Informatique',
            'status' => 'pending',
            'priority_level' => 'medium',
            'estimated_completion_date' => now()->addMonths(3),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $steps = [
            ['step_name' => 'Dossier reçu', 'status' => 'completed'],
            ['step_name' => 'Documents en attente', 'status' => 'pending'],
            ['step_name' => 'Vérification dossier', 'status' => 'in_progress'],
            ['step_name' => 'Soumission université', 'status' => 'pending'],
        ];
        foreach ($steps as $step) {
            \DB::table('application_steps')->insert([
                'application_id' => $applicationId,
                'step_name' => $step['step_name'],
                'status' => $step['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
