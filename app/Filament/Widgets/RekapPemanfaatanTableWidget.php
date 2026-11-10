<?php

namespace App\Filament\Widgets;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use Filament\Forms;
use Filament\Infolists;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RekapPemanfaatanTableWidget extends BaseWidget
{
    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Rekapitulasi Pemanfaatan Barang Milik Daerah (BMD) Pemprov Jawa Barat';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AssetPemanfaatan::query()
                    ->with('asset')
                    ->orderBy('nomor_urut_excel', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('nomor_urut_excel')
                    ->label('No.')
                    ->sortable()
                    ->alignCenter()
                    ->width('50px'),

                Tables\Columns\TextColumn::make('asset.nama_objek')
                    ->label('Objek Sewa & Lokasi')
                    ->searchable()
                    ->wrap()
                    ->weight('bold')
                    ->description(function (AssetPemanfaatan $record): string {
                        $parts = [];
                        if ($record->asset?->kabupaten_kota) {
                            $parts[] = $record->asset->kabupaten_kota;
                        }
                        if ($record->asset?->bentuk_objek) {
                            $parts[] = $record->asset->bentuk_objek;
                        }
                        if ($record->asset?->luas_tanah) {
                            $parts[] = 'Tanah: ' . number_format($record->asset->luas_tanah, 0, ',', '.') . ' m²';
                        }
                        if ($record->asset?->luas_bangunan) {
                            $parts[] = 'Bgn: ' . number_format($record->asset->luas_bangunan, 0, ',', '.') . ' m²';
                        }
                        return implode(' • ', $parts);
                    }),

                Tables\Columns\TextColumn::make('status_pengelola')
                    ->label('Pengelola')
                    ->badge()
                    ->color('primary')
                    ->default('PENGELOLA'),

                Tables\Columns\TextColumn::make('nama_mitra')
                    ->label('Mitra Penyewa')
                    ->searchable()
                    ->wrap()
                    ->description(fn (AssetPemanfaatan $record): string => $record->jenis_mitra ?? 'Swasta / Mitra'),

                Tables\Columns\TextColumn::make('besaran_sewa_tahunan')
                    ->label('Sewa / Thn')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_sewa')
                    ->label('Total Sewa')
                    ->money('IDR', locale: 'id')
                    ->placeholder('-')
                    ->description(function (AssetPemanfaatan $record): string {
                        $durasi = $record->jangka_waktu_teks ?? ($record->jangka_waktu_tahun ? $record->jangka_waktu_tahun . ' thn' : '-');
                        $bayar = $record->mekanisme_pembayaran ?? 'Sekaligus';
                        return "{$durasi} ({$bayar})";
                    }),

                Tables\Columns\TextColumn::make('tanggal_awal')
                    ->label('Periode Masa Sewa')
                    ->formatStateUsing(function (AssetPemanfaatan $record): string {
                        if (!$record->tanggal_awal && !$record->tanggal_akhir) {
                            return '-';
                        }
                        $awal = $record->tanggal_awal ? $record->tanggal_awal->format('d/m/Y') : '-';
                        $akhir = $record->tanggal_akhir ? $record->tanggal_akhir->format('d/m/Y') : '-';
                        return "{$awal} s/d {$akhir}";
                    })
                    ->description(function (AssetPemanfaatan $record): ?string {
                        if ($record->status_perpanjangan === 'peringatan_jatuh_tempo') {
                            return '⚠️ Jatuh tempo ≤ 6 bulan';
                        }
                        return null;
                    }),

                Tables\Columns\TextColumn::make('kategori_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'sewa_aktif' => 'success',
                        'dalam_proses' => 'warning',
                        'berakhir' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'sewa_aktif' => 'Sewa Aktif',
                        'dalam_proses' => 'Dalam Proses',
                        'berakhir' => 'Berakhir',
                        default => ucfirst($state ?? '-'),
                    }),

                Tables\Columns\TextColumn::make('total_disetor')
                    ->label('Disetor Kasda')
                    ->money('IDR', locale: 'id')
                    ->color('success')
                    ->weight('bold')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(fn (AssetPemanfaatan $record) => $record->persentase_pelunasan . '% Lunas'),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan / Progres')
                    ->wrap()
                    ->limit(40)
                    ->tooltip(fn (AssetPemanfaatan $record): ?string => $record->keterangan)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('nomor_perjanjian')
                    ->label('No. PKS / Legalitas')
                    ->placeholder('-')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->description(function (AssetPemanfaatan $record): ?string {
                        $desc = [];
                        if ($record->nomor_kepgub) {
                            $desc[] = 'Kepgub: ' . $record->nomor_kepgub;
                        }
                        if ($record->nomor_persetujuan) {
                            $desc[] = 'Persetujuan: ' . $record->nomor_persetujuan;
                        }
                        return implode(' | ', $desc) ?: null;
                    }),

                Tables\Columns\TextColumn::make('asset.latitude')
                    ->label('Koordinat GPS')
                    ->formatStateUsing(fn (AssetPemanfaatan $record): string => ($record->asset?->latitude && $record->asset?->longitude) ? "{$record->asset->latitude}, {$record->asset->longitude}" : '-')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_status')
                    ->label('Kategori Pemanfaatan')
                    ->options([
                        'sewa_aktif' => 'Sewa Aktif (22 Data)',
                        'dalam_proses' => 'Dalam Proses (13 Data)',
                        'berakhir' => 'Pemanfaatan Berakhir (11 Data)',
                    ]),

                Tables\Filters\SelectFilter::make('status_perpanjangan')
                    ->label('Status Jatuh Tempo')
                    ->options([
                        'peringatan_jatuh_tempo' => '⚠️ Peringatan Jatuh Tempo (≤ 6 Bulan)',
                        'aktif' => 'Sewa Aktif Aman',
                        'dalam_proses' => 'Dalam Proses Usulan',
                        'proses_perpanjangan' => 'Proses Perpanjangan',
                        'selesai' => 'Masa Sewa Berakhir',
                    ]),

                Tables\Filters\SelectFilter::make('kabupaten_kota')
                    ->label('Kabupaten / Kota')
                    ->relationship('asset', 'kabupaten_kota'),

                Tables\Filters\SelectFilter::make('jenis_mitra')
                    ->label('Kategori Mitra')
                    ->options([
                        'Perusahaan (PT)' => 'Perusahaan (PT)',
                        'Perusahaan (CV)' => 'Perusahaan (CV)',
                        'Koperasi' => 'Koperasi',
                        'Yayasan' => 'Yayasan',
                        'Pemerintah Desa' => 'Pemerintah Desa',
                        'Kelompok Masyarakat / Tani' => 'Kelompok Masyarakat / Tani',
                        'Perorangan' => 'Perorangan',
                        'Swasta / Mitra' => 'Swasta / Mitra',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-m-eye')
                    ->color('primary')
                    ->modalHeading(fn (AssetPemanfaatan $record): string => "Detail Rekap Pemanfaatan: #{$record->nomor_urut_excel} - {$record->asset?->nama_objek}")
                    ->modalDescription('Seluruh atribut lengkap yang bersumber dari file Rekap Pemanfaatan BMD (terbaru).xlsx')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->infolist([
                        Infolists\Components\Section::make('1. Identitas Objek Sewa BMD')
                            ->schema([
                                Infolists\Components\TextEntry::make('nomor_urut_excel')
                                    ->label('Nomor Urut Excel'),
                                Infolists\Components\TextEntry::make('asset.kode_aset')
                                    ->label('Kode Aset Sistem'),
                                Infolists\Components\TextEntry::make('asset.nama_objek')
                                    ->label('Nama Objek Sewa')
                                    ->columnSpanFull(),
                                Infolists\Components\TextEntry::make('asset.kabupaten_kota')
                                    ->label('Kabupaten / Kota'),
                                Infolists\Components\TextEntry::make('status_pengelola')
                                    ->label('Status Pengelola')
                                    ->badge()
                                    ->color('primary'),
                                Infolists\Components\TextEntry::make('asset.bentuk_objek')
                                    ->label('Bentuk Objek'),
                                Infolists\Components\TextEntry::make('asset.luas_tanah')
                                    ->label('Luas Tanah')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2, ',', '.') . ' m²' : '-'),
                                Infolists\Components\TextEntry::make('asset.luas_bangunan')
                                    ->label('Luas Bangunan')
                                    ->formatStateUsing(fn ($state) => $state ? number_format($state, 2, ',', '.') . ' m²' : '-'),
                                Infolists\Components\TextEntry::make('asset.latitude')
                                    ->label('Koordinat Latitude')
                                    ->copyable(),
                                Infolists\Components\TextEntry::make('asset.longitude')
                                    ->label('Koordinat Longitude')
                                    ->copyable(),
                            ])->columns(4),

                        Infolists\Components\Section::make('2. Mitra Penyewa & Nilai Pemanfaatan')
                            ->schema([
                                Infolists\Components\TextEntry::make('nama_mitra')
                                    ->label('Nama Mitra / Penyewa')
                                    ->weight('bold')
                                    ->columnSpan(2),
                                Infolists\Components\TextEntry::make('jenis_mitra')
                                    ->label('Kategori Mitra'),
                                Infolists\Components\TextEntry::make('besaran_sewa_tahunan')
                                    ->label('Besaran Sewa per Tahun')
                                    ->money('IDR', locale: 'id'),
                                Infolists\Components\TextEntry::make('jangka_waktu_teks')
                                    ->label('Jangka Waktu Sewa'),
                                Infolists\Components\TextEntry::make('total_sewa')
                                    ->label('Total Nilai Sewa')
                                    ->money('IDR', locale: 'id'),
                                Infolists\Components\TextEntry::make('mekanisme_pembayaran')
                                    ->label('Mekanisme Pembayaran'),
                            ])->columns(4),

                        Infolists\Components\Section::make('3. Dokumen Legalitas & Status Masa Sewa')
                            ->schema([
                                Infolists\Components\TextEntry::make('nomor_persetujuan')
                                    ->label('No. Surat Persetujuan')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('nomor_kepgub')
                                    ->label('No. Keputusan Gubernur (Kepgub)')
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('nomor_perjanjian')
                                    ->label('No. Perjanjian Kerjasama (PKS)')
                                    ->placeholder('-')
                                    ->columnSpan(2),
                                Infolists\Components\TextEntry::make('tanggal_awal')
                                    ->label('Tanggal Awal Sewa')
                                    ->date('d F Y'),
                                Infolists\Components\TextEntry::make('tanggal_akhir')
                                    ->label('Tanggal Akhir Sewa')
                                    ->date('d F Y'),
                                Infolists\Components\TextEntry::make('kategori_status')
                                    ->label('Kategori Status')
                                    ->badge()
                                    ->color(fn (?string $state): string => match ($state) {
                                        'sewa_aktif' => 'success',
                                        'dalam_proses' => 'warning',
                                        'berakhir' => 'danger',
                                        default => 'gray',
                                    }),
                                Infolists\Components\TextEntry::make('status_perpanjangan')
                                    ->label('Status Jatuh Tempo')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('keterangan')
                                    ->label('Keterangan / Catatan Progres Excel')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])->columns(4),

                        Infolists\Components\Section::make('4. Realisasi Setoran Kasda (STS / NTPN)')
                            ->schema([
                                Infolists\Components\TextEntry::make('total_sewa')
                                    ->label('Total Kewajiban Sewa PKS')
                                    ->money('IDR', locale: 'id'),
                                Infolists\Components\TextEntry::make('total_disetor')
                                    ->label('Total Riil Disetor ke Kasda')
                                    ->money('IDR', locale: 'id')
                                    ->weight('bold')
                                    ->color('success'),
                                Infolists\Components\TextEntry::make('persentase_pelunasan')
                                    ->label('Persentase Realisasi Pelunasan')
                                    ->formatStateUsing(fn ($state) => $state . '%')
                                    ->badge()
                                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
                                Infolists\Components\TextEntry::make('sisa_kewajiban')
                                    ->label('Sisa Kewajiban / Termin Mendatang')
                                    ->money('IDR', locale: 'id')
                                    ->color('danger'),
                            ])->columns(4),
                    ]),

                Tables\Actions\Action::make('kelolaSetoran')
                    ->label('Setoran')
                    ->icon('heroicon-m-banknotes')
                    ->color('success')
                    ->tooltip('Kelola Riwayat Termin & Bukti Setor STS Kasda')
                    ->url(fn (AssetPemanfaatan $record): string => route('filament.admin.resources.asset-pemanfaatans.edit', ['record' => $record->id]))
                    ->visible(fn (AssetPemanfaatan $record): bool => $record->kategori_status === 'sewa_aktif'),

                Tables\Actions\Action::make('bukaMaps')
                    ->label('Peta')
                    ->icon('heroicon-m-map-pin')
                    ->color('info')
                    ->url(fn (AssetPemanfaatan $record): string => "https://www.google.com/maps?q={$record->asset?->latitude},{$record->asset?->longitude}")
                    ->openUrlInNewTab()
                    ->visible(fn (AssetPemanfaatan $record): bool => !empty($record->asset?->latitude)),
            ])
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([10, 25, 50, 'all']);
    }
}
