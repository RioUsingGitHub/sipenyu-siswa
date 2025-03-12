<?php

namespace Database\Seeders;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create teachers
        $teacher1 = User::create([
            'name' => 'Jesika, S.Pd',
            'email' => 'jesika@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        $teacher2 = User::create([
            'name' => 'Budi, S.Pd',
            'email' => 'budi@example.com',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        // Create teacher profiles
        $teacherProfile1 = Teacher::create([
            'name' => 'Jesika, S.Pd',
            'nip' => '198501012010012001',
            'gender' => 'P',
            'user_id' => $teacher1->id,
        ]);

        $teacherProfile2 = Teacher::create([
            'name' => 'Budi, S.Pd',
            'nip' => '198601012010011001',
            'gender' => 'L',
            'user_id' => $teacher2->id,
        ]);

        // Create classes
        $class7A = ClassRoom::create([
            'name' => '7A',
            'grade' => 7,
            'year' => '2023/2024',
            'is_active' => true,
        ]);

        $class7B = ClassRoom::create([
            'name' => '7B',
            'grade' => 7,
            'year' => '2023/2024',
            'is_active' => true,
        ]);

        $class8A = ClassRoom::create([
            'name' => '8A',
            'grade' => 8,
            'year' => '2023/2024',
            'is_active' => true,
        ]);

        // Create subjects
        $matematika = Subject::create([
            'name' => 'Matematika',
            'code' => 'MTK',
        ]);

        $fisika = Subject::create([
            'name' => 'Fisika',
            'code' => 'FIS',
        ]);

        $kimia = Subject::create([
            'name' => 'Kimia',
            'code' => 'KIM',
        ]);

        $bahasaIndonesia = Subject::create([
            'name' => 'Bahasa Indonesia',
            'code' => 'BIN',
        ]);

        $seniBudaya = Subject::create([
            'name' => 'Seni Budaya',
            'code' => 'SBD',
        ]);

        // Create students
        $student1 = Student::create([
            'nisn' => '1234567890',
            'name' => 'Khoirul Fuad',
            'password' => Hash::make('password'),
            'gender' => 'L',
        ]);

        $student2 = Student::create([
            'nisn' => '0987654321',
            'name' => 'Siti Aminah',
            'password' => Hash::make('password'),
            'gender' => 'P',
        ]);

        $student3 = Student::create([
            'nisn' => '1122334455',
            'name' => 'Ahmad Rizki',
            'password' => Hash::make('password'),
            'gender' => 'L',
        ]);

        // Assign students to classes
        $student1->classes()->attach($class7A->id, ['is_active' => true]);
        $student2->classes()->attach($class7A->id, ['is_active' => true]);
        $student3->classes()->attach($class7B->id, ['is_active' => true]);

        // Create schedules
        $schedule1 = \App\Models\Schedule::create([
            'class_id' => $class7A->id,
            'subject_id' => $matematika->id,
            'teacher_id' => $teacherProfile1->id,
            'day' => 'Senin',
            'time_start' => '07:30',
            'time_end' => '08:50',
            'year' => '2023/2024',
            'semester' => '1',
            'is_active' => true,
        ]);

        $schedule2 = \App\Models\Schedule::create([
            'class_id' => $class7A->id,
            'subject_id' => $fisika->id,
            'teacher_id' => $teacherProfile2->id,
            'day' => 'Senin',
            'time_start' => '08:50',
            'time_end' => '10:00',
            'year' => '2023/2024',
            'semester' => '1',
            'is_active' => true,
        ]);

        $schedule3 = \App\Models\Schedule::create([
            'class_id' => $class7A->id,
            'subject_id' => $bahasaIndonesia->id,
            'teacher_id' => $teacherProfile1->id,
            'day' => 'Senin',
            'time_start' => '10:25',
            'time_end' => '11:45',
            'year' => '2023/2024',
            'semester' => '1',
            'is_active' => true,
        ]);
    }
}

