<?php

namespace Tests\Feature;

use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TrainerCrudTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * Test creating a new trainer (auto-creates user account)
     */
    public function test_create_trainer_auto_creates_user()
    {
        $admin = $this->createAdminUser();
        
        $trainerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'specialization' => 'Strength Training',
            'phone' => '555-1234',
            'hourly_rate' => 50.00,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/trainers', $trainerData);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user_id',
                'first_name',
                'last_name',
                'specialization',
                'phone',
                'hourly_rate',
            ],
            'message',
        ]);

        // Verify trainer created in database
        $this->assertDatabaseHas('trainers', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'specialization' => 'Strength Training',
        ]);

        // Verify user auto-created
        $user = User::where('email', 'john.doe@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('trainer', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('555-1234', $user->phone);
        $this->assertEquals(50.00, $user->hourly_rate);

        // Verify trainer linked to user
        $trainer = Trainer::findOrFail($response->json('data.id'));
        $this->assertEquals($user->id, $trainer->user_id);
    }

    /**
     * Test creating trainer with existing user_id
     */
    public function test_create_trainer_with_existing_user()
    {
        $admin = $this->createAdminUser();
        
        // Create a user first
        $user = User::create([
            'name' => 'Existing User',
            'email' => 'existing@test.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Existing',
            'last_name' => 'User',
            'role' => 'trainer',
            'is_active' => true,
        ]);

        $trainerData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smith@test.com',  // Different email
            'specialization' => 'Yoga',
            'phone' => '555-5678',
            'hourly_rate' => 45.00,
            'user_id' => $user->id,  // Link to existing user
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/trainers', $trainerData);

        $response->assertStatus(201);

        // Verify trainer linked to provided user
        $trainer = Trainer::findOrFail($response->json('data.id'));
        $this->assertEquals($user->id, $trainer->user_id);
        
        // Verify only one user created
        $this->assertEquals(2, User::count()); // admin + existing user
    }

    /**
     * Test trainer email uniqueness validation
     */
    public function test_trainer_email_must_be_unique()
    {
        $admin = $this->createAdminUser();
        
        // Create first trainer
        $firstData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'specialization' => 'Strength',
            'phone' => '555-1111',
            'hourly_rate' => 50.00,
        ];
        
        $this->actingAs($admin)->postJson('/api/v1/trainers', $firstData);

        // Try to create second trainer with same email
        $secondData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'john@test.com',  // Duplicate email
            'specialization' => 'Yoga',
            'phone' => '555-2222',
            'hourly_rate' => 45.00,
        ];

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/trainers', $secondData);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    /**
     * Test updating trainer also syncs user data
     */
    public function test_update_trainer_syncs_user_data()
    {
        $admin = $this->createAdminUser();
        
        // Create trainer (auto-creates user)
        $trainerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'specialization' => 'Strength Training',
            'phone' => '555-1234',
            'hourly_rate' => 50.00,
        ];

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/v1/trainers', $trainerData);
        $trainerId = $createResponse->json('data.id');
        $trainer = Trainer::find($trainerId);
        $userId = $trainer->user_id;

        // Update trainer
        $updateData = [
            'first_name' => 'Jonathan',
            'last_name' => 'Smith',
            'phone' => '555-9999',
            'specialization' => 'Cardio',
            'hourly_rate' => 60.00,
        ];

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/trainers/{$trainerId}", $updateData);

        $response->assertStatus(200);

        // Verify trainer updated
        $trainer->refresh();
        $this->assertEquals('Jonathan', $trainer->first_name);
        $this->assertEquals('Smith', $trainer->last_name);
        $this->assertEquals('555-9999', $trainer->phone);
        $this->assertEquals('Cardio', $trainer->specialization);
        $this->assertEquals(60.00, $trainer->hourly_rate);

        // Verify user synced with new data
        $user = User::find($userId);
        $this->assertEquals('Jonathan', $user->first_name);
        $this->assertEquals('Smith', $user->last_name);
        $this->assertEquals('Jonathan Smith', $user->name);
        $this->assertEquals('555-9999', $user->phone);
        $this->assertEquals('Cardio', $user->specialization);
        $this->assertEquals(60.00, $user->hourly_rate);
    }

    /**
     * Test deleting trainer deactivates user
     */
    public function test_delete_trainer_deactivates_user()
    {
        $admin = $this->createAdminUser();
        
        // Create trainer
        $trainerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'specialization' => 'Strength Training',
            'phone' => '555-1234',
            'hourly_rate' => 50.00,
        ];

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/v1/trainers', $trainerData);
        $trainerId = $createResponse->json('data.id');
        $trainer = Trainer::find($trainerId);
        $userId = $trainer->user_id;

        // Verify user is active before delete
        $user = User::find($userId);
        $this->assertTrue($user->is_active);

        // Delete trainer
        $response = $this->actingAs($admin)
            ->deleteJson("/api/v1/trainers/{$trainerId}");

        $response->assertStatus(200);

        // Verify trainer deleted
        $this->assertNull(Trainer::find($trainerId));

        // Verify user deactivated (not deleted)
        $user = User::find($userId);
        $this->assertNotNull($user);
        $this->assertFalse($user->is_active);
    }

    /**
     * Test getting trainer with relationships
     */
    public function test_get_trainer_loads_relationships()
    {
        $admin = $this->createAdminUser();
        
        // Create trainer
        $trainerData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@test.com',
            'specialization' => 'Strength',
            'phone' => '555-1234',
            'hourly_rate' => 50.00,
        ];

        $createResponse = $this->actingAs($admin)
            ->postJson('/api/v1/trainers', $trainerData);
        $trainerId = $createResponse->json('data.id');

        // Get trainer
        $response = $this->actingAs($admin)
            ->getJson("/api/v1/trainers/{$trainerId}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'user_id',
                'first_name',
                'last_name',
                'specialization',
                'phone',
                'hourly_rate',
                'user',
                'classes',
                'certifications',
            ],
            'message',
        ]);
    }

    /**
     * Test listing trainers
     */
    public function test_list_trainers()
    {
        $admin = $this->createAdminUser();
        
        // Create multiple trainers
        for ($i = 1; $i <= 3; $i++) {
            $this->actingAs($admin)->postJson('/api/v1/trainers', [
                'first_name' => "Trainer{$i}",
                'last_name' => "Last{$i}",
                'email' => "trainer{$i}@test.com",
                'specialization' => "Spec{$i}",
                'phone' => "555-{$i}00{$i}",
                'hourly_rate' => 50.00 + $i,
            ]);
        }

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/trainers');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'user_id',
                    'first_name',
                    'last_name',
                ]
            ],
            'message',
        ]);
        
        $this->assertCount(3, $response->json('data'));
    }
}
