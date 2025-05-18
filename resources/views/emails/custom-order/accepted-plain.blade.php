Halo {{ $customerName }},

Pembayaran Anda untuk transaksi #{{ $transactionId }} telah berhasil diverifikasi dan disetujui.

DETAIL TRANSAKSI:
- ID Transaksi: {{ $transactionId }}
- Tanggal Order: {{ $orderDate }}
- Metode Pembayaran: {{ $paymentMethod }}
- Total Pembayaran: Rp {{ number_format($totalAmount, 0, ',', '.') }}

DETAIL PESANAN:
@if(empty($orders))
Detail pesanan tidak tersedia
@else
@foreach($orders as $order)
{{ $catatan }}
@endif

{{-- @if($estimasiWaktu)
- Estimasi Waktu Pengerjaan: {{ $estimasiWaktu }}
@endif

@if($catatan)
CATATAN DARI TIM KAMI:
{{ $catatan }}
@endif --}}

Tim kami akan segera mulai memproses pesanan Anda. Jika Anda memiliki pertanyaan atau membutuhkan informasi lebih lanjut, jangan ragu untuk menghubungi kami.

Untuk melacak pesanan Anda, kunjungi: {{ config('app.url') }}/tracking/{{ $customOrder->id }}

--
{{ config('app.name') }}
{{ config('contact.recipient_email', 'jrkonveksiemail@gmail.com') }}