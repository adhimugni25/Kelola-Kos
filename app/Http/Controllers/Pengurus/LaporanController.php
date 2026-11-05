<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\LaporanRequest;
use App\Services\ReportService;
use Dompdf\Dompdf;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(private readonly ReportService $reports) {}

    public function index(LaporanRequest $request): View
    {
        return view('pengurus.laporan.index', [
            'laporan' => $this->laporan($request),
        ]);
    }

    public function csv(LaporanRequest $request): StreamedResponse
    {
        $laporan = $this->laporan($request);

        $namaBerkas = 'laporan-pendapatan-'.$laporan['dari'].'-'.$laporan['sampai'].'.csv';

        return response()->streamDownload(function () use ($laporan): void {
            $keluaran = fopen('php://output', 'w');

            if ($keluaran === false) {
                return;
            }

            fputcsv($keluaran, ['Periode', 'Jumlah Transaksi', 'Total Pendapatan (Rp)']);

            foreach ($laporan['per_bulan'] as $baris) {
                fputcsv($keluaran, [
                    $baris['periode'],
                    $baris['jumlah_transaksi'],
                    number_format((float) $baris['total'], 0, '', ''),
                ]);
            }

            fputcsv($keluaran, [
                'TOTAL',
                $laporan['jumlah_transaksi'],
                number_format((float) $laporan['total'], 0, '', ''),
            ]);

            fclose($keluaran);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(LaporanRequest $request): Response
    {
        $laporan = $this->laporan($request);

        $fontDir = storage_path('fonts');
        $tempDir = storage_path('app/private/dompdf');

        File::ensureDirectoryExists($fontDir);
        File::ensureDirectoryExists($tempDir);

        $dompdf = new Dompdf([
            'fontDir' => $fontDir,
            'fontCache' => $fontDir,
            'tempDir' => $tempDir,
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
        ]);

        $dompdf->loadHtml(view('pengurus.laporan.pdf', [
            'laporan' => $laporan,
            'kos' => $request->user()->kos,
            'dicetakPada' => now(),
        ])->render());

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="laporan-pendapatan-'.$laporan['dari'].'-'.$laporan['sampai'].'.pdf"',
        ]);
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
    private function laporan(LaporanRequest $request): array
    {
        return $this->reports->laporanPendapatan(
            $request->user()->kos_id,
            Carbon::parse($request->validated('dari')),
            Carbon::parse($request->validated('sampai')),
        );
    }
}
