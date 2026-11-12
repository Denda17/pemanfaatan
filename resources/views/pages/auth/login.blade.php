@extends('layouts.app')

@section('title', 'Masuk Akun Terpadu - SIP-BMD Jabar')

@section('content')
<div class="relative bg-mesh-light min-h-[calc(100vh-160px)] py-12 lg:py-16 overflow-hidden flex items-center justify-center">
    <!-- Ambient Blur Glows -->
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full mx-auto px-4 sm:px-6 relative z-10">
        
        <!-- Breadcrumb & Header Title -->
        <div class="text-center mb-8">
            <nav class="flex items-center justify-center gap-2 text-xs text-gray-500 mb-3 font-semibold">
                <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                <span class="text-gray-300">/</span>
                <span class="text-emerald-700 font-extrabold">Masuk Akun</span>
            </nav>

            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-3 border border-emerald-200/80 shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Portal Masuk Terpadu
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
                Masuk ke Akun Anda
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-2 leading-relaxed">
                Satu pintu masuk untuk Calon Mitra Sewa BMD dan Petugas Administrator UPTD. Sistem akan otomatis mendeteksi peran akun Anda.
            </p>
        </div>

        <!-- Success Banner from Registration Redirect -->
        @if (session('success'))
            <div class="mb-6 p-4 rounded-3xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs shadow-xs flex items-start gap-3">
                <div class="w-7 h-7 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <h4 class="font-extrabold text-emerald-950 mb-0.5">Pendaftaran Akun Berhasil!</h4>
                    <p class="text-emerald-800 font-medium leading-relaxed">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs shadow-xs flex items-center gap-2.5">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- Login Form Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 md:p-9 border border-gray-200/90 shadow-xl shadow-gray-200/50">
            
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Gagal Masuk:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1 font-medium text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email', session('registered_email')) }}" required autofocus placeholder="nama@domain.com" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700">
                            Kata Sandi
                        </label>
                    </div>
                    <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 text-gray-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                        <span class="font-medium text-[11px]">Ingat sesi saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Role Direction Notice -->
                <div class="p-3 rounded-2xl bg-emerald-50/50 border border-emerald-100 text-[11px] text-emerald-900 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="leading-relaxed">
                        Akun <strong>Admin UPTD</strong> akan otomatis diarahkan ke Panel Dashboard, sedangkan akun <strong>Calon Mitra</strong> akan diarahkan ke halaman Pengajuan Saya.
                    </span>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full py-3.5 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-extrabold text-xs sm:text-sm shadow-md shadow-emerald-600/25 transition flex items-center justify-center gap-2 mt-2">
                    <span>Masuk ke Akun</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>

                <!-- Footer Link to Register -->
                <div class="text-center pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 font-medium">
                        Belum memiliki akun pemohon?
                        <a href="{{ route('register') }}" class="font-extrabold text-emerald-700 hover:text-emerald-800 underline ml-1">
                            Daftar Akun Baru &rsaquo;
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Helpdesk Info Note -->
        <div class="mt-6 text-center text-xs text-gray-400">
            <span>Mengalami kendala login? Hubungi </span>
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20UPTD,%20saya%20mengalami%20kendala%20masuk%20ke%20akun%20portal%20pemanfaatan" target="_blank" class="text-emerald-700 font-bold hover:underline">
                Helpdesk UPTD WhatsApp
            </a>
        </div>

    </div>
</div>
@endsection
