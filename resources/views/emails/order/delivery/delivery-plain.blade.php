Pesanan Anda Sedang Dikirim - Order #{{ $orderId }}

Halo {{ $customerName }},

Pesanan Anda dengan nomor {{ $orderId }} sedang dalam proses pengiriman.

Detail Pesanan:
- Tanggal Pemesanan: {{ $orderDate }}
- Estimasi Tiba: {{ $estimatedDelivery }}
- Total Pembayaran: Rp {{ number_format($totalPrice, 0, ',', '.') }}

Detail Produk:
@if($productDetails['type'] == 'catalog')
- Produk: {{ $productDetails['name'] }}
- Jumlah: {{ $productDetails['quantity'] }}
- Warna: {{ $productDetails['color'] }}
- Ukuran: {{ $productDetails['size'] }}
@elseif($productDetails['type'] == 'custom')
- Pesanan Kustom: {{ $productDetails['name'] }}
- Jumlah: {{ $productDetails['quantity'] }}
- Ukuran: {{ $productDetails['size'] }}
- Bahan: {{ $productDetails['material'] }}
@endif

Terima kasih telah berbelanja di toko kami. Jika ada pertanyaan, silakan hubungi kami.

Salam,
Tim Unity Indonesia

© {{ date('Y') }} Unity Indonesia. All rights reserved.