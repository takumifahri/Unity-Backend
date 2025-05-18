<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
        .button { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pesanan Anda Sedang Dikirim</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $customerName }},</p>
            
            <p>Pesanan Anda dengan nomor <strong>{{ $orderId }}</strong> sedang dalam proses pengiriman.</p>
            
            <h3>Detail Pesanan:</h3>
            <ul>
                <li>Tanggal Pemesanan: {{ $orderDate }}</li>
                <li>Estimasi Tiba: {{ $estimatedDelivery }}</li>
                <li>Total Pembayaran: Rp {{ number_format($totalPrice, 0, ',', '.') }}</li>
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
            
            <p>Terima kasih telah berbelanja di toko kami. Jika ada pertanyaan, silakan hubungi kami.</p>
            
            <p>Salam,<br>Tim Unity Indonesia</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Unity Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>