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
}