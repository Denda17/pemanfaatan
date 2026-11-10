@extends('layouts.app')

@section('title', $asset->nama_objek . ' - Pemanfaatan BMD Pemprov Jawa Barat')

@section('content')
<!-- Breadcrumbs Bar (Light & Clean) -->
<div class="bg-white border-b border-gray-200/80 py-3.5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-2 text-xs text-gray-500 font-semibold">
            <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('catalog.index') }}" class="hover:text-emerald-700 transition">Katalog Aset</a>
            <span class="text-gray-300">/</span>
            <a href="{{ route('catalog.index', ['kabupaten_kota' => $asset->kabupaten_kota]) }}" class="hover:text-emerald-700 transition">{{ $asset->kabupaten_kota }}</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 font-extrabold truncate">{{ $asset->nama_objek }}</span>
        </nav>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
        
        <!-- Left / Main Column (8 cols) -->
        <div class="lg:col-span-7 xl:col-span-8 space-y-8">
            
            <!-- Asset Title & Status Badges -->
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-3.5">
                    @if($asset->status_ketersediaan === 'tersedia')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            Status: Siap Disewa (Tersedia)
                        </span>
                    @elseif($asset->status_ketersediaan === 'proses_booking')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-300">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Status: Sedang Proses Booking & Telaah Berkas
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-gray-100 text-gray-800 border border-gray-300">
                            Status: Tersewa Aktif (Perjanjian Berjalan)
                        </span>
                    @endif

                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-white text-gray-700 border border-gray-200 shadow-2xs">
                        {{ $asset->bentuk_objek }}
                    </span>

                    @if($asset->kode_aset)
                        <span class="text-xs text-gray-500 font-mono bg-gray-100 px-2.5 py-1 rounded-full border border-gray-200">
                            Kode: {{ $asset->kode_aset }}
                        </span>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-tight">
                    {{ $asset->nama_objek }}
                </h1>
                
                <p class="text-sm text-gray-500 mt-2.5 flex items-start gap-2">
                    <svg class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-medium text-gray-700">{{ $asset->alamat ?: ($asset->nama_objek . ', ' . $asset->kabupaten_kota) }}</span>
                </p>
            </div>

            <!-- Interactive Multi-Photo Gallery & Site Plan Frame (Behance Studio Aesthetic) -->
            <div class="bg-white rounded-3xl overflow-hidden shadow-md shadow-gray-200/50 border border-gray-200/90 p-4 sm:p-5">
                
                <!-- Gallery Mode Switcher: Foto Lapangan vs Denah Tapak -->
                <div class="flex items-center justify-between pb-3.5 mb-3 border-b border-gray-100 flex-wrap gap-2">
                    <div class="inline-flex p-1 rounded-2xl bg-gray-100/90 border border-gray-200/70 text-xs font-bold">
                        <button type="button" id="tab-btn-photos" class="px-3.5 py-1.5 rounded-xl bg-white text-emerald-800 font-extrabold shadow-2xs transition flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Foto Lapangan</span>
                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-50 text-[10px] text-emerald-700 font-extrabold">{{ count($asset->gallery_urls) }}</span>
                        </button>

                        @if($asset->hasSitePlan())
                            <button type="button" id="tab-btn-siteplan" class="px-3.5 py-1.5 rounded-xl text-gray-600 hover:text-gray-900 font-bold transition flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Denah Tapak / Site Plan</span>
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            </button>
                        @endif
                    </div>

                    <!-- Fullscreen Zoom Button -->
                    <button type="button" id="btn-zoom-main" class="px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold border border-gray-200 transition flex items-center gap-1.5" title="Buka Gambar Resolusi Penuh">
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                        <span>Perbesar</span>
                    </button>
                </div>

                <!-- Main Display Frame (Photo Stage) -->
                <div id="gallery-photo-view">
                    <div class="relative aspect-[16/10] sm:aspect-[2/1] bg-gray-100 rounded-2xl overflow-hidden border border-gray-100">
                        <img id="main-gallery-img" src="{{ $asset->thumbnail_url }}" alt="{{ $asset->nama_objek }}" class="w-full h-full object-cover transition duration-300">
                        <div class="absolute bottom-3 right-3 bg-gray-950/70 backdrop-blur-md text-white text-[10px] font-bold px-2.5 py-1 rounded-full border border-white/20">
                            <span id="gallery-counter">1</span> / {{ count($asset->gallery_urls) }} Foto
                        </div>
                    </div>

                    <!-- Thumbnail Strip (Klik untuk berganti foto) -->
                    @if(count($asset->gallery_urls) > 1)
                        <div class="flex items-center gap-2.5 mt-3 overflow-x-auto pb-1 pt-0.5">
                            @foreach($asset->gallery_urls as $idx => $imgUrl)
                                <button type="button" class="gallery-thumb-btn shrink-0 w-20 h-14 sm:w-24 sm:h-16 rounded-xl overflow-hidden border-2 {{ $idx === 0 ? 'border-emerald-600 ring-2 ring-emerald-500/30' : 'border-gray-200 opacity-70 hover:opacity-100' }} transition cursor-pointer" data-img-url="{{ $imgUrl }}" data-index="{{ $idx + 1 }}">
                                    <img src="{{ $imgUrl }}" alt="Sudut {{ $idx + 1 }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Site Plan Display Frame (Hidden by default) -->
                @if($asset->hasSitePlan())
                    <div id="gallery-siteplan-view" class="hidden">
                        <div class="relative aspect-[16/10] sm:aspect-[2/1] bg-gray-50 rounded-2xl overflow-hidden border border-gray-200 p-2 flex items-center justify-center">
                            <img id="siteplan-img" src="{{ $asset->site_plan_image_url }}" alt="Denah Tapak {{ $asset->nama_objek }}" class="max-w-full max-h-full object-contain">
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur-md text-gray-800 text-[10px] font-extrabold px-3 py-1 rounded-full border border-gray-200 shadow-2xs">
                                Peta Ukur & Tapak Bidang Tanah BMD
                            </div>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs text-gray-500 px-1">
                            <span>Format resolusi tinggi denah tanah resmi UPTD</span>
                            <a href="{{ $asset->site_plan_image_url }}" target="_blank" class="text-emerald-700 font-extrabold hover:underline flex items-center gap-1">
                                <span>Unduh / Buka Ukuran Asli</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Key Specifications Grid Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-200/90">
                <h2 class="text-base font-extrabold text-gray-900 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <span>Spesifikasi Fisik Objek BMD</span>
                </h2>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/70">
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block mb-1">Luas Tanah</span>
                        <span class="text-lg font-black text-gray-900">{{ $asset->luas_tanah ? number_format($asset->luas_tanah, 0, ',', '.') . ' m²' : '-' }}</span>
                    </div>

                    <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/70">
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block mb-1">Luas Bangunan</span>
                        <span class="text-lg font-black text-gray-900">{{ $asset->luas_bangunan ? number_format($asset->luas_bangunan, 0, ',', '.') . ' m²' : '-' }}</span>
                    </div>

                    <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/70">
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block mb-1">Bentuk Objek</span>
                        <span class="text-base font-extrabold text-gray-900">{{ $asset->bentuk_objek }}</span>
                    </div>

                    <div class="bg-gray-50/80 p-4 rounded-2xl border border-gray-200/70">
                        <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider block mb-1">Wilayah</span>
                        <span class="text-sm font-extrabold text-gray-900 truncate block">{{ $asset->kabupaten_kota }}</span>
                    </div>
                </div>

                @if($asset->peruntukan)
                    <div class="mt-4 p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/80 text-xs text-emerald-950 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-700 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <strong class="font-bold text-emerald-900">Rekomendasi Peruntukan:</strong> {{ $asset->peruntukan }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Description & Commercial Potential -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-200/90">
                <h2 class="text-base font-extrabold text-gray-900 mb-4 pb-3 border-b border-gray-100">
                    Deskripsi & Potensi Pengembangan
                </h2>
                <div class="text-sm text-gray-600 leading-relaxed space-y-3 font-normal">
                    @if($asset->deskripsi)
                        {!! $asset->deskripsi !!}
                    @else
                        <p>
                            Objek pemanfaatan ini merupakan Barang Milik Daerah (BMD) di bawah pengelolaan Badan Pengelola Keuangan dan Aset Daerah (BPKAD) Provinsi Jawa Barat melalui UPTD Pengamanan dan Pemanfaatan Aset. Berlokasi di wilayah dengan aksesibilitas tinggi dan cocok untuk aktivitas komersial, pergudangan, layanan jasa, maupun kerjasama usaha jangka menengah dan panjang.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Single Asset Leaflet Map -->
            @if($asset->latitude && $asset->longitude)
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-200/90">
                    <h2 class="text-base font-extrabold text-gray-900 mb-4 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Peta Titik Lokasi Aset</span>
                        </span>
                        <span class="text-xs font-mono text-gray-400 font-normal">
                            {{ $asset->latitude }}, {{ $asset->longitude }}
                        </span>
                    </h2>
                    <div id="single-asset-map" class="h-72 w-full rounded-2xl border border-gray-200 overflow-hidden z-10"></div>
                </div>
            @endif

            <!-- Permendagri 7/2024 Document Requirements Checklist (Behance Light Aesthetic) -->
            <div class="bg-emerald-50/70 text-gray-900 rounded-3xl p-6 sm:p-8 shadow-xs border border-emerald-200/80">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm sm:text-base text-gray-900">Panduan 8 Berkas Persyaratan Sewa</h3>
                        <p class="text-xs text-emerald-800 font-medium">Sesuai Permendagri No. 7 Tahun 2024 & PP No. 28 Tahun 2020</p>
                    </div>
                </div>
                
                <p class="text-xs text-gray-600 leading-relaxed mb-5">
                    Setelah Anda mengirim permohonan minat via form di samping, Petugas UPTD Pengamanan dan Pemanfaatan Aset akan menghubungi dan memfasilitasi penginputan 8 berkas persyaratan sewa ke sistem:
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="flex items-start gap-2.5 bg-white p-3.5 rounded-2xl border border-emerald-200/70 shadow-2xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-[11px] shrink-0">1</span>
                        <span class="text-gray-700 font-medium">Surat Permohonan Sewa resmi kepada Pengelola Barang / Gubernur Jabar</span>
                    </div>
                    <div class="flex items-start gap-2.5 bg-white p-3.5 rounded-2xl border border-emerald-200/70 shadow-2xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-[11px] shrink-0">2</span>
                        <span class="text-gray-700 font-medium">Identitas Pemohon (KTP Perorangan / NIB & NPWP Badan Usaha)</span>
                    </div>
                    <div class="flex items-start gap-2.5 bg-white p-3.5 rounded-2xl border border-emerald-200/70 shadow-2xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-[11px] shrink-0">3</span>
                        <span class="text-gray-700 font-medium">Proposal Usaha & Rencana Pemanfaatan Lokasi (Site Plan)</span>
                    </div>
                    <div class="flex items-start gap-2.5 bg-white p-3.5 rounded-2xl border border-emerald-200/70 shadow-2xs">
                        <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-[11px] shrink-0">4</span>
                        <span class="text-gray-700 font-medium">Surat Pernyataan Kesanggupan Membayar Retribusi Sewa ke Kas Daerah</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Sticky Booking Widget (4 cols) -->
        <div class="lg:col-span-5 xl:col-span-4">
            <div class="sticky top-28 space-y-6">

                <!-- Price Card (Behance High-End Clean Aesthetic) -->
                <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-3xl p-6 sm:p-7 shadow-xl shadow-emerald-700/20 border border-emerald-500/30 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs uppercase font-extrabold tracking-wider text-emerald-100">Estimasi Retribusi Sewa</span>
                        <span class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/30">Resmi</span>
                    </div>

                    <div class="text-2xl sm:text-3xl font-black mt-1 tracking-tight text-white">
                        @if($asset->estimasi_tarif_tahunan)
                            Rp {{ number_format($asset->estimasi_tarif_tahunan, 0, ',', '.') }}
                            <span class="text-xs font-normal text-emerald-100 block sm:inline mt-0.5 sm:mt-0">/ Tahun</span>
                        @else
                            Penetapan Nilai Wajar
                            <span class="text-xs font-normal text-emerald-100 block mt-1">Berdasarkan Keputusan Gubernur / KJPP</span>
                        @endif
                    </div>

                    <p class="text-[11px] text-emerald-100/90 mt-3 leading-relaxed border-t border-white/20 pt-3 font-medium">
                        *Tarif sewa definitif ditetapkan secara resmi berdasarkan formula Permendagri No. 7 Tahun 2024.
                    </p>
                </div>

                <!-- Kalkulator & Simulasi Tarif Sewa Permendagri No. 7/2024 -->
                <div class="bg-white rounded-3xl p-5 sm:p-6 shadow-md shadow-emerald-950/5 border border-emerald-500/25 relative overflow-hidden">
                    <div class="absolute -top-8 -right-8 w-28 h-28 bg-emerald-100/60 rounded-full blur-xl pointer-events-none"></div>

                    <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-gray-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-xs sm:text-sm font-extrabold text-gray-900 leading-tight">Simulasi Tarif Sewa BMD</h3>
                                <p class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider">Permendagri No. 7/2024</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                            Insentif Afirmatif
                        </span>
                    </div>

                    <!-- Pilihan Kategori Pemohon -->
                    <div class="space-y-3 mb-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1.5 flex items-center justify-between">
                                <span>Kategori Pemohon / Peruntukan</span>
                                <span id="badge-diskon" class="text-[10px] font-extrabold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300">Diskon 50%</span>
                            </label>
                            <select id="simulasi-kategori" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                <option value="0.50" data-label="UMKM / Koperasi" data-diskon="50%" selected>
                                    Pelaku Usaha Mikro, Kecil & Koperasi (Diskon 50%)
                                </option>
                                <option value="0.25" data-label="Sosial / Keagamaan" data-diskon="75%">
                                    Sosial, Pendidikan, Kebudayaan / Non-Profit (Diskon 75%)
                                </option>
                                <option value="0.75" data-label="Usaha Menengah" data-diskon="25%">
                                    Pelaku Usaha Menengah (Diskon 25%)
                                </option>
                                <option value="1.00" data-label="Komersial / Korporasi" data-diskon="0%">
                                    Badan Usaha / BUMN / BUMD / Swasta (Tarif Penuh 100%)
                                </option>
                            </select>
                        </div>

                        <!-- Jangka Waktu Sewa (Tahun) -->
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1.5 flex items-center justify-between">
                                <span>Jangka Waktu Sewa</span>
                                <span class="text-[10px] text-gray-400 font-medium">Maks. 5 Thn (Permendagri 19/2016)</span>
                            </label>
                            <div class="grid grid-cols-4 gap-1.5" id="group-durasi-simulasi">
                                <button type="button" class="btn-durasi py-1.5 text-xs font-extrabold rounded-xl border border-emerald-500 bg-emerald-600 text-white transition shadow-xs cursor-pointer" data-tahun="1">1 Thn</button>
                                <button type="button" class="btn-durasi py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 transition cursor-pointer" data-tahun="2">2 Thn</button>
                                <button type="button" class="btn-durasi py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 transition cursor-pointer" data-tahun="3">3 Thn</button>
                                <button type="button" class="btn-durasi py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 transition cursor-pointer" data-tahun="5">5 Thn</button>
                            </div>
                        </div>

                        @if(!$asset->estimasi_tarif_tahunan)
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 mb-1">
                                Estimasi Nilai Dasar / Tahun (Rp)
                            </label>
                            <input type="number" id="simulasi-tarif-pokok" value="50000000" min="1000000" step="500000" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            <span class="text-[10px] text-gray-400 block mt-0.5">*Nilai indikatif wajar untuk simulasi pemohon</span>
                        </div>
                        @else
                            <input type="hidden" id="simulasi-tarif-pokok" value="{{ (float) $asset->estimasi_tarif_tahunan }}">
                        @endif
                    </div>

                    <!-- Kotak Hasil Rincian Simulasi -->
                    <div class="bg-gradient-to-br from-emerald-50/80 to-teal-50/50 rounded-2xl p-3.5 border border-emerald-200/70 mb-3 space-y-2">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-gray-500 font-medium">Tarif Pokok / Thn:</span>
                            <span id="hasil-pokok-tahunan" class="font-bold text-gray-700">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="text-gray-500 font-medium">Faktor Penyesuai:</span>
                            <span id="hasil-faktor-persen" class="font-bold text-emerald-800">50% (Afirmatif)</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] pb-1.5 border-b border-emerald-200/60">
                            <span class="text-emerald-800 font-semibold flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-emerald-600 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Insentif yang Dihemat:
                            </span>
                            <span id="hasil-hemat" class="font-extrabold text-emerald-700">Rp 0</span>
                        </div>
                        <div class="flex items-baseline justify-between pt-0.5">
                            <div>
                                <span class="block text-[11px] font-bold text-gray-800">Estimasi Retribusi Sewa:</span>
                                <span id="hasil-durasi-label" class="text-[9.5px] text-gray-500 font-medium leading-none">Untuk 1 Tahun Sewa</span>
                            </div>
                            <div class="text-right">
                                <span id="hasil-total-sewa" class="text-base sm:text-lg font-black text-emerald-900 tracking-tight">Rp 0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Autofill ke Form Booking -->
                    @if($asset->status_ketersediaan === 'tersewa')
                        <button type="button" disabled class="w-full py-2.5 px-3 rounded-xl bg-gray-100 text-gray-400 font-extrabold text-xs border border-gray-200 cursor-not-allowed flex items-center justify-center gap-1.5 opacity-75" title="Aset sedang dalam masa sewa aktif">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Aset Sedang Tersewa Aktif</span>
                        </button>
                    @else
                        <button type="button" id="btn-terapkan-simulasi" class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs hover:shadow-sm transition flex items-center justify-center gap-1.5 group cursor-pointer active:scale-[0.99]">
                            <svg class="w-4 h-4 text-emerald-200 group-hover:translate-y-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                            <span>Gunakan Hasil Simulasi pada Formulir</span>
                        </button>
                    @endif
                </div>

                <!-- Booking Form Card (Clean White Behance Studio Card) -->
                <div id="form-booking" class="bg-white rounded-3xl p-6 sm:p-7 shadow-lg shadow-gray-200/60 border border-gray-200/90">
                    <div class="flex items-center gap-3 mb-5 pb-3 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-extrabold text-gray-900">Formulir Booking Minat Sewa</h2>
                            <p class="text-xs text-gray-500 font-medium">Ajukan minat & petugas UPTD akan membalas via WA</p>
                        </div>
                    </div>

                    @if($asset->status_ketersediaan === 'tersewa')
                        <div class="bg-gray-50 p-5 rounded-2xl text-center text-xs text-gray-600 border border-gray-200">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <strong class="text-gray-900 block mb-1">Objek ini Berstatus Tersewa Aktif</strong>
                            <span>Anda dapat mencari opsi aset lain di katalog yang masih tersedia.</span>
                        </div>
                    @else
                        <form action="{{ route('booking.store', $asset->slug) }}" method="POST" class="space-y-4">
                            @csrf

                            <!-- Nama Pemohon -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Nama Lengkap Pemohon <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="nama_pemohon" value="{{ old('nama_pemohon') }}" required placeholder="Contoh: Dedi Koswara" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                                @error('nama_pemohon')
                                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nomor WhatsApp Aktif -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-700 font-bold text-xs">
                                        WA
                                    </div>
                                    <input type="tel" name="nomor_whatsapp" value="{{ old('nomor_whatsapp') }}" required placeholder="081234567890" class="w-full pl-11 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1 font-medium">Petugas UPTD akan menghubungi nomor WA ini untuk follow-up berkas.</p>
                                @error('nomor_whatsapp')
                                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- NIK & Durasi -->
                            <div class="grid grid-cols-2 gap-2.5">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">NIK / NIB</label>
                                    <input type="text" name="nik_atau_nib" value="{{ old('nik_atau_nib') }}" placeholder="3204..." class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Durasi Sewa</label>
                                    <select id="booking_durasi_sewa_tahun" name="durasi_sewa_tahun" required class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                                        <option value="1">1 Tahun</option>
                                        <option value="2">2 Tahun</option>
                                        <option value="3">3 Tahun</option>
                                        <option value="5">5 Tahun (Maks)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Instansi / Perusahaan -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Instansi / Badan Usaha</label>
                                <input type="text" id="booking_nama_instansi" name="nama_instansi" value="{{ old('nama_instansi') }}" placeholder="PT / CV / Koperasi / Perorangan" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                            </div>

                            <!-- Rencana Peruntukan Usaha -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">
                                    Rencana Peruntukan Usaha <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="booking_rencana_peruntukan" name="rencana_peruntukan" required rows="3" placeholder="Contoh: Kafe kuliner, depo logistik, klinik, mini market, kantor cabang..." class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">{{ old('rencana_peruntukan') }}</textarea>
                                @error('rencana_peruntukan')
                                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tombol Kirim -->
                            <button type="submit" class="w-full py-3.5 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-600/25 hover:shadow-lg transition active:scale-[0.99] flex items-center justify-center gap-2">
                                <span>Ajukan Minat Sewa Sekarang</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>

                            <p class="text-[10px] text-gray-400 text-center leading-normal">
                                Data Anda dilindungi dan hanya digunakan oleh Petugas UPTD Pengamanan dan Pemanfaatan Aset untuk verifikasi sewa.
                            </p>
                        </form>
                    @endif
                </div>

                <!-- Related Assets Card -->
                @if($relatedAssets->isNotEmpty())
                    <div class="bg-white rounded-3xl p-6 shadow-xs border border-gray-200/90">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aset Serupa di Wilayah Ini
                        </h3>
                        <div class="space-y-3">
                            @foreach($relatedAssets as $rel)
                                <a href="{{ route('catalog.show', $rel->slug) }}" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-gray-50 transition group border border-transparent hover:border-gray-200">
                                    <img src="{{ $rel->thumbnail_url }}" alt="{{ $rel->nama_objek }}" class="w-14 h-14 rounded-2xl object-cover shrink-0">
                                    <div class="min-w-0 flex-grow">
                                        <h4 class="text-xs font-extrabold text-gray-900 group-hover:text-emerald-700 truncate">{{ $rel->nama_objek }}</h4>
                                        <p class="text-[11px] text-gray-500 truncate font-medium">{{ $rel->kabupaten_kota }}</p>
                                        <span class="text-[11px] font-extrabold text-emerald-700">
                                            {{ $rel->estimasi_tarif_tahunan ? 'Rp ' . number_format($rel->estimasi_tarif_tahunan, 0, ',', '.') : 'Tersedia' }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectKategori = document.getElementById('simulasi-kategori');
        const badgeDiskon = document.getElementById('badge-diskon');
        const inputTarifPokok = document.getElementById('simulasi-tarif-pokok');
        const durasiButtons = document.querySelectorAll('.btn-durasi');
        const hasilPokokTahunan = document.getElementById('hasil-pokok-tahunan');
        const hasilFaktorPersen = document.getElementById('hasil-faktor-persen');
        const hasilHemat = document.getElementById('hasil-hemat');
        const hasilTotalSewa = document.getElementById('hasil-total-sewa');
        const hasilDurasiLabel = document.getElementById('hasil-durasi-label');
        const btnTerapkan = document.getElementById('btn-terapkan-simulasi');

        let selectedTahun = 1;

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(number);
        }

        function hitungSimulasi() {
            if (!selectKategori) return;

            const factor = parseFloat(selectKategori.value) || 1.0;
            const selectedOption = selectKategori.options[selectKategori.selectedIndex];
            const diskonText = selectedOption.getAttribute('data-diskon') || '0%';
            const basePerYear = parseFloat(inputTarifPokok?.value) || 0;

            if (badgeDiskon) {
                badgeDiskon.textContent = factor < 1.0 ? `Diskon ${diskonText}` : 'Tarif Penuh';
                if (factor <= 0.5) {
                    badgeDiskon.className = 'text-[10px] font-extrabold text-emerald-700 bg-emerald-100 px-1.5 py-0.5 rounded border border-emerald-300';
                } else if (factor < 1.0) {
                    badgeDiskon.className = 'text-[10px] font-extrabold text-blue-700 bg-blue-100 px-1.5 py-0.5 rounded border border-blue-300';
                } else {
                    badgeDiskon.className = 'text-[10px] font-extrabold text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded border border-gray-300';
                }
            }

            const standardTotal = basePerYear * selectedTahun;
            const adjustedTotal = basePerYear * factor * selectedTahun;
            const savedAmount = Math.max(0, standardTotal - adjustedTotal);

            if (hasilPokokTahunan) {
                hasilPokokTahunan.textContent = formatRupiah(basePerYear);
            }

            if (hasilFaktorPersen) {
                const persenStr = Math.round(factor * 100) + '%';
                hasilFaktorPersen.textContent = factor < 1.0 ? `${persenStr} (Insentif)` : `${persenStr} (Standar)`;
            }

            if (hasilHemat) {
                hasilHemat.textContent = formatRupiah(savedAmount);
            }

            if (hasilTotalSewa) {
                hasilTotalSewa.textContent = formatRupiah(adjustedTotal);
            }

            if (hasilDurasiLabel) {
                hasilDurasiLabel.textContent = `Untuk ${selectedTahun} Tahun Sewa (${selectedOption.getAttribute('data-label') || ''})`;
            }
        }

        durasiButtons.forEach(btn => {
            btn.addEventListener('click', function () {
                durasiButtons.forEach(b => {
                    b.className = 'btn-durasi py-1.5 text-xs font-bold rounded-xl border border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 transition cursor-pointer';
                });
                this.className = 'btn-durasi py-1.5 text-xs font-extrabold rounded-xl border border-emerald-500 bg-emerald-600 text-white transition shadow-xs cursor-pointer';
                selectedTahun = parseInt(this.getAttribute('data-tahun')) || 1;
                hitungSimulasi();
            });
        });

        if (selectKategori) {
            selectKategori.addEventListener('change', hitungSimulasi);
        }

        if (inputTarifPokok && inputTarifPokok.type !== 'hidden') {
            inputTarifPokok.addEventListener('input', hitungSimulasi);
        }

        if (btnTerapkan) {
            btnTerapkan.addEventListener('click', function () {
                const formBooking = document.getElementById('form-booking');
                const selectDurasi = document.getElementById('booking_durasi_sewa_tahun');
                const inputInstansi = document.getElementById('booking_nama_instansi');
                const textareaPeruntukan = document.getElementById('booking_rencana_peruntukan');

                if (selectDurasi) {
                    selectDurasi.value = selectedTahun;
                }

                if (selectKategori) {
                    const selectedOption = selectKategori.options[selectKategori.selectedIndex];
                    const label = selectedOption.getAttribute('data-label') || '';
                    if (inputInstansi && (!inputInstansi.value || inputInstansi.value.trim() === '')) {
                        inputInstansi.value = label;
                    }
                    if (textareaPeruntukan && (!textareaPeruntukan.value || textareaPeruntukan.value.trim() === '')) {
                        textareaPeruntukan.value = `Rencana pemanfaatan kegiatan ${label}`;
                    }
                }

                if (formBooking) {
                    formBooking.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    formBooking.classList.add('ring-2', 'ring-emerald-500', 'ring-offset-2');
                    setTimeout(() => {
                        formBooking.classList.remove('ring-2', 'ring-emerald-500', 'ring-offset-2');
                    }, 1800);
                }
            });
        }

        hitungSimulasi();

        // Multi-Photo Gallery & Site Plan Interactive Logic
        const mainImg = document.getElementById('main-gallery-img');
        const counterEl = document.getElementById('gallery-counter');
        const thumbBtns = document.querySelectorAll('.gallery-thumb-btn');
        const btnZoom = document.getElementById('btn-zoom-main');
        const tabBtnPhotos = document.getElementById('tab-btn-photos');
        const tabBtnSiteplan = document.getElementById('tab-btn-siteplan');
        const viewPhotos = document.getElementById('gallery-photo-view');
        const viewSiteplan = document.getElementById('gallery-siteplan-view');
        const siteplanImg = document.getElementById('siteplan-img');

        let currentActiveImgUrl = mainImg ? mainImg.src : '';

        thumbBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const url = this.getAttribute('data-img-url');
                const idx = this.getAttribute('data-index');

                if (mainImg && url) {
                    mainImg.style.opacity = '0.4';
                    setTimeout(() => {
                        mainImg.src = url;
                        mainImg.style.opacity = '1';
                        currentActiveImgUrl = url;
                    }, 150);
                }

                if (counterEl && idx) {
                    counterEl.textContent = idx;
                }

                thumbBtns.forEach(b => {
                    b.classList.remove('border-emerald-600', 'ring-2', 'ring-emerald-500/30');
                    b.classList.add('border-gray-200', 'opacity-70');
                });
                this.classList.add('border-emerald-600', 'ring-2', 'ring-emerald-500/30');
                this.classList.remove('border-gray-200', 'opacity-70');
            });
        });

        if (btnZoom) {
            btnZoom.addEventListener('click', function () {
                const targetUrl = (viewSiteplan && !viewSiteplan.classList.contains('hidden') && siteplanImg) 
                    ? siteplanImg.src 
                    : (mainImg ? mainImg.src : currentActiveImgUrl);
                if (targetUrl) {
                    window.open(targetUrl, '_blank');
                }
            });
        }

        if (tabBtnPhotos && tabBtnSiteplan && viewPhotos && viewSiteplan) {
            tabBtnPhotos.addEventListener('click', function () {
                viewPhotos.classList.remove('hidden');
                viewSiteplan.classList.add('hidden');
                tabBtnPhotos.classList.add('bg-white', 'text-emerald-800', 'font-extrabold', 'shadow-2xs');
                tabBtnPhotos.classList.remove('text-gray-600');
                tabBtnSiteplan.classList.remove('bg-white', 'text-emerald-800', 'font-extrabold', 'shadow-2xs');
                tabBtnSiteplan.classList.add('text-gray-600');
            });

            tabBtnSiteplan.addEventListener('click', function () {
                viewSiteplan.classList.remove('hidden');
                viewPhotos.classList.add('hidden');
                tabBtnSiteplan.classList.add('bg-white', 'text-emerald-800', 'font-extrabold', 'shadow-2xs');
                tabBtnSiteplan.classList.remove('text-gray-600');
                tabBtnPhotos.classList.remove('bg-white', 'text-emerald-800', 'font-extrabold', 'shadow-2xs');
                tabBtnPhotos.classList.add('text-gray-600');
            });
        }
    });
</script>

@if($asset->latitude && $asset->longitude)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lat = {{ (float) $asset->latitude }};
        const lng = {{ (float) $asset->longitude }};

        const map = L.map('single-asset-map').setView([lat, lng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap kontributor &bull; BPKAD Jabar'
        }).addTo(map);

        const customMarkerIcon = L.divIcon({
            className: 'custom-single-marker',
            html: '<div style="background-color: #059669; width: 18px; height: 18px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.25);"></div>',
            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });

        const marker = L.marker([lat, lng], { icon: customMarkerIcon }).addTo(map);
        marker.bindPopup(`
            <div class="p-3 text-gray-800">
                <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white mb-1.5">
                    {{ $asset->status_ketersediaan === 'tersedia' ? 'Siap Disewa' : 'Aset BMD' }}
                </span>
                <strong class="block text-xs font-extrabold text-gray-900 leading-snug">{{ $asset->nama_objek }}</strong>
                <p class="text-[11px] text-gray-500 mt-1 font-medium">{{ $asset->kabupaten_kota }}</p>
            </div>
        `).openPopup();
    });
</script>
@endif
@endpush
