<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\FormEmail;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ContactUsControllerApi extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $user = Auth::user();
        if ($user->role === 'admin' || $user->role === 'owner') {
            $data = ContactUs::all();
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
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        try{
            $validate =$request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'no_hp' => 'required',
                'message' => 'required',
            ],
            [
                'name.required' => 'Nama harus diisi',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Email tidak valid',
                'no_hp.required' => 'Nomor HP harus diisi',
                'message.required' => 'Pesan harus diisi',
            ]);
    
            // Save to database
            $contact = ContactUs::create([
                'name' => $validate['name'],
                'email' => $validate['email'],
                'no_hp' => $validate['no_hp'],
                'message' => $validate['message'],
            ]);
    
              // Menggunakan alamat email yang telah ditentukan
            $reciever = config('contact.recipient_email', 'jrkonveksiemailuser@gmail.com');
            // Send email notification
            Mail::to($reciever)->send(new FormEmail($contact));
    
            return response()->json([
                'message' => 'Pesan berhasil dikirim',
                'data' => $contact,
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }   
        
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $user = Auth::user();
        if ($user->role === 'admin' || $user->role === 'owner') {
            try{
                $data = ContactUs::find($id);
                if ($data !== null) {
                    return response()->json([
                        'message' => 'Data retrieved successfully',
                        'data' => $data
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Contact Us not found'
                    ], 404);
                }
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
