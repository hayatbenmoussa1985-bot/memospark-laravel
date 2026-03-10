<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile page.
     */
    public function show()
    {
        $user = auth()->user();

        $stats = [
            'total_decks' => $user->decks()->count(),
            'total_reviews' => $user->cardProgress()->sum('total_reviews'),
            'badges_count' => $user->badges()->count(),
            'member_since' => $user->created_at->format('M Y'),
        ];

        $badges = $user->badges()->get();

        return view('user.profile.show', compact('user', 'stats', 'badges'));
    }

    /**
     * Update profile.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'school_level' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'locale' => ['nullable', 'in:en,fr,es,ar'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated!');
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password changed!');
    }
}
