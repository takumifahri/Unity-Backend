<?php

namespace App\Mail;

use App\Models\DeliveryProof;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecievedByUsersMails extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $deliveryProof;
    public $customer;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, DeliveryProof $deliveryProof, User $customer)
    {
        $this->order = $order;
        $this->deliveryProof = $deliveryProof;
        $this->customer = $customer;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pesanan Anda Telah Diterima - Order #{$this->order->order_unique_id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order.received.received',
            with: [
                'customerName' => $this->customer->name,
                'orderId' => $this->order->order_unique_id,
                'orderDate' => $this->order->created_at->format('d-m-Y'),
                'deliveryDate' => $this->deliveryProof->delivery_date->format('d-m-Y'),
                'receiverName' => $this->deliveryProof->receiver_name,
                'totalPrice' => $this->order->total_harga,
                'notes' => $this->deliveryProof->notes,
                'imagePath' => asset('storage/' . $this->deliveryProof->image_path),
                'productDetails' => $this->getProductDetails()
            ],
        );
    }

    public function build()
    {
        return $this->view('emails.order.received.received')
                    ->text('emails.order.received.received-plain')
                    ->subject("Pesanan Anda Telah Diterima - Order #{$this->order->order_unique_id}")
                    ->with([
                        'customerName' => $this->customer->name,
                        'orderId' => $this->order->order_unique_id,
                        'orderDate' => $this->order->created_at->format('d-m-Y'),
                        'deliveryDate' => $this->deliveryProof->delivery_date->format('d-m-Y'),
                        'receiverName' => $this->deliveryProof->receiver_name,
                        'totalPrice' => $this->order->total_harga,
                        'notes' => $this->deliveryProof->notes,
                        'imagePath' => asset('storage/' . $this->deliveryProof->image_path),
                        'productDetails' => $this->getProductDetails()
                    ]);
    }

    private function getProductDetails()
    {
        if ($this->order->catalog_id) {
            return [
                'type' => 'catalog',
                'name' => $this->order->catalog ? $this->order->catalog->nama_katalog : 'Produk',
                'quantity' => $this->order->jumlah,
                'color' => $this->order->color ? $this->order->color->color_name : '-',
                'size' => $this->order->size ? $this->order->size : '-',
            ];
        } elseif ($this->order->custom_order_id) {
            return [
                'type' => 'custom',
                'name' => $this->order->customOrder ? $this->order->customOrder->jenis_baju : 'Custom Order',
                'quantity' => $this->order->jumlah,
                'size' => $this->order->customOrder ? $this->order->customOrder->ukuran : '-',
                'material' => $this->order->customOrder ? $this->order->customOrder->detail_bahan : '-',
            ];
        }

        return ['type' => 'unknown', 'name' => 'Pesanan'];
    }


    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
