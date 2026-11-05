<?php

namespace App\Http\Requests\Penghuni;

use App\Models\Kamar;
use App\Models\Pemesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePemesananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPenghuni() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'kamar_id' => ['required', 'integer', 'exists:kamar,id'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'durasi_bulan' => ['required', 'integer', 'min:1', 'max:24'],
            'catatan' => ['nullable', 'string', 'max:1000'],
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

                $kamar = Kamar::withoutGlobalScopes()->with('kos')->find($this->integer('kamar_id'));

                if ($kamar === null) {
                    return;
                }

                if ($kamar->status !== 'kosong') {
                    $validator->errors()->add('kamar_id', 'Kamar ini sudah tidak kosong.');
                }

                if (! $kamar->kos?->is_active) {
                    $validator->errors()->add('kamar_id', 'Kos ini belum menerima pemesanan.');
                }

                $punyaPemesananAktif = Pemesanan::where('user_id', $this->user()->id)
                    ->whereIn('status', ['menunggu', 'aktif'])
                    ->exists();

                if ($punyaPemesananAktif) {
                    $validator->errors()->add('kamar_id', 'Anda masih punya pengajuan atau sewa yang berjalan.');
                }
            },
        ];
    }
}
