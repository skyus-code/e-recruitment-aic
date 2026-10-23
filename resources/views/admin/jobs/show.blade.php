<x-layouts.app title="Detail Lowongan">
    @php
        $maxWeight = max(1, (float) $job->keywords->max('weight'));
        $totalWeight = (float) $job->keywords->sum('weight');
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Kelola Lowongan', 'Detail Lowongan']"
            flow="ROUTE: admin.jobs.show · ID: {{ $job->id }}"
        />

        {{-- Header lowongan --}}
        <x-card class="border-t-4 border-t-blue-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="eyebrow">{{ $job->department?->name ?? 'Departemen' }}</p>
                    <h1 class="page-title mt-1">{{ $job->title }}</h1>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <x-badge variant="blue">{{ $job->job_type->label() }}</x-badge>
                        <x-badge :variant="$job->status->value === 'open' ? 'emerald' : 'red'">
                            {{ $job->status->label() }}
                        </x-badge>
                        <x-badge variant="slate">
                            <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg>
                            {{ $job->location ?? 'Seluruh Indonesia' }}
                        </x-badge>
                        @if ($job->salaryRange())
                            <x-badge variant="emerald">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33"/></svg>
                                {{ $job->salaryRange() }}
                            </x-badge>
                        @endif
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap gap-2.5">
                    <x-button href="{{ route('admin.jobs.edit', $job) }}" variant="primary">Edit Lowongan</x-button>
                    <x-button href="{{ route('admin.jobs.index') }}" variant="secondary">Kembali</x-button>
                </div>
            </div>
        </x-card>

        {{-- Konten inti --}}
        <div class="grid gap-6 lg:grid-cols-[1.5fr_1fr]">
            <div class="flex flex-col gap-6">
                {{-- Deskripsi --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Deskripsi</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Deskripsi Pekerjaan</h2>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">Field · description</span>
                    </div>

                    <div class="mt-4 text-sm leading-relaxed whitespace-pre-line text-slate-600">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </x-card>

                {{-- Persyaratan --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Persyaratan</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Persyaratan & Kualifikasi</h2>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">Field · requirements</span>
                    </div>

                    <div class="mt-4 text-sm leading-relaxed whitespace-pre-line text-slate-600">
                        {!! nl2br(e($job->requirements ?? '')) !!}
                    </div>
                </x-card>
            </div>

            {{-- Kolom kanan: kata kunci --}}
            <div class="flex flex-col gap-6">
                <x-card>
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">NLP Config</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Bobot Kata Kunci</h2>
                            <p class="mt-1 text-sm text-slate-500">Dipakai pipeline scoring terhadap CV pelamar.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-badge variant="solid-blue">{{ $job->keywords->count() }} keyword</x-badge>
                            <x-badge variant="amber">Total {{ number_format($totalWeight, 2, ',', '.') }}</x-badge>
                        </div>
                    </div>

                    @if ($job->keywords->isEmpty())
                        <p class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            Belum ada kata kunci untuk lowongan ini.
                            <a href="{{ route('admin.jobs.edit', $job) }}" class="mt-1 block font-semibold text-blue-900 transition-colors duration-200 hover:text-blue-700">
                                Konfigurasi bobot kata kunci →
                            </a>
                        </p>
                    @else
                        <ul class="mt-4 space-y-3.5">
                            @foreach ($job->keywords as $kw)
                                <li>
                                    <div class="mb-1.5 flex items-center justify-between gap-3">
                                        <span class="truncate text-sm font-semibold text-slate-800">{{ $kw->keyword }}</span>
                                        <span class="flex shrink-0 items-center gap-2">
                                            @if ($kw->is_required)
                                                <span class="font-mono text-[10px] font-semibold tracking-wider text-red-600 uppercase">Wajib</span>
                                            @endif
                                            <span class="font-mono text-xs font-bold text-slate-500">{{ $kw->weight }}</span>
                                        </span>
                                    </div>
                                    <x-progress size="sm" :value="$kw->weight" :max="$maxWeight" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>
        </div>

        {{-- Pelamar masuk --}}
        <x-card padding="p-0" class="overflow-hidden">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="eyebrow">Applications</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Pelamar Masuk</h2>
                    <p class="mt-1 text-sm text-slate-500">Seluruh lamaran yang masuk untuk formasi ini.</p>
                </div>
                <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                    {{ $job->applications->count() }} lamaran
                </span>
            </div>

            @if ($job->applications->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">Belum ada pelamar yang mengirim lamaran untuk formasi ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">Pelamar</th>
                                <th class="table-head">Email</th>
                                <th class="table-head">Status</th>
                                <th class="table-head">Skor</th>
                                <th class="table-head">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($job->applications as $app)
                                <tr class="table-row">
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $app->user->name }}</p>
                                    </td>
                                    <td class="table-cell font-mono text-xs text-slate-500">{{ $app->user->email }}</td>
                                    <td class="table-cell">
                                        <x-status-badge :status="$app->status" />
                                    </td>
                                    <td class="table-cell">
                                        @if ($app->score !== null)
                                            <x-badge variant="solid-blue">Skor {{ number_format((float) $app->score, 1) }}</x-badge>
                                        @else
                                            <x-badge variant="slate">Belum discoring</x-badge>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <x-button href="{{ route('admin.screening.show', $app) }}" size="sm" variant="secondary">
                                            Proses / Detail
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>