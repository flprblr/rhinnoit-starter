<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GoogleAccountPassword;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as GoogleUserContract;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $email = $googleUser->getEmail();

        if (blank($email)) {
            abort(422, 'La cuenta de Google no proporciona un correo electrónico válido.');
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

    private function updateExistingUser(User $user, GoogleUserContract $googleUser): void
    {
        $user->fill([
            'google_id' => $user->google_id ?: $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ])->save();
    }

    private function registerNewUser(GoogleUserContract $googleUser, string $email): User
    {
        $user = User::create([
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $email,
            'email' => $email,
            'password' => Hash::make($plainPassword = Str::random(16)),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
        ]);

        Mail::to($user->email)->send(new GoogleAccountPassword($user, $plainPassword));

        return $user;
    }
}
