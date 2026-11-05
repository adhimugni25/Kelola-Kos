<?php

namespace App\Http\Requests\Pengurus;

use App\Models\Tagihan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTagihanRequest extends FormRequest
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
            'pemesanan_id' => [
                'required',
                'integer',
                Rule::exists('pemesanan', 'id')
                    ->where('kos_id', $this->user()->kos_id)
                    ->where('status', 'aktif'),
            ],
            'periode_bulan' => ['required', 'integer', 'min:1', 'max:12'],
            'periode_tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nominal_sewa' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'biaya_tambahan' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'keterangan_tambahan' => ['nullable', 'string', 'max:255'],
            'jatuh_tempo' => ['required', 'date'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $sudahAda = Tagihan::withoutGlobalScopes()
                    ->where('pemesanan_id', $this->integer('pemesanan_id'))
                    ->where('periode_bulan', $this->integer('periode_bulan'))
                    ->where('periode_tahun', $this->integer('periode_tahun'))
                    ->when($this->route('tagihan') !== null, fn ($query) => $query->whereKeyNot($this->route('tagihan')->id))
                    ->exists();

                if ($sudahAda) {
                    $validator->errors()->add('periode_bulan', 'Tagihan untuk periode ini sudah ada.');
                }
            },
        ];
    }
}
