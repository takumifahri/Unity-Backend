<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visitors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Untuk manajemen USer
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $data = User::all();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    public function countUser(){
        // Untuk manajemen USer
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $data = User::count();
            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Untuk manajemen user admin
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            try {
                $validate = $request->validate([
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|string|min:8',
                    'role' => 'required|in:admin,owner,user',
                    'phone' => 'nullable|string|max:15',
                    'address' => 'nullable|string|max:255',
                    'profile_photo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048'
                ],
                [
                    'email.unique' => 'Email sudah terdaftar',
                ]);
                // Handle upload gambar jika ada
                if ($request->hasFile('profile_photo')) {
                    $fileName = time() . '.' . $request->profile_photo->extension();
                    $request->profile_photo->move(public_path('uploads/user'), $fileName);
                    $validate['profile_photo'] = 'uploads/user/'. $fileName;
                } else {
                    $validate['profile_photo'] = null;
                }

                $user = User::create([
                    'name' => $validate['name'],
                    'email' => $validate['email'],
                    'password' => Hash::make($validate['password']),
                    'role' => $validate['role'],
                    'phone' => $validate['phone'],
                    'address' => $validate['address'],
                    'profile_photo' => $validate['profile_photo'],
                ]);
                return response()->json([
                    'message' => 'User created successfully',
                    'data' => $user,
                    'status' => 'success'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ]);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $data = User::find($id);
            if ($data !== null) {
                return response()->json([
                    'message' => 'Success',
                    'data' => $data
                ]);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    public function addAlamatToUser(Request $request, string $id)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $data = User::find($id);
            if ($data !== null) {
                try {
                    $validate = $request->validate([
                        'address' => 'required|string|max:255',
                        'longitude' => 'required|numeric',
                        'latitude' => 'required|numeric',
                    ]);
                    $data->update([
                        'address' => $validate['address'],
                        'longitude' => $validate['longitude'],
                        'latitude' => $validate['latitude'],
                    ]);
                    return response()->json([
                        'message' => 'User updated successfully',
                        'data' => $data,
                        'status' => 'success'
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'status' => 'error'
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $data = User::find($id);
            if ($data !== null) {
                try {
                    $validate = $request->validate([
                        'name' => 'sometimes|string|max:255',
                        'email' => 'sometimes|email|unique:users,email,' . $id,
                        'role' => 'sometimes|in:admin,owner,staff',
                        'phone' => 'sometimes|string|max:15',
                        'address' => 'sometimes|string|max:255',
                        'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
                    ], 
                    [
                        'email.unique' => 'Email sudah terdaftar',
                        'phone.sometimes' => 'Nomor telepon harus diisi',
                        'address.sometimes' => 'Alamat harus diisi',
                        'profile_photo.sometimes' => 'Foto harus berupa gambar',
                    ]);

                    // Handle file upload
                    if ($request->hasFile('profile_photo')) {
                        $file = $request->file('profile_photo');
                        $path = $file->store('uploads/user');
                        $validate['profile_photo'] = $path;
                    }
                    $data->update([
                        'name' => $request->has('name') ? $validate['name'] : $data->name,
                        'email' => $request->has('email') ? $validate['email'] : $data->email,
                        'role' => $request->has('role') ? $validate['role'] : $data->role,
                        'phone' => $request->has('phone') ? $validate['phone'] : $data->phone,
                        'address' => $request->has('address') ? $validate['address'] : $data->address,
                        'profile_photo' => $request->hasFile('profile_photo') ? $validate['profile_photo']->store('uploads/user') : $data->profile_photo,
                    ]);
                    return response()->json([
                        'message' => 'User updated successfully',
                        'data' => $data,
                        'status' => 'success'
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'status' => 'error'
                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
    $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $data = User::find($id);
            if ($data !== null) {
                $data->delete();
                return response()->json([
                    'message' => 'User deleted successfully',
                    'status' => 'success'
                ]);
            } else {
                return response()->json([
                    'message' => 'User not found'
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

    public function visitorStore(Request $request){
        try{
            Visitors::create([
                'ip_address' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url_visited' => $request->input('url_visited'),
            ]);

            return response()->json(['message' => 'Visitor tracked']);
        }catch(\Exception $e){
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ]);
        }
    }

    public function VisitorCount()
    {
        $user = User::findOrFail(Auth::id());
            if ($user->isAdmin() || $user->isOwner()) {
                $totalVisitors = Visitors::count();
                $currentMonthVisitors = Visitors::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)
                                ->count();
                $lastMonthVisitors = Visitors::whereMonth('created_at', now()->subMonth()->month)
                            ->whereYear('created_at', now()->subMonth()->year)
                            ->count();

                return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => 'success',
                'data' => [
                    'total_visitors' => $totalVisitors,
                    'current_month_visitors' => $currentMonthVisitors,
                    'last_month_visitors' => $lastMonthVisitors
                ]
                ]);
            } else {
                return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
                ], 403);
            }
       
       
    }

    public function topCustomer()
    {
        $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
            $topCustomers = User::withCount('orders')
                            ->where('role', 'user')
                            ->orderBy('total_order', 'desc')
                            ->limit(5)
                            ->get();
            
            return response()->json([
                'message' => 'Data retrieved successfully',
                'status' => 'success',
                'data' => $topCustomers
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized. Only admin can access this feature',
                'status' => 'error'
            ], 403);
        }
    }

}
