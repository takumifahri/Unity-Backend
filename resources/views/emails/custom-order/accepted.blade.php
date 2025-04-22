// File: resources/views/emails/custom-order/accepted.blade.php
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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4a86e8;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
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
            border-radius: 5px;
        }
        .button {
            display: inline-block;
            background-color: #4a86e8;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Custom Order Disetujui</h1>
    </div>
    
    <div class="content">
        <p>Halo <strong>{{ $nama }}</strong>,</p>
        
        <p>Kami dengan senang hati memberitahukan bahwa custom order Anda telah disetujui.</p>
        
        <div class="order-detail">
            <h3>Detail Pesanan:</h3>
            <ul>
                <li><strong>Jenis Baju:</strong> {{ $jenisBaju }}</li>
                <li><strong>Ukuran:</strong> {{ $ukuran }}</li>
                @if($estimasiWaktu)
                <li><strong>Estimasi Waktu Pengerjaan:</strong> {{ $estimasiWaktu }}</li>
                @endif
            </ul>
            
            @if($catatan)
            <h3>Catatan dari Tim Kami:</h3>
            <p>{{ $catatan }}</p>
            @endif
        </div>
        
        <p>Tim kami akan segera mulai memproses pesanan Anda. Jika Anda memiliki pertanyaan atau membutuhkan informasi lebih lanjut, jangan ragu untuk menghubungi kami.</p>
        
        <a href="{{ config('app.url') }}/tracking/{{ $customOrder->id }}" class="button">Lacak Pesanan Anda</a>
    </div>
    
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>Jika Anda memiliki pertanyaan, silahkan hubungi kami di {{ config('contact.recipient_email', 'jrkonveksiemail@gmail.com') }}</p>
    </div>
</body>
</html>

// File: resources/views/emails/custom-order/accepted-plain.blade.php
Halo {{ $nama }},

Kami dengan senang hati memberitahukan bahwa custom order Anda telah disetujui.

DETAIL PESANAN:
- Jenis Baju: {{ $jenisBaju }}
- Ukuran: {{ $ukuran }}
@if($estimasiWaktu)
- Estimasi Waktu Pengerjaan: {{ $estimasiWaktu }}
@endif

@if($catatan)
CATATAN DARI TIM KAMI:
{{ $catatan }}
@endif

Tim kami akan segera mulai memproses pesanan Anda. Jika Anda memiliki pertanyaan atau membutuhkan informasi lebih lanjut, jangan ragu untuk menghubungi kami.

Untuk melacak pesanan Anda, kunjungi: {{ config('app.url') }}/tracking/{{ $customOrder->id }}

--
{{ config('app.name') }}
{{ config('contact.recipient_email', 'jrkonveksiemail@gmail.com') }}