<?php

namespace Tests\Feature\Reports;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    private Kos $kos;

    private User $pengurus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kos = Kos::factory()->create();
        $this->pengurus = User::factory()->pengurus($this->kos)->create();
    }

    public function test_revenue_report_uses_cash_basis_and_own_kos_only(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->catatPembayaran($this->kos, $penghuni, '2026-01-15 10:00:00', 900000);
        $this->catatPembayaran($this->kos, $penghuni, '2026-02-10 10:00:00', 450000);
        $this->catatPembayaran($this->kos, $penghuni, '2026-03-01 10:00:00', 800000, 'menunggu');
        $this->catatPembayaran(Kos::factory()->create(), $penghuni, '2026-01-20 10:00:00', 700000);

        $this->actingAs($this->pengurus)
            ->get(route('pengurus.laporan.index', [
                'dari' => '2026-01-01',
                'sampai' => '2026-03-31',
            ]))
            ->assertOk()
            ->assertViewHas('laporan', function (array $laporan): bool {
                $perBulan = collect($laporan['per_bulan'])->keyBy('periode');

                return (float) $laporan['total'] === 1350000.0
                    && $laporan['jumlah_transaksi'] === 2
                    && (float) $perBulan['2026-01']['total'] === 900000.0
                    && (float) $perBulan['2026-02']['total'] === 450000.0
                    && (float) $perBulan['2026-03']['total'] === 0.0;
            });
    }

    public function test_date_range_is_validated(): void
    {
        $this->actingAs($this->pengurus)
            ->get(route('pengurus.laporan.index', [
                'dari' => '2026-03-31',
                'sampai' => '2026-01-01',
            ]))
            ->assertSessionHasErrors('sampai');
    }

    public function test_csv_export_streams_report_rows(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);
        $this->catatPembayaran($this->kos, $penghuni, '2026-01-15 10:00:00', 900000);

        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan.csv', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));

        $isi = $response->streamedContent();

        $this->assertStringContainsString('2026-01', $isi);
        $this->assertStringContainsString('900000', $isi);
    }

    public function test_pdf_export_returns_pdf_document(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);
        $this->catatPembayaran($this->kos, $penghuni, '2026-01-15 10:00:00', 900000);

        $response = $this->actingAs($this->pengurus)->get(route('pengurus.laporan.pdf', [
            'dari' => '2026-01-01',
            'sampai' => '2026-01-31',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_penghuni_cannot_access_reports(): void
    {
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        $this->actingAs($penghuni)->get(route('pengurus.laporan.index'))->assertForbidden();
        $this->actingAs($penghuni)->get(route('pengurus.laporan.csv'))->assertForbidden();
    }

    private function catatPembayaran(
        Kos $kos,
        User $penghuni,
        string $diverifikasiPada,
        float $jumlah,
        string $status = 'diterima',
    ): Pembayaran {
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);

        $pemesanan = Pemesanan::factory()->aktif()->create([
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'user_id' => $penghuni->id,
            'harga_bulanan' => $jumlah,
        ]);

        $tagihan = Tagihan::factory()->create([
            'kos_id' => $kos->id,
            'pemesanan_id' => $pemesanan->id,
            'nominal_sewa' => $jumlah,
            'total' => $jumlah,
        ]);

        return Pembayaran::factory()->create([
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran_id' => MetodePembayaran::factory()->create(['kos_id' => $kos->id])->id,
            'jumlah' => $jumlah,
            'status' => $status,
            'diverifikasi_pada' => $diverifikasiPada,
        ]);
    }
}
