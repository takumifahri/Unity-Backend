<!-- filepath: resources/views/emails/order/completed.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
        .product-details { background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .thank-you { font-size: 18px; font-weight: bold; color: #4CAF50; margin: 20px 0; }
        .button { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pesanan Anda Telah Selesai</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $customerName }},</p>
            
            <p>Pesanan Anda dengan nomor <strong>{{ $orderId }}</strong> telah selesai pada tanggal {{ $completedDate }}.</p>
            
            <div class="product-details">
                <h3>Detail Pesanan:</h3>
                <p>
                    <strong>Tanggal Pemesanan:</strong> {{ $orderDate }}<br>
                    <strong>Tanggal Selesai:</strong> {{ $completedDate }}<br>
                    <strong>Total Pembayaran:</strong> Rp {{ number_format($totalPrice, 0, ',', '.') }}
                </p>
                
                <h4>Produk:</h4>
                @if($productDetails['type'] == 'catalog')
                    <p>
                        <strong>Nama Produk:</strong> {{ $productDetails['name'] }}<br>
                        <strong>Jumlah:</strong> {{ $productDetails['quantity'] }}<br>
                        <strong>Warna:</strong> {{ $productDetails['color'] }}<br>
                        <strong>Ukuran:</strong> {{ $productDetails['size'] }}
                    </p>
                @elseif($productDetails['type'] == 'custom')
                    <p>
                        <strong>Pesanan Kustom:</strong> {{ $productDetails['name'] }}<br>
                        <strong>Jumlah:</strong> {{ $productDetails['quantity'] }}<br>
                        <strong>Ukuran:</strong> {{ $productDetails['size'] }}<br>
                        <strong>Bahan:</strong> {{ $productDetails['material'] }}
                    </p>
                @endif
            </div>
            
            <div class="thank-you">
                Terima kasih telah berbelanja di Unity Indonesia!
            </div>
            
            <p>Kami sangat menghargai kepercayaan Anda pada produk kami. Jika Anda puas dengan pesanan ini, kami sangat berterima kasih jika Anda dapat memberikan ulasan pada produk yang telah Anda terima.</p>
            
            <p>Untuk pemberian ulasan, silakan kunjungi halaman "Pesanan Saya" di aplikasi atau website kami.</p>
            
            <p>Semoga hari Anda menyenangkan!</p>
            
            <p>Salam,<br>Tim Unity Indonesia</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Unity Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>