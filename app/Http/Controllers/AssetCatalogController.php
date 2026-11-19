<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetCatalogController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::query();

        // Search Keyword
        $searchTerm = $request->get('q', $request->get('keyword'));
        if (! empty($searchTerm)) {
            $keyword = trim((string) $searchTerm);
            $likeOp = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($keyword, $likeOp) {
                $q->where('nama_objek', $likeOp, "%{$keyword}%")
                    ->orWhere('alamat', $likeOp, "%{$keyword}%")
                    ->orWhere('kode_aset', $likeOp, "%{$keyword}%")
                    ->orWhere('peruntukan', $likeOp, "%{$keyword}%");
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
        $isSiapSewa = ($request->get('status') === 'tersedia') || $request->routeIs('catalog.siap-sewa');

        if ($isSiapSewa) {
            $query->siapSewa();
        } elseif ($request->filled('status')) {
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
        $mapQuery = Asset::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [-9.0, -5.0])
            ->whereBetween('longitude', [105.0, 110.0])
            ->when($request->filled('kabupaten_kota'), fn ($q) => $q->where('kabupaten_kota', $request->kabupaten_kota));

        if ($isSiapSewa) {
            $mapQuery->siapSewa();
        } elseif ($request->filled('status')) {
            $mapQuery->where('status_ketersediaan', $request->status);
        }

        $mapAssets = $mapQuery->get(['id', 'nama_objek', 'slug', 'kabupaten_kota', 'bentuk_objek', 'luas_tanah', 'luas_bangunan', 'status_ketersediaan', 'booking_deadline_at', 'estimasi_tarif_tahunan', 'latitude', 'longitude'])
            ->map(function ($item) {
                $isBookingOpen = $item->isBookingOpen();
                $isAdministrative = $item->isAdministrativeProcess();
                $statusLabel = match (true) {
                    $item->status_ketersediaan === 'tersedia' => 'Tersedia Disewa',
                    $item->status_ketersediaan === 'proses_booking' && $isBookingOpen => 'Minat Terbuka ('.($item->remaining_booking_time ?? 'Batas 5 Hari').')',
                    $item->status_ketersediaan === 'proses_booking' && $isAdministrative => 'Proses Administrasi',
                    $item->status_ketersediaan === 'proses_booking' => 'Minat Ditutup (Verifikasi)',
                    $item->status_ketersediaan === 'tersewa' => 'Tersewa Aktif',
                    default => ucfirst($item->status_ketersediaan),
                };

                return [
                    'id' => $item->id,
                    'nama_objek' => $item->nama_objek,
                    'nama' => $item->nama_objek,
                    'slug' => $item->slug,
                    'kabupaten_kota' => $item->kabupaten_kota,
                    'lokasi' => $item->kabupaten_kota,
                    'bentuk_objek' => $item->bentuk_objek,
                    'bentuk' => $item->bentuk_objek,
                    'luas_tanah' => $item->luas_tanah ? number_format($item->luas_tanah, 0, ',', '.').' m²' : '-',
                    'luas_bangunan' => $item->luas_bangunan ? number_format($item->luas_bangunan, 0, ',', '.').' m²' : '-',
                    'luas' => $item->luas_tanah ? number_format($item->luas_tanah, 0, ',', '.').' m²' : '-',
                    'estimasi_tarif' => $item->estimasi_tarif_tahunan ? 'Rp '.number_format($item->estimasi_tarif_tahunan, 0, ',', '.').' / thn' : 'Sesuai Penilaian Resmi',
                    'tarif' => $item->estimasi_tarif_tahunan ? 'Rp '.number_format($item->estimasi_tarif_tahunan, 0, ',', '.').' / thn' : 'Sesuai Penilaian Resmi',
                    'status_ketersediaan' => $item->status_ketersediaan,
                    'status' => $item->status_ketersediaan,
                    'status_label' => $statusLabel,
                    'is_booking_open' => $isBookingOpen,
                    'remaining_booking_time' => $item->remaining_booking_time,
                    'latitude' => (float) $item->latitude,
                    'longitude' => (float) $item->longitude,
                    'lat' => (float) $item->latitude,
                    'lng' => (float) $item->longitude,
                    'thumbnail_url' => $item->thumbnail_url,
                    'url' => route('catalog.show', $item->slug),
                    'booking_url' => route('catalog.show', $item->slug).'#form-booking',
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
            ->with(['pemanfaatanAktif', 'units'])
            ->firstOrFail();

        $userBooking = null;
        if (Auth::check()) {
            $user = Auth::user();
            $userBooking = Booking::where('asset_id', $asset->id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('email', $user->email);
                })
                ->whereNotIn('status', ['rejected'])
                ->latest()
                ->first();
        }

        $relatedAssets = Asset::where('id', '!=', $asset->id)
            ->where(function ($q) use ($asset) {
                $q->where('kabupaten_kota', $asset->kabupaten_kota)
                    ->orWhere('bentuk_objek', $asset->bentuk_objek);
            })
            ->take(3)
            ->get();

        return view('pages.asset-detail', compact('asset', 'relatedAssets', 'userBooking'));
    }
}
