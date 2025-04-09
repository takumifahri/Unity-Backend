<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AutoResponse;
use App\Mail\FormEmail;
use App\Models\ContactUs;
use App\Models\User;
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
    $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
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

    private function convertToWhatsAppLink(string $phoneNumber): string
    {
        // Remove any non-numeric characters
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Handle different phone number formats
        if (str_starts_with($phoneNumber, '08')) {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        } elseif (str_starts_with($phoneNumber, '021')) {
            $phoneNumber = '62' . substr($phoneNumber, 1);
        } elseif (str_starts_with($phoneNumber, '+62')) {
            $phoneNumber = substr($phoneNumber, 1);
        }

        // Return the WhatsApp link
        return 'https://wa.me/' . $phoneNumber;
    }
    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        try {
            $validate = $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'no_hp' => 'required',
                'subject' => 'required',
                'message' => 'required',
                'attachment' => 'nullable|file|max:10240', // Max 10MB
            ],
            [
                'name.required' => 'Nama harus diisi',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Email tidak valid',
                'subject.required' => 'Subjek harus diisi',
                'no_hp.required' => 'Nomor HP harus diisi',
                'message.required' => 'Pesan harus diisi',
                'attachment.file' => 'Attachment harus berupa file',
                'attachment.max' => 'Ukuran attachment maksimal 10MB',
            ]);
    
            // Inisialisasi data contact
            $contactData = [
                'name' => $validate['name'],
                'email' => $validate['email'],
                'no_hp' => $this->convertToWhatsAppLink($validate['no_hp']), // Convert no_hp to WhatsApp link format
                'subject' => $validate['subject'],
                'message' => $validate['message'],
                'attachment' =>  null,
            ];
    
            // Upload attachment jika ada
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Simpan file ke storage/app/public/attachment/emails/contactus
                $path = $file->storeAs('attachment/emails/contactus', $fileName, 'public');
                
                // Simpan path file ke database
                $contactData['attachment'] = $path;
            }
            // Save to database
            $contact = ContactUs::create($contactData);
    
            // Menggunakan alamat email yang telah ditentukan
            $receiver = config('contact.recipient_email', 'jrkonveksiemail@gmail.com');
            
            // Send email notification to the administrator
            Mail::to($receiver)->send(new FormEmail($contact)); // Ganti ContactEmail dengan FormEmail sesuai class yang dibuat sebelumnya
            
            // Send auto-response back to the user
            Mail::to($contact->email)->send(new AutoResponse($contact));
    
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
    $user = User::findOrFail(Auth::id());
        if ($user->isAdmin() || $user->isOwner()) {
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
