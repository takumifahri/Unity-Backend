Pembayaran Anda Ditolak - Transaksi #{{ $transactionId }}

Halo {{ $customerName }},

Pembayaran Anda untuk transaksi #{{ $transactionId }} tidak dapat diverifikasi dan telah ditolak.

Detail Transaksi:
- ID Transaksi: {{ $transactionId }}
- Tanggal Order: {{ $orderDate }}
- Metode Pembayaran: {{ $paymentMethod }}
- Total Pembayaran: Rp {{ number_format($totalAmount, 0, ',', '.') }}

Detail Pesanan:
@foreach($orders as $order)
- {{ $order['product_name'] }} ({{ $order['quantity'] }}x) - Rp {{ number_format($order['subtotal'], 0, ',', '.') }}
@endforeach

Total: Rp {{ number_format($totalAmount, 0, ',', '.') }}

Alasan umum penolakan pembayaran meliputi:
- Bukti pembayaran tidak jelas
- Jumlah pembayaran tidak sesuai
- Informasi rekening tidak sesuai
- Pembayaran belum diterima

Silakan lakukan pembayaran ulang dengan mengunjungi halaman detail pesanan di akun Anda.

Untuk bantuan lebih lanjut, silakan hubungi customer service kami.

Salam,
Tim Unity Indonesia

© {{ date('Y') }} Unity Indonesia. All rights reserved.