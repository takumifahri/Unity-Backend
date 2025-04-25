<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Validation\Rules;
use Laravel\Socialite\Facades\Socialite;

class AuthControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function register(Request $request): JsonResponse
    {
        
        $validateData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'role' => ['required', 'in:admin,user,owner,developer'],
            'phone' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female'],
            'password' => ['required', Rules\Password::defaults()],
            'isAgree' => ['required', 'boolean'],
        ]);

      
        $user = User::create([  
            'name' => $validateData['name'],
            'email' => $validateData['email'],
            'phone' => $validateData['phone'],
            'role' => $validateData['role'],
            'gender' => $validateData['gender'],
            'isAgree' => $validateData['isAgree'],
            'password' => Hash::make($validateData['password']),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Cek kredensial
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'The provided credentials do not match our records.'
            ], 401);
        }

        // Ambil user yang berhasil login
        $user = User::where('email', $request->email)->firstOrFail();
        $user->isActive = true;
        $user->save();
        // Hapus token lama jika ada
        $user->tokens()->delete();
        
        // Buat token baru
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
    public function logout(Request $request)
    {
        // Revoke semua token dari user yang sedang login
        // Set isActive to false
        $user = $request->user();
        $user->isActive = false;
        $user->save();

        // Revoke all tokens
        $user->tokens()->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }


   

    
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            // Removed stateless() as it is not defined
            ->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Validate state parameter for security
            if (!$request->has('state') || !$request->has('code')) {
                throw new \Exception('Invalid OAuth response');
            }


            // In your controller, before using Socialite
            \Illuminate\Support\Facades\Config::set('services.google.guzzle', [
                'verify' => false
            ]);

            // Get authenticated Google user
            $googleUser = Socialite::driver('google')->user();
                // ->stateless() // Important for API usage
                
            // Validate required fields
            if (empty($googleUser->email)) {
                throw new \Exception('Email not provided by Google');
            }

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name ?? 'No Name Provided',
                    'google_id' => $googleUser->id,
                    'email' => $googleUser->email,
                    'email_verified_at' => now(), // Mark email as verified
                    'role' => 'user',
                    'password' => Hash::make(Str::random(16)), // Optional: Generate a random password
                    'total_order' => 0, // Default value for total_order
                    'isAgree' => true, // Default value for isAgree
                    'phone' => null, // Default value for phone
                    'profile_photo' => $googleUser->avatar ?? null, // Use Google avatar if available
                ]
            );

            // Trigger registered event if new user
            if ($user->wasRecentlyCreated) {
                event(new Registered($user));
            }

            // Log the user in
            Auth::login($user);
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $token = $user->createToken('auth_token')->plainTextToken;
            return redirect("{$frontendUrl}/Akun?token={$token}");
           
        } catch (\Exception $e) {
            Log::error('Google Auth Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Authentication failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 401);
        }
    }
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }


    public function handleFacebookCallback(Request $request)
    {
        try {
            // Get authenticated Facebook user
            $facebookUser = Socialite::driver('facebook')->user();
            
            // Validate required fields
            if (empty($facebookUser->email)) {
                throw new \Exception('Email not provided by Facebook');
            }

            // Find or create user
            $user = User::updateOrCreate(
                ['email' => $facebookUser->email],
                [
                    'name' => $facebookUser->name ?? 'No Name Provided',
                    'facebook_id' => $facebookUser->id,
                    'email' => $facebookUser->email,
                    'email_verified_at' => now(), // Mark email as verified
                    'role' => 'user',
                    'password' => Hash::make(Str::random(16)), // Optional: Generate a random password
                    'total_order' => 0, // Default value for total_order
                    'isAgree' => true, // Default value for isAgree
                    'phone' => null, // Default value for phone
                    'profile_photo' => $facebookUser->avatar ?? null, // Use Facebook avatar if available
                ]
            );

            // Trigger registered event if new user
            if ($user->wasRecentlyCreated) {
                event(new Registered($user));
            }

            // Generate token and return response
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Redirect to frontend with token
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            return redirect()->away("{$frontendUrl}/Akun?token={$token}");
            
        } catch (Exception $e) {
            return redirect()->away(config('app.frontend_url') . '/login-error?message=' . urlencode($e->getMessage()));
        }
    }

}