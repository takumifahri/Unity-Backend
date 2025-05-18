<?php

namespace App\Mail;

use App\Models\CustomOrder;
use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendDeliveryMails extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $customOrder;
    public $customer;

    /**
     * Create a new message instance.
     * 
     * @param Order $order
     * @param CustomOrder $customOrder
     * @param User $customer
     * @return void
     */
    public function __construct(Order $order, CustomOrder $customOrder, User $customer)
    {
        $this->order = $order;
        $this->customOrder = $customOrder;
        $this->customer = $customer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pesanan Anda Sedang Dikirim - Order #{$this->order->order_unique_id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order.delivery.delivery',
            with: [
                'customerName' => $this->customer->name,
                'orderId' => $this->order->order_unique_id ?? $this->order->id,
                'orderDate' => $this->order->created_at->format('d-m-Y'),
                'totalPrice' => $this->order->total_harga,
                'productDetails' => $this->getProductDetails(),
                'estimatedDelivery' => now()->addDays(3)->format('d-m-Y'), // Estimasi 3 hari
            ],
        );
    }

    /**
     * Get product details based on order type
     * 
     * @return array
     */
    private function getProductDetails()
    {
        if ($this->order->catalog_id) {
            return [
                'type' => 'catalog',
                'name' => $this->order->catalog ? $this->order->catalog->nama_katalog : 'Produk',
                'quantity' => $this->order->jumlah,
                'color' => $this->order->color ? $this->order->color : '-',
                'size' => $this->order->size ? $this->order->size : '-',
            ];
        } elseif ($this->customOrder) {
            return [
                'type' => 'custom',
                'name' => $this->customOrder->jenis_baju ?? 'Custom Order',
                'quantity' => $this->order->jumlah,
                'size' => $this->customOrder->ukuran ?? '-',
                'material' => $this->customOrder->detail_bahan ?? '-',
            ];
        }

        return ['type' => 'unknown', 'name' => 'Pesanan'];
    }
    public function build()
    {
        return $this->view('emails.order.delivery.delivery')
                    ->text('emails.order.delivery.delivery-plain')
                    ->subject("Pesanan Anda Sedang Dikirim - Order #{$this->order->order_unique_id}")
                    ->with([
                        'customerName' => $this->customer->name,
                        'orderId' => $this->order->order_unique_id,
                        'orderDate' => $this->order->created_at->format('d-m-Y'),
                        'totalPrice' => $this->order->total_harga,
                        'productDetails' => $this->getProductDetails(),
                        'estimatedDelivery' => now()->addDays(3)->format('d-m-Y'),
                    ]);
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
