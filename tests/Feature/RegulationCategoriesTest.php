<?php

namespace Tests\Feature;

use App\Filament\Resources\RegulationResource\Pages\ListRegulations;
use App\Models\Regulation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class RegulationCategoriesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_dasar_hukum_separates_bmd_kdkmp_and_sop_pillars(): void
    {
        $bmdReg = Regulation::create([
            'slug' => 'test-bmd-reg-2026',
            'kategori_regulasi' => Regulation::KATEGORI_PENGELOLAAN_BMD,
            'nomor' => 'PERGUB-BMD/99/2026',
            'tahun' => 2026,
            'jenis' => 'Peraturan Gubernur (Pergub)',
            'tingkat' => 'daerah',
            'penerbit' => 'Gubernur Jawa Barat',
            'judul' => 'Regulasi Uji Pengelolaan BMD',
            'file_path' => 'regulasi-docs/dummy.pdf',
            'is_active' => true,
        ]);

        $kdkmpReg = Regulation::create([
            'slug' => 'test-kdkmp-reg-2026',
            'kategori_regulasi' => Regulation::KATEGORI_KDKMP,
            'nomor' => 'INPRES-KDKMP/09/2026',
            'tahun' => 2026,
            'jenis' => 'Undang-Undang / Inpres',
            'tingkat' => 'pusat',
            'penerbit' => 'Presiden Republik Indonesia',
            'judul' => 'Percepatan Pembentukan Koperasi Desa Merah Putih',
            'file_path' => 'regulasi-docs/dummy.pdf',
            'is_active' => true,
        ]);

        $sopReg = Regulation::create([
            'slug' => 'test-sop-reg-2026',
            'kategori_regulasi' => Regulation::KATEGORI_SOP,
            'nomor' => 'SOP-UPTD/12/2026',
            'tahun' => 2026,
            'jenis' => 'Standar Operasional Prosedur (SOP)',
            'tingkat' => 'sop',
            'penerbit' => 'UPTD Pemanfaatan Aset Jabar',
            'judul' => 'SOP Pelayanan Verifikasi Sewa BMD',
            'file_path' => 'regulasi-docs/dummy.pdf',
            'is_active' => true,
        ]);

        // 1. Default (/dasar-hukum) menampilkan Pengelolaan BMD saja (terpisah dari KDKMP & SOP)
        $resBmd = $this->get(route('regulasi.index'));
        $resBmd->assertOk();
        $resBmd->assertSee('1. Dasar Hukum Pengelolaan BMD');
        $resBmd->assertSee('2. Dasar Hukum KDKMP');
        $resBmd->assertSee('3. SOP & Petunjuk Teknis');
        $resBmd->assertSee($bmdReg->nomor);
        $resBmd->assertDontSee($kdkmpReg->nomor);
        $resBmd->assertDontSee($sopReg->nomor);

        // 2. Tab KDKMP (?kelompok=kdkmp) hanya menampilkan regulasi KDKMP
        $resKdkmp = $this->get(route('regulasi.index', ['kelompok' => 'kdkmp']));
        $resKdkmp->assertOk();
        $resKdkmp->assertSee($kdkmpReg->nomor);
        $resKdkmp->assertDontSee($bmdReg->nomor);
        $resKdkmp->assertDontSee($sopReg->nomor);

        // 3. Tab SOP (?kelompok=sop) hanya menampilkan dokumen SOP
        $resSop = $this->get(route('regulasi.index', ['kelompok' => 'sop']));
        $resSop->assertOk();
        $resSop->assertSee($sopReg->nomor);
        $resSop->assertDontSee($bmdReg->nomor);
        $resSop->assertDontSee($kdkmpReg->nomor);
    }

    public function test_admin_list_regulations_filters_by_three_category_tabs(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $kdkmpReg = Regulation::create([
            'slug' => 'admin-kdkmp-tab-test',
            'kategori_regulasi' => Regulation::KATEGORI_KDKMP,
            'nomor' => 'SE-KDKMP/01/2026',
            'tahun' => 2026,
            'jenis' => 'Surat Edaran (SE)',
            'tingkat' => 'daerah',
            'penerbit' => 'BPKAD Jabar',
            'judul' => 'Edaran Kemitraan KDKMP Jabar',
            'file_path' => 'regulasi-docs/dummy.pdf',
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(ListRegulations::class)
            ->set('activeTab', 'kdkmp')
            ->assertCanSeeTableRecords([$kdkmpReg]);
    }
}
