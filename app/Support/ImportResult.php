<?php

namespace App\Support;

final class ImportResult
{
    /**
     * @param  array<int, string>  $errors
     * @param  array<int, array{nama: string, email: string, password: string}>  $kredensial
     */
    public function __construct(
        public readonly int $jumlahBerhasil,
        public readonly array $errors = [],
        public readonly array $kredensial = [],
    ) {}

    public function berhasil(): bool
    {
        return $this->errors === [];
    }
}
