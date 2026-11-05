@php($isEdit = isset($kamar))

<div class="space-y-4">
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="nomor" class="mb-1 block text-sm font-medium">Nomor kamar</label>
            <input id="nomor" name="nomor" type="text" required value="{{ old('nomor', $kamar->nomor ?? '') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="tipe" class="mb-1 block text-sm font-medium">Tipe</label>
            <input id="tipe" name="tipe" type="text" required value="{{ old('tipe', $kamar->tipe ?? '') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="harga_bulanan" class="mb-1 block text-sm font-medium">Harga per bulan (Rp)</label>
            <input id="harga_bulanan" name="harga_bulanan" type="number" min="0" step="1000" required
                   value="{{ old('harga_bulanan', $kamar->harga_bulanan ?? '') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="status" class="mb-1 block text-sm font-medium">Status</label>
            <select id="status" name="status" required class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                @foreach (['kosong' => 'Kosong', 'terisi' => 'Terisi', 'perbaikan' => 'Perbaikan'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $kamar->status ?? 'kosong') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label for="fasilitas" class="mb-1 block text-sm font-medium">Fasilitas kamar</label>
        <textarea id="fasilitas" name="fasilitas" rows="2" maxlength="500"
                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('fasilitas', $kamar->fasilitas ?? '') }}</textarea>
    </div>

    <div>
        <label for="catatan" class="mb-1 block text-sm font-medium">Catatan</label>
        <textarea id="catatan" name="catatan" rows="2" maxlength="500"
                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">{{ old('catatan', $kamar->catatan ?? '') }}</textarea>
    </div>

    <div>
        <label for="foto" class="mb-1 block text-sm font-medium">Foto kamar (JPG/PNG, maks. 2 MB)</label>
        <input id="foto" name="foto" type="file" accept="image/png,image/jpeg"
               class="w-full text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm">
        @if ($isEdit && $kamar->foto_path)
            <p class="mt-2 text-xs text-slate-500">Foto saat ini: {{ basename($kamar->foto_path) }}</p>
        @endif
    </div>
</div>
