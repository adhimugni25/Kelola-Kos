<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Kamar;
use App\Models\Kos;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\Pemesanan;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Kata sandi khusus data demo lokal. Jangan dipakai di produksi.
     */
    private const KATA_SANDI_DEMO = 'Password123!';

    private const BUKTI_CONTOH_BASE64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2n2kAAAAASUVORK5CYII=';

    private const HARGA_KAMAR = [900000, 1000000, 1100000, 1200000, 950000, 1050000];

    public function run(): void
    {
        if (Kos::where('slug', 'kos-melati')->exists()) {
            $this->command?->warn('Data demo sudah ada. Jalankan migrate:fresh --seed untuk mengulang.');

            return;
        }

        $buktiPath = $this->siapkanBuktiContoh();

        $this->buatKos(
            nama: 'Kos Melati',
            slug: 'kos-melati',
            emailPengurus: 'pengurus.melati@kelolakos.test',
            alamat: 'Jl. Kaliurang Km. 5 No. 12',
            kota: 'Yogyakarta',
            penghuni: [
                ['nama' => 'Budi Santoso', 'email' => 'budi.santoso@kelolakos.test', 'telepon' => '081234567801'],
                ['nama' => 'Siti Aminah', 'email' => 'siti.aminah@kelolakos.test', 'telepon' => '081234567802'],
                ['nama' => 'Rudi Hartono', 'email' => 'rudi.hartono@kelolakos.test', 'telepon' => '081234567803'],
            ],
            buktiPath: $buktiPath,
        );

        $this->buatKos(
            nama: 'Kos Anggrek',
            slug: 'kos-anggrek',
            emailPengurus: 'pengurus.anggrek@kelolakos.test',
            alamat: 'Jl. Gejayan No. 45',
            kota: 'Yogyakarta',
            penghuni: [
                ['nama' => 'Dewi Lestari', 'email' => 'dewi.lestari@kelolakos.test', 'telepon' => '081234567804'],
                ['nama' => 'Agus Prasetyo', 'email' => 'agus.prasetyo@kelolakos.test', 'telepon' => '081234567805'],
                ['nama' => 'Rina Wulandari', 'email' => 'rina.wulandari@kelolakos.test', 'telepon' => '081234567806'],
            ],
            buktiPath: $buktiPath,
        );
    }

    /**
     * @param  array<int, array{nama: string, email: string, telepon: string}>  $penghuni
     */
    private function buatKos(
        string $nama,
        string $slug,
        string $emailPengurus,
        string $alamat,
        string $kota,
        array $penghuni,
        string $buktiPath,
    ): void {
        $kos = Kos::create([
            'nama' => $nama,
            'slug' => $slug,
            'alamat' => $alamat,
            'kota' => $kota,
            'deskripsi' => "{$nama} dengan kamar nyaman, lingkungan tenang, dan akses dekat kampus.",
            'kontak' => '081200000000',
            'fasilitas_umum' => 'WiFi, parkir motor, dapur bersama, keamanan 24 jam',
            'is_active' => true,
        ]);

        $pengurus = User::create([
            'name' => "Pengurus {$nama}",
            'email' => $emailPengurus,
            'password' => self::KATA_SANDI_DEMO,
            'role' => 'pengurus',
            'phone' => '081200000001',
            'kos_id' => $kos->id,
            'is_active' => true,
        ]);

        $metode = MetodePembayaran::create([
            'kos_id' => $kos->id,
            'tipe' => 'bank',
            'nama' => 'BCA',
            'nomor' => '1234567890',
            'atas_nama' => "Pemilik {$nama}",
            'is_active' => true,
        ]);

        $kamar = [];

        for ($nomor = 1; $nomor <= 6; $nomor++) {
            $kamar[] = Kamar::create([
                'kos_id' => $kos->id,
                'nomor' => 'A'.$nomor,
                'tipe' => $nomor <= 2 ? 'Standar' : ($nomor <= 4 ? 'AC' : 'VIP'),
                'harga_bulanan' => self::HARGA_KAMAR[$nomor - 1],
                'fasilitas' => 'Kasur, lemari, meja belajar, kamar mandi '.($nomor % 2 === 0 ? 'dalam' : 'luar'),
                'status' => match (true) {
                    $nomor <= 3 => 'terisi',
                    $nomor === 4 => 'perbaikan',
                    default => 'kosong',
                },
            ]);
        }

        foreach (array_slice($kamar, 0, 3) as $indeks => $kamarTerisi) {
            $dataPenghuni = $penghuni[$indeks];

            $akun = User::create([
                'name' => $dataPenghuni['nama'],
                'email' => $dataPenghuni['email'],
                'password' => self::KATA_SANDI_DEMO,
                'role' => 'penghuni',
                'phone' => $dataPenghuni['telepon'],
                'kos_id' => null,
                'is_active' => true,
            ]);

            $mulai = now()->startOfMonth()->subMonthsNoOverflow(2)->day(1);

            $pemesanan = Pemesanan::create([
                'kos_id' => $kos->id,
                'kamar_id' => $kamarTerisi->id,
                'user_id' => $akun->id,
                'tanggal_mulai' => $mulai->toDateString(),
                'durasi_bulan' => 12,
                'harga_bulanan' => $kamarTerisi->harga_bulanan,
                'berakhir_pada' => $mulai->copy()->addMonthsNoOverflow(12)->toDateString(),
                'status' => 'aktif',
                'disetujui_oleh' => $pengurus->id,
                'disetujui_pada' => $mulai,
            ]);

            $this->buatRiwayatTagihan($pemesanan, $metode, $pengurus, $indeks, $buktiPath);
        }
    }

    private function buatRiwayatTagihan(
        Pemesanan $pemesanan,
        MetodePembayaran $metode,
        User $pengurus,
        int $indeksPenghuni,
        string $buktiPath,
    ): void {
        foreach ([2, 1, 0] as $mundur) {
            $periode = now()->startOfMonth()->subMonthsNoOverflow($mundur);

            $status = $mundur > 0
                ? 'lunas'
                : match ($indeksPenghuni) {
                    0 => 'lunas',
                    1 => 'menunggu_verifikasi',
                    default => 'belum_bayar',
                };

            $tagihan = Tagihan::create([
                'kos_id' => $pemesanan->kos_id,
                'pemesanan_id' => $pemesanan->id,
                'periode_bulan' => (int) $periode->format('n'),
                'periode_tahun' => (int) $periode->format('Y'),
                'nominal_sewa' => $pemesanan->harga_bulanan,
                'biaya_tambahan' => 0,
                'total' => $pemesanan->harga_bulanan,
                'jatuh_tempo' => $periode->copy()->day(10)->toDateString(),
                'status' => $status,
            ]);

            if ($status === 'lunas') {
                $this->catatPembayaran($tagihan, $metode, $pengurus, 'diterima', $periode->copy()->day(8), $buktiPath);
            }

            if ($status === 'menunggu_verifikasi') {
                $this->catatPembayaran($tagihan, $metode, $pengurus, 'menunggu', null, $buktiPath);
            }
        }
    }

    private function catatPembayaran(
        Tagihan $tagihan,
        MetodePembayaran $metode,
        User $pengurus,
        string $status,
        ?Carbon $diverifikasiPada,
        string $buktiPath,
    ): void {
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $tagihan->id,
            'metode_pembayaran_id' => $metode->id,
            'jumlah' => $tagihan->total,
            'bukti_path' => $buktiPath,
            'catatan_penghuni' => $status === 'menunggu' ? 'Mohon diverifikasi, terima kasih.' : null,
            'status' => $status,
            'diverifikasi_oleh' => $status === 'diterima' ? $pengurus->id : null,
            'diverifikasi_pada' => $diverifikasiPada,
        ]);

        if ($status !== 'diterima') {
            return;
        }

        AuditLog::create([
            'user_id' => $pengurus->id,
            'kos_id' => $tagihan->kos_id,
            'aksi' => 'pembayaran.diterima',
            'entitas' => 'pembayaran',
            'entitas_id' => $pembayaran->id,
            'detail' => [
                'pembayaran_id' => $pembayaran->id,
                'jumlah' => (string) $tagihan->total,
            ],
            'ip' => '127.0.0.1',
            'user_agent' => 'DatabaseSeeder',
            'created_at' => $diverifikasiPada ?? now(),
        ]);
    }

    private function siapkanBuktiContoh(): string
    {
        $path = 'demo/bukti-contoh.png';

        if (! Storage::disk('bukti')->exists($path)) {
            Storage::disk('bukti')->put($path, base64_decode(self::BUKTI_CONTOH_BASE64));
        }

        return $path;
    }
}
