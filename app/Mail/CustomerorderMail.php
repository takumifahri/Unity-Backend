<?php

namespace App\Mail;

use App\Models\CustomOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerorderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $customOrder;

    /**
     * Create a new message instance.
     *
     * @param CustomOrder $customOrder
     * @return void
     */
    public function __construct(CustomOrder $customOrder)
    {
        $this->customOrder = $customOrder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.custom-order.accepted')
                    ->text('emails.custom-order.accepted-plain')
                    ->subject("Custom Order Anda Telah Disetujui - {$this->customOrder->jenis_baju}")
                    ->with([
                        'nama' => $this->customOrder->nama_lengkap,
                        'email' => $this->customOrder->email,
                        'noTelp' => $this->customOrder->no_telp,
                        'jenisBaju' => $this->customOrder->jenis_baju,
                        'ukuran' => $this->customOrder->ukuran,
                        'jumlah' => $this->customOrder->jumlah,
                        'sumberKain' => $this->customOrder->sumber_kain,
                        'estimasiWaktu' => $this->customOrder->estimasi_waktu,
                        'catatan' => $this->customOrder->catatan
                    ]);
    }
}
