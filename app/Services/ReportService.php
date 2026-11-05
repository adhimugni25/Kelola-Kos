<?php

namespace App\Services;

use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Support\Carbon;

class ReportService
{
    /**
     * @var array<int, string>
     */
    private const BULAN_INDONESIA = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
        7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * @return array<string, int|float>
     */
    public function ringkasanDashboard(int $kosId): array
    {
        $totalKamar = Kamar::withoutGlobalScopes()->where('kos_id', $kosId)->count();

        $perStatus = Kamar::withoutGlobalScopes()
            ->where('kos_id', $kosId)
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        return [
            'total_kamar' => $totalKamar,
            'kamar_kosong' => (int) ($perStatus['kosong'] ?? 0),
            'kamar_terisi' => (int) ($perStatus['terisi'] ?? 0),
            'kamar_perbaikan' => (int) ($perStatus['perbaikan'] ?? 0),
            'tingkat_keterisian' => $totalKamar === 0
                ? 0.0
                : round(((int) ($perStatus['terisi'] ?? 0)) / $totalKamar * 100, 1),
            'pendapatan_bulan_ini' => $this->totalDiterima($kosId, now()->startOfMonth(), now()->endOfMonth()),
            'tagihan_belum_lunas' => (float) Tagihan::withoutGlobalScopes()
                ->where('kos_id', $kosId)
                ->whereIn('status', ['belum_bayar', 'menunggu_verifikasi'])
                ->sum('total'),
            'pembayaran_menunggu' => Pembayaran::query()
                ->where('status', 'menunggu')
                ->whereHas('tagihan', fn ($query) => $query->where('kos_id', $kosId))
                ->count(),
        ];
    }

    /**
     * @return array{
     *     dari: string,
     *     sampai: string,
     *     per_bulan: array<int, array{periode: string, label: string, jumlah_transaksi: int, total: float}>,
     *     total: float,
     *     jumlah_transaksi: int
     * }
     */
    public function laporanPendapatan(int $kosId, Carbon $dari, Carbon $sampai): array
    {
        $pembayaran = Pembayaran::query()
            ->where('status', 'diterima')
            ->whereBetween('diverifikasi_pada', [
                $dari->copy()->startOfDay(),
                $sampai->copy()->endOfDay(),
            ])
            ->whereHas('tagihan', fn ($query) => $query->where('kos_id', $kosId))
            ->get(['jumlah', 'diverifikasi_pada']);

        $perBulan = $pembayaran->groupBy(
            fn (Pembayaran $item): string => $item->diverifikasi_pada->format('Y-m')
        );

        $baris = [];
        $kursor = $dari->copy()->startOfMonth();
        $akhir = $sampai->copy()->startOfMonth();

        while ($kursor->lessThanOrEqualTo($akhir)) {
            $periode = $kursor->format('Y-m');
            $items = $perBulan->get($periode, collect());

            $baris[] = [
                'periode' => $periode,
                'label' => self::BULAN_INDONESIA[(int) $kursor->format('n')].' '.$kursor->format('Y'),
                'jumlah_transaksi' => $items->count(),
                'total' => (float) $items->sum(fn (Pembayaran $item): float => (float) $item->jumlah),
            ];

            $kursor->addMonthNoOverflow();
        }

        return [
            'dari' => $dari->toDateString(),
            'sampai' => $sampai->toDateString(),
            'per_bulan' => $baris,
            'total' => (float) $pembayaran->sum(fn (Pembayaran $item): float => (float) $item->jumlah),
            'jumlah_transaksi' => $pembayaran->count(),
        ];
    }

    private function totalDiterima(int $kosId, Carbon $dari, Carbon $sampai): float
    {
        return (float) Pembayaran::query()
            ->where('status', 'diterima')
            ->whereBetween('diverifikasi_pada', [$dari, $sampai])
            ->whereHas('tagihan', fn ($query) => $query->where('kos_id', $kosId))
            ->sum('jumlah');
    }
}
