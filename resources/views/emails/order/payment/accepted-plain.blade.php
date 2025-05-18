Halo {{ $customerName }},

Pembayaran Anda untuk transaksi #{{ $transactionId }} telah berhasil diverifikasi dan disetujui.

DETAIL TRANSAKSI:
- ID Transaksi: {{ $transactionId }}
- Tanggal Order: {{ $orderDate }}
- Metode Pembayaran: {{ $paymentMethod }}
- Total Pembayaran: Rp {{ number_format($totalAmount, 0, ',', '.') }}

DETAIL PESANAN:
@if(empty($orders))
Detail pesanan tidak tersedia
@else
@foreach($orders as $order)
- {{ $order['product_name'] ?? 'Produk' }} ({{ $order['quantity'] ?? 1 }}): Rp {{ number_format($order['price'] ?? 0, 0, ',', '.') }} x {{ $order['quantity'] ?? 1 }} = Rp {{ number_format($order['subtotal'] ?? 0, 0, ',', '.') }}
@endforeach
@endif

Total: Rp {{ number_format($totalAmount, 0, ',', '.') }}

Pesanan Anda sekarang sedang diproses dan akan segera disiapkan untuk pengiriman.

Terima kasih telah berbelanja di toko kami. Jika ada pertanyaan, silakan hubungi kami.

Salam,
Tim Unity Indonesia

--
Unity Indonesia
© {{ date('Y') }} Unity Indonesia. All rights reserved.
