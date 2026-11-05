@php($isEdit = isset($kos))

<div class="space-y-4">
    <div>
        <label for="nama" class="mb-1 block text-sm font-medium">Nama kos</label>
        <input id="nama" name="nama" type="text" required value="{{ old('nama', $kos->nama ?? '') }}"
               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
    </div>

    <div>
        <label for="slug" class="mb-1 block text-sm font-medium">Slug halaman publik</label>
        <input id="slug" name="slug" type="text" required value="{{ old('slug', $kos->slug ?? '') }}"
               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        <p class="mt-1 text-xs text-slate-500">Dipakai di alamat /k/slug. Hanya huruf, angka, dan tanda hubung.</p>
    </div>

    <div>
        <label for="alamat" class="mb-1 block text-sm font-medium">Alamat</label>
        <textarea id="alamat" name="alamat" rows="2" required
                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('alamat', $kos->alamat ?? '') }}</textarea>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="kota" class="mb-1 block text-sm font-medium">Kota</label>
            <input id="kota" name="kota" type="text" required value="{{ old('kota', $kos->kota ?? '') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>
        <div>
            <label for="kontak" class="mb-1 block text-sm font-medium">Kontak (HP/WA)</label>
            <input id="kontak" name="kontak" type="text" required value="{{ old('kontak', $kos->kontak ?? '') }}"
                   class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">
        </div>
    </div>

    <div>
        <label for="deskripsi" class="mb-1 block text-sm font-medium">Deskripsi</label>
        <textarea id="deskripsi" name="deskripsi" rows="3"
                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('deskripsi', $kos->deskripsi ?? '') }}</textarea>
    </div>

    <div>
        <label for="fasilitas_umum" class="mb-1 block text-sm font-medium">Fasilitas umum</label>
        <textarea id="fasilitas_umum" name="fasilitas_umum" rows="2"
                  class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-100">{{ old('fasilitas_umum', $kos->fasilitas_umum ?? '') }}</textarea>
    </div>

    @unless ($isEdit)
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300">
            Langsung aktifkan kos ini
        </label>
    @endunless
</div>
