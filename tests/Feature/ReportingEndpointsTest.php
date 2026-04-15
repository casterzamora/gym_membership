<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\FitnessClass;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportingEndpointsTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseData(): array
    {
        $plan = MembershipPlan::create([
            'plan_name' => 'Report Plan',
            'price' => 1200,
            'duration_months' => 1,
            'max_classes_per_week' => 4,
        ]);

        $trainer = Trainer::create([
            'first_name' => 'Rep',
            'last_name' => 'Ort',
            'email' => 'report-trainer@example.com',
            'specialization' => 'General',
            'phone' => '555-2222',
        ]);

        $fitnessClass = FitnessClass::create([
            'class_name' => 'Report HIIT',
            'description' => 'Class for reporting tests',
            'trainer_id' => $trainer->id,
            'max_participants' => 20,
            'difficulty_level' => 'Intermediate',
        ]);

        $schedule = ClassSchedule::create([
            'class_id' => $fitnessClass->id,
            'class_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $memberLow = Member::create([
            'first_name' => 'Low',
            'last_name' => 'Attendance',
            'email' => 'low-attendance@example.com',
            'username' => 'lowattendance',
            'password_hash' => Hash::make('password123'),
            'phone' => '555-1111',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $plan->id,
            'membership_start' => now()->subMonth()->toDateString(),
            'membership_end' => now()->addMonth()->toDateString(),
            'membership_status' => 'active',
        ]);

        $memberHigh = Member::create([
            'first_name' => 'High',
            'last_name' => 'Attendance',
            'email' => 'high-attendance@example.com',
            'username' => 'highattendance',
            'password_hash' => Hash::make('password123'),
            'phone' => '555-3333',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $plan->id,
            'membership_start' => now()->subMonth()->toDateString(),
            'membership_end' => now()->addMonth()->toDateString(),
            'membership_status' => 'active',
        ]);

        Attendance::create([
            'member_id' => $memberHigh->id,
            'schedule_id' => $schedule->id,
            'attendance_status' => 'Present',
            'recorded_at' => now(),
        ]);

        $method = PaymentMethod::first();

        Payment::create([
            'member_id' => $memberHigh->id,
            'amount_paid' => 1500,
            'payment_date' => now()->startOfMonth()->toDateString(),
            'payment_method_id' => $method->payment_method_id,
            'coverage_start' => now()->startOfMonth()->toDateString(),
            'coverage_end' => now()->endOfMonth()->toDateString(),
        ]);

        Payment::create([
            'member_id' => $memberLow->id,
            'amount_paid' => 1000,
            'payment_date' => now()->subMonth()->startOfMonth()->toDateString(),
            'payment_method_id' => $method->payment_method_id,
            'coverage_start' => now()->subMonth()->startOfMonth()->toDateString(),
            'coverage_end' => now()->subMonth()->endOfMonth()->toDateString(),
        ]);

        return [$memberLow, $memberHigh, $fitnessClass];
    }

    public function test_admin_can_fetch_revenue_report(): void
    {
        $this->seedBaseData();

        $admin = User::create([
            'name' => 'Report Admin',
            'email' => 'report-admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/reports/revenue?group_by=month');

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $this->assertGreaterThan(0, (float) $response->json('data.total_revenue'));
    }

    public function test_admin_can_fetch_class_popularity_and_low_attendance_members(): void
    {
        [$memberLow, $memberHigh, $fitnessClass] = $this->seedBaseData();

        $admin = User::create([
            'name' => 'Report Admin 2',
            'email' => 'report-admin-2@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $token = $admin->createToken('admin-token')->plainTextToken;

        $classResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/reports/class-popularity?limit=5');
        $classResponse->assertOk();
        $classResponse->assertJsonPath('data.0.class_id', $fitnessClass->id);

        $memberResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/reports/low-attendance-members?limit=5');
        $memberResponse->assertOk();

        $members = $memberResponse->json('data.members');
        $this->assertNotEmpty($members);
        $this->assertSame($memberLow->id, $members[0]['member_id']);
        $this->assertLessThanOrEqual($members[1]['present_count'] ?? PHP_INT_MAX, $members[0]['present_count']);
        $this->assertNotSame($memberHigh->id, $members[0]['member_id']);
    }

    public function test_non_admin_cannot_access_reports(): void
    {
        $plan = MembershipPlan::create([
            'plan_name' => 'Non Admin Report Plan',
            'price' => 1000,
            'duration_months' => 1,
            'max_classes_per_week' => 3,
        ]);

        $member = Member::create([
            'first_name' => 'Regular',
            'last_name' => 'Member',
            'email' => 'regular-report-member@example.com',
            'username' => 'regularreportmember',
            'password_hash' => Hash::make('password123'),
            'phone' => '555-4444',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $plan->id,
            'membership_start' => now()->subDays(10)->toDateString(),
            'membership_end' => now()->addDays(20)->toDateString(),
            'membership_status' => 'active',
        ]);

        $token = $member->createToken('member-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/reports/revenue');

        $response->assertForbidden();
    }
}
