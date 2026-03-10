<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated.',
            ], 403);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/v1/auth/signup
     */
    public function signup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'sometimes|in:parent,learner',
        ]);

        // Accept multiple name formats for backward compatibility
        $name = $request->name
            ?? $request->full_name
            ?? trim(($request->first_name ?? '') . ' ' . ($request->last_name ?? ''))
            ?: 'User';

        $user = User::create([
            'name' => $name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'learner',
            'last_login_at' => now(),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    /**
     * POST /api/v1/auth/firebase-sync
     *
     * Compatibility endpoint for the mobile app that still uses Firebase
     * for Google/Apple Sign-In. Accepts a Firebase ID token, decodes it,
     * and returns a Sanctum token.
     */
    public function firebaseSync(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
            'firebase_uid' => 'nullable|string',
            'email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
            'role' => 'nullable|in:parent,learner',
        ]);

        // Decode the Firebase JWT to extract user info
        $tokenPayload = $this->decodeFirebaseToken($request->id_token);

        if (!$tokenPayload) {
            return response()->json([
                'message' => 'Invalid or expired Firebase token.',
            ], 401);
        }

        $email = $tokenPayload['email'] ?? $request->email;
        $name = $request->name ?? $tokenPayload['name'] ?? null;
        $firebaseUid = $tokenPayload['user_id'] ?? $tokenPayload['sub'] ?? $request->firebase_uid;

        if (!$email) {
            return response()->json([
                'message' => 'Email not found in token. Please provide email.',
            ], 422);
        }

        // Determine social provider from Firebase token
        $provider = $tokenPayload['firebase']['sign_in_provider'] ?? 'password';

        // Try to find existing user
        $user = null;

        // 1. Try by Google ID if provider is google.com
        if ($provider === 'google.com' && $firebaseUid) {
            $user = User::where('google_id', $firebaseUid)->first();
        }

        // 2. Try by Apple ID if provider is apple.com
        if (!$user && $provider === 'apple.com' && $firebaseUid) {
            $user = User::where('apple_user_id', $firebaseUid)->first();
        }

        // 3. Try by email
        if (!$user) {
            $user = User::where('email', $email)->first();
        }

        $isNewUser = false;

        if ($user) {
            // Existing user — update social IDs if missing
            $updates = ['last_login_at' => now()];

            if ($provider === 'google.com' && !$user->google_id && $firebaseUid) {
                $updates['google_id'] = $firebaseUid;
            }
            if ($provider === 'apple.com' && !$user->apple_user_id && $firebaseUid) {
                $updates['apple_user_id'] = $firebaseUid;
            }
            if ($name && !$user->name) {
                $updates['name'] = $name;
            }

            $user->update($updates);
        } else {
            // New user — if no name provided, request profile completion
            if (!$name) {
                return response()->json([
                    'requires_profile_completion' => true,
                    'email' => $email,
                    'firebase_uid' => $firebaseUid,
                    'provider' => $provider,
                ]);
            }

            $socialField = match ($provider) {
                'google.com' => 'google_id',
                'apple.com' => 'apple_user_id',
                default => null,
            };

            $user = User::create(array_filter([
                'name' => $name,
                'email' => $email,
                'role' => $request->role ?? 'learner',
                'email_verified_at' => now(),
                'last_login_at' => now(),
                $socialField => $firebaseUid,
            ]));

            $isNewUser = true;
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated.',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
            'is_new_user' => $isNewUser,
        ]);
    }

    /**
     * POST /api/v1/auth/complete-profile
     *
     * Complete profile for new social users (called after firebase-sync
     * returns requires_profile_completion).
     */
    public function completeProfile(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'role' => 'nullable|in:parent,learner',
        ]);

        $name = trim($request->first_name . ' ' . ($request->last_name ?? ''));

        // Find or create user by email
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->update(array_filter([
                'name' => $name,
                'date_of_birth' => $request->date_of_birth,
                'role' => $request->role ?? $user->role->value,
                'last_login_at' => now(),
            ]));
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $request->email,
                'role' => $request->role ?? 'learner',
                'date_of_birth' => $request->date_of_birth,
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
            'is_new_user' => true,
        ]);
    }

    /**
     * POST /api/v1/auth/social/google
     */
    public function socialGoogle(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->id_token);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid Google token.',
            ], 401);
        }

        $user = $this->findOrCreateSocialUser(
            email: $googleUser->getEmail(),
            name: $googleUser->getName(),
            socialField: 'google_id',
            socialId: $googleUser->getId(),
            avatarUrl: $googleUser->getAvatar(),
        );

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/v1/auth/social/apple
     */
    public function socialApple(Request $request): JsonResponse
    {
        $request->validate([
            'authorization_code' => 'required|string',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $appleUser = Socialite::driver('apple')
                ->stateless()
                ->user();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Invalid Apple authorization.',
            ], 401);
        }

        $user = $this->findOrCreateSocialUser(
            email: $appleUser->getEmail(),
            name: $request->name ?? $appleUser->getName() ?? 'Apple User',
            socialField: 'apple_user_id',
            socialId: $appleUser->getId(),
        );

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * POST /api/v1/auth/change-password
     * Mobile sends { oldPassword, newPassword } or { current_password, password, password_confirmation }
     */
    public function changePassword(Request $request): JsonResponse
    {
        // Accept both field name styles
        $currentPassword = $request->current_password ?? $request->oldPassword ?? $request->old_password;
        $newPassword = $request->password ?? $request->newPassword ?? $request->new_password;
        $confirmPassword = $request->password_confirmation ?? $request->newPassword ?? $request->new_password;

        if (!$newPassword) {
            return response()->json([
                'message' => 'New password is required.',
            ], 422);
        }

        if (strlen($newPassword) < 8) {
            return response()->json([
                'message' => 'Password must be at least 8 characters.',
            ], 422);
        }

        $user = $request->user();

        if ($currentPassword && $user->password && !Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($newPassword)]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * POST /api/v1/auth/delete-account
     * DELETE /api/v1/auth/me (alias)
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        // Accept both confirmation formats
        if ($request->confirmation !== 'DELETE' && !$request->boolean('confirm')) {
            $request->validate(['confirmation' => 'required|in:DELETE']);
        }

        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete related content
        $user->decks()->delete();

        // Deactivate account
        $user->update([
            'is_active' => false,
            'email' => 'deleted_' . $user->id . '_' . $user->email,
        ]);

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }

    // ──────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────

    /**
     * Decode a Firebase ID token (JWT) to extract user information.
     * Does NOT fully verify the signature — for transition period only.
     */
    private function decodeFirebaseToken(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true
            );

            if (!$payload || !is_array($payload)) {
                return null;
            }

            // Basic expiration check
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                Log::warning('Firebase token expired', ['exp' => $payload['exp']]);
                return null;
            }

            // Basic issuer check (Firebase tokens are issued by securetoken.google.com)
            $iss = $payload['iss'] ?? '';
            if ($iss && !str_contains($iss, 'securetoken.google.com') && !str_contains($iss, 'accounts.google.com')) {
                Log::warning('Firebase token unexpected issuer', ['iss' => $iss]);
                // Don't reject — might be a different token format during transition
            }

            return $payload;
        } catch (\Exception $e) {
            Log::error('Firebase token decode error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Find an existing user by social ID or email, or create a new one.
     */
    private function findOrCreateSocialUser(string $email, string $name, string $socialField, string $socialId, ?string $avatarUrl = null): User
    {
        $user = User::where($socialField, $socialId)->first();

        if ($user) {
            $user->update(['last_login_at' => now()]);
            return $user;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                $socialField => $socialId,
                'last_login_at' => now(),
            ]);
            return $user;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            $socialField => $socialId,
            'role' => 'learner',
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);
    }

    /**
     * Format user data for API response.
     * Includes ALL fields expected by both mobile User type and web.
     */
    private function formatUser(User $user): array
    {
        // Split name into first/last for mobile compatibility
        $nameParts = explode(' ', $user->name ?? '', 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'full_name' => $user->name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'role' => $user->role->value,
            'status' => $user->is_active ? 'active' : 'suspended',
            'locale' => $user->locale,
            'preferred_language' => $user->locale,
            'timezone' => $user->timezone,
            'avatar_path' => $user->avatar_path,
            'avatar_url' => $user->avatar_path,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'school_level' => $user->school_level,
            'is_active' => $user->is_active,
            'has_password' => !is_null($user->password),
            'has_google' => !is_null($user->google_id),
            'has_apple' => !is_null($user->apple_user_id),
            'email_verified' => !is_null($user->email_verified_at),
            'has_active_subscription' => $user->hasActiveSubscription(),
            'premium_expires_at' => $user->activeSubscription()?->current_period_end?->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }
}
