@extends('layouts.app')

@section('title', ($isSiapSewa ?? false) ? 'Aset Siap Sewa BMD Jawa Barat - Marketplace Pemanfaatan' : 'Katalog Aset BMD Jawa Barat - Marketplace Pemanfaatan')

@section('content')
<!-- Page Header (Behance Style: Ultra-Clean, Light & Modern) -->
<div class="relative bg-mesh-light text-gray-900 py-12 lg:py-16 border-b border-gray-200/80 overflow-hidden">
    <!-- Ambient Blur Glows -->
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div>
                <nav class="flex items-center gap-2 text-xs text-gray-500 mb-3 font-semibold">
                    <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('catalog.index') }}" class="hover:text-emerald-700 transition">Katalog Aset</a>
                    @if($isSiapSewa ?? false)
                        <span class="text-gray-300">/</span>
                        <span class="text-emerald-700 font-extrabold">Aset Siap Sewa</span>
                    @endif
                </nav>

                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-3 border border-emerald-200/80 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    @if($isSiapSewa ?? false)
                        Inventaris Siap Kerjasama & Bebas Sewa
                    @else
                        Eksplorasi Ruang & Lahan Pemerintah Daerah
                    @endif
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900">
                    @if($isSiapSewa ?? false)
                        Katalog Aset Daerah Siap Sewa
                    @else
                        Katalog Pemanfaatan Aset Daerah
                    @endif
                </h1>

                <p class="text-sm sm:text-base text-gray-600 mt-2 max-w-2xl font-normal leading-relaxed">
                    @if($isSiapSewa ?? false)
                        Daftar objek tanah, gedung kantor, dan ruang usaha milik Pemprov Jawa Barat yang berstatus <strong class="text-emerald-700 font-bold">Tersedia</strong> dan siap langsung dikerjasamakan untuk investasi bisnis Anda.
                    @else
                        Telusuri seluruh objek tanah, gedung kantor, dan ruang usaha milik Pemprov Jawa Barat yang terdata resmi dan siap dikerjasamakan untuk investasi bisnis Anda.
                    @endif
                </p>
            </div>

            <!-- Toggle Map & View Mode Button -->
            <div class="flex items-center gap-2.5 bg-white p-1.5 rounded-full border border-gray-200/90 shadow-2xs self-start md:self-auto shrink-0">
                <button id="toggle-map-btn" type="button" class="px-5 py-2.5 rounded-full text-xs font-extrabold transition-all duration-200 flex items-center gap-2 bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                    <span>Peta GIS Leaflet</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-800/80 text-[10px] text-white font-extrabold">{{ $mapAssets->count() }} Titik</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Main Catalog Container -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Interactive Leaflet Map Section (Togglable) -->
    <div id="map-container" class="mb-10 bg-white rounded-3xl shadow-lg shadow-gray-200/50 border border-gray-200/90 overflow-hidden transition-all duration-300">
        <div class="p-4 sm:p-5 bg-gray-50/80 border-b border-gray-200/80 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-gray-900">
                        @if($isSiapSewa ?? false)
                            Peta Interaktif Sebaran Aset BMD Siap Sewa
                        @else
                            Peta Interaktif Sebaran Aset BMD Jabar
                        @endif
                    </h3>
                    <p class="text-xs text-gray-500 font-medium">
                        @if($isSiapSewa ?? false)
                            Menampilkan titik lokasi aset yang saat ini siap disewa. Gunakan scroll mouse untuk zoom, dan sorot/klik titik untuk melihat spesifikasi serta opsi sewa.
                        @else
                            Menampilkan seluruh sebaran aset BMD. Gunakan scroll mouse untuk zoom, dan sorot/klik titik untuk melihat detail nama objek, status, dan tombol detail lengkap.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Legend Status Badges -->
            <div class="flex items-center gap-2.5 text-xs font-bold">
                @if($isSiapSewa ?? false)
                    <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        <span>Aset Siap Sewa ({{ $mapAssets->count() }} Lokasi Tersedia)</span>
                    </div>
                @else
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                        <span>Tersedia Disewa</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>Proses Booking</span>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 text-gray-700 border border-gray-300">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-600"></span>
                        <span>Tersewa Aktif</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Map Container Element -->
        <div id="leaflet-map" class="h-[420px] sm:h-[480px] w-full z-10"></div>
    </div>

    <!-- Filter & Search Toolbar Card (Behance Floating Pill Style) -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-md shadow-gray-200/60 border border-gray-200/90 mb-10">
        <form action="{{ ($isSiapSewa ?? false) ? route('catalog.siap-sewa') : route('catalog.index') }}" method="GET" class="space-y-4">
            
            @if($isSiapSewa ?? false)
                <input type="hidden" name="status" value="tersedia">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                
                <!-- Search Keyword -->
                <div class="{{ ($isSiapSewa ?? false) ? 'lg:col-span-6' : 'lg:col-span-4' }}">
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Kata Kunci / Lokasi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cth: Caringin, Diponegoro, gudang..." class="w-full pl-10 pr-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Kabupaten / Kota Filter -->
                <div class="{{ ($isSiapSewa ?? false) ? 'lg:col-span-3' : 'lg:col-span-3' }}">
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Kabupaten / Kota</label>
                    <select name="kabupaten_kota" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                        <option value="">Semua Wilayah Jabar (27 Kab/Kota)</option>
                        @foreach($regencies as $reg)
                            <option value="{{ $reg }}" {{ request('kabupaten_kota') == $reg ? 'selected' : '' }}>{{ $reg }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Bentuk Objek Filter -->
                <div class="{{ ($isSiapSewa ?? false) ? 'lg:col-span-3' : 'lg:col-span-2' }}">
                    <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Bentuk Objek</label>
                    <select name="bentuk_objek" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                        <option value="">Semua Bentuk</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('bentuk_objek') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Ketersediaan (Only on General Catalog) -->
                @if(!($isSiapSewa ?? false))
                    <div class="lg:col-span-3">
                        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Status Ketersediaan</label>
                        <select name="status" class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia Disewa</option>
                            <option value="proses_booking" {{ request('status') == 'proses_booking' ? 'selected' : '' }}>Proses Booking</option>
                            <option value="tersewa" {{ request('status') == 'tersewa' ? 'selected' : '' }}>Tersewa (Kontrak Aktif)</option>
                        </select>
                    </div>
                @endif

            </div>

            <!-- Bottom Row: Sort & Buttons -->
            <div class="pt-3 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 font-semibold">Urutkan:</span>
                    <select name="sort" onchange="this.form.submit()" class="px-3.5 py-1.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-bold text-gray-700 focus:outline-none focus:ring-1 focus:ring-emerald-500 cursor-pointer">
                        <option value="terbaru" {{ request('sort') == 'terbaru' ? 'selected' : '' }}>Terbaru Terdaftar</option>
                        <option value="luas_terbesar" {{ request('sort') == 'luas_terbesar' ? 'selected' : '' }}>Luas Terbesar</option>
                        <option value="luas_terkecil" {{ request('sort') == 'luas_terkecil' ? 'selected' : '' }}>Luas Terkecil</option>
                        <option value="tarif_terendah" {{ request('sort') == 'tarif_terendah' ? 'selected' : '' }}>Tarif Terendah</option>
                        <option value="tarif_tertinggi" {{ request('sort') == 'tarif_tertinggi' ? 'selected' : '' }}>Tarif Tertinggi</option>
                    </select>
                    <span class="text-gray-300">|</span>
                    <span class="text-emerald-800 font-extrabold bg-emerald-50 px-2.5 py-0.5 rounded-full">Total: {{ $assets->total() }} aset</span>
                    @if($isSiapSewa ?? false)
                        <span class="text-emerald-700 font-bold bg-emerald-100/70 px-2.5 py-0.5 rounded-full text-[11px]">Mode: Siap Sewa</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @if(request()->hasAny(['q', 'kabupaten_kota', 'bentuk_objek', 'status', 'sort']))
                        <a href="{{ ($isSiapSewa ?? false) ? route('catalog.siap-sewa') : route('catalog.index') }}" class="px-4 py-2 rounded-xl text-gray-600 bg-gray-100 hover:bg-gray-200 transition font-bold">
                            Reset Filter
                        </a>
                    @endif
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold shadow-sm transition active:scale-95 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Assets Cards Grid (Behance Showcase Grid) -->
    @if($assets->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200 max-w-md mx-auto my-12 shadow-sm">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-extrabold text-gray-900 mb-1">Objek Aset Tidak Ditemukan</h3>
            <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                @if($isSiapSewa ?? false)
                    Tidak ada objek aset siap sewa yang sesuai dengan filter pencarian Anda.
                @else
                    Tidak ada objek BMD yang cocok dengan filter pencarian Anda.
                @endif
            </p>
            <a href="{{ ($isSiapSewa ?? false) ? route('catalog.siap-sewa') : route('catalog.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-full text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm">
                Tampilkan Semua
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($assets as $asset)
                <div class="behance-card rounded-3xl overflow-hidden flex flex-col group">
                    
                    <!-- Thumbnail with 16:10 Ratio -->
                    <div class="relative aspect-[16/10] bg-gray-100 overflow-hidden">
                        <img src="{{ $asset->thumbnail_url }}" alt="{{ $asset->nama_objek }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-950/70 via-transparent to-transparent pointer-events-none"></div>

                        <!-- Top Status Badge -->
                        <div class="absolute top-3 left-3 z-10">
                            @if($asset->status_ketersediaan === 'tersedia')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-xs">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Tersedia
                                </span>
                            @elseif($asset->status_ketersediaan === 'proses_booking')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs">
                                    Proses Booking
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-gray-800 text-white shadow-xs">
                                    Tersewa
                                </span>
                            @endif
                        </div>

                        <!-- Shape Tag -->
                        <div class="absolute top-3 right-3 z-10">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/95 backdrop-blur-md text-gray-800 shadow-2xs border border-white/60">
                                {{ $asset->bentuk_objek }}
                            </span>
                        </div>

                        <!-- Price Tag Overlay -->
                        <div class="absolute bottom-3 inset-x-3 z-10 text-white">
                            <span class="text-[9px] text-gray-200 uppercase font-bold tracking-wider block">Tarif Retribusi Sewa</span>
                            <div class="text-sm font-extrabold truncate text-white">
                                @if($asset->estimasi_tarif_tahunan)
                                    Rp {{ number_format($asset->estimasi_tarif_tahunan, 0, ',', '.') }} <span class="text-[10px] font-normal text-gray-200">/ thn</span>
                                @else
                                    <span class="text-xs font-semibold text-amber-200">Sesuai Penilaian Resmi</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-grow flex flex-col justify-between">
                        <div>
                            <!-- City -->
                            <div class="flex items-center gap-1 text-[11px] text-emerald-700 font-bold mb-1.5 truncate">
                                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $asset->kabupaten_kota }}</span>
                            </div>

                            <!-- Name -->
                            <h3 class="font-extrabold text-sm text-gray-900 group-hover:text-emerald-600 transition line-clamp-2 leading-snug mb-3 min-h-[2.5rem]">
                                <a href="{{ route('catalog.show', $asset->slug) }}">
                                    {{ $asset->nama_objek }}
                                </a>
                            </h3>

                            <!-- Specs Chips -->
                            <div class="grid grid-cols-2 gap-1.5 text-[11px] py-2.5 border-y border-gray-100 text-gray-600 mb-3 bg-gray-50 rounded-2xl px-2.5">
                                <div>
                                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Luas Tanah:</span>
                                    <span class="font-extrabold text-gray-900">{{ $asset->luas_tanah ? number_format($asset->luas_tanah, 0, ',', '.') . ' m²' : '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400 text-[10px] uppercase font-bold block">Luas Bgn:</span>
                                    <span class="font-extrabold text-gray-900">{{ $asset->luas_bangunan ? number_format($asset->luas_bangunan, 0, ',', '.') . ' m²' : '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons Action -->
                        <div class="pt-2 flex items-center gap-2">
                            <a href="{{ route('catalog.show', $asset->slug) }}" class="flex-grow py-2 px-3 rounded-full text-center text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition">
                                Detail Aset
                            </a>
                            <a href="{{ route('catalog.show', $asset->slug) }}#form-booking" class="py-2 px-3 rounded-full text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 transition flex items-center justify-center shrink-0 shadow-xs" title="Ajukan Minat Sewa">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </a>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Pagination Section -->
        <div class="mt-12">
            {{ $assets->links() }}
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('toggle-map-btn');
        const mapContainer = document.getElementById('map-container');
        const isSiapSewaMode = @json($isSiapSewa ?? false);
        
        let map = null;

        function initLeafletMap() {
            if (map) return;

            // Default viewpoint: centered at Bandung & Jawa Barat with scroll wheel zoom enabled
            map = L.map('leaflet-map', {
                scrollWheelZoom: true,
                wheelDebounceTime: 40,
                wheelPxPerZoomLevel: 60
            }).setView([-6.917464, 107.619122], 9);

            window.leafletMap = map;

            // OpenStreetMap Tile Layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap kontributor | BPKAD Provinsi Jawa Barat'
            }).addTo(map);

            const mapAssets = @json($mapAssets);
            const bounds = L.latLngBounds();
            let validPointsCount = 0;

            mapAssets.forEach(item => {
                const lat = parseFloat(item.latitude || item.lat);
                const lng = parseFloat(item.longitude || item.lng);

                if (!isNaN(lat) && !isNaN(lng)) {
                    validPointsCount++;
                    bounds.extend([lat, lng]);

                    const statusKey = (item.status_ketersediaan || item.status || 'tersedia').toLowerCase();
                    
                    // Status style configuration
                    let statusConfig = {
                        color: '#059669', // Emerald
                        label: item.status_label || 'Tersedia Disewa',
                        badgeBg: '#ECFDF5',
                        badgeText: '#065F46',
                        badgeBorder: '#A7F3D0',
                        isAvailable: true
                    };

                    if (statusKey === 'proses_booking') {
                        statusConfig = {
                            color: '#D97706', // Amber
                            label: item.status_label || 'Sedang Proses Booking',
                            badgeBg: '#FFFBEB',
                            badgeText: '#92400E',
                            badgeBorder: '#FDE68A',
                            isAvailable: false
                        };
                    } else if (statusKey === 'tersewa') {
                        statusConfig = {
                            color: '#475569', // Slate
                            label: item.status_label || 'Tersewa Aktif (PKS)',
                            badgeBg: '#F1F5F9',
                            badgeText: '#334155',
                            badgeBorder: '#CBD5E1',
                            isAvailable: false
                        };
                    }

                    // Eye-catching custom marker with pulse on available assets
                    const markerHtml = `
                        <div class="bmd-marker-wrapper" style="width: 26px; height: 26px;">
                            ${statusConfig.isAvailable ? `<div class="bmd-marker-pulse" style="background-color: ${statusConfig.color};"></div>` : ''}
                            <div style="background-color: ${statusConfig.color}; width: 22px; height: 22px; border-radius: 50%; border: 3px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.28); display: flex; align-items: center; justify-content: center; position: relative; z-index: 2;">
                                <div style="background-color: #ffffff; width: 6px; height: 6px; border-radius: 50%;"></div>
                            </div>
                        </div>
                    `;

                    const customIcon = L.divIcon({
                        className: 'custom-bmd-marker-icon',
                        html: markerHtml,
                        iconSize: [26, 26],
                        iconAnchor: [13, 13],
                        popupAnchor: [0, -14]
                    });

                    // Build Popup Content based on current menu context
                    let popupHtml = '';

                    if (isSiapSewaMode) {
                        // Khusus Menu Aset Siap Sewa: Detail titik berfokus pada ketersediaan, kepastian hukum, dan aksi sewa langsung
                        popupHtml = `
                            <div class="p-4 sm:p-5 max-w-[290px] sm:max-w-xs text-gray-900 font-sans">
                                <!-- Top Highlights Header -->
                                <div class="mb-2.5 flex items-center justify-between gap-1.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                        Siap Sewa
                                    </span>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full">
                                        Bebas Kontrak
                                    </span>
                                </div>

                                <!-- Asset Title -->
                                <h4 class="font-extrabold text-sm leading-snug mb-1.5 text-gray-900 line-clamp-2">
                                    ${item.nama_objek || item.nama}
                                </h4>

                                <!-- Location & Shape -->
                                <p class="text-[11px] text-gray-500 mb-3 flex items-center gap-1 font-semibold">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>${item.kabupaten_kota || item.lokasi} &bull; ${item.bentuk_objek || item.bentuk}</span>
                                </p>

                                <!-- Mini Specs -->
                                <div class="bg-gray-50 rounded-xl p-2.5 mb-3.5 border border-gray-100 grid grid-cols-2 gap-1.5 text-[10px]">
                                    <div>
                                        <span class="text-gray-400 block font-bold uppercase">Luas:</span>
                                        <span class="font-extrabold text-gray-800">${item.luas_tanah || item.luas || '-'}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block font-bold uppercase">Tarif Retribusi:</span>
                                        <span class="font-extrabold text-emerald-700">${item.estimasi_tarif || item.tarif || 'Penilaian Resmi'}</span>
                                    </div>
                                </div>

                                <!-- Dual Action Buttons: Detail & Ajukan Sewa -->
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="${item.url}" class="py-2 px-3 text-center rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs transition flex items-center justify-center">
                                        <span>Detail Aset</span>
                                    </a>
                                    <a href="${item.booking_url || (item.url + '#form-booking')}" class="py-2 px-3 text-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-1 group">
                                        <span>Ajukan Sewa</span>
                                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                </div>
                            </div>
                        `;
                    } else {
                        // Menu Katalog Aset: Menampilkan status masing-masing objek (Tersedia, Booking, Tersewa)
                        popupHtml = `
                            <div class="p-4 sm:p-5 max-w-[280px] sm:max-w-xs text-gray-900 font-sans">
                                <!-- Status Pill Badge -->
                                <div class="mb-2.5">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wide border shadow-2xs" style="background-color: ${statusConfig.badgeBg}; color: ${statusConfig.badgeText}; border-color: ${statusConfig.badgeBorder};">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: ${statusConfig.color};"></span>
                                        ${statusConfig.label}
                                    </span>
                                </div>

                                <!-- Asset Title -->
                                <h4 class="font-extrabold text-sm leading-snug mb-1.5 text-gray-900 line-clamp-2">
                                    ${item.nama_objek || item.nama}
                                </h4>

                                <!-- Location & Shape -->
                                <p class="text-[11px] text-gray-500 mb-3 flex items-center gap-1 font-semibold">
                                    <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>${item.kabupaten_kota || item.lokasi} &bull; ${item.bentuk_objek || item.bentuk}</span>
                                </p>

                                <!-- Mini Specs -->
                                <div class="bg-gray-50 rounded-xl p-2.5 mb-3.5 border border-gray-100 grid grid-cols-2 gap-1.5 text-[10px]">
                                    <div>
                                        <span class="text-gray-400 block font-bold uppercase">Luas:</span>
                                        <span class="font-extrabold text-gray-800">${item.luas_tanah || item.luas || '-'}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-400 block font-bold uppercase">Estimasi Sewa:</span>
                                        <span class="font-extrabold text-emerald-700">${item.estimasi_tarif || item.tarif || 'Penilaian Resmi'}</span>
                                    </div>
                                </div>

                                <!-- Detail Action Button -->
                                <a href="${item.url}" class="block w-full py-2.5 px-4 text-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-1.5 group">
                                    <span>Lihat Detail Lengkap</span>
                                    <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </a>
                            </div>
                        `;
                    }

                    const marker = L.marker([lat, lng], { icon: customIcon })
                        .addTo(map)
                        .bindPopup(popupHtml, {
                            closeButton: true,
                            autoPan: true,
                            offset: [0, -10]
                        });

                    // Interaksi: sorot (hover / mouseover) membuka popup otomatis
                    marker.on('mouseover', function () {
                        this.openPopup();
                    });

                    // Interaksi: klik titik marker membuka popup & memusatkan tampilan ke titik lokasi
                    marker.on('click', function () {
                        this.openPopup();
                        map.panTo([lat, lng], { animate: true, duration: 0.4 });
                    });
                }
            });

            // Auto fit map view to bounds if points exist
            if (validPointsCount > 0) {
                map.fitBounds(bounds, {
                    padding: [45, 45],
                    maxZoom: 13
                });
            }
        }

        initLeafletMap();

        if (toggleBtn && mapContainer) {
            toggleBtn.addEventListener('click', function () {
                if (mapContainer.classList.contains('hidden')) {
                    mapContainer.classList.remove('hidden');
                    setTimeout(() => { if (map) map.invalidateSize(); }, 200);
                } else {
                    mapContainer.classList.add('hidden');
                }
            });
        }
    });
</script>
@endpush
