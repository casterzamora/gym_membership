<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Normalize User into a consistent auth payload for frontend consumers.
     */
    private function buildAuthUserPayload($user): array
    {
        if ($user instanceof User) {
            $payload = [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'type' => $user->role, // member, trainer, or admin
            ];
            
            // For members, also include the member ID so frontend knows which member to fetch
            if ($user->role === 'member') {
                $member = $user->member;
                if ($member) {
                    $payload['member_id'] = $member->id;
                }
            }
            
            // For trainers, also include the trainer ID so frontend knows which trainer to fetch
            if ($user->role === 'trainer') {
                $trainer = $user->trainer;
                if ($trainer) {
                    $payload['trainer_id'] = $trainer->id;
                }
            }
            
            return $payload;
        }

        // Legacy support for Member objects
        if ($user instanceof Member) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => 'member',
                'type' => 'member',
                'member_id' => $user->id,
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
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date',
                'plan_id' => 'required|integer|exists:membership_plans,id',
                'fitness_goal' => 'nullable|string|max:255',
                'health_notes' => 'nullable|string',
                'registration_type' => 'nullable|string',
            ]);
            
            // Set default registration_type if not provided
            if (!isset($validated['registration_type']) || !$validated['registration_type']) {
                $validated['registration_type'] = 'standard';
            }

            $checkoutToken = Str::random(64);
            $verificationToken = Str::random(64);

            // Use transaction to ensure both user and member are created atomically
            $user = DB::transaction(function () use ($validated, $checkoutToken, $verificationToken) {
                // Create user first
                $user = User::create([
                    'name' => trim($validated['first_name'] . ' ' . $validated['last_name']),
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? '',
                    'role' => 'member',
                    'is_active' => false,
                    'email_verification_token' => hash('sha256', $verificationToken),
                ]);

                $user->checkout_token_hash = hash('sha256', $checkoutToken);
                $user->checkout_token_expires_at = now()->addHours(2);
                $user->save();

                // Create member profile - if this fails, the entire transaction rolls back
                Member::create([
                    'user_id' => $user->id,
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? '',
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'plan_id' => $validated['plan_id'],
                    'fitness_goal' => $validated['fitness_goal'] ?? null,
                    'health_notes' => $validated['health_notes'] ?? null,
                    'registration_type' => $validated['registration_type'] ?? 'standard',
                    'membership_start' => null,
                    'membership_end' => null,
                    'membership_status' => 'pending_payment',
                ]);

                return $user;
            });

            // Send verification email
            $emailSent = false;
            try {
                $user->notify(new VerifyEmailNotification($user, $verificationToken));
                $emailSent = true;
            } catch (\Exception $mailException) {
                \Log::warning('Failed to send verification email', [
                    'user_id' => $user->id,
                    'error' => $mailException->getMessage(),
                ]);
            }

            $normalizedUser = $this->buildAuthUserPayload($user);

            return $this->success([
                'user' => $normalizedUser,
                'checkout_token' => $checkoutToken,
                'checkout_expires_at' => $user->checkout_token_expires_at,
                'next_step' => 'checkout',
                'email_verification_sent' => $emailSent,
                'email_delivery_mode' => config('mail.default'),
            ], config('mail.default') === 'log'
                ? 'Signup complete. Email delivery is set to log mode, so verification email is saved in server logs (not sent to inbox).'
                : 'Signup complete. A confirmation email has been sent to your email address. Please verify your email and complete checkout to activate your account.', 201);
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to register member: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Validate checkout session token for pending registrations.
     */
    public function checkoutSession(Request $request)
    {
        try {
            $validated = $request->validate([
                'checkout_token' => 'required|string',
            ]);

            $user = $this->resolvePendingCheckoutUser($validated['checkout_token']);
            if (!$user) {
                return $this->error('Checkout session is invalid or expired', null, 404);
            }

            $member = $user->member()->with('plan')->first();
            if (!$member) {
                return $this->error('Member profile was not found for this checkout session', null, 404);
            }

            return $this->success([
                'expires_at' => $user->checkout_token_expires_at,
                'member' => [
                    'id' => $member->id,
                    'plan_id' => $member->plan_id,
                    'membership_status' => $member->membership_status,
                ],
                'plan' => $member->plan ? [
                    'id' => $member->plan->id,
                    'name' => $member->plan->plan_name,
                    'price' => $member->plan->price,
                    'duration_months' => $member->plan->duration_months,
                ] : null,
            ], 'Checkout session is valid');
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to validate checkout session: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Verify email address using verification token
     */
    public function verifyEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'token' => 'required|string',
            ]);

            $hashedToken = hash('sha256', $validated['token']);
            $user = User::where('email_verification_token', $hashedToken)->first();

            if (!$user) {
                return $this->error('Invalid or expired verification token', null, 404);
            }

            if ($user->email_verified_at) {
                return $this->success([
                    'already_verified' => true,
                ], 'Email is already verified');
            }

            // Mark email as verified
            $user->email_verified_at = now();
            $user->email_verification_token = null;
            $user->save();

            return $this->success([
                'verified' => true,
                'email' => $user->email,
            ], 'Email verified successfully. You can now proceed to checkout.');
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to verify email: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerificationEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|exists:users',
            ]);

            $user = User::where('email', $validated['email'])
                ->where('email_verified_at', null)
                ->first();

            if (!$user) {
                return $this->error('Email not found or already verified', null, 404);
            }

            $verificationToken = Str::random(64);
            $user->email_verification_token = hash('sha256', $verificationToken);
            $user->save();

            $emailSent = false;
            try {
                $user->notify(new VerifyEmailNotification($user, $verificationToken));
                $emailSent = true;
            } catch (\Exception $mailException) {
                \Log::warning('Failed to resend verification email', [
                    'user_id' => $user->id,
                    'error' => $mailException->getMessage(),
                ]);
                return $this->error('Failed to send email. Please try again later.', null, 500);
            }

            return $this->success([
                'email_verification_sent' => $emailSent,
                'email_delivery_mode' => config('mail.default'),
            ], config('mail.default') === 'log'
                ? 'Verification email generated in log mode. Check server logs for the verification link.'
                : 'Verification email sent. Please check your inbox.');
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to resend verification email: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Send a password reset link to the user's email.
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return $this->success([], 'If that email exists, a reset link has been sent.');
            }

            $token = Str::random(64);
            $emailSent = false;

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ]
            );

            try {
                $user->notify(new ResetPasswordNotification($user, $token));
                $emailSent = true;
            } catch (\Exception $mailException) {
                \Log::warning('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'error' => $mailException->getMessage(),
                ]);
            }

            return $this->success([
                'email_sent' => $emailSent,
                'email_delivery_mode' => config('mail.default'),
            ], config('mail.default') === 'log'
                ? 'Password reset link generated in log mode. Check server logs for the link.'
                : 'If that email exists, a reset link has been sent.');
        } catch (\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to process password reset request: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Reset the user's password using a token.
     */
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $record = DB::table('password_reset_tokens')
                ->where('email', $validated['email'])
                ->first();

            if (!$record) {
                return $this->error('Invalid or expired password reset token', null, 404);
            }

            $tokenAgeMinutes = now()->diffInMinutes(Carbon::parse($record->created_at));
            if ($tokenAgeMinutes > 60 || !Hash::check($validated['token'], $record->token)) {
                return $this->error('Invalid or expired password reset token', null, 404);
            }

            $user = User::where('email', $validated['email'])->first();
            if (!$user) {
                return $this->error('User not found', null, 404);
            }

            $user->password = Hash::make($validated['password']);
            $user->save();

            DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

            return $this->success([], 'Password reset successfully. You can now sign in with your new password.');
        } catch (\ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to reset password: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Complete checkout, persist payment, activate account, then send confirmation email.
     */
    public function completeCheckout(Request $request)
    {
        try {
            $validated = $request->validate([
                'checkout_token' => 'required|string',
                'full_name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'payment_method_id' => 'required|exists:payment_methods,payment_method_id',
                'payment_reference' => 'nullable|string|max:255',
                'card_number' => 'nullable|string|max:32',
                'card_exp_month' => 'nullable|string|max:2',
                'card_exp_year' => 'nullable|string|max:4',
                'card_cvv' => 'nullable|string|max:4',
            ]);

            $user = $this->resolvePendingCheckoutUser($validated['checkout_token']);
            if (!$user) {
                return $this->error('Checkout session is invalid or expired', null, 404);
            }

            $member = $user->member()->with('plan')->first();
            if (!$member) {
                return $this->error('Member profile not found for this account', null, 422);
            }

            // If member doesn't have a plan, assign the first/cheapest plan as default
            if (!$member->plan_id) {
                $defaultPlan = MembershipPlan::orderBy('price', 'asc')->first();
                if (!$defaultPlan) {
                    return $this->error('No membership plans available', null, 422);
                }
                $member->plan_id = $defaultPlan->id;
                $member->save();
                $member->load('plan');
            } elseif (!$member->plan) {
                $member->load('plan');
            }

            if (!$member->plan) {
                return $this->error('Membership plan could not be loaded', null, 422);
            }

            if (strtolower(trim($validated['email'])) !== strtolower(trim($user->email))) {
                return $this->error('Checkout email does not match the signup email', null, 422);
            }

            $expectedFullName = strtolower(trim($user->first_name . ' ' . $user->last_name));
            if (strtolower(trim($validated['full_name'])) !== $expectedFullName) {
                return $this->error('Checkout full name does not match the signup name', null, 422);
            }

            $paymentMethod = PaymentMethod::find($validated['payment_method_id']);
            if (!$paymentMethod) {
                return $this->error('Payment method was not found', null, 422);
            }

            $isCardPayment = Str::contains(strtolower($paymentMethod->method_name), 'card');

            $cardBrand = null;
            $cardLast4 = null;
            $failureReason = null;

            if ($isCardPayment) {
                if (
                    empty($validated['card_number']) ||
                    empty($validated['card_exp_month']) ||
                    empty($validated['card_exp_year']) ||
                    empty($validated['card_cvv'])
                ) {
                    return $this->error('Card details are required for card payments', null, 422);
                }

                $cardDigits = preg_replace('/\D+/', '', $validated['card_number']);
                if (strlen($cardDigits) < 13 || strlen($cardDigits) > 19) {
                    return $this->error('Card number is invalid', null, 422);
                }

                // Deterministic failure path for testing retries.
                if (str_ends_with($cardDigits, '0000')) {
                    $failureReason = 'Payment was declined by the card processor. Please try another card.';
                }

                $cardLast4 = substr($cardDigits, -4);
                $cardBrand = match (substr($cardDigits, 0, 1)) {
                    '4' => 'Visa',
                    '5' => 'Mastercard',
                    '3' => 'Amex',
                    default => 'Card',
                };
            } else {
                if (empty($validated['payment_reference'])) {
                    return $this->error('Payment reference is required for this payment method', null, 422);
                }
            }

            $today = now()->toDateString();
            $coverageStart = now()->toDateString();
            $coverageEnd = now()->addMonths((int) ($member->plan->duration_months ?: 1))->toDateString();

            if ($failureReason) {
                Payment::create([
                    'user_id' => $user->id,
                    'member_id' => $member->id,
                    'amount_paid' => $member->plan->price,
                    'payment_date' => $today,
                    'payment_method_id' => $paymentMethod->payment_method_id,
                    'coverage_start' => $today,
                    'coverage_end' => $today,
                    'payment_status' => 'failed',
                    'checkout_full_name' => $validated['full_name'],
                    'checkout_email' => $validated['email'],
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'card_brand' => $cardBrand,
                    'card_last4' => $cardLast4,
                    'billing_address_line1' => null,
                    'billing_address_line2' => null,
                    'billing_city' => null,
                    'billing_state' => null,
                    'billing_postal_code' => null,
                    'billing_country' => null,
                    'payment_failure_reason' => $failureReason,
                ]);

                return $this->error($failureReason, ['retry_allowed' => true], 402);
            }

            DB::transaction(function () use (
                $user,
                $member,
                $validated,
                $paymentMethod,
                $cardBrand,
                $cardLast4,
                $coverageStart,
                $coverageEnd,
                $today
            ) {
                Payment::create([
                    'user_id' => $user->id,
                    'member_id' => $member->id,
                    'amount_paid' => $member->plan->price,
                    'payment_date' => $today,
                    'payment_method_id' => $paymentMethod->payment_method_id,
                    'coverage_start' => $coverageStart,
                    'coverage_end' => $coverageEnd,
                    'payment_status' => 'completed',
                    'checkout_full_name' => $validated['full_name'],
                    'checkout_email' => $validated['email'],
                    'payment_reference' => $validated['payment_reference'] ?? null,
                    'card_brand' => $cardBrand,
                    'card_last4' => $cardLast4,
                    'billing_address_line1' => null,
                    'billing_address_line2' => null,
                    'billing_city' => null,
                    'billing_state' => null,
                    'billing_postal_code' => null,
                    'billing_country' => null,
                    'payment_failure_reason' => null,
                ]);

                $user->is_active = true;
                $user->email_verified_at = now();
                $user->checkout_token_hash = null;
                $user->checkout_token_expires_at = null;
                $user->save();

                $member->membership_start = $coverageStart;
                $member->membership_end = $coverageEnd;
                $member->membership_status = 'active';
                $member->save();
            });

            try {
                Mail::raw(
                    "Your account is now active. Payment received and membership is confirmed.",
                    function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Gym Membership Confirmation');
                    }
                );
            } catch (\Exception $mailException) {
                \Log::warning('Payment completed but confirmation email failed', [
                    'user_id' => $user->id,
                    'error' => $mailException->getMessage(),
                ]);
            }

            return $this->success([
                'can_login' => true,
                'email_confirmation_triggered' => true,
            ], 'Payment completed. Your account is now active.');
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            return $this->error('Failed to complete checkout: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Resolve a pending member user from a public checkout token.
     */
    private function resolvePendingCheckoutUser(string $checkoutToken): ?User
    {
        return User::where('checkout_token_hash', hash('sha256', $checkoutToken))
            ->where('role', 'member')
            ->where('is_active', false)
            ->whereNotNull('checkout_token_expires_at')
            ->where('checkout_token_expires_at', '>', now())
            ->first();
    }

    /**
     * Login user (works for all roles: member, trainer, admin)
     * Unified authentication via users table
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // Authenticate against unified users table
            $user = User::where('email', $validated['email'])->first();
            
            if (!$user) {
                return $this->error('Invalid credentials', null, 401);
            }

            if (!Hash::check($validated['password'], $user->password)) {
                return $this->error('Invalid credentials', null, 401);
            }

            // Check that user is active
            if (!$user->is_active) {
                return $this->error('User account is inactive', null, 403);
            }

            // For members, check membership status from member profile
            if ($user->role === 'member') {
                $member = $user->member;
                if (!$member || $member->membership_status !== 'active') {
                    return $this->error('Membership is not active', null, 403);
                }
            }

            $token = $user->createToken('api-token')->plainTextToken;
            $normalizedUser = $this->buildAuthUserPayload($user);

            return $this->success([
                'user' => $normalizedUser,
                'token' => $token,
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->validationError($e->errors(), 'Validation failed');
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage(), ['exception' => $e]);
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
