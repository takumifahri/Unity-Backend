<!-- filepath: c:\laragon\www\SIG\Frontend\Unity-Backend\resources\views\emails\order\payment\accepted.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
        .button { background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pembayaran Anda Telah Diverifikasi</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $customerName }},</p>
            
            <p>Pembayaran Anda untuk transaksi <strong>#{{ $transactionId }}</strong> telah berhasil diverifikasi dan disetujui.</p>
            
            <h3>Detail Transaksi:</h3>
            <p>
                <strong>ID Transaksi:</strong> {{ $transactionId }}<br>
                <strong>Tanggal Order:</strong> {{ $orderDate }}<br>
                <strong>Metode Pembayaran:</strong> {{ $paymentMethod }}<br>
                <strong>Total Pembayaran:</strong> Rp {{ number_format($totalAmount, 0, ',', '.') }}
            </p>
            
            <h3>Detail Pesanan:</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($orders))
                        <tr>
                            <td colspan="4" style="text-align: center;">Detail pesanan tidak tersedia</td>
                        </tr>
                    @else
                        @foreach($orders as $order)
                        <tr>
                            <td>{{ $order['product_name'] ?? 'Produk' }}</td>
                            <td>{{ $order['quantity'] ?? 1 }}</td>
                            <td>Rp {{ number_format($order['price'] ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($order['subtotal'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="total">Total</td>
                        <td>Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
            
            <p>Pesanan Anda sekarang sedang diproses dan akan segera disiapkan untuk pengiriman.</p>
            
            <p>Terima kasih telah berbelanja di toko kami. Jika ada pertanyaan, silakan hubungi kami.</p>
            
            <p>Salam,<br>Tim Unity Indonesia</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Unity Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>