<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\MembershipPlan;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\Certification;
use App\Models\TrainerCertification;
use App\Models\FitnessClass;
use App\Models\ClassSchedule;
use App\Models\Equipment;
use App\Models\ClassEquipment;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\EquipmentUsage;
use App\Models\MembershipUpgrade;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@gym.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create test user for login
        $testUser = User::create([
            'name' => 'Test Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        // Create 4 membership plans
        $plans = [];
        foreach (['Bronze Monthly', 'Silver Monthly', 'Gold Annual', 'Platinum Annual'] as $name) {
            $plans[] = MembershipPlan::create(['plan_name' => $name, 'price' => 50, 'duration_months' => 1, 'description' => 'Plan']);
        }

        // Create member linked to test user
        Member::create([
            'user_id' => $testUser->id,
            'first_name' => 'Test',
            'last_name' => 'Member',
            'phone' => '555-0100',
            'date_of_birth' => now()->subYears(30),
            'plan_id' => $plans[0]->id,
        ]);

        // Create 5 trainers
        $trainers = [];
        foreach (['John Smith', 'Sarah Johnson', 'Mike Brown', 'Emma Davis', 'David Wilson'] as $i => $name) {
            [$first, $last] = explode(' ', $name);
            $trainerUser = User::create([
                'name' => $name,
                'email' => "trainer$i@gym.com",
                'password' => Hash::make('password'),
                'role' => 'trainer',
            ]);
            $trainers[] = Trainer::create([
                'user_id' => $trainerUser->id,
                'first_name' => $first,
                'last_name' => $last,
                'specialization' => 'Fitness',
                'phone' => "555-010$i",
                'hourly_rate' => 60 + $i
            ]);
        }

        // Create 6 certifications
        $certifications = [];
        foreach (['CPR', 'NASM', 'ACE', 'ISSF', 'IYASA', 'AFAA'] as $cert) {
            $certifications[] = Certification::create(['cert_name' => $cert, 'issuing_organization' => 'Org', 'cert_number' => $cert . '001', 'issue_date' => now(), 'expiry_date' => now()->addYears(3)]);
        }

        // Create 8 fitness classes  
        $classes = [];
        foreach (['Yoga', 'HIIT', 'Cardio', 'Weightlifting', 'Pilates', 'Zumba', 'Boxing', 'Spinning'] as $i => $name) {
            $classes[] = FitnessClass::create(['class_name' => $name, 'description' => $name, 'max_participants' => 20, 'trainer_id' => $trainers[$i % 5]->id]);
        }

        // Create 8 pieces of equipment
        $equipment = [];
        foreach (['Dumbbells', 'Treadmill', 'Elliptical', 'Stationary Bike', 'Yoga Mat', 'Bench Press', 'Rowing Machine', 'Pull-up Bar'] as $name) {
            $equipment[] = Equipment::create(['equipment_name' => $name]);
        }

        // Create 20 members with users
        $members = [];
        for ($i = 1; $i <= 20; $i++) {
            $memberUser = User::create([
                'name' => "Member$i Test",
                'email' => "m$i@gym.com",
                'password' => Hash::make('password'),
                'role' => 'member',
            ]);
            $members[] = Member::create([
                'user_id' => $memberUser->id,
                'first_name' => "Member$i",
                'last_name' => 'Test',
                'phone' => "555-010$i",
                'date_of_birth' => now()->subYears(30),
                'plan_id' => $plans[0]->id
            ]);
        }

        // Create 20 class schedules  
        $schedules = [];
        foreach ($classes as $class) {
            for ($i = 0; $i < 2; $i++) {
                $schedules[] = ClassSchedule::create(['class_id' => $class->id, 'class_date' => now()->addDays($i), 'start_time' => '10:00', 'end_time' => '11:00']);
            }
        }

        // Create 30 attendance records
        $count = 0;
        foreach ($members as $j => $member) {
            if ($count < 30) {
                Attendance::create(['member_id' => $member->id, 'schedule_id' => $schedules[$j % count($schedules)]->id, 'attendance_status' => 'Present', 'recorded_at' => now()]);
                $count++;
            }
        }

        // Create 20 payments
        foreach ($members as $member) {
            Payment::create(['member_id' => $member->id, 'amount_paid' => 50, 'payment_date' => now(), 'payment_method' => 'Card', 'coverage_start' => now(), 'coverage_end' => now()->addMonths(1), 'status' => 'Completed']);
        }

        echo "\n✅ Backend setup complete! Database populated with test data.\n";
        echo "✅ Admin user created: admin@gym.com / password\n";
        echo "✅ Test user created: member@example.com / password\n";
    }
}

