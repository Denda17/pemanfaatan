<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingDocumentAccessController extends Controller
{
    /**
     * Tampilkan / stream berkas persyaratan booking secara aman bagi yang berhak.
     */
    public function viewDocument(Request $request, BookingDocument $document): BinaryFileResponse
    {
        $this->authorizeDocumentAccess($document);

        $path = $this->resolveFilePath($document->file_path);

        $this->logAccess('Lihat Dokumen', "BookingDocument #{$document->id} ({$document->requirement?->nama_persyaratan})", $document->booking);

        return response()->file($path, [
            'Content-Type' => $document->file_type ?? mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($document->file_name ?? basename($path)).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Unduh berkas dokumen persyaratan booking.
     */
    public function downloadDocument(Request $request, BookingDocument $document): BinaryFileResponse
    {
        $this->authorizeDocumentAccess($document);

        $path = $this->resolveFilePath($document->file_path);

        $this->logAccess('Unduh Dokumen', "BookingDocument #{$document->id} ({$document->requirement?->nama_persyaratan})", $document->booking);

        return response()->download($path, $document->file_name ?? basename($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Tampilkan / stream bukti setor termin kasda secara aman.
     */
    public function viewPaymentProof(Request $request, BookingPayment $payment): BinaryFileResponse
    {
        $booking = $payment->booking;
        $user = Auth::user();

        if (! $booking || ! $booking->isOwnedBy($user)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat bukti pembayaran ini.');
        }

        $path = $this->resolveFilePath($payment->file_path);

        $this->logAccess('Lihat Bukti Bayar', "BookingPayment #{$payment->id} (Termin ke-{$payment->termin_ke})", $booking);

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($payment->file_name ?? basename($path)).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Tampilkan / stream proposal bisnis secara aman.
     */
    public function viewProposal(Request $request, Booking $booking): BinaryFileResponse
    {
        $user = Auth::user();

        if (! $booking->isOwnedBy($user)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk melihat proposal bisnis ini.');
        }

        if (! $booking->proposal_bisnis_path) {
            abort(404, 'Proposal bisnis belum diunggah untuk permohonan ini.');
        }

        $path = $this->resolveFilePath($booking->proposal_bisnis_path);

        $this->logAccess('Lihat Proposal Bisnis', "Proposal Bisnis Booking #{$booking->kode_booking}", $booking);

        return response()->file($path, [
            'Content-Type' => mime_content_type($path) ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="Proposal-'.$booking->kode_booking.'.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Lihat berkas Berita Acara Seleksi / Beauty Contest (PDF).
     */
    public function viewBeritaAcaraSeleksi(Request $request, Booking $booking): BinaryFileResponse
    {
        $user = Auth::user();
        abort_unless($user && ($user->isAdmin() || $booking->isOwnedBy($user)), 403, 'Akses ditolak.');

        if (! $booking->beauty_contest_berita_acara_path) {
            abort(404, 'Berita acara seleksi belum dilampirkan.');
        }

        $path = $this->resolveFilePath($booking->beauty_contest_berita_acara_path);

        $this->logAccess('Lihat Berita Acara Seleksi', "Booking #{$booking->id} ({$booking->kode_booking})", $booking);

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Berita_Acara_Seleksi_Beauty_Contest.pdf"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Verifikasi otorisasi akses: Hanya pemilik akun atau admin yang berhak.
     */
    protected function authorizeDocumentAccess(BookingDocument $document): void
    {
        $user = Auth::user();
        $booking = $document->booking;

        if (! $user || ! $booking || ! $booking->isOwnedBy($user)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki wewenang untuk mengakses dokumen ini.');
        }

        // Jika bukan admin (peminat / pemohon), batasi akses hanya ke dokumen publik/eksternal yang sah
        if (! $user->isAdmin() && ! $document->isViewableByApplicant()) {
            abort(403, 'Akses ditolak: Dokumen ini merupakan arsip internal kedinasan UPTD dan tidak dipublikasikan kepada pemohon.');
        }
    }

    /**
     * Resolusi path berkas fisik dengan prioritas private disk (local) lalu fallback public.
     */
    protected function resolveFilePath(?string $relativePath): string
    {
        if (empty($relativePath)) {
            abort(404, 'Lokasi file tidak terdefinisi.');
        }

        // Cek disk private ('local' -> storage/app/private)
        if (Storage::disk('local')->exists($relativePath)) {
            return Storage::disk('local')->path($relativePath);
        }

        // Fallback cek disk public (transisi file lama)
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->path($relativePath);
        }

        abort(404, 'Berkas fisik tidak ditemukan di server.');
    }

    /**
     * Catat audit log jika diakses oleh admin atau untuk audit keamanan PDP.
     */
    protected function logAccess(string $action, string $target, Booking $booking): void
    {
        $user = Auth::user();
        if ($user && $user->isAdmin()) {
            Log::info(sprintf(
                '[AUDIT PDP] Admin ID: %d (%s) melakukan %s pada %s [Booking: %s, Pemohon: %s]',
                $user->id,
                $user->email,
                $action,
                $target,
                $booking->kode_booking,
                $booking->nama_pemohon
            ));
        }
    }
}
