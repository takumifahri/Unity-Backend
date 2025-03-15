<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Catalog;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;

class MidtransService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $isSanitized;
    protected $is3ds;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->isProduction = config('midtrans.is_production');
        $this->isSanitized = config('midtrans.is_sanitized');
        $this->is3ds = config('midtrans.is_3ds');

        $this->_configureMidtrans();
    }

    protected function _configureMidtrans()
    {
        Config::$serverKey = $this->serverKey;
        Config::$clientKey = $this->clientKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = $this->isSanitized;
        Config::$is3ds = $this->is3ds;
    }

    public function createTransaction($orderData, $customerData, $itemsData)
    {
        $bookingCode = 'TRX-' . strtoupper(string: Str::random(10));
        
        $transactionDetails = [
            'order_id' => $bookingCode,
            'gross_amount' => $orderData['total_harga'],
        ];

        $customerDetails = [
            'first_name' => $customerData['name'],
            'email' => $customerData['email'],
            'phone' => $customerData['phone'] ?? '',
            'billing_address' => [
                'address' => $orderData['alamat'],
            ],
            'shipping_address' => [
                'address' => $orderData['alamat'],
            ]
        ];

        $paymentParams = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $itemsData,
            'expiry' => [
                'start_time' => date('Y-m-d H:i:s O', time()),
                'unit' => 'hour',
                'duration' => 24,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($paymentParams);
            $paymentUrl = Snap::getSnapUrl($paymentParams);

            // Menyimpan data transaksi
            $transaction = \App\Models\transaction::create([
                'order_id' => $orderData['id'],
                'status' => 'pending',
                'amount' => $orderData['total_harga'],
                'payment_url' => $paymentUrl,
                'snap_token' => $snapToken,
                'midtrans_booking_code' => $bookingCode,
                'payment_details' => $paymentParams,
                'expired_at' => now()->addHours(24),
            ]);

            return [
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->id,
                    'snap_token' => $snapToken,
                    'payment_url' => $paymentUrl,
                    'midtrans_booking_code' => $bookingCode,
                    'expired_at' => $transaction->expired_at,
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}