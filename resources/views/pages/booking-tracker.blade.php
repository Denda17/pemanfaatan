@extends('layouts.app')

@section('title', 'Lacak Status Permohonan Booking BMD Jawa Barat')

@section('content')
<!-- Header Banner (Behance Light Aesthetic) -->
<div class="relative bg-mesh-light text-gray-900 py-14 lg:py-16 border-b border-gray-200/80 overflow-hidden">
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-4 border border-emerald-200/80">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span>Transparansi Pelayanan Publik UPTD</span>
        </div>
        
        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-gray-900">
            Lacak Status Pengajuan Sewa BMD
        </h1>
        <p class="mt-3 text-sm sm:text-base text-gray-600 max-w-lg mx-auto leading-relaxed font-normal">
            Pantau perkembangan permohonan pemanfaatan aset Anda dari awal booking, verifikasi 8 berkas persyaratan, hingga penandatanganan kontrak sewa (PKS).
        </p>

        <!-- Search Form Card (Floating Clean Studio Box) -->
        <div class="mt-8 bg-white p-3 sm:p-4 rounded-3xl shadow-xl shadow-gray-200/70 max-w-2xl mx-auto text-gray-800 border border-gray-200/90">
            <form action="{{ route('booking.tracker') }}" method="GET" class="flex flex-col sm:flex-row gap-2.5">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="q" value="{{ $search }}" required placeholder="Ketik Kode Booking (cth: BK-202611-0001) atau No. WhatsApp..." class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>
                <button type="submit" class="py-3 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition active:scale-95 shrink-0 flex items-center justify-center gap-2">
                    <span>Lacak Status</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if(!empty($search))
        <!-- Hasil Pencarian -->
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-gray-900">
                Hasil Pencarian: <span class="text-emerald-700 font-mono">"{{ $search }}"</span>
            </h2>
            <span class="text-xs font-extrabold text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">{{ $bookings->count() }} pengajuan ditemukan</span>
        </div>

        @if($bookings->isEmpty())
            <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-base font-extrabold text-gray-900 mb-1">Pengajuan Tidak Ditemukan</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto mb-5 leading-relaxed font-normal">
                    Pastikan Kode Booking atau Nomor WhatsApp yang Anda masukkan sesuai dengan data saat mengajukan minat sewa di sistem.
                </p>
                <a href="{{ route('booking.tracker') }}" class="inline-flex px-5 py-2.5 rounded-full text-xs font-extrabold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition border border-emerald-200">
                    Coba Cari Ulang
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($bookings as $item)
                    <div class="behance-card rounded-3xl p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <span class="px-3.5 py-1.5 rounded-xl text-xs font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    {{ $item->kode_booking }}
                                </span>
                                <span class="text-xs text-gray-500 font-medium">
                                    Diajukan: {{ $item->tanggal_booking ? $item->tanggal_booking->format('d M Y, H:i') : $item->created_at->format('d M Y') }} WIB
                                </span>
                            </div>
                            
                            <!-- Badge Status -->
                            <div>
                                @php
                                    $badgeColors = [
                                        'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                                        'contacted' => 'bg-blue-50 text-blue-800 border-blue-200',
                                        'document_review' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                        'field_survey' => 'bg-purple-50 text-purple-800 border-purple-200',
                                        'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                        'contract_signed' => 'bg-teal-50 text-teal-800 border-teal-200',
                                        'rejected' => 'bg-rose-50 text-rose-800 border-rose-200',
                                    ];
                                    $badge = $badgeColors[$item->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                                @endphp
                                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold border {{ $badge }}">
                                    {{ $item->status_label }}
                                </span>
                            </div>
                        </div>

                        <div class="py-4 grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                            <div class="md:col-span-8">
                                <h3 class="text-base font-extrabold text-gray-900 mb-1">
                                    {{ $item->asset ? $item->asset->nama_objek : 'Objek BMD Jabar' }}
                                </h3>
                                <p class="text-xs text-gray-500 font-medium">
                                    Pemohon: <strong class="text-gray-800 font-bold">{{ $item->nama_pemohon }}</strong>
                                    @if($item->nama_instansi)
                                        ({{ $item->nama_instansi }})
                                    @endif
                                    &bull; Durasi Rencana: {{ $item->durasi_sewa_tahun }} Tahun
                                </p>
                            </div>

                            <div class="md:col-span-4 flex justify-start md:justify-end">
                                <a href="{{ route('booking.status', $item->kode_booking) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm active:scale-95">
                                    <span>Lihat Linimasa Progres</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    @else
        <!-- Information Info Box when no query is typed -->
        <div class="bg-white rounded-3xl p-8 border border-gray-200/90 shadow-sm text-center max-w-xl mx-auto">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-extrabold text-gray-900 mb-2">Panduan Pelacakan Berkas</h3>
            <p class="text-xs text-gray-500 leading-relaxed mb-6 font-normal">
                Masukkan <strong>Kode Booking</strong> yang tertera saat Anda submit formulir minat (contoh: <code class="bg-gray-100 px-2 py-0.5 rounded text-emerald-800 font-bold">BK-202611-0001</code>) atau masukkan <strong>Nomor WhatsApp</strong> aktif yang Anda daftarkan.
            </p>
            <div class="pt-4 border-t border-gray-100 flex items-center justify-center gap-4 text-xs font-bold text-gray-600">
                <a href="{{ route('catalog.index') }}" class="text-emerald-700 hover:underline">
                    &larr; Kembali ke Katalog
                </a>
                <span>&bull;</span>
                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="text-emerald-700 hover:underline">
                    Hubungi Admin UPTD
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
