PESANAN ANDA TELAH DITERIMA
=============================

Halo {{ $customerName }},

Pesanan Anda dengan nomor {{ $orderId }} telah diterima pada tanggal {{ $deliveryDate }}.

DETAIL PESANAN:
--------------
* Tanggal Pemesanan: {{ $orderDate }}
* Tanggal Diterima: {{ $deliveryDate }}
* Penerima: {{ $receiverName }}
* Total Pembayaran: Rp {{ number_format($totalPrice, 0, ',', '.') }}
@if($notes)
* Catatan: {{ $notes }}
@endif

DETAIL PRODUK:
-------------
@if($productDetails['type'] == 'catalog')
* Produk: {{ $productDetails['name'] }}
* Jumlah: {{ $productDetails['quantity'] }}
* Warna: {{ $productDetails['color'] }}
* Ukuran: {{ $productDetails['size'] }}
@elseif($productDetails['type'] == 'custom')
* Pesanan Kustom: {{ $productDetails['name'] }}
* Jumlah: {{ $productDetails['quantity'] }}
* Ukuran: {{ $productDetails['size'] }}
@endif