
Halo Admin,

Custom order berikut telah disetujui dan siap untuk diproses:

DETAIL PESANAN #{{ $orderId }}:
- Nama Pelanggan: {{ $nama }}
- Jenis Baju: {{ $jenisBaju }}
- Ukuran: {{ $ukuran }}
@if($estimasiWaktu)
- Estimasi Waktu Pengerjaan: {{ $estimasiWaktu }}
@endif

@if($catatan)
CATATAN:
{{ $catatan }}
@endif

Mohon untuk segera menindaklanjuti pesanan ini.

Lihat Detail Pesanan: {{ config('app.url') }}/admin/custom-orders/{{ $customOrder->id }}

--
Email ini dikirim secara otomatis. Mohon tidak membalas email ini.