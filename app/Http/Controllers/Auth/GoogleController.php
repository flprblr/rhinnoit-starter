<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GoogleAccountPassword;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as GoogleUserContract;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleController extends Controller
{
    /**
     * Redirect the user to the Google authentication page
     */
    public function redirect(): SymfonyRedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle the callback from Google OAuth
     */
    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();

        if (blank($email)) {
            abort(422, 'The Google account does not provide a valid email address.');
        }

        // Enforce allowed domain from env
        $allowedDomain = (string) (config('services.google.allowed_domain') ?: env('GOOGLE_ALLOWED_DOMAIN'));
        $allowedDomain = trim(strtolower($allowedDomain));
        $atPos = strrpos($email, '@');
        $emailDomain = $atPos !== false ? strtolower(substr($email, $atPos + 1)) : '';
        if ($allowedDomain === '') {
            return redirect()
                ->route('login')
                ->with('error', __('Configuración inválida: GOOGLE_ALLOWED_DOMAIN no está definido.'));
        }

        if ($emailDomain === '' || ! hash_equals($allowedDomain, $emailDomain)) {
            return redirect()
                ->route('login')
                ->with('error', __('Solo se permiten cuentas del dominio :domain.', ['domain' => $allowedDomain]));
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->updateExistingUser($user, $googleUser);
        } else {
            $user = $this->registerNewUser($googleUser, $email);
        }

        Auth::login($user);

        return redirect()->intended('/dashboard');
    }

    /**
     * Update an existing user with Google information
     */
    private function updateExistingUser(User $user, GoogleUserContract $googleUser): void
    {
        $user->fill([
            'google_id' => $user->google_id ?: $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ])->save();
    }

    /**
     * Register a new user from Google OAuth
     */
    private function registerNewUser(GoogleUserContract $googleUser, string $email): User
    {
        $plainPassword = Str::random(16);

        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $email,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        Mail::to($user->email)->send(new GoogleAccountPassword($user, $plainPassword));

        return $user;
    }
}
