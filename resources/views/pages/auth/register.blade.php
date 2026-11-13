@extends('layouts.app')

@section('title', 'Daftar Akun Calon Mitra - Marketplace Pemanfaatan BMD Jabar')

@section('content')
<div class="relative bg-mesh-light min-h-[calc(100vh-160px)] py-12 lg:py-16 overflow-hidden flex items-center justify-center">
    <!-- Ambient Blur Glows -->
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-2xl w-full mx-auto px-4 sm:px-6 relative z-10">
        
        <!-- Breadcrumb & Header Title -->
        <div class="text-center mb-8">
            <nav class="flex items-center justify-center gap-2 text-xs text-gray-500 mb-3 font-semibold">
                <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                <span class="text-gray-300">/</span>
                <span class="text-emerald-700 font-extrabold">Pendaftaran Akun</span>
            </nav>

            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-3 border border-emerald-200/80 shadow-2xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Registrasi Calon Mitra / Pemohon Sewa BMD
            </div>

            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-gray-900">
                Buat Akun Portal Pemanfaatan
            </h1>
            <p class="text-xs sm:text-sm text-gray-600 mt-2 max-w-md mx-auto leading-relaxed">
                Daftarkan akun resmi Anda untuk mulai mengajukan minat sewa dan pemanfaatan aset Pemprov Jawa Barat.
            </p>
        </div>

        <!-- Registration Form Card -->
        <div class="bg-white rounded-3xl p-6 sm:p-8 md:p-10 border border-gray-200/90 shadow-xl shadow-gray-200/50">
            
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Terdapat kesalahan pada data yang Anda isi:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-1 font-medium text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST" id="register-form" class="space-y-5">
                @csrf

                <!-- Nama Lengkap & WhatsApp -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Contoh: Dedi Koswara" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                            Nomor WhatsApp Aktif <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="Contoh: 081234567890" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                        <span class="text-[10px] text-gray-400 mt-1 block font-medium">Petugas UPTD akan berkoordinasi via nomor ini.</span>
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                        Alamat Email Resmi <span class="text-rose-500">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@domain.com (digunakan untuk masuk akun)" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                </div>

                <!-- Password & Confirmation -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                            Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-extrabold uppercase tracking-wider text-gray-700 mb-1.5">
                            Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Ulangi kata sandi" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                    </div>
                </div>

                <!-- Terms & Disclaimer -->
                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-200 text-xs text-gray-700 flex items-start gap-3 transition hover:bg-emerald-50/30 hover:border-emerald-200">
                    <input type="checkbox" id="agree" name="agree" value="1" {{ old('agree') ? 'checked' : '' }} class="mt-0.5 w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-gray-300 cursor-pointer shrink-0">
                    <label for="agree" class="text-xs leading-relaxed select-none cursor-pointer">
                        <span class="font-extrabold text-gray-900 block mb-0.5">Pernyataan Keabsahan Data & Akun <span class="text-rose-500">*</span></span>
                        <span class="text-gray-600 text-[11px] block">
                            Saya menyatakan bahwa data kontak dan informasi akun yang saya daftarkan adalah benar, aktif, dan dapat dipertanggungjawabkan untuk pemanfaatan Barang Milik Daerah (BMD) Provinsi Jawa Barat.
                        </span>
                    </label>
                </div>

                <!-- Submit Button with Dynamic Disabled State -->
                <div class="space-y-2">
                    <button type="submit" id="btn-submit-register" disabled class="w-full py-3.5 px-6 rounded-2xl font-extrabold text-xs sm:text-sm transition flex items-center justify-center gap-2 bg-gray-200 text-gray-400 cursor-not-allowed pointer-events-none shadow-none">
                        <span>Daftar Akun Sekarang</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                    <p id="agree-hint" class="text-[11px] text-amber-700 font-semibold text-center flex items-center justify-center gap-1.5 transition">
                        <svg class="w-3.5 h-3.5 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Centang pernyataan keabsahan di atas untuk mengaktifkan tombol pendaftaran.</span>
                    </p>
                </div>

                <!-- Footer Link to Login -->
                <div class="text-center pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-500 font-medium">
                        Sudah memiliki akun terdaftar? 
                        <a href="{{ route('login') }}" class="font-extrabold text-emerald-700 hover:text-emerald-800 underline ml-1">
                            Masuk ke Akun Anda &rsaquo;
                        </a>
                    </p>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateSubmitButtonState() {
        const agreeCheckbox = document.getElementById('agree');
        const submitBtn = document.getElementById('btn-submit-register');
        const hintText = document.getElementById('agree-hint');

        if (!agreeCheckbox || !submitBtn) return;

        if (agreeCheckbox.checked) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('bg-gray-200', 'text-gray-400', 'cursor-not-allowed', 'pointer-events-none', 'shadow-none');
            submitBtn.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'active:scale-[0.99]', 'text-white', 'cursor-pointer', 'shadow-md', 'shadow-emerald-600/25');
            if (hintText) hintText.classList.add('hidden');
        } else {
            submitBtn.disabled = true;
            submitBtn.classList.add('bg-gray-200', 'text-gray-400', 'cursor-not-allowed', 'pointer-events-none', 'shadow-none');
            submitBtn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'active:scale-[0.99]', 'text-white', 'cursor-pointer', 'shadow-md', 'shadow-emerald-600/25');
            if (hintText) hintText.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const agreeCheckbox = document.getElementById('agree');
        if (agreeCheckbox) {
            agreeCheckbox.addEventListener('change', updateSubmitButtonState);
            updateSubmitButtonState();
        }
    });
</script>
@endpush
