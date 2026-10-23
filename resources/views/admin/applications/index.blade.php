<x-layouts.app title="Seleksi Berkas NLP">
    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Seleksi Berkas NLP']"
            flow="MODULE 03 · PARSER · ORDER: score DESC"
        />

        <x-section-header
            eyebrow="MODULE 03 · PARSER"
            title="Seleksi Berkas NLP"
            subtitle="Daftar lamaran diurutkan berdasarkan skor NLP tertinggi. Tekan Mulai Screening untuk menjalankan pipeline parsing, stemming, dan pencocokan kata kunci."
        >
            <x-slot name="actions">
                <x-button href="{{ route('admin.dashboard') }}" variant="secondary">Ke Overview</x-button>
            </x-slot>
        </x-section-header>

        {{-- Filter --}}
        <x-card>
            <form method="GET" action="{{ route('admin.screening.index') }}" class="flex flex-col gap-3 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <x-input
                        type="search"
                        name="q"
                        label="Cari Pelamar / Formasi"
                        placeholder="cth: Ahmad, Guru Tahfidz"
                        :value="$filters['q']"
                    />
                </div>

                <div class="w-full lg:w-52">
                    <x-select
                        name="status"
                        label="Filter Status"
                        :options="[
                            '' => 'Semua Status',
                            'applied' => 'Dilamar',
                            'screening' => 'Screening',
                            'passed' => 'Lolos Seleksi',
                            'interview' => 'Interview',
                            'hired' => 'Diterima',
                            'rejected' => 'Ditolak',
                        ]"
                        :selected="$filters['status']"
                    />
                </div>

                <div class="w-full lg:w-64">
                    <x-select
                        name="job"
                        label="Filter Formasi"
                        :options="$jobs->pluck('title', 'id')"
                        placeholder="Semua Formasi"
                        :selected="$filters['job']"
                    />
                </div>

                <div class="flex shrink-0 items-center gap-2">
                    <x-button type="submit" variant="primary">Terapkan Filter</x-button>

                    @if ($filters['q'] !== '' || $filters['status'] !== '' || $filters['job'] > 0)
                        <x-button href="{{ route('admin.screening.index') }}" variant="ghost">Reset</x-button>
                    @endif
                </div>
            </form>
        </x-card>

        @if ($applications->isEmpty())
            <x-empty-state
                title="Tidak ada lamaran ditemukan"
                description="Belum ada lamaran yang cocok dengan filter ini, atau belum ada pelamar yang mengirim berkas."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('admin.screening.index') }}" variant="secondary">Tampilkan Semua</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <x-card padding="p-0" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">#</th>
                                <th class="table-head">Pelamar</th>
                                <th class="table-head">Formasi</th>
                                <th class="table-head">Tanggal</th>
                                <th class="table-head">Skor</th>
                                <th class="table-head">Status</th>
                                <th class="table-head">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $a)
                                <tr class="table-row">
                                    <td class="table-cell font-mono text-xs text-slate-400">
                                        {{ $applications->firstItem() + $loop->index }}
                                    </td>
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $a->user->name }}</p>
                                        <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">{{ $a->user->email }}</p>
                                    </td>
                                    <td class="table-cell">
                                        <p class="text-slate-800">{{ $a->job->title }}</p>
                                        <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                            {{ $a->job->department?->name ?? 'Departemen' }}
                                        </p>
                                    </td>
                                    <td class="table-cell font-mono text-xs text-slate-500">
                                        {{ $a->applied_at?->format('d M Y') ?? '—' }}
                                    </td>
                                    <td class="table-cell">
                                        @if ($a->score !== null)
                                            <div class="w-32">
                                                <div class="flex items-center justify-between gap-2">
                                                    <span class="font-mono text-xs font-bold text-slate-900">
                                                        {{ number_format((float) $a->score, 1) }}
                                                    </span>
                                                    <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                                        {{ $a->score_details_count }} detail
                                                    </span>
                                                </div>
                                                <x-progress size="sm" :value="(int) $a->score" class="mt-1" />
                                            </div>
                                        @else
                                            <x-badge variant="slate">Belum discoring</x-badge>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <x-status-badge :status="$a->status" />
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            @if ($a->score === null)
                                                <form method="POST" action="{{ route('admin.screening.start', $a) }}">
                                                    @csrf
                                                    <x-button type="submit" variant="primary" size="sm">Mulai Screening</x-button>
                                                </form>
                                            @endif

                                            <x-button href="{{ route('admin.screening.show', $a) }}" variant="secondary" size="sm">
                                                Proses / Detail
                                            </x-button>

                                            @if (! in_array($a->status->value, ['rejected', 'hired'], true))
                                                <form method="POST" action="{{ route('admin.screening.decide', $a) }}" onsubmit="return confirm('Tolak lamaran ini?')">
                                                    @csrf
                                                    <input type="hidden" name="decision" value="rejected">
                                                    <x-button type="submit" variant="danger" size="sm">Quick Reject</x-button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-4 py-4">
                    {{ $applications->links() }}
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>