<?php

namespace App\Http\Requests\Pengurus;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKamarRequest extends FormRequest
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
            'nomor' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kamar', 'nomor')
                    ->where('kos_id', $this->user()->kos_id)
                    ->ignore($this->route('kamar')),
            ],
            'tipe' => ['required', 'string', 'max:100'],
            'harga_bulanan' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'fasilitas' => ['nullable', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['kosong', 'terisi', 'perbaikan'])],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
