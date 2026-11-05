@extends('layouts.app')

@section('title', 'Detail Tagihan — KelolaKos')

@section('content')
    <a href="{{ route('penghuni.tagihan.index') }}" class="text-sm text-emerald-700 hover:underline">&larr; Kembali</a>

    <h1 class="mt-3 text-2xl font-semibold">
        Tagihan {{ $tagihan->periode_bulan }}/{{ $tagihan->periode_tahun }}
    </h1>
    <p class="mt-1 text-sm text-slate-600">
        {{ $tagihan->pemesanan->kos->nama }} — Kamar {{ $tagihan->pemesanan->kamar->nomor }}
    </p>

    @if ($errors->any())
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Nominal sewa</dt>
                    <dd>Rp {{ number_format((float) $tagihan->nominal_sewa, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Biaya tambahan</dt>
                    <dd>Rp {{ number_format((float) $tagihan->biaya_tambahan, 0, ',', '.') }}</dd>
                </div>
                @if ($tagihan->keterangan_tambahan)
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Keterangan</dt>
                        <dd>{{ $tagihan->keterangan_tambahan }}</dd>
                    </div>
                @endif
                <div class="flex justify-between border-t border-slate-200 pt-2 font-semibold">
                    <dt>Total</dt>
                    <dd>Rp {{ number_format((float) $tagihan->total, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Jatuh tempo</dt>
                    <dd>{{ $tagihan->jatuh_tempo->format('d/m/Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Status</dt>
                    <dd>{{ str_replace('_', ' ', ucfirst($tagihan->status)) }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4">
            @if ($tagihan->status === 'belum_bayar')
                @if ($metodePembayaran->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                        Pengurus belum menambahkan metode pembayaran untuk kos ini. Hubungi pengurus untuk instruksi transfer.
                    </div>
                @else
                    <form method="POST" action="{{ route('penghuni.tagihan.bayar', $tagihan) }}"
                          enctype="multipart/form-data" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5">
                        @csrf

                        <div>
                            <label for="metode_pembayaran_id" class="mb-1 block text-sm font-medium">Metode pembayaran</label>
                            <select id="metode_pembayaran_id" name="metode_pembayaran_id" required
                                    class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                                @foreach ($metodePembayaran as $metode)
                                    <option value="{{ $metode->id }}" @selected(old('metode_pembayaran_id') == $metode->id)>
                                        {{ $metode->nama }} — {{ $metode->nomor }} ({{ $metode->atas_nama }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="jumlah" class="mb-1 block text-sm font-medium">Jumlah transfer (Rp)</label>
                            <input id="jumlah" name="jumlah" type="number" min="0" step="1" required
                                   value="{{ old('jumlah', (float) $tagihan->total) }}"
                                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                            <p class="mt-1 text-xs text-slate-500">Harus sama persis dengan total tagihan.</p>
                        </div>

                        <div>
                            <label for="bukti" class="mb-1 block text-sm font-medium">Bukti transfer (JPG/PNG/PDF, maks. 2 MB)</label>
                            <input id="bukti" name="bukti" type="file" required accept="image/jpeg,image/png,application/pdf"
                                   class="w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm">
                        </div>

                        <div>
                            <label for="catatan_penghuni" class="mb-1 block text-sm font-medium">Catatan (opsional)</label>
                            <textarea id="catatan_penghuni" name="catatan_penghuni" rows="2" maxlength="500"
                                      class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('catatan_penghuni') }}</textarea>
                        </div>

                        <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Kirim bukti pembayaran
                        </button>
                    </form>
                @endif
            @endif

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <h2 class="font-semibold">Riwayat pembayaran</h2>

                <ul class="mt-3 space-y-3 text-sm">
                    @forelse ($tagihan->pembayaran->sortByDesc('created_at') as $pembayaran)
                        <li class="rounded-md border border-slate-200 px-3 py-2">
                            <div class="flex items-center justify-between">
                                <span>{{ $pembayaran->created_at->format('d/m/Y H:i') }}</span>
                                <span class="text-xs font-medium">{{ ucfirst($pembayaran->status) }}</span>
                            </div>
                            <div class="text-xs text-slate-500">
                                Rp {{ number_format((float) $pembayaran->jumlah, 0, ',', '.') }}
                                · {{ $pembayaran->metodePembayaran?->nama }}
                            </div>
                            @if ($pembayaran->catatan_verifikasi)
                                <div class="mt-1 text-xs text-red-600">{{ $pembayaran->catatan_verifikasi }}</div>
                            @endif
                            <a href="{{ route('pembayaran.bukti', $pembayaran) }}" target="_blank" rel="noopener"
                               class="mt-1 inline-block text-xs text-emerald-700 hover:underline">
                                Lihat bukti
                            </a>
                        </li>
                    @empty
                        <li class="text-slate-500">Belum ada pembayaran.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
