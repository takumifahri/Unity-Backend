<!-- filepath: resources/views/emails/order/completed-plain.blade.php -->
Pesanan Anda Telah Selesai - Order #{{ $orderId }}

Halo {{ $customerName }},

Pesanan Anda dengan nomor {{ $orderId }} telah selesai pada tanggal {{ $completedDate }}.

Detail Pesanan:
- Tanggal Pemesanan: {{ $orderDate }}
- Tanggal Selesai: {{ $completedDate }}
- Total Pembayaran: Rp {{ number_format($totalPrice, 0, ',', '.') }}

Produk:
@if($productDetails['type'] == 'catalog')
- Nama Produk: {{ $productDetails['name'] }}
- Jumlah: {{ $productDetails['quantity'] }}
- Warna: {{ $productDetails['color'] }}
- Ukuran: {{ $productDetails['size'] }}
@elseif($productDetails['type'] == 'custom')
- Pesanan Kustom: {{ $productDetails['name'] }}
- Jumlah: {{ $productDetails['quantity'] }}
- Ukuran: {{ $productDetails['size'] }}
- Bahan: {{ $productDetails['material'] }}
@endif

Terima kasih telah berbelanja di Unity Indonesia!

Kami sangat menghargai kepercayaan Anda pada produk kami. Jika Anda puas dengan pesanan ini, kami sangat berterima kasih jika Anda dapat memberikan ulasan pada produk yang telah Anda terima.

Untuk pemberian ulasan, silakan kunjungi halaman "Pesanan Saya" di aplikasi atau website kami.

Semoga hari Anda menyenangkan!

Salam,
Tim Unity Indonesia

© {{ date('Y') }} Unity Indonesia. All rights reserved.