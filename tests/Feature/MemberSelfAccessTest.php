<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberSelfAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createMember(string $email, string $username): Member
    {
        return Member::create([
            'first_name' => 'Test',
            'last_name' => 'Member',
            'email' => $email,
            'username' => $username,
            'password_hash' => Hash::make('password123'),
            'phone' => '555-0000',
            'date_of_birth' => '1998-01-01',
            'membership_status' => 'active',
        ]);
    }

    public function test_member_can_view_own_record_but_not_other_members(): void
    {
        MembershipPlan::create([
            'plan_name' => 'Test Plan',
            'price' => 1000,
            'duration_months' => 1,
            'max_classes_per_week' => 3,
        ]);

        $memberA = $this->createMember('member-a@example.com', 'membera');
        $memberB = $this->createMember('member-b@example.com', 'memberb');

        $tokenA = $memberA->createToken('test-token')->plainTextToken;

        $ownResponse = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/members/' . $memberA->id);
        $ownResponse->assertOk();

        $otherResponse = $this->withHeader('Authorization', 'Bearer ' . $tokenA)
            ->getJson('/api/v1/members/' . $memberB->id);
        $otherResponse->assertForbidden();
    }

    public function test_admin_can_view_any_member_record(): void
    {
        MembershipPlan::create([
            'plan_name' => 'Admin Test Plan',
            'price' => 1200,
            'duration_months' => 1,
            'max_classes_per_week' => 4,
        ]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin-test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $member = $this->createMember('member-c@example.com', 'memberc');

        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/members/' . $member->id);

        $response->assertOk();
    }
}
