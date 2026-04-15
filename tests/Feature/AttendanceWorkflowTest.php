<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\FitnessClass;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createPlan(): MembershipPlan
    {
        return MembershipPlan::firstOrCreate(
            ['plan_name' => 'Test Plan'],
            [
                'price' => 1000,
                'duration_months' => 1,
                'max_classes_per_week' => 4,
            ]
        );
    }

    private function createMember(string $email, string $username, string $status = 'active'): Member
    {
        return Member::create([
            'first_name' => 'Test',
            'last_name' => 'Member',
            'email' => $email,
            'username' => $username,
            'password_hash' => Hash::make('password123'),
            'phone' => '555-0000',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $this->createPlan()->id,
            'membership_start' => now()->subDays(5)->toDateString(),
            'membership_end' => now()->addDays(30)->toDateString(),
            'membership_status' => $status,
        ]);
    }

    private function createTrainerWithUser(string $email): array
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

    private function createClassSchedule(int $trainerId): ClassSchedule
    {
        $class = FitnessClass::create([
            'class_name' => 'HIIT',
            'description' => 'High intensity class',
            'trainer_id' => $trainerId,
            'max_participants' => 20,
            'difficulty_level' => 'Intermediate',
        ]);

        return ClassSchedule::create([
            'class_id' => $class->id,
            'class_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);
    }

    public function test_member_cannot_check_in_another_member(): void
    {
        [$trainer] = $this->createTrainerWithUser('trainer-a@example.com');
        $schedule = $this->createClassSchedule($trainer->id);

        $memberA = $this->createMember('member-a@example.com', 'membera');
        $memberB = $this->createMember('member-b@example.com', 'memberb');

        $tokenA = $memberA->createToken('member-a-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/v1/attendance/check-in', [
                'member_id' => $memberB->id,
                'class_id' => $schedule->class_id,
            ]);

        $response->assertForbidden();
    }

    public function test_inactive_member_cannot_check_in(): void
    {
        [$trainer] = $this->createTrainerWithUser('trainer-b@example.com');
        $schedule = $this->createClassSchedule($trainer->id);

        $inactiveMember = $this->createMember('inactive@example.com', 'inactive', 'suspended');
        $token = $inactiveMember->createToken('inactive-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/attendance/check-in', [
                'member_id' => $inactiveMember->id,
                'class_id' => $schedule->class_id,
            ]);

        $response->assertStatus(403);
    }

    public function test_trainer_cannot_check_in_to_other_trainers_class(): void
    {
        [$trainerA, $trainerUserA] = $this->createTrainerWithUser('trainer-c@example.com');
        [$trainerB] = $this->createTrainerWithUser('trainer-d@example.com');

        $otherTrainerSchedule = $this->createClassSchedule($trainerB->id);
        $member = $this->createMember('member-c@example.com', 'memberc');

        $trainerToken = $trainerUserA->createToken('trainer-a-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $trainerToken)
            ->postJson('/api/v1/attendance/check-in', [
                'member_id' => $member->id,
                'class_id' => $otherTrainerSchedule->class_id,
            ]);

        $response->assertForbidden();
    }
}
