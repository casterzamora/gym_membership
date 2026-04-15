<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Normalize Member/User into a consistent auth payload for frontend consumers.
     */
    private function buildAuthUserPayload($user): array
    {
        if ($user instanceof Member) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => 'member',
                'type' => 'member',
            ];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'member',
            'type' => 'user',
        ];
    }

    /**
     * Register a new member
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:members',
                'username' => 'required|string|max:255|unique:members',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date',
                'plan_id' => 'required|integer|exists:membership_plans,id',
                'fitness_goal' => 'nullable|string|max:255',
                'health_notes' => 'nullable|string',
                'registration_type' => 'nullable|string|default:standard',
            ]);

            // Create member
            $member = Member::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password_hash' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? '',
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'plan_id' => $validated['plan_id'],
                'fitness_goal' => $validated['fitness_goal'] ?? null,
                'health_notes' => $validated['health_notes'] ?? null,
                'registration_type' => $validated['registration_type'] ?? 'standard',
                'membership_start' => now()->toDateString(),
                'membership_end' => now()->addMonths(3)->toDateString(), // Default to 3 month plan
                'membership_status' => 'active',
            ]);

            $token = Member::find($member->id)->createToken('api-token')->plainTextToken;

            $normalizedUser = $this->buildAuthUserPayload($member);

            return $this->success([
                'member' => $member,
                'user' => $normalizedUser,
                'token' => $token,
            ], 'Member registered successfully', 201);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to register member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login member or user (admin/trainer)
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Try to authenticate as a Member first
            $member = Member::where('email', $validated['email'])->first();
            if ($member && Hash::check($validated['password'], $member->password_hash)) {
                if ($member->membership_status !== 'active') {
                    return $this->error('Membership is not active', null, 403);
                }
                $token = $member->createToken('api-token')->plainTextToken;

                $normalizedUser = $this->buildAuthUserPayload($member);

                return $this->success([
                    'member' => $member,
                    'user' => $normalizedUser,
                    'token' => $token,
                ], 'Login successful');
            }

            // Try to authenticate as a User (admin/trainer)
            $user = User::where('email', $validated['email'])->first();
            if ($user && Hash::check($validated['password'], $user->password)) {
                $token = $user->createToken('api-token')->plainTextToken;

                $normalizedUser = $this->buildAuthUserPayload($user);

                return $this->success([
                    'member' => null,
                    'user' => $user,
                    'auth_user' => $normalizedUser,
                    'token' => $token,
                ], 'Login successful');
            }

            // If neither worked, return invalid credentials
            return $this->error('Invalid credentials', null, 401);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to login: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return $this->error('No authorization token', null, 401);
            }

            $patToken = PersonalAccessToken::findToken($token);

            if (!$patToken) {
                return $this->error('Invalid or expired token', null, 401);
            }

            $patToken->delete();
            return $this->success(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->error('Failed to logout: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get current user (Member or User/Admin)
     */
    public function me(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            if (!$token) {
                return $this->error('No authorization token', null, 401);
            }
            
            // Use Sanctum to find the token
            $patToken = PersonalAccessToken::findToken($token);
            
            if (!$patToken) {
                return $this->error('Invalid or expired token', null, 401);
            }
            
            // Get the user based on tokenable_type
            $model = $patToken->tokenable_type;
            $user = $model::find($patToken->tokenable_id);
            
            if (!$user) {
                return $this->error('User not found', null, 404);
            }

            $normalizedUser = $this->buildAuthUserPayload($user);
            
            return $this->success($normalizedUser, 'Current user retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve current user: ' . $e->getMessage(), null, 500);
        }
    }
}
