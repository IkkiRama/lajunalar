<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();
        $email = strtolower((string) $googleUser->getEmail());
        $isAdministrator = in_array($email, config('lajunalar.admin_emails'), true);
        $user = User::query()
            ->where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user === null) {
            $user = User::create([
                'name' => $googleUser->getName() ?: 'Pengguna LajuNalar',
                'email' => $email,
                'email_verified_at' => now(),
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'last_login_at' => now(),
                'role' => $isAdministrator ? 'admin' : 'user',
            ]);
        } else {
            $user->update([
                'name' => $googleUser->getName() ?: $user->name,
                'google_id' => $googleUser->getId(),
                'avatar_url' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?? now(),
                'last_login_at' => now(),
                'role' => $isAdministrator ? 'admin' : $user->role,
            ]);
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    }
}
