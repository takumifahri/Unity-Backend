<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
        .proof-image { max-width: 100%; height: auto; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pesanan Anda Telah Diterima</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $customerName }},</p>
            
            <p>Pesanan Anda dengan nomor <strong>{{ $orderId }}</strong> telah diterima pada tanggal {{ $deliveryDate }}.</p>
            
            <h3>Detail Pesanan:</h3>
            <ul>
                <li>Tanggal Pemesanan: {{ $orderDate }}</li>
                <li>Tanggal Diterima: {{ $deliveryDate }}</li>
                <li>Penerima: {{ $receiverName }}</li>
                <li>Total Pembayaran: Rp {{ number_format($totalPrice, 0, ',', '.') }}</li>
                @if($notes)
                    <li>Catatan: {{ $notes }}</li>
                @endif
            </ul>
            
            <h3>Detail Produk:</h3>
            @if($productDetails['type'] == 'catalog')
                <ul>
                    <li>Produk: {{ $productDetails['name'] }}</li>
                    <li>Jumlah: {{ $productDetails['quantity'] }}</li>
                    <li>Warna: {{ $productDetails['color'] }}</li>
                    <li>Ukuran: {{ $productDetails['size'] }}</li>
                </ul>
            @elseif($productDetails['type'] == 'custom')
                <ul>
                    <li>Pesanan Kustom: {{ $productDetails['name'] }}</li>
                    <li>Jumlah: {{ $productDetails['quantity'] }}</li>
                    <li>Ukuran: {{ $productDetails['size'] }}</li>
                    <li>Bahan: {{ $productDetails['material'] }}</li>
                </ul>
            @endif
            
            <h3>Bukti Pengiriman:</h3>
            <img src="{{ $imagePath }}" alt="Bukti Pengiriman" class="proof-image">
            
            <p>Terima kasih telah berbelanja di toko kami. Jika ada pertanyaan, silakan hubungi kami.</p>
            
            <p>Salam,<br>Tim Unity Indonesia</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} JR Konveksi 2025. All rights reserved.</p>
        </div>
    </div>
</body>
</html>