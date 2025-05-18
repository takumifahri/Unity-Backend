<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; }
        .header { background: #e74c3c; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; }
        .table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .table th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
        .button { background: #e74c3c; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; }
        .alert { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Pembayaran Anda Ditolak</h1>
        </div>
        
        <div class="content">
            <p>Halo {{ $customerName }},</p>
            
            <p>Pembayaran Anda untuk transaksi <strong>#{{ $transactionId }}</strong> tidak dapat diverifikasi dan telah ditolak.</p>
            
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
                    @foreach($orders as $order)
                    <tr>
                        <td>{{ $order['product_name'] }}</td>
                        <td>{{ $order['quantity'] }}</td>
                        <td>Rp {{ number_format($order['price'], 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($order['subtotal'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="total">Total</td>
                        <td>Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="alert">
                <p><strong>Alasan umum penolakan pembayaran meliputi:</strong></p>
                <ul>
                    <li>Bukti pembayaran tidak jelas</li>
                    <li>Jumlah pembayaran tidak sesuai</li>
                    <li>Informasi rekening tidak sesuai</li>
                    <li>Pembayaran belum diterima</li>
                </ul>
            </div>
            
            <p>Silakan lakukan pembayaran ulang dengan mengunjungi halaman detail pesanan di akun Anda.</p>
            
            <p>Untuk bantuan lebih lanjut, silakan hubungi customer service kami.</p>
            
            <p>Salam,<br>Tim Unity Indonesia</p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Unity Indonesia. All rights reserved.</p>
        </div>
    </div>
</body>
</html>