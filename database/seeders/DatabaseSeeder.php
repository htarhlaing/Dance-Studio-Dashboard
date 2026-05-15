<?php

namespace Database\Seeders;

use App\Models\DanceStudio\ClassType;
use App\Models\DanceStudio\PackageType;
use App\Models\DanceStudio\RegularClass;
use App\Models\DanceStudio\Room;
use App\Models\DanceStudio\Student;
use App\Models\DanceStudio\StudentPackage;
use App\Models\DanceStudio\Studio;
use App\Models\DanceStudio\Teacher;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $studio = Studio::query()->updateOrCreate(
            ['name' => 'Demo Dance Studio'],
            [
                'timezone' => 'Asia/Yangon',
                'currency' => 'MMK',
                'business_start_time' => '08:30:00',
                'business_end_time' => '20:30:00',
                'booking_interval_minutes' => 90,
                'is_active' => true,
            ]
        );

        $roomA = Room::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Studio A'],
            ['is_active' => true]
        );
        $roomB = Room::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Studio B'],
            ['is_active' => true]
        );
        $roomC = Room::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Studio C'],
            ['is_active' => true]
        );

        $passwordHash = Hash::make('password');

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => $passwordHash,
                'studio_id' => $studio->id,
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'frontdesk@example.com'],
            [
                'name' => 'Front Desk',
                'password' => $passwordHash,
                'studio_id' => $studio->id,
                'role' => 'front_desk',
            ]
        );

        $teacher1User = User::query()->updateOrCreate(
            ['email' => 'teacher1@example.com'],
            [
                'name' => 'Teacher A Account',
                'password' => $passwordHash,
                'studio_id' => $studio->id,
                'role' => 'teacher',
            ]
        );

        $teacher2User = User::query()->updateOrCreate(
            ['email' => 'teacher2@example.com'],
            [
                'name' => 'Teacher B Account',
                'password' => $passwordHash,
                'studio_id' => $studio->id,
                'role' => 'teacher',
            ]
        );

        $teacherA = Teacher::query()->updateOrCreate(
            ['user_id' => $teacher1User->id],
            [
                'studio_id' => $studio->id,
                'display_name' => 'Teacher A',
                'is_active' => true,
            ]
        );

        $teacherB = Teacher::query()->updateOrCreate(
            ['user_id' => $teacher2User->id],
            [
                'studio_id' => $studio->id,
                'display_name' => 'Teacher B',
                'is_active' => true,
            ]
        );

        $studentSuSu = Student::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Su Su'],
            ['is_active' => true]
        );
        Student::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Aye Aye'],
            ['is_active' => true]
        );
        $studentDavid = Student::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'David'],
            ['is_active' => true]
        );

        $regularClassType = ClassType::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Regular Class'],
            ['kind' => 'regular', 'default_deduct_units' => 1, 'is_active' => true]
        );

        ClassType::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Private Lesson'],
            ['kind' => 'private', 'default_duration_minutes' => 90, 'default_deduct_units' => 1, 'is_active' => true]
        );

        $packageType4 = PackageType::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Private 4 Lessons'],
            [
                'lessons_count' => 4,
                'price' => 0,
                'currency' => 'MMK',
                'is_active' => true,
            ]
        );
        $packageType8 = PackageType::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Private 8 Lessons'],
            [
                'lessons_count' => 8,
                'price' => 0,
                'currency' => 'MMK',
                'is_active' => true,
            ]
        );
        PackageType::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'name' => 'Private 12 Lessons'],
            [
                'lessons_count' => 12,
                'price' => 0,
                'currency' => 'MMK',
                'is_active' => true,
            ]
        );

        $now = now();

        StudentPackage::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'student_id' => $studentSuSu->id, 'package_type_id' => $packageType8->id],
            [
                'purchased_at' => $now,
                'total_units' => 8,
                'remaining_units' => 8,
                'status' => 'active',
            ]
        );

        StudentPackage::query()->updateOrCreate(
            ['studio_id' => $studio->id, 'student_id' => $studentDavid->id, 'package_type_id' => $packageType4->id],
            [
                'purchased_at' => $now,
                'total_units' => 4,
                'remaining_units' => 4,
                'status' => 'active',
            ]
        );

        RegularClass::query()->updateOrCreate(
            [
                'studio_id' => $studio->id,
                'day_of_week' => 1,
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'room_id' => $roomA->id,
            ],
            [
                'teacher_id' => $teacherA->id,
                'class_type_id' => $regularClassType->id,
                'is_active' => true,
            ]
        );

        RegularClass::query()->updateOrCreate(
            [
                'studio_id' => $studio->id,
                'day_of_week' => 1,
                'start_time' => '18:00:00',
                'end_time' => '19:00:00',
                'room_id' => $roomB->id,
            ],
            [
                'teacher_id' => $teacherB->id,
                'class_type_id' => $regularClassType->id,
                'is_active' => true,
            ]
        );

        RegularClass::query()->updateOrCreate(
            [
                'studio_id' => $studio->id,
                'day_of_week' => 2,
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'room_id' => $roomC->id,
            ],
            [
                'teacher_id' => $teacherA->id,
                'class_type_id' => $regularClassType->id,
                'is_active' => true,
            ]
        );
    }
}
