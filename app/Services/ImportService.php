<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Kamar;
use App\Models\Pemesanan;
use App\Models\User;
use App\Support\ImportResult;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportService
{
    /**
     * @var array<int, string>
     */
    private const KOLOM_KAMAR = ['nomor', 'tipe', 'harga_bulanan', 'fasilitas', 'status'];

    /**
     * @var array<int, string>
     */
    private const KOLOM_PENGHUNI = ['nama', 'email', 'no_hp', 'nomor_kamar', 'tanggal_mulai', 'durasi_bulan'];

    /**
     * @var array<int, string>
     */
    private const STATUS_KAMAR = ['kosong', 'terisi', 'perbaikan'];

    public function imporKamar(User $pengurus, UploadedFile $berkas): ImportResult
    {
        $csv = $this->bacaCsv($berkas);

        $errors = $this->periksaHeader($csv['header'], self::KOLOM_KAMAR);

        if ($errors !== []) {
            return $this->gagal($pengurus, 'impor.kamar', $errors, 0);
        }

        if ($csv['data'] === []) {
            return $this->gagal($pengurus, 'impor.kamar', ['Berkas tidak berisi data.'], 0);
        }

        $nomorTerpakai = Kamar::withoutGlobalScopes()
            ->where('kos_id', $pengurus->kos_id)
            ->pluck('nomor')
            ->map(fn ($nomor): string => mb_strtolower(trim((string) $nomor)))
            ->all();

        $nomorDalamBerkas = [];
        $siap = [];
        $jumlahGagal = 0;

        foreach ($csv['data'] as $baris) {
            $nomorBaris = $baris['__baris'];
            $nomor = trim((string) $baris['nomor']);
            $tipe = trim((string) $baris['tipe']);
            $harga = trim((string) $baris['harga_bulanan']);
            $fasilitas = trim((string) $baris['fasilitas']);
            $status = trim((string) $baris['status']);
            $kunci = mb_strtolower($nomor);
            $masalah = [];

            if ($nomor === '') {
                $masalah[] = 'nomor wajib diisi';
            } elseif (in_array($kunci, $nomorTerpakai, true)) {
                $masalah[] = "nomor {$nomor} sudah dipakai";
            } elseif (isset($nomorDalamBerkas[$kunci])) {
                $masalah[] = "nomor {$nomor} duplikat di dalam berkas";
            }

            if ($tipe === '') {
                $masalah[] = 'tipe wajib diisi';
            }

            if (! is_numeric($harga) || (float) $harga <= 0) {
                $masalah[] = 'harga_bulanan harus berupa angka positif';
            }

            if (mb_strlen($fasilitas) > 500) {
                $masalah[] = 'fasilitas maksimal 500 karakter';
            }

            if (! in_array($status, self::STATUS_KAMAR, true)) {
                $masalah[] = 'status harus kosong, terisi, atau perbaikan';
            }

            if ($masalah !== []) {
                $jumlahGagal++;

                foreach ($masalah as $pesan) {
                    $errors[] = "Baris {$nomorBaris}: {$pesan}.";
                }

                continue;
            }

            $nomorDalamBerkas[$kunci] = true;
            $sekarang = now();

            $siap[] = [
                'kos_id' => $pengurus->kos_id,
                'nomor' => $nomor,
                'tipe' => $tipe,
                'harga_bulanan' => (float) $harga,
                'fasilitas' => $fasilitas === '' ? null : $fasilitas,
                'status' => $status,
                'created_at' => $sekarang,
                'updated_at' => $sekarang,
            ];
        }

        if ($errors !== []) {
            return $this->gagal($pengurus, 'impor.kamar', $errors, $jumlahGagal);
        }

        DB::transaction(fn () => Kamar::insert($siap));

        $this->catat($pengurus, 'impor.kamar', count($siap), 0, []);

        return new ImportResult(count($siap));
    }

    public function imporPenghuni(User $pengurus, UploadedFile $berkas): ImportResult
    {
        $csv = $this->bacaCsv($berkas);

        $errors = $this->periksaHeader($csv['header'], self::KOLOM_PENGHUNI);

        if ($errors !== []) {
            return $this->gagal($pengurus, 'impor.penghuni', $errors, 0);
        }

        if ($csv['data'] === []) {
            return $this->gagal($pengurus, 'impor.penghuni', ['Berkas tidak berisi data.'], 0);
        }

        $emailDalamBerkas = [];
        $kamarDalamBerkas = [];
        $siap = [];
        $jumlahGagal = 0;

        foreach ($csv['data'] as $baris) {
            $nomorBaris = $baris['__baris'];
            $nama = trim((string) $baris['nama']);
            $email = mb_strtolower(trim((string) $baris['email']));
            $noHp = trim((string) $baris['no_hp']);
            $nomorKamar = trim((string) $baris['nomor_kamar']);
            $tanggalMulai = trim((string) $baris['tanggal_mulai']);
            $durasi = trim((string) $baris['durasi_bulan']);
            $masalah = [];

            if ($nama === '' || mb_strlen($nama) > 255) {
                $masalah[] = 'nama wajib diisi dan maksimal 255 karakter';
            }

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $masalah[] = 'email tidak valid';
            } elseif (isset($emailDalamBerkas[$email])) {
                $masalah[] = "email {$email} duplikat di dalam berkas";
            }

            if ($noHp !== '' && mb_strlen($noHp) > 20) {
                $masalah[] = 'no_hp maksimal 20 karakter';
            }

            $kamar = null;

            if ($nomorKamar === '') {
                $masalah[] = 'nomor_kamar wajib diisi';
            } else {
                $kamar = Kamar::withoutGlobalScopes()
                    ->where('kos_id', $pengurus->kos_id)
                    ->whereRaw('lower(nomor) = ?', [mb_strtolower($nomorKamar)])
                    ->first();

                if ($kamar === null) {
                    $masalah[] = "nomor kamar {$nomorKamar} tidak ditemukan di kos ini";
                } elseif (isset($kamarDalamBerkas[$kamar->id])) {
                    $masalah[] = "nomor kamar {$nomorKamar} dipakai lebih dari sekali di dalam berkas";
                } elseif ($kamar->status !== 'kosong') {
                    $masalah[] = "nomor kamar {$nomorKamar} tidak kosong";
                }
            }

            $mulai = null;

            if ($tanggalMulai === '' || ! Carbon::hasFormat($tanggalMulai, 'Y-m-d')) {
                $masalah[] = 'tanggal_mulai harus berformat YYYY-MM-DD';
            } else {
                $mulai = Carbon::createFromFormat('!Y-m-d', $tanggalMulai);

                if ($mulai->isFuture()) {
                    $masalah[] = 'tanggal_mulai tidak boleh di masa depan';
                }
            }

            if (! ctype_digit($durasi) || (int) $durasi < 1 || (int) $durasi > 24) {
                $masalah[] = 'durasi_bulan harus bilangan bulat 1 sampai 24';
            }

            if ($email !== '') {
                $akunLama = User::where('email', $email)->first();

                if ($akunLama !== null && $akunLama->role !== 'penghuni') {
                    $masalah[] = "email {$email} sudah dipakai akun {$akunLama->role}";
                } elseif ($akunLama !== null && mb_strtolower(trim($akunLama->name)) !== mb_strtolower($nama)) {
                    $masalah[] = "email {$email} sudah terdaftar dengan nama berbeda ({$akunLama->name})";
                }
            }

            if ($masalah !== []) {
                $jumlahGagal++;

                foreach ($masalah as $pesan) {
                    $errors[] = "Baris {$nomorBaris}: {$pesan}.";
                }

                continue;
            }

            $emailDalamBerkas[$email] = true;
            $kamarDalamBerkas[$kamar->id] = true;

            $siap[] = [
                'nama' => $nama,
                'email' => $email,
                'no_hp' => $noHp === '' ? null : $noHp,
                'kamar_id' => $kamar->id,
                'harga_bulanan' => $kamar->harga_bulanan,
                'tanggal_mulai' => $mulai->toDateString(),
                'durasi_bulan' => (int) $durasi,
            ];
        }

        if ($errors !== []) {
            return $this->gagal($pengurus, 'impor.penghuni', $errors, $jumlahGagal);
        }

        $kredensial = [];

        DB::transaction(function () use ($pengurus, $siap, &$kredensial): void {
            foreach ($siap as $baris) {
                $akun = User::where('email', $baris['email'])->first();

                if ($akun === null) {
                    $password = Str::password(12);

                    $akun = User::create([
                        'name' => $baris['nama'],
                        'email' => $baris['email'],
                        'phone' => $baris['no_hp'],
                        'role' => 'penghuni',
                        'password' => $password,
                        'is_active' => true,
                    ]);

                    $kredensial[] = [
                        'nama' => $baris['nama'],
                        'email' => $baris['email'],
                        'password' => $password,
                    ];
                }

                $mulai = Carbon::parse($baris['tanggal_mulai']);

                Pemesanan::create([
                    'kos_id' => $pengurus->kos_id,
                    'kamar_id' => $baris['kamar_id'],
                    'user_id' => $akun->id,
                    'tanggal_mulai' => $mulai->toDateString(),
                    'durasi_bulan' => $baris['durasi_bulan'],
                    'harga_bulanan' => $baris['harga_bulanan'],
                    'berakhir_pada' => $mulai->copy()->addMonthsNoOverflow($baris['durasi_bulan'])->toDateString(),
                    'status' => 'aktif',
                    'disetujui_oleh' => $pengurus->id,
                    'disetujui_pada' => now(),
                ]);

                Kamar::withoutGlobalScopes()
                    ->whereKey($baris['kamar_id'])
                    ->update(['status' => 'terisi']);
            }
        });

        $this->catat($pengurus, 'impor.penghuni', count($siap), 0, []);

        return new ImportResult(count($siap), [], $kredensial);
    }

    /**
     * @return array{header: array<int, string>, data: array<int, array<string, string|int>>}
     */
    private function bacaCsv(UploadedFile $berkas): array
    {
        $handle = fopen($berkas->getRealPath(), 'rb');

        if ($handle === false) {
            return ['header' => [], 'data' => []];
        }

        $header = [];
        $data = [];
        $nomorBaris = 0;

        while (($kolom = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            $nomorBaris++;

            if ($nomorBaris === 1) {
                $kolom[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) ($kolom[0] ?? ''));
            }

            $kolom = array_map(fn ($nilai): string => trim((string) $nilai), $kolom);

            if (array_filter($kolom, fn (string $nilai): bool => $nilai !== '') === []) {
                continue;
            }

            if ($header === []) {
                $header = array_map(fn (string $nilai): string => mb_strtolower($nilai), $kolom);

                continue;
            }

            $baris = ['__baris' => $nomorBaris];

            foreach ($header as $indeks => $namaKolom) {
                $baris[$namaKolom] = $kolom[$indeks] ?? '';
            }

            $data[] = $baris;
        }

        fclose($handle);

        return ['header' => $header, 'data' => $data];
    }

    /**
     * @param  array<int, string>  $header
     * @param  array<int, string>  $wajib
     * @return array<int, string>
     */
    private function periksaHeader(array $header, array $wajib): array
    {
        if ($header === []) {
            return ['Berkas CSV kosong atau header tidak terbaca.'];
        }

        $errors = [];

        foreach ($wajib as $kolom) {
            if (! in_array($kolom, $header, true)) {
                $errors[] = "Kolom {$kolom} tidak ditemukan pada header CSV.";
            }
        }

        return $errors;
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function gagal(User $pengurus, string $aksi, array $errors, int $jumlahGagal): ImportResult
    {
        $this->catat($pengurus, $aksi, 0, $jumlahGagal, $errors);

        return new ImportResult(0, $errors);
    }

    /**
     * @param  array<int, string>  $errors
     */
    private function catat(User $pengurus, string $aksi, int $sukses, int $gagal, array $errors): void
    {
        AuditLog::create([
            'user_id' => $pengurus->id,
            'kos_id' => $pengurus->kos_id,
            'aksi' => $aksi,
            'entitas' => 'impor',
            'entitas_id' => null,
            'detail' => [
                'jumlah_sukses' => $sukses,
                'jumlah_gagal' => $gagal,
                'errors' => array_slice($errors, 0, 20),
            ],
            'ip' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
