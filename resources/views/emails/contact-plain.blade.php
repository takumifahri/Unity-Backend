{{$contact->subject}}

Nama: {{ $contact->name }}
Email: {{ $contact->email }}
No HP: {{ $contact->no_hp }}
Pesan: {{ $contact->message }}

@if($contact->attachment)
    Lampiran: {{ basename($contact->attachment) }}
    File terlampir dalam email ini
@endif