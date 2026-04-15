<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\MembershipUpgrade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MembershipLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function createPlan(string $name, int $durationMonths): MembershipPlan
    {
        return MembershipPlan::create([
            'plan_name' => $name,
            'price' => 1000,
            'duration_months' => $durationMonths,
            'max_classes_per_week' => 4,
        ]);
    }

    private function createMember(MembershipPlan $plan, string $email, string $username): Member
    {
        return Member::create([
            'first_name' => 'Life',
            'last_name' => 'Cycle',
            'email' => $email,
            'username' => $username,
            'password_hash' => Hash::make('password123'),
            'phone' => '555-0000',
            'date_of_birth' => '1998-01-01',
            'plan_id' => $plan->id,
            'membership_start' => '2026-01-01',
            'membership_end' => '2026-01-31',
            'membership_status' => 'active',
        ]);
    }

    public function test_member_can_renew_own_membership(): void
    {
        $plan = $this->createPlan('Renew Plan', 1);
        $member = $this->createMember($plan, 'renew-member@example.com', 'renewmember');

        $token = $member->createToken('member-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/members/' . $member->id . '/renew', [
                'start_date' => '2026-02-01',
            ]);

        $response->assertOk();

        $member->refresh();
        $this->assertSame('2026-02-28', $member->membership_end->toDateString());
        $this->assertSame('active', $member->membership_status);
    }

    public function test_member_cannot_renew_another_member(): void
    {
        $plan = $this->createPlan('Renew Plan 2', 1);
        $memberA = $this->createMember($plan, 'renew-a@example.com', 'renewa');
        $memberB = $this->createMember($plan, 'renew-b@example.com', 'renewb');

        $tokenA = $memberA->createToken('member-a-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->postJson('/api/v1/members/' . $memberB->id . '/renew');

        $response->assertForbidden();
    }

    public function test_admin_upgrade_creates_history_and_updates_plan(): void
    {
        $oldPlan = $this->createPlan('Old Plan', 1);
        $newPlan = $this->createPlan('New Plan', 3);
        $member = $this->createMember($oldPlan, 'upgrade-member@example.com', 'upgrademember');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'lifecycle-admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson('/api/v1/members/' . $member->id . '/upgrade', [
                'new_plan_id' => $newPlan->id,
                'effective_date' => '2026-02-01',
            ]);

        $response->assertOk();

        $member->refresh();
        $this->assertSame($newPlan->id, $member->plan_id);
        $this->assertSame('2026-04-30', $member->membership_end->toDateString());

        $this->assertDatabaseHas('membership_upgrades', [
            'member_id' => $member->id,
            'old_plan_id' => $oldPlan->id,
            'new_plan_id' => $newPlan->id,
            'upgrade_date' => '2026-02-01',
        ]);

        $this->assertSame(1, MembershipUpgrade::where('member_id', $member->id)->count());
    }
}
