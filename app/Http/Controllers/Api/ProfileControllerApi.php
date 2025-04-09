<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ProfileControllerApi extends Controller
{
    private function convertToWhatsapp($phone)
    {
        // Menghapus karakter non-numerik dari nomor telepon
        $phone = preg_replace('/\D/', '', $phone);
        
        // Mengubah nomor telepon yang diawali dengan 08 menjadi format dengan kode negara Indonesia (62)
        if (substr($phone, 0, 2) == '08') {
            $phone = '62' . substr($phone, 1);
        }
        // Menghapus tanda + jika nomor telepon diawali dengan +62
        elseif (substr($phone, 0, 3) == '620') {
            $phone = '62' . substr($phone, 3);
        }
        
        return 'https://wa.me/62'.$phone;
    }

    public function updateProfilePhoto(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        try{
            if($user != null){
                $validate = $request->validate([
                    'profile_photo' => 'required|image|mimes:jpg,jpeg,png|max:2048'
                ], [
                    'profile_photo.max' => 'The profile photo may not be greater than 2 MB.',
                    'profile_photo.mimes' => 'The profile photo must be a file of type: jpg, jpeg, png.',
                ]);

                if($request->hasFile('profile_photo')){
                    // Hapus foto profil lama jika perlu
                    if ($user->profile_photo && file_exists(storage_path('app/public/' . $user->profile_photo))) {
                        unlink(storage_path('app/public/' . $user->profile_photo));
                    }
                
                    $fileName = time() . '.' . $request->profile_photo->extension();
                    $request->profile_photo->move(storage_path('app/public/profile-photos'), $fileName);
                    $user->update(['profile_photo' => 'profile-photos/' . $fileName]);
                }
            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile photo updated successfully',
                    'user' => $user,
                    'profile_photo' => asset('storage/' . $user->profile_photo), // <--- tambahkan ini
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

    public function updateProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        try{
            if($user != null){
                $validate = $request->validate([
                    'name' => 'sometimes|string|max:255',
                    'email' => 'sometimes|email|max:255',
                    'phone' => 'sometimes|string|max:255',
                    'gender' => 'sometimes|in:laki,perempuan',
                ], [
                    'name.required' => 'The name field is required.',
                    'phone.required' => 'The phone field is required.',
                    'gender.in' => 'The gender must be one of the following: male, female, or other.',
                ]);

                $updateProfile = [
                    'name' => $validate['name'] ?? $user->name,
                    'phone' => $this->convertToWhatsapp( $validate['phone']) ?? $user->phone,
                    'gender'=> $validate['gender'] ?? $user->gender,
                    'email' => $validate['email'] ?? $user->email,
                ];

            
                $user->update($updateProfile);
            
                return response()->json([
                    'status' => 'success',
                    'message' => 'Profile updated successfully',
                    'user' => $user
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
                'message' => 'An error occurred while updating the profile',
                'error' => $e->getMessage()
            ], 500);
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
         
         // Tambahkan return statement untuk mengembalikan data user
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
