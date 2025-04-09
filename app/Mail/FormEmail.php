<?php

namespace App\Mail;

use App\Models\ContactUs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FormEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public function __construct(ContactUs $contact)
    {
        $this->contact = $contact;
    }

    public function build()
    {
        
        $text = "Contact Us Email Form!\n\n";
        $text .= "Nama: {$this->contact->name}\n";
        $text .= "Subjek: {$this->contact->subject}\n";
        $text .= "Email: {$this->contact->email}\n";
        $text .= "No HP: {$this->contact->no_hp}\n";
        $text .= "Pesan: {$this->contact->message}\n";
        
        // Buat builder email terlebih dahulu dan simpan dalam variabel
        $mailBuilder = $this->view('emails.contact') // HTML version
                            ->text('emails.contact-plain') // Plain text version
                            ->subject('Pesan Baru dari Form Kontak')
                            ->with([
                                'messageText' => $text
                            ]);

        // Jika ada attachment, tambahkan ke email
        if ($this->contact->attachment) {
            // Path yang benar sesuai dengan yang disimpan di controller
            $attachmentPath = storage_path('app/public/' . $this->contact->attachment);
            
            if (file_exists($attachmentPath)) {
                $mailBuilder->attach($attachmentPath, [
                    'as' => basename($this->contact->attachment),
                    'mime' => mime_content_type($attachmentPath)
                ]);
                
                // Tambahkan informasi attachment ke text
                $text .= "Attachment: " . basename($this->contact->attachment) . "\n";
                
                // Update pesan text dengan informasi attachment
                $mailBuilder->with([
                    'messageText' => $text
                ]);
            }
        }
        
        return $mailBuilder;
    }
}