<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Custom Order Disetujui</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #34495e;
            color: white;
            padding: 10px 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .order-detail {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Custom Order #{{ $orderId }} Disetujui</h1>
        </div>
        
        <div class="content">
            <p>Halo Admin,</p>
            
            <p>Custom order berikut telah disetujui dan siap untuk diproses:</p>
            
            <div class="order-detail">
                <h3>Detail Pesanan #{{ $orderId }}:</h3>
                <ul>
                    <li><strong>Nama Pelanggan:</strong> {{ $nama }}</li>
                    <li><strong>Jenis Baju:</strong> {{ $jenisBaju }}</li>
                    <li><strong>Ukuran:</strong> {{ $ukuran }}</li>
                    @if($estimasiWaktu)
                    <li><strong>Estimasi Waktu Pengerjaan:</strong> {{ $estimasiWaktu }}</li>
                    @endif
                </ul>
                
                @if($catatan)
                <h3>Catatan:</h3>
                <p>{{ $catatan }}</p>
                @endif
            </div>
            
            <p>Mohon untuk segera menindaklanjuti pesanan ini.</p>
            
            <p>
                <a href="{{ config('app.url') }}/admin/custom-orders/{{ $customOrder->id }}">
                    Lihat Detail Pesanan
                </a>
            </p>
        </div>
        
        <div class="footer">
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>