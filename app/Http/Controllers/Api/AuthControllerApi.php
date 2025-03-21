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
use Illuminate\Validation\Rules;

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
            'role' => ['required', 'in:admin,user,owner'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([  
            'name' => $validateData['name'],
            'email' => $validateData['email'],
            'role' => $validateData['role'],
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
    }public function logout(Request $request)
    {
        // Revoke semua token dari user yang sedang login
        $request->user()->tokens()->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out'
        ]);
    }

    public function whoami(Request $request)
    {
        // Ambil user yang sedang login dari request
        $user = $request->user();
        
        // Jika tidak ada user yang login, kembalikan error
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        // Tambahkan return statement untuk mengembalikan data user
        return response()->json([
            'status' => 'success',
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        try{
            if($user != null){
                $validate = $request->validate([
                    'name' => 'sometimes|string|max:255',
                    'phone' => 'sometimes|string|max:255',
                    // 'address' => 'sometimes|string|max:255',
                    'profile_photo' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048'
                ], [
                    'profile_photo.max' => 'The profile photo may not be greater than 2 MB.',
                    'profile_photo.mimes' => 'The profile photo must be a file of type: jpg, jpeg, png.',
                    'name.required' => 'The name field is required.',
                    'email.required' => 'The email field is required.',
                ]);

                $updateProfile = [
                    'name' => $validate['name'] ?? $user->name,
                    'phone' => $validate['phone'] ?? $user->phone,
                    // 'address' => $validate['address'] ?? $user->address,
                    'profile_photo' => $validate['profile_photo'] ?? $user->profile_photo,
                ];

                if($request->hasFile('profile_photo')){
                    // Hapus foto profil lama jika perlu
                    if ($user->profile_photo && file_exists(storage_path('app/public/' . $user->profile_photo))) {
                        unlink(storage_path('app/public/' . $user->profile_photo));
                    }
                    
                    $fileName = time() . '.' . $request->profile_photo->extension();
                    $request->profile_photo->move(storage_path('app/public/profile-photos'), $fileName);
                    $updateProfile['profile_photo'] = 'profile-photos/' . $fileName;
                }

                $user->update($updateProfile);
            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'user' => $user
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}