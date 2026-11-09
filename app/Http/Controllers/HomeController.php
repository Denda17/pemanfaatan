<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use App\Models\Booking;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredAssets = Asset::where('is_featured', true)
            ->where('status_ketersediaan', '!=', 'pemeliharaan')
            ->take(6)
            ->get();

        if ($featuredAssets->isEmpty()) {
            $featuredAssets = Asset::where('status_ketersediaan', '!=', 'pemeliharaan')
                ->take(6)
                ->get();
        }

        $totalAssets = Asset::count();
        $totalTersedia = Asset::where('status_ketersediaan', 'tersedia')->count();
        $totalTersewa = Asset::where('status_ketersediaan', 'tersewa')->count();
        $totalPad = AssetPemanfaatan::sum('besaran_sewa_tahunan');

        $regencies = Asset::select('kabupaten_kota')
            ->whereNotNull('kabupaten_kota')
            ->distinct()
            ->orderBy('kabupaten_kota')
            ->pluck('kabupaten_kota');

        $categories = Asset::select('bentuk_objek')
            ->whereNotNull('bentuk_objek')
            ->distinct()
            ->pluck('bentuk_objek');

        return view('pages.home', compact(
            'featuredAssets',
            'totalAssets',
            'totalTersedia',
            'totalTersewa',
            'totalPad',
            'regencies',
            'categories'
        ));
    }
}
