<?php

namespace App\Http\Requests\Pengurus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetodePembayaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPengurus() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipe' => ['required', Rule::in(['bank', 'ewallet'])],
            'nama' => ['required', 'string', 'max:100'],
            'nomor' => ['required', 'string', 'max:50'],
            'atas_nama' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
