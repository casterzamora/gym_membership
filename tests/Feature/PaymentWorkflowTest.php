<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function plan(): MembershipPlan
    {
        return MembershipPlan::firstOrCreate(
            ['plan_name' => 'Payment Test Plan'],
            ['price' => 1000, 'duration_months' => 1, 'max_classes_per_week' => 4]
        );
    }

    private function member(string $email, string $username): Member
    {
        return Member::create([
            'first_name' => 'Pay',
            'last_name' => 'Member',
            'email' => $email,
            'username' => $username,
            'password_hash' => Hash::make('password123'),
            'phone' => '555-0000',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $this->plan()->id,
            'membership_start' => now()->subDays(7)->toDateString(),
            'membership_end' => now()->addDays(30)->toDateString(),
            'membership_status' => 'active',
        ]);
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Admin Test',
            'email' => 'admin-pay@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    }

    public function test_member_cannot_create_payment(): void
    {
        $member = $this->member('member-pay-a@example.com', 'memberpaya');
        $memberToken = $member->createToken('member-token')->plainTextToken;
        $method = PaymentMethod::first();

        $response = $this->withHeader('Authorization', 'Bearer ' . $memberToken)
            ->postJson('/api/v1/payments', [
                'member_id' => $member->id,
                'amount_paid' => 1000,
                'payment_date' => now()->toDateString(),
                'payment_method_id' => $method->payment_method_id,
                'coverage_start' => now()->toDateString(),
                'coverage_end' => now()->addMonth()->toDateString(),
            ]);

        $response->assertForbidden();
    }

    public function test_admin_cannot_create_overlapping_payment_period_for_same_member(): void
    {
        $admin = $this->adminUser();
        $adminToken = $admin->createToken('admin-token')->plainTextToken;
        $member = $this->member('member-pay-b@example.com', 'memberpayb');
        $method = PaymentMethod::first();

        Payment::create([
            'member_id' => $member->id,
            'amount_paid' => 1000,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $method->payment_method_id,
            'coverage_start' => '2026-04-01',
            'coverage_end' => '2026-04-30',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/v1/payments', [
                'member_id' => $member->id,
                'amount_paid' => 1200,
                'payment_date' => now()->toDateString(),
                'payment_method_id' => $method->payment_method_id,
                'coverage_start' => '2026-04-15',
                'coverage_end' => '2026-05-10',
            ]);

        $response->assertStatus(422);
    }

    public function test_member_only_sees_own_payments_in_index(): void
    {
        $memberA = $this->member('member-pay-c@example.com', 'memberpayc');
        $memberB = $this->member('member-pay-d@example.com', 'memberpayd');
        $method = PaymentMethod::first();

        Payment::create([
            'member_id' => $memberA->id,
            'amount_paid' => 1000,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $method->payment_method_id,
            'coverage_start' => '2026-06-01',
            'coverage_end' => '2026-06-30',
        ]);

        Payment::create([
            'member_id' => $memberB->id,
            'amount_paid' => 1100,
            'payment_date' => now()->toDateString(),
            'payment_method_id' => $method->payment_method_id,
            'coverage_start' => '2026-07-01',
            'coverage_end' => '2026-07-30',
        ]);

        $tokenA = $memberA->createToken('member-a-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/payments');

        $response->assertOk();
        $items = $response->json('data');
        $this->assertCount(1, $items);
        $this->assertSame($memberA->id, $items[0]['member_id']);
    }
}
