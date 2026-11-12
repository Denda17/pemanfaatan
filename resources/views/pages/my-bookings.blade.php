@extends('layouts.app')

@section('title', 'Pengajuan Sewa Saya - Marketplace Pemanfaatan BMD Jabar')

@section('content')
<!-- Page Header (Clean Behance Studio Style) -->
<div class="relative bg-mesh-light text-gray-900 py-12 lg:py-14 border-b border-gray-200/80 overflow-hidden">
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="max-w-2xl">
                <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2.5 font-semibold">
                    <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-emerald-700 font-extrabold">Pengajuan Sewa Saya</span>
                </nav>

                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-3 border border-emerald-200/80 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Portal Mandiri Calon Mitra Pemprov Jabar
                </div>

                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-gray-900">
                    Daftar Pengajuan Sewa Saya
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 mt-2 font-normal leading-relaxed">
                    Pantau status permohonan, jadwal survei lapangan, dan tahapan penerbitan Perjanjian Kerjasama (PKS) aset yang Anda ajukan.
                </p>
            </div>

            <!-- Profile Summary Card -->
            <div class="bg-white p-4 rounded-3xl border border-gray-200/90 shadow-xs flex items-center gap-4 shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-base shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-sm text-gray-900">{{ $user->name }}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 uppercase">
                            {{ $user->tipe_pemohon === 'badan_usaha' ? 'Badan Usaha' : 'Perorangan' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 font-medium">{{ $user->instansi ?: $user->email }} &bull; {{ $user->phone }}</p>
                    <span class="text-[11px] text-emerald-700 font-extrabold mt-0.5 block">
                        Total: {{ $bookings->count() }} Permohonan Diajukan
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Application Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @if($bookings->isEmpty())
        <!-- Empty State -->
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200/90 shadow-sm max-w-xl mx-auto my-8">
            <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <h3 class="text-base font-extrabold text-gray-900 mb-1">Belum Ada Pengajuan Sewa</h3>
            <p class="text-xs text-gray-500 mb-6 max-w-md mx-auto leading-relaxed">
                Anda belum pernah mengajukan permohonan minat sewa aset. Jelajahi katalog aset BMD Jawa Barat yang siap disewa dan pilih lokasi strategis untuk usaha Anda.
            </p>
            <div class="flex items-center justify-center gap-3">
                <a href="{{ route('catalog.siap-sewa') }}" class="px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5 active:scale-95">
                    <span>Lihat Aset Siap Sewa</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="{{ route('catalog.index') }}" class="px-4 py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs transition">
                    Katalog Lengkap
                </a>
            </div>
        </div>
    @else
        <!-- Bookings Grid -->
        <div class="space-y-4">
            @foreach($bookings as $item)
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
                    $badgeStyle = $badgeColors[$item->status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                @endphp

                <div class="bg-white rounded-3xl p-5 sm:p-6 border border-gray-200/90 shadow-md shadow-gray-200/40 hover:shadow-lg hover:border-emerald-200 transition flex flex-col md:flex-row md:items-center justify-between gap-6 group">
                    
                    <!-- Left: Thumbnail & Asset Info -->
                    <div class="flex items-start gap-4">
                        @if($item->asset)
                            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden bg-gray-100 shrink-0 border border-gray-200/80">
                                <img src="{{ $item->asset->thumbnail_url }}" alt="{{ $item->asset->nama_objek }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                        @endif

                        <div>
                            <!-- Booking Code & Date -->
                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $item->kode_booking }}
                                </span>
                                <span class="text-[11px] text-gray-400 font-medium">
                                    Diajukan: {{ $item->tanggal_booking ? $item->tanggal_booking->format('d M Y, H:i') : $item->created_at->format('d M Y') }} WIB
                                </span>
                            </div>

                            <!-- Asset Title -->
                            <h3 class="text-base font-extrabold text-gray-900 group-hover:text-emerald-700 transition leading-snug">
                                {{ $item->asset?->nama_objek ?? 'Aset Tidak Ditemukan' }}
                            </h3>

                            <p class="text-xs text-gray-500 font-medium flex items-center gap-1 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                <span>{{ $item->asset?->kabupaten_kota ?? '-' }}</span>
                                <span class="text-gray-300">&bull;</span>
                                <span>Durasi: <strong>{{ $item->durasi_sewa_tahun }} Tahun</strong></span>
                            </p>

                            <!-- Peruntukan preview -->
                            <p class="text-xs text-gray-600 mt-2 line-clamp-1 italic font-normal">
                                Peruntukan: "{{ $item->rencana_peruntukan }}"
                            </p>
                        </div>
                    </div>

                    <!-- Right: Status Badge & Action Button -->
                    <div class="flex flex-row md:flex-col items-center md:items-end justify-between md:justify-center gap-3 shrink-0 pt-4 md:pt-0 border-t md:border-t-0 border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="px-3.5 py-1.5 rounded-full text-xs font-extrabold border {{ $badgeStyle }} shadow-2xs flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-current animate-pulse"></span>
                                <span>{{ $item->status_label }}</span>
                            </span>
                        </div>

                        <a href="{{ route('booking.status', $item->kode_booking) }}" class="px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-sm shadow-emerald-600/20 transition flex items-center gap-1.5">
                            <span>Lihat Progres & Berkas</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <!-- Helpdesk Banner -->
    <div class="mt-12 bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/90 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center font-bold shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <div>
                <h4 class="text-sm font-extrabold text-gray-900">Perlu Koordinasi atau Revisi Data Permohonan?</h4>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Petugas UPTD Pengamanan dan Pemanfaatan Aset siap melayani konsultasi jadwal survei lapangan dan telaah berkas.</p>
            </div>
        </div>
        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20UPTD,%20saya%20ingin%20koordinasi%20mengenai%20permohonan%20sewa%20aset%20saya" target="_blank" class="px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition flex items-center gap-1.5 shrink-0 active:scale-95">
            <span>Hubungi Helpdesk WA</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </a>
    </div>

</div>
@endsection
