<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirect($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        // For stateless APIs, we must use stateless()
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function callback($provider)
    {
        $validated = $this->validateProvider($provider);
        if (!is_null($validated)) {
            return $validated;
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            // Find or create user
            $user = User::where('email', $socialUser->getEmail())->first();

            if ($user) {
                // Link provider if not linked yet
                if (!$user->provider_id) {
                    $user->update([
                        'provider' => $provider,
                        'provider_id' => $socialUser->getId(),
                    ]);
                }
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'username' => Str::slug($socialUser->getName() ?? $socialUser->getNickname() ?? 'user') . '-' . Str::random(5),
                    'email' => $socialUser->getEmail(),
                    'password' => null,
                    'provider' => $provider,
                    'provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                ]);
            }

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;
            
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            
            // Redirect to frontend with the token
            return redirect()->away($frontendUrl . '/auth/callback?token=' . $token);

        } catch (\Exception $e) {
            Log::error('Social Auth Error: ' . $e->getMessage());
            
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            return redirect()->away($frontendUrl . '/login?error=auth_failed');
        }
    }

    /**
     * Validate if the provider is supported.
     */
    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['google', 'apple'])) {
            return response()->json(['error' => 'Please login using google or apple'], 422);
        }
        
        return null;
    }
}
