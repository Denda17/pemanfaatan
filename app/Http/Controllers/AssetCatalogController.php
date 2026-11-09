<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        // Search Keyword
        if ($request->filled('q')) {
            $keyword = trim($request->q);
            $query->where(function ($q) use ($keyword) {
                $q->where('nama_objek', 'ilike', "%{$keyword}%")
                  ->orWhere('alamat', 'ilike', "%{$keyword}%")
                  ->orWhere('kode_aset', 'ilike', "%{$keyword}%")
                  ->orWhere('peruntukan', 'ilike', "%{$keyword}%");
            });
        }

        // Filter Kabupaten / Kota
        if ($request->filled('kabupaten_kota')) {
            $query->where('kabupaten_kota', $request->kabupaten_kota);
        }

        // Filter Bentuk Objek
        if ($request->filled('bentuk_objek')) {
            $query->where('bentuk_objek', $request->bentuk_objek);
        }

        // Filter Status Ketersediaan
        if ($request->filled('status')) {
            $query->where('status_ketersediaan', $request->status);
        }

        // Sorting
        $sort = $request->get('sort', 'terbaru');
        match ($sort) {
            'tarif_terendah' => $query->orderBy('estimasi_tarif_tahunan', 'asc'),
            'tarif_tertinggi' => $query->orderBy('estimasi_tarif_tahunan', 'desc'),
            'luas_terbesar' => $query->orderBy('luas_tanah', 'desc'),
            'luas_terkecil' => $query->orderBy('luas_tanah', 'asc'),
            default => $query->latest('created_at'),
        };

        $assets = $query->paginate(12)->withQueryString();

        // Data titik peta (hanya aset yang memiliki latitude & longitude valid di wilayah Jabar)
        $mapAssets = Asset::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-9.0, -5.0])
            ->whereBetween('longitude', [105.0, 110.0])
            ->when($request->filled('status'), fn ($q) => $q->where('status_ketersediaan', $request->status))
            ->when($request->filled('kabupaten_kota'), fn ($q) => $q->where('kabupaten_kota', $request->kabupaten_kota))
            ->get(['id', 'nama_objek', 'slug', 'kabupaten_kota', 'bentuk_objek', 'luas_tanah', 'luas_bangunan', 'status_ketersediaan', 'estimasi_tarif_tahunan', 'latitude', 'longitude'])
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_objek' => $item->nama_objek,
                    'nama' => $item->nama_objek,
                    'slug' => $item->slug,
                    'kabupaten_kota' => $item->kabupaten_kota,
                    'lokasi' => $item->kabupaten_kota,
                    'bentuk_objek' => $item->bentuk_objek,
                    'bentuk' => $item->bentuk_objek,
                    'luas_tanah' => $item->luas_tanah ? number_format($item->luas_tanah, 0, ',', '.') . ' m²' : '-',
                    'luas_bangunan' => $item->luas_bangunan ? number_format($item->luas_bangunan, 0, ',', '.') . ' m²' : '-',
                    'luas' => $item->luas_tanah ? number_format($item->luas_tanah, 0, ',', '.') . ' m²' : '-',
                    'estimasi_tarif' => $item->estimasi_tarif_tahunan ? 'Rp ' . number_format($item->estimasi_tarif_tahunan, 0, ',', '.') . ' / thn' : 'Sesuai Penilaian Resmi',
                    'tarif' => $item->estimasi_tarif_tahunan ? 'Rp ' . number_format($item->estimasi_tarif_tahunan, 0, ',', '.') . ' / thn' : 'Sesuai Penilaian Resmi',
                    'status_ketersediaan' => $item->status_ketersediaan,
                    'status' => $item->status_ketersediaan,
                    'status_label' => match ($item->status_ketersediaan) {
                        'tersedia' => 'Tersedia Disewa',
                        'proses_booking' => 'Proses Booking',
                        'tersewa' => 'Tersewa Aktif',
                        default => ucfirst($item->status_ketersediaan)
                    },
                    'latitude' => (float) $item->latitude,
                    'longitude' => (float) $item->longitude,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'thumbnail_url' => $item->thumbnail_url,
                    'url' => route('catalog.show', $item->slug),
                    'booking_url' => route('catalog.show', $item->slug) . '#form-booking',
                ];
            });

        $regencies = Asset::select('kabupaten_kota')
            ->whereNotNull('kabupaten_kota')
            ->distinct()
            ->orderBy('kabupaten_kota')
            ->pluck('kabupaten_kota');

        $categories = Asset::select('bentuk_objek')
            ->whereNotNull('bentuk_objek')
            ->distinct()
            ->pluck('bentuk_objek');

        $isSiapSewa = ($request->get('status') === 'tersedia') || $request->routeIs('catalog.siap-sewa');

        return view('pages.catalog', compact('assets', 'mapAssets', 'regencies', 'categories', 'isSiapSewa'));
    }

    public function siapSewa(Request $request)
    {
        $request->merge(['status' => 'tersedia']);
        return $this->index($request);
    }

    public function show(string $slug)
    {
        $asset = Asset::where('slug', $slug)
            ->with(['pemanfaatanAktif'])
            ->firstOrFail();

        $relatedAssets = Asset::where('id', '!=', $asset->id)
            ->where(function ($q) use ($asset) {
                $q->where('kabupaten_kota', $asset->kabupaten_kota)
                  ->orWhere('bentuk_objek', $asset->bentuk_objek);
            })
            ->take(3)
            ->get();

        return view('pages.asset-detail', compact('asset', 'relatedAssets'));
    }
}
