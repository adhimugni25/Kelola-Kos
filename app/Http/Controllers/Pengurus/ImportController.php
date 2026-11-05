<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pengurus\ImportCsvRequest;
use App\Services\ImportService;
use App\Support\ImportResult;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function __construct(private readonly ImportService $imports) {}

    public function index(): View
    {
        return view('pengurus.impor.index');
    }

    public function templateKamar(): StreamedResponse
    {
        return $this->unduhTemplate(
            'template-impor-kamar.csv',
            ['nomor', 'tipe', 'harga_bulanan', 'fasilitas', 'status'],
            ['A1', 'Standard', 900000, 'AC, WiFi', 'kosong'],
        );
    }

    public function templatePenghuni(): StreamedResponse
    {
        return $this->unduhTemplate(
            'template-impor-penghuni.csv',
            ['nama', 'email', 'no_hp', 'nomor_kamar', 'tanggal_mulai', 'durasi_bulan'],
            ['Budi Santoso', 'budi@example.com', '08123456789', 'A1', '2026-01-10', 6],
        );
    }

    public function imporKamar(ImportCsvRequest $request): RedirectResponse
    {
        return $this->selesaikan(
            $this->imports->imporKamar($request->user(), $request->file('berkas')),
            'kamar',
        );
    }

    public function imporPenghuni(ImportCsvRequest $request): RedirectResponse
    {
        return $this->selesaikan(
            $this->imports->imporPenghuni($request->user(), $request->file('berkas')),
            'penghuni',
        );
    }

    /**
     * @param  array<int, string>  $header
     * @param  array<int, string|int>  $contoh
     */
    private function unduhTemplate(string $namaBerkas, array $header, array $contoh): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $contoh): void {
            $keluaran = fopen('php://output', 'w');

            if ($keluaran === false) {
                return;
            }

            fputcsv($keluaran, $header);
            fputcsv($keluaran, $contoh);
            fclose($keluaran);
        }, $namaBerkas, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function selesaikan(ImportResult $hasil, string $jenis): RedirectResponse
    {
        if (! $hasil->berhasil()) {
            return redirect()->route('pengurus.impor.index')
                ->withErrors(['impor' => $hasil->errors])
                ->with('error', "Impor {$jenis} gagal. Tidak ada data yang disimpan.");
        }

        $redirect = redirect()->route('pengurus.impor.index')
            ->with('status', "{$hasil->jumlahBerhasil} data {$jenis} berhasil diimpor.");

        if ($hasil->kredensial !== []) {
            $redirect->with('kredensial', $hasil->kredensial);
        }

        return $redirect;
    }
}
