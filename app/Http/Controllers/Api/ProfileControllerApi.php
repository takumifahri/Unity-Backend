<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ProfileControllerApi extends Controller
{
    
    public function updateProfilePhoto(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        try{
            if($user != null){
                $request->validate([
                    'profile_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048'
                ], [
                    'profile_photo.max' => 'The profile photo may not be greater than 2 MB.',
                    'profile_photo.mimes' => 'The profile photo must be a file of type: jpg, jpeg, png.',
                ]);

                if ($request->hasFile('profile_photo')) {
                    // Hapus foto profil lama jika perlu
                    if ($user->profile_photo && file_exists(public_path("uploads/profile/{$user->profile_photo}"))) {
                        unlink(public_path("uploads/profile/{$user->profile_photo}"));
                    }

                    $fileName = time() . '.' . $request->profile_photo->extension();
                    $request->profile_photo->move(public_path('uploads/profile'), $fileName);
                    $user->update(['profile_photo' => "uploads/profile/{$fileName}"]);
                }
            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile photo updated successfully',
                    'user' => $user,
                    'profile_photo' => asset("storage/{$user->profile_photo}"),
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unautheticated. Login first'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while updating the profile photo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function index(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        try {
            if ($user != null) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User data retrieved successfully',
                    'user' => $user
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Login first'
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving user data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // public function updateProfile(Request $request)
    // {
    //     $user = User::findOrFail(Auth::id());
    //     try {
    //         if ($user != null) {
    //             $validate = $request->validate([
    //                 'name' => 'sometimes|string|max:255',
    //                 'email' => 'sometimes|email|max:255',
    //                 'phone' => 'sometimes|string|max:255',
    //                 'gender' => 'sometimes|in:male,female',
    //                 // Location fields
    //                 'label' => 'sometimes|string',
    //                 'latitude' => 'sometimes|numeric',
    //                 'longitude' => 'sometimes|numeric',
    //                 'address' => 'sometimes|string',
    //                 'city' => 'sometimes|string',
    //                 'region' => 'sometimes|string',
    //                 'postal_code' => 'sometimes|string',
    //             ], [
    //                 'name.required' => 'The name field is required.',
    //                 'phone.required' => 'The phone field is required.',
    //                 'gender.in' => 'The gender must be one of the following: male, female.',
    //                 'latitude.numeric' => 'The latitude must be a numeric value.',
    //                 'longitude.numeric' => 'The longitude must be a numeric value.',
    //             ]);

    //             $updateProfile = [
    //                 'name' => $validate['name'] ?? $user->name,
    //                 'phone' => $validate['phone'] ?? $user->phone,
    //                 'gender' => $validate['gender'] ?? $user->gender,
    //                 'email' => $validate['email'] ?? $user->email,
    //             ];

    //             // Update basic user profile
    //             $user->update($updateProfile);

    //             // Handle location update if any location field is provided
    //             if (isset($validate['label']) || isset($validate['latitude']) || isset($validate['longitude'])) {
    //                 // If user already has a location, update it
    //                 if ($user->location_id) {
    //                     $location = location::find($user->location_id);
                        
    //                     $locationData = [
    //                         'label' => $validate['label'] ?? $location->label,
    //                         'latitude' => $validate['latitude'] ?? $location->latitude,
    //                         'longitude' => $validate['longitude'] ?? $location->longitude,
    //                         'address' => $validate['address'] ?? $location->address,
    //                         'city' => $validate['city'] ?? $location->city,
    //                         'region' => $validate['region'] ?? $location->region,
    //                         'postal_code' => $validate['postal_code'] ?? $location->postal_code,
    //                     ];
                        
    //                     $location->update($locationData);
    //                 } else {
    //                     // Ensure all required location fields are present
    //                     if (!isset($validate['label']) || !isset($validate['latitude']) || !isset($validate['longitude'])) {
    //                         return response()->json([
    //                             'status' => 'error',
    //                             'message' => 'Label, latitude, and longitude are required when creating a new location'
    //                         ], 422);
    //                     }
                        
    //                     // If user doesn't have a location, create a new one
    //                     $location = Location::create([
    //                         'user_id' => $user->id,
    //                         'label' => $validate['label'],
    //                         'latitude' => $validate['latitude'],
    //                         'longitude' => $validate['longitude'],
    //                         'address' => $validate['address'] ?? null,
    //                         'city' => $validate['city'] ?? null,
    //                         'region' => $validate['region'] ?? null,
    //                         'postal_code' => $validate['postal_code'] ?? null,
    //                     ]);
                        
    //                     // Update user with new location ID
    //                     $user->location_id = $location->id;
    //                     $user->save();
    //                 }
    //             }
    //             // If address is provided but no coordinates
    //             if (isset($validate['address']) && (!isset($validate['latitude']) || !isset($validate['longitude']))) {
    //                 // Build address string
    //                 $addressString = $validate['address'];
    //                 if (isset($validate['city'])) $addressString .= ', ' . $validate['city'];
    //                 if (isset($validate['region'])) $addressString .= ', ' . $validate['region'];
    //                 if (isset($validate['postal_code'])) $addressString .= ' ' . $validate['postal_code'];
                    
    //                 // Geocode the address (using a service like Google Maps, Nominatim/OpenStreetMap, etc.)
    //                 $coordinates = $this->geocodeAddress($addressString);
                    
    //                 if ($coordinates) {
    //                     $validate['latitude'] = $coordinates['latitude'];
    //                     $validate['longitude'] = $coordinates['longitude'];
    //                 }
    //             }
    //             // Refresh user data with location
    //             $user = User::with('location')->find($user->id);
                
    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'Profile updated successfully',
    //                 'user' => $user
    //             ]);
    //         } else {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'Unauthenticated. Login first'
    //             ], 401);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'An error occurred while updating the profile',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // // }
    // public function updateProfile(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email,' . Auth::id(),
    //         'phone' => 'nullable|string|max:20',
    //         'gender' => 'nullable|string|in:male,female',
    //         'address' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['message' => $validator->errors()], 422);
    //     }

    //     try {
    //         $user = User::find(Auth::id());
    //         $user->name = $request->name;
    //         $user->email = $request->email;
    //         $user->telepon = $request->telepon;
    //         $user->gender = $request->gender;
            
    //         // Periksa apakah alamat berubah
    //         $addressChanged = $user->address !== $request->address;
    //         $user->address = $request->address;
            
    //         // Jika alamat berubah, lakukan geocoding
    //         if ($addressChanged) {
    //             Log::info('Alamat berubah, melakukan geocoding untuk: ' . $request->address);
                
    //             // Dapatkan koordinat dari alamat
    //             $coordinates = $this->geocodeAddress($request->address);
                
    //             if ($coordinates) {
    //                 $user->latitude = $coordinates['latitude'];
    //                 $user->longitude = $coordinates['longitude'];
    //                 Log::info('Geocoding berhasil: ' . $coordinates['latitude'] . ', ' . $coordinates['longitude']);
    //             } else {
    //                 Log::warning('Geocoding gagal untuk alamat: ' . $request->address);
    //                 return response()->json([
    //                     'message' => 'Alamat tidak dapat ditemukan. Pastikan alamat lengkap dengan format: Jalan, Kecamatan, Kota'
    //                 ], 422);
    //             }
    //         }
            
    //         $user->save();
            
    //         return response()->json($user);
            
    //     } catch (\Exception $e) {
    //         Log::error('Error pada update profile: ' . $e->getMessage());
    //         return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
    //     }
    // }
    
    
    
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|string|in:male,female',
            'address' => 'required|string',
            'latitude' => 'required|numeric', // Latitude wajib dikirim
            'longitude' => 'required|numeric', // Longitude wajib dikirim
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }
    
        try {
            $user = User::find(Auth::id());
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->gender = $request->gender;
    
            // Periksa apakah alamat atau koordinat berubah
            $addressChanged = $user->address !== $request->address || 
                              $user->latitude != $request->latitude || 
                              $user->longitude != $request->longitude;
    
            $user->address = $request->address;
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
    
            if ($addressChanged) {
                Log::info('Alamat atau koordinat berubah: ' . $request->address . ' (' . $request->latitude . ', ' . $request->longitude . ')');
            }
    
            $user->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'user' => $user
            ]);
    
        } catch (\Exception $e) {
            Log::error('Error pada update profile: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    // private function geocodeAddress($address)
    // {
    //     // Example using Nominatim (OpenStreetMap)
    //     $address = urlencode($address);
    //     $url = "https://nominatim.openstreetmap.org/search?q={$address}&format=json&limit=1";
        
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_USERAGENT, 'YourApp/1.0'); // Required by Nominatim's ToS
    //     $response = curl_exec($ch);
    //     curl_close($ch);
        
    //     $data = json_decode($response, true);
        
    //     if (!empty($data)) {
    //         return [
    //             'latitude' => $data[0]['lat'],
    //             'longitude' => $data[0]['lon']
    //         ];
    //     }
        
    //     return null;
    // }
    private function geocodeAddress($address)
    {
        try {
            // Tambahkan ", Indonesia" untuk meningkatkan akurasi jika tidak ada
            if (!str_contains(strtolower($address), 'indonesia')) {
                $address .= ', Indonesia';
            }
            
            // Menggunakan Nominatim dari OpenStreetMap (free)
            $response = Http::withHeaders([
                'User-Agent' => 'YourApp/1.0 (your@email.com)' // Wajib menurut ToS Nominatim
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);
            
            Log::info('Nominatim API response: ' . $response->body());
            
            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];
                
                return [
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon']
                ];
            }
            
            // Fallback ke Google Maps API jika Nominatim gagal (opsional)
            // return $this->geocodeAddressWithGoogle($address);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Alternatif geocoding menggunakan Google Maps API
     * Perlu API key Google Maps yang valid
     */
    private function geocodeAddressWithGoogle($address)
    {
        try {
            $apiKey = config('services.google.maps_api_key');
            
            if (!$apiKey) {
                Log::warning('Google Maps API key tidak ditemukan');
                return null;
            }
            
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $apiKey,
            ]);
            
            Log::info('Google Maps API response: ' . $response->body());
            
            if ($response->successful() && $response->json()['status'] === 'OK') {
                $location = $response->json()['results'][0]['geometry']['location'];
                
                return [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng']
                ];
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Google Geocoding error: ' . $e->getMessage());
            return null;
        }
    }
    /**
     * Display a listing of the resource.
     */
   
     public function me(Request $request)
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
         
         // Load the location relationship
         $user->load('location');
         
         // Tambahkan return statement untuk mengembalikan data user dengan location
         return response()->json([
             'status' => 'success',
             'user' => $user
         ]);
     }

    public function changePassword(Request $request){
        $user = User::findOrFail(Auth::id());
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        $validate = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($validate['old_password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Old password is incorrect'
            ], 400);
        }
        $user->password = Hash::make($validate['new_password']);
        $user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Password changed successfully'
        ]);
    }

    public function linkResetPassword(Request $request)
    {
        //
        $user = $request->user();
        if (!$user) {
            return response()->json([
            'status' => 'error',
            'message' => 'User not found'
            ], 404);
        }

        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['status' => __($status)])
            : response()->json(['email' => __($status)], 400);
    }

    
    public function updatePassword (Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }

        $validate = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
        
        $status = Password::reset(
            $validate,
            function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
            }
        );
        
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password berhasil direset!'])
            : response()->json(['message' => __($status)], 400);
    }

    public function DeleteAccount(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ], 404);
        }
        $user->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Account deleted successfully'
        ]);
    }
}
