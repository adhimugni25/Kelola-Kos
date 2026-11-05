<?php

namespace App\Http\Requests\Pengurus;

use Illuminate\Foundation\Http\FormRequest;

class RejectPembayaranRequest extends FormRequest
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
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ];
    }
}
