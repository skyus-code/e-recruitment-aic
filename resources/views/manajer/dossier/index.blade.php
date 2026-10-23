<x-layouts.app title="Review Dossier">
    <div class="space-y-6">
        <x-section-header
            eyebrow="MODULE 03 · DOSSIER"
            title="Review Dossier & Berkas Kandidat"
            subtitle="Tinjau CV, ijazah, teks hasil parse, dan data stemming tiap kandidat."
        >
            <x-slot name="actions">
                <x-button href="{{ route('manajer.dashboard') }}" variant="secondary">Portal Keputusan</x-button>
                <x-button href="{{ route('manajer.decisions.index') }}" variant="ghost">Rekap Keputusan</x-button>
            </x-slot>
        </x-section-header>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                {{ $applications->total() }} kandidat · status lolos / interview / diterima
            </span>
            <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                SORT: score DESC
            </span>
        </div>

        @if ($applications->isEmpty())
            <x-empty-state
                title="Belum ada dossier untuk ditinjau"
                description="Dossier muncul setelah kandidat lolos seleksi, dijadwalkan interview, atau diterima."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('manajer.dashboard') }}" variant="secondary">Kembali ke Dashboard</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <x-card padding="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1080px] text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">Pelamar</th>
                                <th class="table-head">Formasi</th>
                                <th class="table-head">Status</th>
                                <th class="table-head text-right">Skor</th>
                                <th class="table-head text-right">Detail Keyword</th>
                                <th class="table-head text-right">Kata Stemmer</th>
                                <th class="table-head">Berkas</th>
                                <th class="table-head text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                <tr class="table-row">
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $application->user->name }}</p>
                                        <p class="mt-0.5 text-xs break-all text-slate-500">{{ $application->user->email }}</p>
                                        @if ($application->user->phone)
                                            <p class="mt-1 font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                                {{ $application->user->phone }}
                                            </p>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <p class="font-medium text-slate-700">{{ $application->job->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ $application->job->department?->name ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="table-cell">
                                        <x-status-badge :status="$application->status" />
                                    </td>
                                    <td class="table-cell text-right">
                                        <x-badge variant="solid-blue">Skor NLP {{ $application->score ?? '—' }}</x-badge>
                                    </td>
                                    <td class="table-cell text-right">
                                        <span class="font-mono text-xs font-semibold text-slate-700">
                                            {{ $application->score_details_count }}
                                        </span>
                                    </td>
                                    <td class="table-cell text-right">
                                        <span class="font-mono text-xs font-semibold text-slate-700">
                                            {{ $application->stemmer_data_count }}
                                        </span>
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex flex-wrap gap-2">
                                            <a
                                                href="{{ route('files.cv', $application) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-blue-900 ring-1 ring-inset ring-blue-200 transition-all duration-200 ease-in-out hover:bg-blue-900 hover:text-white hover:ring-blue-900"
                                            >
                                                CV
                                            </a>
                                            <a
                                                href="{{ route('files.ijazah', $application) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-amber-700 ring-1 ring-inset ring-amber-200 transition-all duration-200 ease-in-out hover:bg-amber-600 hover:text-white hover:ring-amber-600"
                                            >
                                                Ijazah
                                            </a>
                                        </div>
                                    </td>
                                    <td class="table-cell text-right">
                                        <x-button href="{{ route('manajer.dossier.show', $application) }}" size="sm">
                                            Buka Dossier
                                        </x-button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
