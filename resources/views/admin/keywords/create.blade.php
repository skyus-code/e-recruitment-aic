<x-layouts.app title="Tambah Keyword">
    <div class="space-y-6">
        <div>
            <p class="eyebrow">Master & Settings</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Tambah Keyword</h1>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">
                Keyword baru dipakai oleh pipeline screening untuk mencocokkan CV pelamar.
            </p>
        </div>

        <x-card>
            <form method="POST" action="{{ route('admin.keywords.store') }}" class="space-y-4">
                @csrf

                <x-select
                    name="job_id"
                    label="Lowongan"
                    :options="$jobs->mapWithKeys(fn ($job) => [$job->id => $job->title])->all()"
                    :selected="old('job_id')"
                    placeholder="Pilih lowongan"
                    required
                />

                <x-input name="keyword" label="Keyword" placeholder="cth: fikih" :value="old('keyword')" required />

                <x-input name="weight" label="Bobot (1–100)" type="number" :value="old('weight', 1)" required />

                <x-input name="category" label="Kategori (opsional)" placeholder="cth: hard_skill" :value="old('category')" />

                @if ($errors->any())
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <x-button type="submit">Simpan</x-button>
                    <x-button href="{{ route('admin.keywords.index') }}" variant="secondary">Batal</x-button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
