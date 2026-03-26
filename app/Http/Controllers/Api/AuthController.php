<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Member;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'plan_id' => 'nullable|integer|exists:membership_plans,id',
            ]);

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'member',
            ]);

            // Parse name into first and last
            $nameParts = explode(' ', $validated['name']);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : 'Member';

            // Create associated member profile with selected or default plan
            try {
                Member::create([
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => '',
                    'date_of_birth' => now()->subYears(30)->format('Y-m-d'),
                    'plan_id' => $validated['plan_id'] ?? 1, // Use provided plan or default
                ]);
            } catch (\Exception $memberError) {
                // If member creation fails, delete the user to maintain consistency
                $user->delete();
                throw new \Exception('Failed to create member profile: ' . $memberError->getMessage());
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ], 'User registered successfully', 201);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to register user: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return $this->error('Invalid credentials', null, 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return $this->success([
                'user' => $user,
                'token' => $token,
            ], 'Login successful');
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
            $request->user()->currentAccessToken()->delete();
            return $this->success(null, 'Logout successful');
        } catch (\Exception $e) {
            return $this->error('Failed to logout: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return $this->success($request->user(), 'Current user retrieved successfully');
    }
}
