<!DOCTYPE html>
<html>
<head>
    <title>📩 Permintaan Informasi tentang {{ $contact->subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>📩 Permintaan Informasi tentang {{ $contact->subject }}</h2>
        
        <p>Halo <strong>Tim Konveksi</strong> 👋,</p>
        
        <p>Saya tertarik untuk mengetahui lebih lanjut mengenai layanan <strong>{{ $contact->subject }}</strong> yang ditawarkan. Berikut adalah detail kontak saya:</p>

        <p><strong>📧 Email:</strong> {{ $contact->email }}</p>
        <p><strong>📱 No HP:</strong> {{ $contact->no_hp }}</p>
        <p><strong>📝 Pesan:</strong></p>
        <p>{{ $contact->message }}</p>

        @if($contact->attachment)
        <p><strong>📎 Lampiran:</strong> {{ basename($contact->attachment) }}</p>
        <p style="font-size: 12px; color: #666;">(File terlampir dalam email ini)</p>
        @endif

        <p>Saya berharap dapat memperoleh informasi lebih lanjut terkait kebutuhan konveksi saya, baik itu sablon, pembuatan seragam, kaos, atau produk lainnya. Terima kasih atas perhatiannya! 🙌</p>

        <p><strong>Salam hangat,</strong><br>
        {{ $contact->name }}</p>
    </div>
</body>
</html>
