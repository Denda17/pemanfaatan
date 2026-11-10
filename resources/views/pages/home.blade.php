@extends('layouts.app')

@section('title', 'Marketplace Pemanfaatan BMD Jawa Barat - Kolaborasi Investasi Aset Daerah')

@section('content')
<!-- Hero Section (Behance Style: Ultra-Light, Clean, Vibrant & Eye-Catching) -->
<section class="relative bg-mesh-light text-gray-900 overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-28 border-b border-gray-200/70">
    <!-- Subtle Ambient Radial Glows -->
    <div class="absolute top-0 right-10 w-[500px] h-[500px] bg-emerald-400/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-[450px] h-[450px] bg-blue-400/10 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/3 w-[300px] h-[300px] bg-amber-300/10 rounded-full blur-[90px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        <!-- Header Text -->
        <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-6 border border-emerald-200/80 shadow-2xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Pemanfaatan Barang Milik Daerah &bull; Provinsi Jawa Barat</span>
            </div>
            
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.12] text-gray-900">
                Temukan Ruang & Lahan Strategis, <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-emerald-600 via-teal-600 to-blue-600 bg-clip-text text-transparent">Siap Mengembangkan Bisnis Anda</span>
            </h1>
            
            <p class="mt-5 text-base sm:text-lg text-gray-600 leading-relaxed font-normal max-w-2xl mx-auto">
                Portal resmi UPTD Pengamanan dan Pemanfaatan Aset untuk penyewaan tanah, gedung, dan ruang usaha milik Pemprov Jawa Barat dengan kepastian hukum dan pendampingan WhatsApp.
            </p>
        </div>

        <!-- Integrated Floating Smart Search Box (Behance Style Pill / Card) -->
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-3xl shadow-[0_20px_50px_-10px_rgba(0,0,0,0.08)] p-4 sm:p-7 text-gray-800 border border-gray-200/80 relative">
                
                <!-- Quick Category Tabs (Horizontal Scroll Pill Tabs) -->
                <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-4 border-b border-gray-100 text-xs font-bold text-gray-600">
                    <span class="text-gray-400 text-[11px] uppercase tracking-wider shrink-0 mr-1">Kategori:</span>
                    <a href="{{ route('catalog.index') }}" class="px-4 py-2 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200/80 hover:bg-emerald-100 transition whitespace-nowrap">
                        Semua Kategori ({{ $totalAssets }})
                    </a>
                    <a href="{{ route('catalog.index', ['bentuk_objek' => 'Tanah dan bangunan']) }}" class="px-4 py-2 rounded-full bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200 whitespace-nowrap">
                        Tanah & Bangunan
                    </a>
                    <a href="{{ route('catalog.index', ['bentuk_objek' => 'Tanah']) }}" class="px-4 py-2 rounded-full bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200 whitespace-nowrap">
                        Lahan Kosong
                    </a>
                    <a href="{{ route('catalog.index', ['bentuk_objek' => 'Bangunan']) }}" class="px-4 py-2 rounded-full bg-gray-50 text-gray-700 hover:bg-gray-100 transition border border-gray-200 whitespace-nowrap">
                        Gedung & Ruang Usaha
                    </a>
                    <a href="{{ route('catalog.index', ['status' => 'tersedia']) }}" class="px-4 py-2 rounded-full bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-xs whitespace-nowrap ml-auto font-extrabold">
                        Aset Tersedia ({{ $totalTersedia }})
                    </a>
                </div>

                <form action="{{ route('catalog.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-center">
                    
                    <!-- Keyword Search -->
                    <div class="md:col-span-5">
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Nama Objek / Lokasi Jalan</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" name="q" placeholder="Cth: Caringin, Turangga, Saparua..." class="w-full pl-10 pr-4 py-3 bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                        </div>
                    </div>

                    <!-- Kabupaten / Kota Dropdown -->
                    <div class="md:col-span-4">
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Wilayah Kabupaten / Kota</label>
                        <div class="relative">
                            <select name="kabupaten_kota" class="w-full px-4 py-3 bg-gray-50 hover:bg-gray-100/60 focus:bg-white border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition cursor-pointer">
                                <option value="">Seluruh Jawa Barat (27 Kota/Kab)</option>
                                @foreach($regencies as $regency)
                                    <option value="{{ $regency }}">{{ $regency }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Tombol Cari (Behance Bold Eye-Catching Button) -->
                    <div class="md:col-span-3 pt-1 md:pt-5">
                        <button type="submit" class="w-full py-3.5 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-sm shadow-md shadow-emerald-600/25 hover:shadow-lg hover:shadow-emerald-600/35 transition active:scale-[0.99] flex items-center justify-center gap-2">
                            <span>Temukan Aset</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Search Footer Quick Links -->
                <div class="mt-4 pt-3.5 border-t border-gray-100 flex flex-wrap items-center justify-between text-xs text-gray-500 gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-gray-700">Pencarian Populer:</span>
                        <a href="{{ route('catalog.index', ['kabupaten_kota' => 'Kota Bandung']) }}" class="px-3 py-1 rounded-full bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 transition font-medium">Kota Bandung</a>
                        <a href="{{ route('catalog.index', ['kabupaten_kota' => 'Kota Bogor']) }}" class="px-3 py-1 rounded-full bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 transition font-medium">Kota Bogor</a>
                        <a href="{{ route('catalog.index', ['kabupaten_kota' => 'Kota Cirebon']) }}" class="px-3 py-1 rounded-full bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 transition font-medium">Kota Cirebon</a>
                        <a href="{{ route('catalog.index', ['kabupaten_kota' => 'Kabupaten Garut']) }}" class="px-3 py-1 rounded-full bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 transition font-medium">Kab. Garut</a>
                    </div>
                    
                    <a href="{{ route('catalog.index') }}" class="text-emerald-700 font-extrabold hover:text-emerald-800 transition flex items-center gap-1 group">
                        <span>Peta Interaktif Leaflet GIS</span>
                        <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Stats Metrics Strip (Bright & Elevated Cards) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 sm:-mt-10 relative z-20">
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Metric 1: Total Aset -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-lg shadow-gray-200/60 border border-gray-200/80 hover:border-emerald-300 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Aset Terdata</span>
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($totalAssets, 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 font-medium mt-1">Barang Milik Daerah Pemprov</p>
        </div>

        <!-- Metric 2: Tersedia -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-lg shadow-gray-200/60 border border-gray-200/80 hover:border-emerald-300 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Siap Disewa</span>
                <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 tracking-tight">{{ number_format($totalTersedia, 0, ',', '.') }} Objek</div>
            <p class="text-xs text-emerald-700/80 font-medium mt-1">Status Tersedia di Katalog</p>
        </div>

        <!-- Metric 3: Tersewa Aktif -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-lg shadow-gray-200/60 border border-gray-200/80 hover:border-blue-300 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kerjasama Berjalan</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ number_format($totalTersewa, 0, ',', '.') }} Mitra</div>
            <p class="text-xs text-gray-500 font-medium mt-1">Aset Produktif Berperjanjian (PKS)</p>
        </div>

        <!-- Metric 4: Realisasi PAD -->
        <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-lg shadow-gray-200/60 border border-gray-200/80 hover:border-amber-300 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Potensi Sewa (PAD)</span>
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-2xl sm:text-3xl font-extrabold text-amber-600 tracking-tight">Rp {{ number_format($totalPad / 1000000000, 2, ',', '.') }} M</div>
            <p class="text-xs text-gray-500 font-medium mt-1">Retribusi ke Kas Daerah Jabar</p>
        </div>

    </div>
</section>

<!-- Featured Assets Showcase (Behance Creative Project Grid Aesthetic) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end mb-10 gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 text-xs font-extrabold text-emerald-700 uppercase tracking-wider mb-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Rekomendasi Pilihan
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Objek Aset Siap Disewa & Berdaya Guna</h2>
            <p class="text-sm text-gray-500 mt-1 max-w-xl">Pilihan lokasi strategis dengan visibilitas komersial tinggi di Jawa Barat.</p>
        </div>

        <a href="{{ route('catalog.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 transition border border-gray-200 shadow-2xs">
            <span>Lihat Semua {{ $totalAssets }} Aset</span>
            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>

    <!-- Assets Cards Grid (Behance Showcase Card Style) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-7">
        @foreach($featuredAssets as $asset)
            <div class="behance-card rounded-3xl overflow-hidden flex flex-col group">
                
                <!-- Thumbnail Image with Aspect Ratio -->
                <div class="relative aspect-[16/10] bg-gray-100 overflow-hidden">
                    <img src="{{ $asset->thumbnail_url }}" alt="{{ $asset->nama_objek }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    
                    <!-- Soft gradient on bottom of image for contrast -->
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950/60 via-transparent to-transparent pointer-events-none"></div>

                    <!-- Top Status Badge -->
                    <div class="absolute top-3.5 left-3.5 z-10">
                        @if($asset->status_ketersediaan === 'tersedia')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-600 text-white shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                Tersedia Disewa
                            </span>
                        @elseif($asset->status_ketersediaan === 'proses_booking')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-amber-500 text-white shadow-sm">
                                Proses Booking
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-gray-800 text-white shadow-sm">
                                Tersewa
                            </span>
                        @endif
                    </div>

                    <!-- Shape Tag -->
                    <div class="absolute top-3.5 right-3.5 z-10">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/95 backdrop-blur-md text-gray-800 shadow-sm border border-white/60">
                            {{ $asset->bentuk_objek }}
                        </span>
                    </div>

                    <!-- Bottom Price Overlay -->
                    <div class="absolute bottom-3.5 inset-x-3.5 z-10 text-white flex items-end justify-between">
                        <div>
                            <span class="text-[10px] text-gray-200 uppercase tracking-wider font-bold block">Tarif Retribusi Sewa</span>
                            <div class="text-base font-extrabold text-white">
                                @if($asset->estimasi_tarif_tahunan)
                                    Rp {{ number_format($asset->estimasi_tarif_tahunan, 0, ',', '.') }} <span class="text-xs font-normal text-gray-200">/ thn</span>
                                @else
                                    <span class="text-sm font-semibold text-amber-200">Sesuai Penilaian Resmi</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Body (Behance Clean Info Typography) -->
                <div class="p-6 flex-grow flex flex-col justify-between">
                    <div>
                        <!-- Location Line -->
                        <div class="flex items-center gap-1.5 text-xs text-emerald-700 font-bold mb-2">
                            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="truncate">{{ $asset->kabupaten_kota }}</span>
                            @if($asset->kode_aset)
                                <span class="text-gray-300">&bull;</span>
                                <span class="text-gray-400 font-mono text-[11px]">{{ $asset->kode_aset }}</span>
                            @endif
                        </div>

                        <!-- Asset Title -->
                        <h3 class="font-extrabold text-base text-gray-900 group-hover:text-emerald-600 transition line-clamp-2 leading-snug mb-3">
                            <a href="{{ route('catalog.show', $asset->slug) }}">
                                {{ $asset->nama_objek }}
                            </a>
                        </h3>

                        <!-- Specifications Chip Grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs py-3 border-y border-gray-100 text-gray-600 mb-4 bg-gray-50 rounded-2xl px-3.5">
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Luas Tanah:</span>
                                <span class="font-extrabold text-gray-900">{{ $asset->luas_tanah ? number_format($asset->luas_tanah, 0, ',', '.') . ' m²' : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 text-[10px] uppercase font-bold block">Luas Bangunan:</span>
                                <span class="font-extrabold text-gray-900">{{ $asset->luas_bangunan ? number_format($asset->luas_bangunan, 0, ',', '.') . ' m²' : '-' }}</span>
                            </div>
                        </div>

                        @if($asset->peruntukan)
                            <p class="text-xs text-gray-500 line-clamp-1 mb-4">
                                <span class="font-bold text-gray-700">Peruntukan:</span> {{ $asset->peruntukan }}
                            </p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center gap-2">
                        <a href="{{ route('catalog.show', $asset->slug) }}" class="flex-grow py-2.5 px-4 rounded-full text-center text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                            Lihat Detail
                        </a>
                        @if($asset->status_ketersediaan === 'tersewa')
                            <button type="button" disabled class="py-2.5 px-4 rounded-full text-xs font-extrabold text-gray-400 bg-gray-100 border border-gray-200 cursor-not-allowed flex items-center gap-1.5 shrink-0 opacity-75" title="Aset sedang dalam masa sewa aktif">
                                <span>Tersewa</span>
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </button>
                        @else
                            <a href="{{ route('catalog.show', $asset->slug) }}#form-booking" class="py-2.5 px-4 rounded-full text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 shrink-0">
                                <span>Ajukan Minat</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Flow / Alur Sewa Permendagri 7/2024 (Behance Step Grid) -->
<section class="bg-gray-50/90 py-20 border-y border-gray-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-16">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-extrabold mb-3 border border-emerald-200">
                Pedoman Permendagri No. 7 Tahun 2024
            </div>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Alur Pemanfaatan BMD yang Mudah & Pasti</h2>
            <p class="text-sm text-gray-600 mt-2">
                Pemanfaatan aset daerah didampingi petugas UPTD secara langsung agar investasi Anda terhindar dari perantara tidak resmi.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            
            <!-- Step 1 -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-gray-200/80 relative flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <span class="w-12 h-12 rounded-2xl bg-emerald-600 text-white font-black text-base flex items-center justify-center shadow-md shadow-emerald-600/20">
                            01
                        </span>
                        <span class="text-[11px] font-extrabold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">Langkah Awal</span>
                    </div>
                    <h3 class="font-extrabold text-base text-gray-900 mb-2">Pilih Aset & Ajukan Minat</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Pilih objek BMD di marketplace, cantumkan durasi rencana sewa serta nomor WhatsApp aktif Anda sebagai pemohon.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 text-[11px] text-gray-400 font-semibold">
                    &bull; Bebas biaya pendaftaran
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-gray-200/80 relative flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <span class="w-12 h-12 rounded-2xl bg-teal-600 text-white font-black text-base flex items-center justify-center shadow-md shadow-teal-600/20">
                            02
                        </span>
                        <span class="text-[11px] font-extrabold text-teal-700 bg-teal-50 px-3 py-1 rounded-full">Respon Cepat</span>
                    </div>
                    <h3 class="font-extrabold text-base text-gray-900 mb-2">Koordinasi Langsung Petugas WA</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Petugas UPTD menghubungi WhatsApp Anda untuk klarifikasi kesesuaian peruntukan ruang dan menjadwalkan survei lapangan bersama.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 text-[11px] text-teal-700 font-semibold">
                    &bull; Komunikasi transparan & resmi
                </div>
            </div>

            <!-- Step 3 -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-gray-200/80 relative flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <span class="w-12 h-12 rounded-2xl bg-blue-600 text-white font-black text-base flex items-center justify-center shadow-md shadow-blue-600/20">
                            03
                        </span>
                        <span class="text-[11px] font-extrabold text-blue-700 bg-blue-50 px-3 py-1 rounded-full">Verifikasi Legal</span>
                    </div>
                    <h3 class="font-extrabold text-base text-gray-900 mb-2">Input Berkas Persyaratan</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Admin UPTD menginput dan menelaah 8 berkas persyaratan sewa (identitas KTP/NIB, NPWP, proposal usaha, surat pernyataan kesanggupan).
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 text-[11px] text-gray-400 font-semibold">
                    &bull; Ditelaah tim teknis BPKAD
                </div>
            </div>

            <!-- Step 4 -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-xs border border-gray-200/80 relative flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between mb-5">
                        <span class="w-12 h-12 rounded-2xl bg-amber-500 text-white font-black text-base flex items-center justify-center shadow-md shadow-amber-500/20">
                            04
                        </span>
                        <span class="text-[11px] font-extrabold text-amber-800 bg-amber-50 px-3 py-1 rounded-full">PKS Sah</span>
                    </div>
                    <h3 class="font-extrabold text-base text-gray-900 mb-2">Persetujuan & Terbit Kontrak</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Penerbitan Surat Persetujuan Sewa Gubernur/Sekda, pembayaran retribusi ke Kas Daerah (PAD), dan penandatanganan Perjanjian Kerjasama (PKS).
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 text-[11px] text-amber-700 font-semibold">
                    &bull; Jaminan hukum sewa 1–5 tahun
                </div>
            </div>

        </div>

        <!-- Trust Features Grid -->
        <div class="mt-12 bg-white rounded-3xl p-6 sm:p-8 border border-gray-200/80 shadow-xs grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-gray-900 mb-1">Kepastian Hukum Mutlak</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Status aset merupakan tanah/bangunan bersertifikat resmi milik Pemprov Jabar (SHP/HPL terdaftar).</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-gray-900 mb-1">Tarif Wajar & Bebas Pungli</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Besaran sewa dihitung berdasarkan penilaian appraisal independen / peraturan retribusi daerah.</p>
                </div>
            </div>

            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-extrabold text-gray-900 mb-1">Masa Sewa Fleksibel</h4>
                    <p class="text-xs text-gray-500 leading-relaxed">Jangka waktu sewa hingga 5 tahun dan dapat diperpanjang kembali sesuai kebutuhan usaha.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Call to Action Banner (Vibrant & Eye-Catching Light Gradient) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
    <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 rounded-3xl p-8 sm:p-14 text-white shadow-2xl shadow-emerald-700/20 relative overflow-hidden">
        
        <div class="max-w-2xl relative z-10">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-extrabold mb-5 border border-white/30">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                Konsultasi & Kunjungan Lapangan Terbuka
            </span>
            
            <h2 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight text-white">
                Punya Rencana Usaha & Membutuhkan Lokasi di Jawa Barat?
            </h2>
            
            <p class="mt-4 text-sm sm:text-base text-emerald-100 leading-relaxed font-medium">
                Tim UPTD Pengamanan dan Pemanfaatan Aset siap memberikan rekomendasi objek tanah atau bangunan yang sesuai dengan model bisnis Anda.
            </p>
            
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('catalog.index') }}" class="px-7 py-3.5 rounded-full bg-white text-emerald-900 font-black text-sm shadow-lg hover:bg-emerald-50 transition active:scale-[0.99]">
                    Jelajahi Katalog Lengkap
                </a>
                <a href="https://wa.me/6281234567890?text=Halo%20Admin%20UPTD,%20saya%20ingin%20berkonsultasi%20mengenai%20sewa%20aset%20BMD" target="_blank" rel="noopener noreferrer" class="px-7 py-3.5 rounded-full bg-emerald-800/60 hover:bg-emerald-800 text-white font-extrabold text-sm border border-emerald-300/40 transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-200" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <span>Hubungi Petugas UPTD</span>
                </a>
            </div>
        </div>

        <!-- Ambient Emblem Art -->
        <div class="hidden lg:block absolute -right-16 -bottom-16 opacity-10 pointer-events-none">
            <svg class="w-[450px] h-[450px]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
    </div>
</section>
@endsection
