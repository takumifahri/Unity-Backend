<?php

namespace App\Mail;

use App\Models\transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VerifPaymentMails extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;
    public $orders;
    public $customer;
    public $status;

    /**
     * Create a new message instance.
     * 
     * @param Transaction $transaction
     * @param mixed $orders
     * @param User|null $customer
     * @param string $status
     * @return void
     */
    public function __construct(Transaction $transaction, $orders = null, User $customer = null, $status)
    {
        $this->transaction = $transaction;
        $this->orders = $orders ?? collect([$transaction->order]);
        $this->customer = $customer ?? $transaction->order->user;
        $this->status = $status;
        
        // Debug information
        Log::info('VerifPaymentMails constructed with: ', [
            'transaction_id' => $transaction->id,
            'orders_count' => is_countable($orders) ? count($orders) : 'not countable',
            'customer_id' => $customer ? $customer->id : 'null',
            'status' => $status
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Use transaction ID as fallback if unique ID is empty
        $transactionIdentifier = !empty($this->transaction->transaction_unique_id) 
            ? $this->transaction->transaction_unique_id 
            : $this->transaction->id;
            
        $subject = $this->status == 'approve' 
            ? "Pembayaran Anda Telah Diverifikasi - Transaksi #{$transactionIdentifier}"
            : "Pembayaran Anda Ditolak - Transaksi #{$transactionIdentifier}";
            
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = $this->status == 'approve' ? 'emails.order.payment.accepted' : 'emails.order.payment.rejected';
        
        // Use transaction ID as fallback if unique ID is empty
        $transactionIdentifier = !empty($this->transaction->transaction_unique_id) 
            ? $this->transaction->transaction_unique_id 
            : $this->transaction->id;
        
        // Get order details and log for debugging    
        $orderDetails = $this->getOrderDetails();
        Log::info('Order details for email: ', ['details' => $orderDetails]);
            
        return new Content(
            view: $view,
            with: [
                'customerName' => $this->customer->name,
                'transactionId' => $transactionIdentifier,
                'orderDate' => $this->transaction->created_at->format('d-m-Y'),
                'paymentMethod' => $this->transaction->payment_method,
                'totalAmount' => $this->transaction->amount,
                'orders' => $orderDetails,
                'status' => $this->status,
            ],
        );
    }

    /**
     * Build the message (with plain text support).
     */
    public function build()
    {
        // Use transaction ID as fallback if unique ID is empty
        $transactionIdentifier = !empty($this->transaction->transaction_unique_id) 
            ? $this->transaction->transaction_unique_id 
            : $this->transaction->id;
            
        $subject = $this->status == 'approve' 
            ? "Pembayaran Anda Telah Diverifikasi - Transaksi #{$transactionIdentifier}"
            : "Pembayaran Anda Ditolak - Transaksi #{$transactionIdentifier}";
            
        $view = $this->status == 'approve' ? 'emails.order.payment.accepted' : 'emails.order.payment.rejected';
        $plainView = $this->status == 'approve' ? 'emails.order.payment.accepted-plain' : 'emails.order.payment.rejected-plain';
        
        // Get order details  
        $orderDetails = $this->getOrderDetails();
        
        return $this->view($view)
                    ->text($plainView)
                    ->subject($subject)
                    ->with([
                        'customerName' => $this->customer->name,
                        'transactionId' => $transactionIdentifier,
                        'orderDate' => $this->transaction->created_at->format('d-m-Y'),
                        'paymentMethod' => $this->transaction->payment_method,
                        'totalAmount' => $this->transaction->amount,
                        'orders' => $orderDetails,
                        'status' => $this->status,
                    ]);
    }

    /**
     * Get formatted order details
     * 
     * @return array
     */
    private function getOrderDetails()
    {
        // Check if orders is a collection
        if (!($this->orders instanceof Collection)) {
            $this->orders = collect([$this->orders]);
        }
        
        // Add more debugging for order data
        Log::info('Orders in getOrderDetails:', [
            'count' => $this->orders->count(),
            'first_order_id' => $this->orders->first() ? $this->orders->first()->id : 'null'
        ]);
        
        // Handle potential empty collection
        if ($this->orders->isEmpty()) {
            Log::warning('Order collection is empty, returning fallback data');
            return [[
                'id' => 0,
                'product_name' => 'Lihat detail di akun Anda',
                'quantity' => 1,
                'price' => 0,
                'subtotal' => $this->transaction->amount ?: 0,
                'status' => 'Diproses'
            ]];
        }
        
        $result = $this->orders->map(function($order) {
            $productName = 'Produk';
            $price = 0;
            
            try {
                // Handle case when catalog is null but custom_order exists
                if ($order->catalog) {
                    $productName = $order->catalog->nama_katalog;
                    $price = $order->catalog->price ?: 0;
                } elseif ($order->customOrder || $order->custom_order) {
                    $customOrder = $order->customOrder ?? $order->custom_order;
                    $productName = "Custom: " . ($customOrder->jenis_baju ?? 'Custom Order');
                    $price = $customOrder->total_harga ?: 0;
                }
                
                return [
                    'id' => $order->id,
                    'product_name' => $productName,
                    'quantity' => $order->jumlah ?: 1,
                    'price' => $price,
                    'subtotal' => $order->total_harga ?: $price,
                    'status' => $order->status ?: 'Diproses'
                ];
            } catch (\Exception $e) {
                Log::error('Error processing order in getOrderDetails: ' . $e->getMessage(), [
                    'order_id' => $order->id ?? 'unknown'
                ]);
                
                // Return fallback data in case of error
                return [
                    'id' => $order->id ?? 0,
                    'product_name' => 'Produk (Error dalam pemrosesan data)',
                    'quantity' => 1,
                    'price' => 0,
                    'subtotal' => $order->total_harga ?? 0,
                    'status' => $order->status ?? 'Diproses'
                ];
            }
        })->toArray();
        
        // Final check to make sure we have at least one item
        if (empty($result)) {
            Log::warning('Result array is empty after mapping, returning fallback data');
            return [[
                'id' => 0,
                'product_name' => 'Lihat detail di akun Anda',
                'quantity' => 1,
                'price' => 0,
                'subtotal' => $this->transaction->amount ?: 0,
                'status' => 'Diproses'
            ]];
        }
        
        return $result;
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
