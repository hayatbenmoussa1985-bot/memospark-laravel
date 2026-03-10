<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Login with email and password, return Sanctum token.
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
     * Register a new user account.
     */
    public function signup(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'sometimes|in:parent,learner',
        ]);

        $user = User::create([
            'name' => $request->name,
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
     * POST /api/v1/auth/social/google
     * Login or register via Google ID token.
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
     * Login or register via Apple authorization code.
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
                ->user(); // Uses authorization_code from the request
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
     * Revoke the current token.
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
     * Return the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->formatUser($request->user()),
        ]);
    }

    /**
     * POST /api/v1/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    /**
     * POST /api/v1/auth/delete-account
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => 'required|in:DELETE',
        ]);

        $user = $request->user();

        // Revoke all tokens
        $user->tokens()->delete();

        // Soft delete related content
        $user->decks()->delete();

        // Deactivate account (we don't hard-delete users)
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
     * Find an existing user by social ID or email, or create a new one.
     */
    private function findOrCreateSocialUser(string $email, string $name, string $socialField, string $socialId, ?string $avatarUrl = null): User
    {
        // First try to find by social ID
        $user = User::where($socialField, $socialId)->first();

        if ($user) {
            $user->update(['last_login_at' => now()]);
            return $user;
        }

        // Then try by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // Link social account to existing user
            $user->update([
                $socialField => $socialId,
                'last_login_at' => now(),
            ]);
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $name,
            'email' => $email,
            $socialField => $socialId,
            'role' => 'learner',
            'email_verified_at' => now(), // Social login = verified email
            'last_login_at' => now(),
        ]);
    }

    /**
     * Format user data for API response.
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'locale' => $user->locale,
            'timezone' => $user->timezone,
            'avatar_path' => $user->avatar_path,
            'date_of_birth' => $user->date_of_birth?->toDateString(),
            'school_level' => $user->school_level,
            'is_active' => $user->is_active,
            'has_password' => !is_null($user->password),
            'has_google' => !is_null($user->google_id),
            'has_apple' => !is_null($user->apple_user_id),
            'email_verified' => !is_null($user->email_verified_at),
            'has_active_subscription' => $user->hasActiveSubscription(),
            'created_at' => $user->created_at->toIso8601String(),
        ];
    }
}
