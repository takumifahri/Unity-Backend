
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