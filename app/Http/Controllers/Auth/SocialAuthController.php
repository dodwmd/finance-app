<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect(string $provider)
    {
        if (!in_array($provider, ['google', 'github'])) {
            return redirect()->route('login')->with('error', 'Invalid social provider');
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function callback(string $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
            
            $user = User::where('provider_id', $socialUser->getId())
                   ->where('provider', $provider)
                   ->first();

            if (!$user) {
                // Check if a user with the same email exists
                $existingUser = User::where('email', $socialUser->getEmail())->first();
                
                if ($existingUser) {
                    // Update existing user with provider info if they don't have a provider set
                    if (!$existingUser->provider) {
                        $existingUser->update([
                            'provider' => $provider,
                            'provider_id' => $socialUser->getId(),
                            'avatar' => $socialUser->getAvatar(),
                        ]);
                        
                        $user = $existingUser;
                    } else {
                        // User already has a different social login
                        return redirect()->route('login')
                            ->with('error', 'An account with this email already exists. Please log in using your original method.');
                    }
                } else {
                    // Create a new user
                    $user = User::create([
                        'name' => $socialUser->getName(),
                        'email' => $socialUser->getEmail(),
                        'password' => null, // No password for social logins
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                        'email_verified_at' => now(), // Social logins are pre-verified
                    ]);
                }
            }

            // Login the user
            Auth::login($user, true);
            
            return redirect()->intended('/dashboard');
            
        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'An error occurred during social login. Please try again later.');
        }
    }
}
