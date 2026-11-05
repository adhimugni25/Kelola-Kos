<?php

namespace App\Http\Requests\Penghuni;

use App\Models\Tagihan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UploadBuktiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tagihan = $this->route('tagihan');

        return $tagihan instanceof Tagihan
            && $this->user() !== null
            && $this->user()->can('view', $tagihan);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tagihan = $this->route('tagihan');

        return [
            'metode_pembayaran_id' => [
                'required',
                'integer',
                Rule::exists('metode_pembayaran', 'id')
                    ->where('kos_id', $tagihan?->kos_id)
                    ->where('is_active', true),
            ],
            'jumlah' => ['required', 'numeric', 'min:0'],
            'catatan_penghuni' => ['nullable', 'string', 'max:500'],
            'bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $tagihan = $this->route('tagihan');

                if (! $tagihan instanceof Tagihan || $validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($tagihan->status !== 'belum_bayar') {
                    $validator->errors()->add('jumlah', 'Tagihan ini tidak bisa dibayar pada status saat ini.');

                    return;
                }

                if (round((float) $this->input('jumlah'), 2) !== round((float) $tagihan->total, 2)) {
                    $validator->errors()->add('jumlah', 'Jumlah pembayaran harus sama persis dengan total tagihan.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'metode_pembayaran_id.exists' => 'Metode pembayaran tidak tersedia untuk kos ini.',
            'bukti.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'bukti.max' => 'Ukuran bukti maksimal 2 MB.',
        ];
    }
}
