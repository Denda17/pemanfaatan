<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegulasiController extends Controller
{
    /**
     * Halaman indeks daftar dasar hukum & regulasi pemanfaatan BMD.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $kategori = trim((string) $request->get('kategori', 'semua'));

        $query = Regulation::active()->ordered();

        if (!empty($kategori) && $kategori !== 'semua') {
            $query->where('tingkat', $kategori);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor', 'like', "%{$search}%")
                  ->orWhere('judul', 'like', "%{$search}%")
                  ->orWhere('jenis', 'like', "%{$search}%")
                  ->orWhere('penerbit', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhere('poin_kunci', 'like', "%{$search}%");
            });
        }

        $regulations = $query->get();

        $activeQuery = Regulation::active();
        $counts = [
            'semua' => (clone $activeQuery)->count(),
            'daerah' => (clone $activeQuery)->where('tingkat', 'daerah')->count(),
            'kemendagri' => (clone $activeQuery)->where('tingkat', 'kemendagri')->count(),
            'pusat' => (clone $activeQuery)->where('tingkat', 'pusat')->count(),
            'sop' => (clone $activeQuery)->where('tingkat', 'sop')->count(),
        ];

        return view('pages.regulasi', compact('regulations', 'search', 'kategori', 'counts'));
    }

    /**
     * Tampilkan berkas PDF langsung di peramban (in-browser preview).
     */
    public function preview(string $slug): BinaryFileResponse
    {
        $regulation = Regulation::where('slug', $slug)->first();
        abort_unless($regulation, 404, 'Regulasi tidak ditemukan.');

        $filePath = $regulation->resolveFilePath();
        abort_unless($filePath && file_exists($filePath), 404, 'Berkas dokumen regulasi belum tersedia di server.');

        $downloadName = $regulation->download_name ?: basename($filePath);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }

    /**
     * Unduh berkas PDF dokumen regulasi.
     */
    public function download(string $slug): BinaryFileResponse
    {
        $regulation = Regulation::where('slug', $slug)->first();
        abort_unless($regulation, 404, 'Regulasi tidak ditemukan.');

        $filePath = $regulation->resolveFilePath();
        abort_unless($filePath && file_exists($filePath), 404, 'Berkas dokumen regulasi belum tersedia di server.');

        $downloadName = $regulation->download_name ?: basename($filePath);

        return response()->download($filePath, $downloadName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
