<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@sekolah.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Guru Piket 1',
            'email' => 'piket@sekolah.com',
            'password' => bcrypt('password'),
            'role' => 'guru_piket',
        ]);

        User::factory()->create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@sekolah.com',
            'password' => bcrypt('password'),
            'role' => 'kepala_sekolah',
        ]);

        User::factory()->create([
            'name' => 'Guru BK',
            'email' => 'bk@sekolah.com',
            'password' => bcrypt('password'),
            'role' => 'guru_bk',
        ]);

        // Import real data from JSON
        $jsonData = file_get_contents(__DIR__ . '/students_data.json');
        $classes = json_decode($jsonData, true);
        $seenNisns = [];

        foreach ($classes as $classData) {
            // Create Wali Kelas User
            $wali = User::create([
                'name' => $classData['wali_name'],
                'email' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $classData['wali_name'])) . '@sekolah.com',
                'password' => bcrypt('password'),
                'role' => 'wali_kelas'
            ]);

            // Create Class
            $schoolClass = \App\Models\SchoolClass::create([
                'name' => $classData['class_name'],
                'wali_kelas_id' => $wali->id
            ]);

            // Create Students
            foreach ($classData['students'] as $studentData) {
                // Determine identifier (use NISN if available, else NIS)
                $identifier = !empty($studentData['nisn']) ? $studentData['nisn'] : $studentData['nis'];
                
                // Handle duplicates
                $originalIdentifier = $identifier;
                $counter = 1;
                while (in_array($identifier, $seenNisns)) {
                    $identifier = $originalIdentifier . '-' . $counter;
                    $counter++;
                }
                $seenNisns[] = $identifier;
                
                \App\Models\Student::create([
                    'nisn' => $identifier,
                    'name' => $studentData['name'],
                    'school_class_id' => $schoolClass->id,
                    'no_wa_ortu' => null,
                ]);
            }
        }
    }
}
