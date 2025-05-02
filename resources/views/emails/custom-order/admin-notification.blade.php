<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Custom Order Disetujui</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 20px;
        }
        .content p {
            margin: 10px 0;
        }
        .order-detail {
            background-color: #ecf0f1;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .order-detail h3 {
            margin-top: 0;
            color: #2980b9;
        }
        .order-detail ul {
            list-style: none;
            padding: 0;
        }
        .order-detail ul li {
            margin: 5px 0;
        }
        .order-detail ul li strong {
            color: #2c3e50;
        }
        .cta-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .cta-button:hover {
            background-color: #2ecc71;
        }
        .footer {
            background-color: #bdc3c7;
            color: #2c3e50;
            text-align: center;
            padding: 10px;
            font-size: 12px;
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
                <h3>DETAIL PESANAN Ke-{{ $orderId }}:</h3>
                <ul>
                    <li><strong>Nama Pelanggan:</strong> {{ $nama }}</li>
                    <li><strong>Email:</strong> {{ $email }}</li>
                    <li><strong>No. Telepon:</strong> {{ $noTelp }}</li>
                    <li><strong>Jenis Baju:</strong> {{ $jenisBaju }}</li>
                    <li><strong>Ukuran:</strong> {{ $ukuran }}</li>
                    <li><strong>Jumlah:</strong> {{ $jumlah }}</li>
                    <li><strong>Sumber Kain:</strong> {{ $sumberKain }}</li>
                    @if($estimasiWaktu)
                    <li><strong>Estimasi Waktu Pengerjaan:</strong> {{ $estimasiWaktu }}</li>
                    @endif
                </ul>
                
                @if($catatan)
                <h3>CATATAN:</h3>
                <p>{{ $catatan }}</p>
                @endif
            </div>
            
            <p>Mohon untuk segera menindaklanjuti pesanan ini.</p>
            
            <p>
                <a href="{{ config('app.url') }}/admin/custom-orders/{{ $customOrder->id }}" class="cta-button">
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