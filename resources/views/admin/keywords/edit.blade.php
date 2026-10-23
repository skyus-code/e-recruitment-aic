<x-layouts.app title="Edit Keyword">
    <div class="space-y-6">
        <div>
            <p class="eyebrow">Master & Settings</p>
            <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Edit Keyword</h1>
        </div>

        <x-card>
            <form method="POST" action="{{ route('admin.keywords.update', $keyword) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-select
                    name="job_id"
                    label="Lowongan"
                    :options="$jobs->mapWithKeys(fn ($job) => [$job->id => $job->title])->all()"
                    :selected="old('job_id', $keyword->job_id)"
                    required
                />

                <x-input name="keyword" label="Keyword" placeholder="cth: fikih" :value="old('keyword', $keyword->keyword)" required />

                <x-input name="weight" label="Bobot (1–100)" type="number" :value="old('weight', $keyword->weight)" required />

                <x-input name="category" label="Kategori (opsional)" placeholder="cth: hard_skill" :value="old('category', $keyword->category)" />

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
