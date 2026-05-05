<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MembershipPlan;
use App\Models\Member;
use App\Models\Trainer;
use App\Models\Certification;
use App\Models\FitnessClass;
use App\Models\ClassSchedule;
use App\Models\Equipment;
use App\Models\ClassEquipment;
use App\Models\Attendance;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\EquipmentUsage;
use App\Models\MembershipUpgrade;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default system users for admin/trainer dashboard login.
        User::firstOrCreate(
            ['email' => 'admin@gym.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'trainer0@gym.com'],
            [
                'name' => 'Trainer User',
                'password' => Hash::make('password'),
                'role' => 'trainer',
                'email_verified_at' => now(),
            ]
        );

        // Payment methods are already seeded in migration, but verify they exist
        if (PaymentMethod::count() === 0) {
            PaymentMethod::insert([
                ['method_name' => 'Cash'],
                ['method_name' => 'Credit Card'],
                ['method_name' => 'Debit Card'],
                ['method_name' => 'Bank Transfer'],
                ['method_name' => 'GCash'],
                ['method_name' => 'PayMaya'],
            ]);
        }

        // Create 3 membership plans
        $plans = [];
        $planData = [
            ['name' => 'Bronze', 'price' => 750, 'duration' => 1, 'classes' => 4, 'description' => 'Perfect for beginners'],
            ['name' => 'Silver', 'price' => 1000, 'duration' => 2, 'classes' => 8, 'description' => 'For regular gym-goers'],
            ['name' => 'Gold', 'price' => 1500, 'duration' => 3, 'classes' => 999, 'description' => 'Premium membership'],
        ];
        foreach ($planData as $data) {
            $plans[] = MembershipPlan::create([
                'plan_name' => $data['name'],
                'price' => $data['price'],
                'duration_months' => $data['duration'],
                'description' => $data['description'],
                'max_classes_per_week' => $data['classes'],
            ]);
        }

        // Create 5 trainers (standalone, no user relationship)
        $trainers = [];
        foreach (['John Smith', 'Sarah Johnson', 'Mike Brown', 'Emma Davis', 'David Wilson'] as $i => $name) {
            [$first, $last] = explode(' ', $name);
            $trainers[] = Trainer::create([
                'first_name' => $first,
                'last_name' => $last,
                'email' => "trainer$i@gym.com",
                'specialization' => 'Fitness',
                'phone' => "555-010$i",
            ]);
        }

        // Create 6 certifications
        $certifications = [];
        foreach (['CPR', 'NASM', 'ACE', 'ISSF', 'IYASA', 'AFAA'] as $cert) {
            $certifications[] = Certification::create([
                'cert_name' => $cert,
                'issuing_organization' => 'Professional Org',
            ]);
        }

        // Attach certifications to trainers
        foreach ($trainers as $trainer) {
            $trainer->certifications()->attach(
                $certifications[array_rand($certifications)],
                ['date_obtained' => now()]
            );
        }

        // Create 8 fitness classes  
        $classes = [];
        foreach (['Yoga', 'HIIT', 'Cardio', 'Weightlifting', 'Pilates', 'Zumba', 'Boxing', 'Spinning'] as $i => $name) {
            $classes[] = FitnessClass::create([
                'class_name' => $name,
                'description' => "$name fitness class for all levels",
                'max_participants' => 20,
                'difficulty_level' => ['Beginner', 'Intermediate', 'Advanced'][$i % 3],
                'trainer_id' => $trainers[$i % 5]->id,
                'is_special' => false,
            ]);
        }

        // Allow all membership plans to access all seeded classes by default.
        $planIds = $plans->pluck('id')->all();
        foreach ($classes as $class) {
            $class->membershipPlans()->sync($planIds);
        }

        // Create 8 pieces of equipment
        $equipment = [];
        foreach (['Dumbbells', 'Treadmill', 'Elliptical', 'Stationary Bike', 'Yoga Mat', 'Bench Press', 'Rowing Machine', 'Pull-up Bar'] as $name) {
            $equipment[] = Equipment::create([
                'equipment_name' => $name,
                'status' => 'Available',
                'last_maintenance' => now()->subDays(7),
            ]);
        }

        // Attach equipment to classes
        foreach ($classes as $class) {
            $equipmentIds = [];
            $equip = $equipment;
            shuffle($equip);
            foreach (array_slice($equip, 0, rand(2, 3)) as $equip_item) {
                $equipmentIds[] = $equip_item->id;
            }
            $class->equipment()->attach($equipmentIds);
        }

        // Create 20 members (standalone, no user relationship)
        $members = [];
        for ($i = 1; $i <= 20; $i++) {
            $members[] = Member::create([
                'first_name' => "Member$i",
                'last_name' => 'Test',
                'email' => "member$i@gym.com",
                'username' => "member$i",
                'password_hash' => Hash::make('password'),
                'phone' => "555-010$i",
                'date_of_birth' => now()->subYears(25 + $i),
                'plan_id' => $plans[($i - 1) % count($plans)]->id,
                'fitness_goal' => ['Weight Loss', 'Build Muscle', 'General Fitness'][$i % 3],
                'health_notes' => 'Cleared for all exercises',
                'registration_type' => 'standard',
                'membership_start' => now()->subMonths(2),
                'membership_end' => now()->addMonths(4),
                'membership_status' => 'active',
            ]);
        }

        // Create demo member for testing
        Member::create([
            'first_name' => 'Demo',
            'last_name' => 'User',
            'email' => 'demo@gym.com',
            'username' => 'demo',
            'password_hash' => Hash::make('password'),
            'phone' => '555-0000',
            'date_of_birth' => now()->subYears(30),
            'plan_id' => $plans[0]->id,
            'fitness_goal' => 'General Fitness',
            'health_notes' => 'No restrictions',
            'registration_type' => 'standard',
            'membership_start' => now(),
            'membership_end' => now()->addMonths(3),
            'membership_status' => 'active',
        ]);

        // Create 16 class schedules  
        $schedules = [];
        foreach ($classes as $class) {
            $schedules[] = ClassSchedule::create([
                'class_id' => $class->id,
                'class_date' => now()->addDays(1),
                'start_time' => '10:00',
                'end_time' => '11:00',
                'recurrence_type' => 'weekly',
                'recurrence_end_date' => now()->addMonths(3),
            ]);
            $schedules[] = ClassSchedule::create([
                'class_id' => $class->id,
                'class_date' => now()->addDays(3),
                'start_time' => '18:00',
                'end_time' => '19:00',
                'recurrence_type' => 'weekly',
                'recurrence_end_date' => now()->addMonths(3),
            ]);
        }

        // Create 30 attendance records
        $count = 0;
        foreach ($members as $j => $member) {
            if ($count < 30) {
                Attendance::create([
                    'member_id' => $member->id,
                    'schedule_id' => $schedules[$j % count($schedules)]->id,
                    'attendance_status' => ['Present', 'Absent', 'Late'][rand(0, 2)],
                    'attendance_notes' => 'Test attendance record',
                    'recorded_at' => now(),
                ]);
                $count++;
            }
        }

        // Create 20 payments
        $paymentMethods = PaymentMethod::all();
        foreach ($members as $member) {
            Payment::create([
                'member_id' => $member->id,
                'amount_paid' => 750 + rand(0, 500),
                'payment_date' => now()->subDays(5),
                'payment_method_id' => $paymentMethods->random()->payment_method_id,
                'coverage_start' => now(),
                'coverage_end' => now()->addMonths(1),
            ]);
        }

        // Create 10 equipment usage records
        foreach (array_slice($schedules, 0, 10) as $schedule) {
            EquipmentUsage::create([
                'equipment_id' => $equipment[array_rand($equipment)]->id,
                'schedule_id' => $schedule->id,
                'usage_duration' => rand(30, 120),
            ]);
        }

        echo "\n✅ Database seeded successfully!\n";
        echo "✅ Admin user: admin@gym.com / password\n";
        echo "✅ Trainer user: trainer0@gym.com / password\n";
        echo "✅ Demo member created: demo@gym.com / password\n";
        echo "✅ $i test members created (member1@gym.com through member20@gym.com / password)\n";
    }
}

