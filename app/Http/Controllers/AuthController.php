<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman pendaftaran akun pemohon.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('catalog.index');
        }

        return view('pages.auth.register');
    }

    /**
     * Proses pendaftaran akun baru bagi pemohon/calon mitra.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tipe_pemohon' => ['required', 'in:perorangan,badan_usaha'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:25'],
            'nik' => ['nullable', 'string', 'max:20', 'required_if:tipe_pemohon,perorangan'],
            'instansi' => ['nullable', 'string', 'max:255', 'required_if:tipe_pemohon,badan_usaha'],
            'nib' => ['nullable', 'string', 'max:30'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'agree' => ['required', 'accepted'],
        ], [
            'tipe_pemohon.required' => 'Pilih kategori pemohon (Perorangan atau Badan Usaha).',
            'name.required' => 'Nama lengkap penanggung jawab wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan masuk menggunakan akun tersebut.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'phone.required' => 'Nomor WhatsApp aktif wajib diisi untuk koordinasi survei lapangan.',
            'nik.required_if' => 'NIK wajib diisi untuk pemohon kategori Perorangan.',
            'instansi.required_if' => 'Nama instansi/perusahaan wajib diisi untuk pemohon Badan Usaha.',
            'agree.required' => 'Pernyataan keabsahan data dan kepatuhan regulasi wajib disetujui.',
            'agree.accepted' => 'Pernyataan keabsahan data dan kepatuhan regulasi wajib disetujui.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'], // Eloquent cast 'hashed' hashes this
            'role' => 'peminat',
            'tipe_pemohon' => $validated['tipe_pemohon'],
            'nik' => $validated['nik'] ?? null,
            'nib' => $validated['nib'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'phone' => $validated['phone'],
            'instansi' => $validated['instansi'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'is_active' => true,
        ]);

        // Sesuai ketentuan: JANGAN langsung login. Arahkan ke halaman login agar user menginput ulang kredensial.
        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Akun Anda telah siap. Silakan masuk menggunakan alamat email dan kata sandi yang baru saja Anda daftarkan.')
            ->with('registered_email', $user->email);
    }

    /**
     * Tampilkan formulir login terpadu (Pemohon & Admin).
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            if (Auth::user()->isAdmin()) {
                return redirect()->intended('/admin');
            }

            return redirect()->intended(route('booking.my'));
        }

        return view('pages.auth.login');
    }

    /**
     * Proses autentikasi login terpadu pengguna.
     * Mengarahkan admin ke dashboard admin dan pemohon ke halaman akunnya.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && !$user->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan oleh administrator. Silakan hubungi UPTD Pengamanan dan Pemanfaatan Aset Jabar.',
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password'], 'is_active' => true], $remember)) {
            $request->session()->regenerate();
            $loggedInUser = Auth::user();

            // 1. Jika terdeteksi sebagai Admin UPTD -> arahkan ke dashboard admin
            if ($loggedInUser->isAdmin()) {
                return redirect()->intended('/admin')
                    ->with('success', 'Selamat datang di Panel Administrasi UPTD, ' . $loggedInUser->name . '!');
            }

            // 2. Selain itu (Pemohon / Calon Mitra) -> arahkan ke akun masing-masing (Pengajuan Saya)
            return redirect()->intended(route('booking.my'))
                ->with('success', 'Selamat datang kembali, ' . $loggedInUser->name . '! Anda dapat memantau permohonan atau mengajukan sewa aset.');
        }

        return back()->withErrors([
            'email' => 'Alamat email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Keluar dari sesi akun (Logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}
