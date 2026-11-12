<!DOCTYPE html>
<html lang="id" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Marketplace Pemanfaatan BMD Pemprov Jawa Barat') - UPTD Pengamanan dan Pemanfaatan Aset</title>

    <!-- Favicons: Logo Resmi Pemerintah Provinsi Jawa Barat -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo-jabar.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo-jabar.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-jabar.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

    <!-- Leaflet GIS CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Tailwind CSS (Compiled via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    </style>

    @stack('styles')
</head>
<body class="flex flex-col min-h-screen text-gray-900 antialiased selection:bg-emerald-600 selection:text-white bg-[#F9FAFB]">

    <!-- Top Official Announcement Bar (Fresh & Light) -->
    <div class="bg-emerald-50/90 text-emerald-950 text-xs py-2 px-4 border-b border-emerald-200/60 relative z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-2">
            <div class="flex items-center gap-2.5">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-600 text-white shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                    Portal Resmi
                </span>
                <span class="font-bold text-emerald-900 hidden sm:inline">Pemerintah Daerah Provinsi Jawa Barat &bull; BPKAD</span>
                <span class="text-emerald-400 hidden sm:inline">&bull;</span>
                <span class="text-emerald-800 font-medium truncate">Layanan Pemanfaatan Barang Milik Daerah (PP No. 28/2020 & Permendagri No. 7/2024)</span>
            </div>

            <div class="flex items-center gap-4 text-xs font-semibold text-emerald-800">
                <a href="https://wa.me/6281234567890?text=Halo%20Admin%20UPTD%20Pemanfaatan%20Aset%20Jabar,%20saya%20ingin%20konsultasi%20sewa%20BMD" target="_blank" rel="noopener noreferrer" class="hover:text-emerald-950 transition flex items-center gap-1.5 text-emerald-700">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <span>Helpdesk WA UPTD</span>
                </a>
                <span class="text-emerald-300">|</span>
                <a href="{{ url('/admin') }}" class="hover:text-emerald-950 transition flex items-center gap-1 font-bold text-emerald-900">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Masuk Petugas</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Behance Clean White Aesthetic) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b border-gray-200/80 shadow-2xs transition-all duration-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                
                <!-- Brand / Logo UPTD -->
                <a href="{{ route('home') }}" class="flex items-center gap-3.5 group">
                    <img src="{{ asset('images/logo-jabar.svg') }}" alt="Logo Pemerintah Provinsi Jawa Barat" class="h-11 w-auto object-contain group-hover:scale-105 transition-transform duration-300 drop-shadow-xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-xl tracking-tight text-gray-900 group-hover:text-emerald-600 transition">SIP-BMD</span>
                            <span class="text-[10px] font-extrabold tracking-wider px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">JABAR</span>
                        </div>
                        <p class="text-xs text-gray-500 font-semibold tracking-tight">UPTD Pengamanan dan Pemanfaatan Aset</p>
                    </div>
                </a>

                <!-- Desktop Navigation Links (Behance Horizontal Tabs) -->
                <div class="hidden md:flex items-center gap-1.5 bg-gray-100/80 p-1.5 rounded-full border border-gray-200/70">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 {{ request()->routeIs('home') ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                        Beranda
                    </a>
                    <a href="{{ route('catalog.index') }}" class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 {{ request()->routeIs('catalog.index') && !request('status') && !request()->routeIs('catalog.siap-sewa') ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                        Katalog Aset
                    </a>
                    <a href="{{ route('catalog.siap-sewa') }}" class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 {{ (request()->routeIs('catalog.siap-sewa') || request('status') === 'tersedia') ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>Aset Siap Sewa
                    </a>
                    <a href="{{ route('regulasi.index') }}" class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 {{ request()->routeIs('regulasi.*') ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                        Dasar Hukum
                    </a>
                    @auth
                        <a href="{{ route('booking.my') }}" class="px-4 py-2 rounded-full text-xs font-bold transition-all duration-200 {{ request()->routeIs('booking.*') ? 'bg-white text-emerald-700 shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                            Pengajuan Saya
                        </a>
                    @endauth
                </div>

                <!-- Call To Action & Auth Buttons -->
                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-all duration-200">
                            <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>Masuk</span>
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-extrabold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all duration-200 active:scale-[0.98]">
                            <span>Daftar Akun</span>
                        </a>
                    @else
                        <!-- Logged-in User Profile Pill -->
                        <div class="inline-flex items-center gap-2 pl-3 pr-1 py-1 rounded-full bg-gray-100 border border-gray-200/80">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="text-xs font-extrabold text-gray-800 max-w-[120px] truncate" title="{{ Auth::user()->name }}">
                                    {{ Auth::user()->name }}
                                </span>
                            </div>

                            @if(Auth::user()->isAdmin())
                                <a href="{{ url('/admin') }}" class="px-2.5 py-1 rounded-full bg-emerald-600 text-white text-[10px] font-bold hover:bg-emerald-700 transition" title="Buka Panel Admin">
                                    Admin
                                </a>
                            @endif

                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1 rounded-full text-gray-400 hover:text-rose-600 hover:bg-white transition" title="Keluar Akun">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                        </div>
                    @endguest
                </div>
            </div>
        </nav>
    </header>

    <!-- Global Flash Notification Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 mb-4 flex items-start gap-3.5 text-emerald-900 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div class="pt-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-800">Berhasil</h4>
                    <p class="text-sm font-medium leading-relaxed mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 mb-4 flex items-start gap-3.5 text-rose-900 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div class="pt-1">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-rose-800">Perhatian</h4>
                    <p class="text-sm font-medium leading-relaxed mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Main Content Body -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Modern Clean Studio Footer (Behance Style: Light, Crisp & Sophisticated) -->
    <footer class="bg-white text-gray-600 mt-24 border-t border-gray-200/90 relative overflow-hidden">
        <!-- Top Thin Accent Line -->
        <div class="h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-amber-400"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-10 lg:gap-12">
                
                <!-- Col 1: Brand & Identity (5 cols) -->
                <div class="md:col-span-5">
                    <div class="flex items-center gap-3.5 mb-5">
                        <img src="{{ asset('images/logo-jabar.svg') }}" alt="Logo Pemerintah Provinsi Jawa Barat" class="h-12 w-auto object-contain">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900 font-extrabold text-lg tracking-tight">SIP-BMD JABAR</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">UPTD</span>
                            </div>
                            <p class="text-xs text-gray-500 font-medium">UPTD Pengamanan dan Pemanfaatan Aset</p>
                        </div>
                    </div>

                    <p class="text-sm text-gray-600 leading-relaxed max-w-md mb-6 font-normal">
                        Platform resmi digitalisasi dan fasilitasi kerjasama pemanfaatan Barang Milik Daerah (BMD) Pemerintah Provinsi Jawa Barat. Mewujudkan tata kelola aset yang akuntabel, transparan, dan berdampak pada peningkatan Pendapatan Asli Daerah (PAD).
                    </p>

                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200/80 max-w-md">
                        <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            <span>Landasan Hukum Pelayanan</span>
                        </div>
                        <p class="text-[11px] text-gray-500 leading-relaxed">
                            PP No. 27/2014 jo. PP No. 28/2020 tentang Pengelolaan BMD dan Permendagri No. 19/2016 jo. Permendagri No. 7/2024 tentang Pedoman Pengelolaan BMD.
                        </p>
                    </div>
                </div>

                <!-- Col 2: Navigasi Cepat (3 cols) -->
                <div class="md:col-span-3">
                    <h3 class="text-gray-900 font-bold text-xs uppercase tracking-wider mb-5 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Navigasi Layanan
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600 font-medium">
                        <li>
                            <a href="{{ route('catalog.index') }}" class="hover:text-emerald-700 transition flex items-center gap-2">
                                <span class="text-gray-400">&rsaquo;</span> Katalog Lengkap Aset
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('catalog.index', ['status' => 'tersedia']) }}" class="hover:text-emerald-700 transition flex items-center gap-2">
                                <span class="text-gray-400">&rsaquo;</span> Aset Siap Disewa (Tersedia)
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('regulasi.index') }}" class="hover:text-emerald-700 transition flex items-center gap-2">
                                <span class="text-gray-400">&rsaquo;</span> Dasar Hukum & Regulasi BMD
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('booking.my') }}" class="hover:text-emerald-700 transition flex items-center gap-2">
                                <span class="text-gray-400">&rsaquo;</span> Pengajuan Sewa Saya
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/admin') }}" class="hover:text-emerald-700 transition flex items-center gap-2">
                                <span class="text-gray-400">&rsaquo;</span> Backoffice Petugas UPTD
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Col 3: Kantor & Helpdesk (4 cols) -->
                <div class="md:col-span-4">
                    <h3 class="text-gray-900 font-bold text-xs uppercase tracking-wider mb-5 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        Kantor & Konsultasi
                    </h3>
                    
                    <div class="space-y-4 text-sm text-gray-600">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 shrink-0 mt-0.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-xs leading-relaxed">
                                <strong class="text-gray-900 block font-bold mb-0.5">UPTD Pengamanan & Pemanfaatan Aset</strong>
                                Jl. Kawaluyaan No. 4, Jatisari, Kec. Buahbatu, Kota Bandung, Jawa Barat 40286
                            </p>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                            </div>
                            <div class="text-xs leading-relaxed">
                                <strong class="text-gray-900 block font-bold mb-0.5">Layanan Fast-Track WhatsApp:</strong>
                                <a href="https://wa.me/6281234567890" target="_blank" rel="noopener noreferrer" class="text-emerald-600 font-extrabold hover:underline">
                                    +62 812-3456-7890
                                </a>
                                <span class="text-gray-400 block text-[11px]">Senin – Jumat: 08.00 – 16.00 WIB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-8 mt-12 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center text-xs text-gray-500 gap-3">
                <p>&copy; {{ date('Y') }} Pemerintah Daerah Provinsi Jawa Barat. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-4 text-[11px] text-gray-500 font-medium">
                    <span>UPTD Pengamanan dan Pemanfaatan Aset</span>
                    <span>&bull;</span>
                    <span>BPKAD Provinsi Jawa Barat</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @stack('scripts')
</body>
</html>
