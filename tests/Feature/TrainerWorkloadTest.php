<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\FitnessClass;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrainerWorkloadTest extends TestCase
{
    use RefreshDatabase;

    private function plan(): MembershipPlan
    {
        return MembershipPlan::firstOrCreate(
            ['plan_name' => 'Workload Plan'],
            ['price' => 1000, 'duration_months' => 1, 'max_classes_per_week' => 4]
        );
    }

    private function makeMember(string $email, string $username): Member
    {
        return Member::create([
            'first_name' => 'Member',
            'last_name' => 'Load',
            'email' => $email,
            'username' => $username,
            'password_hash' => Hash::make('password123'),
            'phone' => '555-0000',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $this->plan()->id,
            'membership_start' => now()->subDays(5)->toDateString(),
            'membership_end' => now()->addDays(30)->toDateString(),
            'membership_status' => 'active',
        ]);
    }

    private function makeTrainerPair(string $email): array
    {
        $trainer = Trainer::create([
            'first_name' => 'Train',
            'last_name' => 'Er',
            'email' => $email,
            'specialization' => 'General',
            'phone' => '555-1000',
        ]);

        $user = User::create([
            'name' => 'Trainer User',
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => 'trainer',
        ]);

        return [$trainer, $user];
    }

    private function makeScheduleForTrainer(Trainer $trainer): ClassSchedule
    {
        $class = FitnessClass::create([
            'class_name' => 'Trainer Class ' . $trainer->id,
            'description' => 'Workload test',
            'trainer_id' => $trainer->id,
            'max_participants' => 20,
            'difficulty_level' => 'Beginner',
        ]);

        return ClassSchedule::create([
            'class_id' => $class->id,
            'class_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);
    }

    public function test_admin_can_view_specific_trainer_workload(): void
    {
        [$trainer] = $this->makeTrainerPair('trainer-workload-a@example.com');
        $schedule = $this->makeScheduleForTrainer($trainer);

        $member = $this->makeMember('workload-member-a@example.com', 'workloadmembera');
        Attendance::create([
            'member_id' => $member->id,
            'schedule_id' => $schedule->id,
            'attendance_status' => 'Present',
            'recorded_at' => now(),
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'workload-admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/trainers/' . $trainer->id . '/workload');

        $response->assertOk();
        $response->assertJsonPath('data.metrics.total_classes', 1);
        $response->assertJsonPath('data.metrics.total_schedules', 1);
        $response->assertJsonPath('data.metrics.total_attendance_records', 1);
    }

    public function test_trainer_can_view_own_workload_but_not_others(): void
    {
        [$trainerA, $userA] = $this->makeTrainerPair('trainer-workload-b@example.com');
        [$trainerB] = $this->makeTrainerPair('trainer-workload-c@example.com');

        $tokenA = $userA->createToken('trainer-a-token')->plainTextToken;

        $own = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/trainers/' . $trainerA->id . '/workload');
        $own->assertOk();

        $other = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/trainers/' . $trainerB->id . '/workload');
        $other->assertForbidden();
    }

    public function test_admin_can_view_workload_summary(): void
    {
        [$trainerA] = $this->makeTrainerPair('trainer-workload-d@example.com');
        [$trainerB] = $this->makeTrainerPair('trainer-workload-e@example.com');
        $this->makeScheduleForTrainer($trainerA);
        $this->makeScheduleForTrainer($trainerB);

        $admin = User::create([
            'name' => 'Admin Summary',
            'email' => 'workload-admin2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/trainers/workload-summary');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }
}
