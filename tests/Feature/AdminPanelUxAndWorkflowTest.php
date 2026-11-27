<?php

namespace Tests\Feature;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\BookingResource\Pages\EditBooking;
use App\Filament\Resources\BookingResource\Pages\ListBookings;
use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingPayment;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelUxAndWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_dashboard_renders_action_inbox_and_clean_modern_elements(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $response = $this->actingAs($admin, 'web')->get('/admin');

        $response->assertOk();
        $response->assertSee('Antrian Tindakan Prioritas UPTD');
        $response->assertSee('Permintaan Kode Bayar Termin');
        $response->assertSee('Mode Layar TV');
        $response->assertSee('Lihat Portal Publik');
    }

    public function test_navigation_badge_and_tabs_reflect_payment_code_requests(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $asset = Asset::factory()->create(['status_ketersediaan' => 'tersewa']);

        $baselineBadge = (int) (BookingResource::getNavigationBadge() ?? 0);

        $booking = Booking::create([
            'kode_booking' => 'BMD-UX-BADGE-01',
            'asset_id' => $asset->id,
            'user_id' => $tenant->id,
            'nama_pemohon' => $tenant->name,
            'nomor_whatsapp' => '081234567890',
            'email' => $tenant->email,
            'tipe_pemohon' => 'perusahaan',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'bertahap',
            'status' => 'approved',
            'tanggal_booking' => now(),
        ]);

        BookingPayment::create([
            'booking_id' => $booking->id,
            'termin_ke' => 2,
            'tahun_ke' => 'Tahun ke-2',
            'status_pembayaran' => 'kode_diminta',
            'kode_diminta_at' => now(),
        ]);

        $newBadge = (int) (BookingResource::getNavigationBadge() ?? 0);
        $this->assertSame($baselineBadge + 1, $newBadge);

        Livewire::actingAs($admin)
            ->test(ListBookings::class)
            ->set('activeTab', 'minta_kode_bayar')
            ->assertCanSeeTableRecords([$booking]);
    }

    public function test_contract_signed_guardrail_blocks_unverified_pks_and_allows_verified_pks(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $asset = Asset::factory()->create(['status_ketersediaan' => 'proses_booking']);

        $booking = Booking::create([
            'kode_booking' => 'BMD-UX-GUARD-01',
            'asset_id' => $asset->id,
            'user_id' => $tenant->id,
            'nama_pemohon' => $tenant->name,
            'nomor_whatsapp' => '081234567899',
            'email' => $tenant->email,
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'approved',
            'tanggal_booking' => now(),
        ]);

        // 1. Tanpa dokumen PKS terverifikasi -> diblokir oleh guardrail
        Livewire::actingAs($admin)
            ->test(EditBooking::class, ['record' => $booking->id])
            ->fillForm(['status' => 'contract_signed'])
            ->call('save');

        $booking->refresh();
        $this->assertSame('approved', $booking->status);

        // 2. Setelah dokumen REQ_PERJANJIAN_SEWA terverifikasi -> diizinkan & aset otomatis 'tersewa'
        $reqPks = BookingRequirement::where('kode', 'REQ_PERJANJIAN_SEWA')->firstOrFail();
        BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqPks->id,
            'nomor_dokumen' => 'PKS/001/BPKAD/2026',
            'tanggal_dokumen' => now(),
            'file_path' => 'booking-documents/pks_test.pdf',
            'file_name' => 'pks_test.pdf',
            'file_size' => 1024,
            'status_verifikasi' => 'verified',
            'admin_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(EditBooking::class, ['record' => $booking->id])
            ->fillForm(['status' => 'contract_signed'])
            ->call('save')
            ->assertHasNoFormErrors();

        $booking->refresh();
        $asset->refresh();
        $this->assertSame('contract_signed', $booking->status);
        $this->assertSame('tersewa', $asset->status_ketersediaan);
    }
}
