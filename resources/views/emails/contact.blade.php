<!DOCTYPE html>
<html>
<head>
    <title>Pesan Kontak Baru</title>
</head>
<body>
    <h2>Pesan Baru dari Form Kontak</h2>
    <p><strong>Nama:</strong> {{ $contact->name }}</p>
    <p><strong>Email:</strong> {{ $contact->email }}</p>
    <p><strong>No HP:</strong> {{ $contact->no_hp }}</p>
    <p><strong>Pesan:</strong></p>
    <p>{{ $contact->message }}</p>
</body>
</html>