<?php

namespace App\Http\Controllers;

use App\Models\Regulation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegulasiController extends Controller
{
    /**
     * Halaman indeks daftar dasar hukum, regulasi KDKMP, & SOP pemanfaatan BMD.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $kelompok = trim((string) $request->get('kelompok', ''));
        $kategori = trim((string) $request->get('kategori', 'semua'));

        // Kompatibilitas tautan lama (?kategori=sop)
        if ($kategori === 'sop' && $kelompok === '') {
            $kelompok = Regulation::KATEGORI_SOP;
            $kategori = 'semua';
        }

        if (! in_array($kelompok, ['semua', Regulation::KATEGORI_PENGELOLAAN_BMD, Regulation::KATEGORI_KDKMP, Regulation::KATEGORI_SOP], true)) {
            $kelompok = ($search !== '' && $kategori === 'semua')
                ? 'semua'
                : Regulation::KATEGORI_PENGELOLAAN_BMD;
        }

        $query = Regulation::active()->ordered();

        if ($kelompok !== 'semua') {
            $query->where('kategori_regulasi', $kelompok);
        }

        if (! empty($kategori) && $kategori !== 'semua' && $kelompok !== Regulation::KATEGORI_SOP) {
            $query->where('tingkat', $kategori);
        }

        if (! empty($search)) {
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

        $allActive = Regulation::active();
        $groupCounts = [
            'semua' => (clone $allActive)->count(),
            Regulation::KATEGORI_PENGELOLAAN_BMD => (clone $allActive)->where('kategori_regulasi', Regulation::KATEGORI_PENGELOLAAN_BMD)->count(),
            Regulation::KATEGORI_KDKMP => (clone $allActive)->where('kategori_regulasi', Regulation::KATEGORI_KDKMP)->count(),
            Regulation::KATEGORI_SOP => (clone $allActive)->where('kategori_regulasi', Regulation::KATEGORI_SOP)->count(),
        ];

        $scopedForHierarchy = Regulation::active();
        if ($kelompok !== 'semua') {
            $scopedForHierarchy->where('kategori_regulasi', $kelompok);
        }

        $counts = [
            'semua' => (clone $scopedForHierarchy)->count(),
            'total_all' => $groupCounts['semua'],
            'daerah' => (clone $scopedForHierarchy)->where('tingkat', 'daerah')->count(),
            'kemendagri' => (clone $scopedForHierarchy)->where('tingkat', 'kemendagri')->count(),
            'pusat' => (clone $scopedForHierarchy)->where('tingkat', 'pusat')->count(),
            'sop' => $groupCounts[Regulation::KATEGORI_SOP],
        ];

        return view('pages.regulasi', compact('regulations', 'search', 'kelompok', 'kategori', 'groupCounts', 'counts'));
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
            'Content-Disposition' => 'inline; filename="'.$downloadName.'"',
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
