<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'user:create-super-admin {email} {--name=Super Admin} {--password=}';

    protected $description = 'Membuat akun super admin (password tidak ditulis di kode)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $password = (string) ($this->option('password') ?? '');

        if ($password === '') {
            if (! $this->input->isInteractive()) {
                $this->error('Password wajib diisi lewat --password pada mode non-interaktif.');

                return self::FAILURE;
            }

            $password = (string) $this->secret('Password super admin');
            $konfirmasi = (string) $this->secret('Ulangi password');

            if ($password !== $konfirmasi) {
                $this->error('Konfirmasi password tidak cocok.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $pesan) {
                $this->error($pesan);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => (string) $this->option('name'),
            'email' => $email,
            'password' => $password,
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->info('Super admin berhasil dibuat. Segera ganti password setelah login pertama.');

        return self::SUCCESS;
    }
}
