<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:kos,slug'],
            'alamat' => ['required', 'string', 'max:1000'],
            'kota' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'kontak' => ['required', 'string', 'max:20'],
            'fasilitas_umum' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
