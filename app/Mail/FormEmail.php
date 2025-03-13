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
        $text .= "Email: {$this->contact->email}\n";
        $text .= "No HP: {$this->contact->no_hp}\n";
        $text .= "Pesan: {$this->contact->message}\n";
        
        return $this->view('emails.contact') // HTML version
                    ->text('emails.contact-plain') // Plain text version
                    ->subject('Pesan Baru dari Form Kontak')
                    ->with([
                        'messageText' => $text
                    ]);
    }
}