<?php

namespace App\Http\Requests\Pengurus;

use Illuminate\Foundation\Http\FormRequest;

class ImportCsvRequest extends FormRequest
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
            'berkas' => ['required', 'file', 'max:5120', 'extensions:csv'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'berkas.required' => 'Pilih berkas CSV terlebih dahulu.',
            'berkas.file' => 'Berkas tidak valid.',
            'berkas.max' => 'Ukuran berkas maksimal 5 MB.',
            'berkas.extensions' => 'Berkas harus berekstensi .csv.',
        ];
    }
}
