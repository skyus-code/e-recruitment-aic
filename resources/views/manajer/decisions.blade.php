<x-layouts.app title="Rekap & Offering Log">
    @php
        $filters = [
            'all' => 'Semua',
            'applied' => 'Dilamar',
            'screening' => 'Screening',
            'passed' => 'Lolos Seleksi',
            'interview' => 'Interview',
            'hired' => 'Diterima',
            'rejected' => 'Ditolak',
        ];
    @endphp

    <div class="space-y-6">
        <x-section-header
            eyebrow="MODULE 04 · OFFERING"
            title="Rekap Keputusan & Log Offering"
            subtitle="Seluruh kandidat berskor NLP, diurutkan dari skor tertinggi, beserta status akhir, jadwal wawancara, dan catatan offering."
        >
            <x-slot name="actions">
                @foreach ($filters as $value => $label)
                    <a
                        href="{{ route('manajer.decisions.index', ['status' => $value]) }}"
                        @class([
                            'rounded-full px-3 py-1.5 text-[11px] font-semibold tracking-wide transition-all duration-200 ease-in-out',
                            'bg-blue-900 text-white shadow-sm ring-1 ring-blue-900' => $status === $value,
                            'border border-slate-200 bg-white text-slate-600 hover:border-blue-100 hover:bg-blue-100 hover:text-blue-900' => $status !== $value,
                        ])
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </x-slot>
        </x-section-header>

        <div class="grid gap-4 sm:grid-cols-3">
            <x-stat-card
                label="Diterima"
                :value="$stats['hired']"
                accent="emerald"
                meta="final decision: hire"
            />
            <x-stat-card
                label="Ditolak"
                :value="$stats['rejected']"
                accent="red"
                meta="final decision: reject"
            />
            <x-stat-card
                label="Menunggu"
                :value="$stats['pending']"
                accent="amber"
                meta="belum ditentukan keputusan"
            />
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                Filter: {{ $filters[$status] ?? strtoupper($status) }} · {{ $applications->total() }} kandidat
            </span>
            <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                SORT: score DESC
            </span>
        </div>

        @if ($applications->isEmpty())
            <x-empty-state
                title="Tidak ada data pada filter ini"
                description="Belum ada kandidat dengan status yang dipilih. Coba pilih filter lain untuk melihat rekap keputusan."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('manajer.decisions.index', ['status' => 'all']) }}">Tampilkan Semua</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <x-card padding="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1040px] text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">Pelamar</th>
                                <th class="table-head">Formasi</th>
                                <th class="table-head">Skor</th>
                                <th class="table-head">Status</th>
                                <th class="table-head">Jadwal</th>
                                <th class="table-head">Catatan</th>
                                <th class="table-head text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                <tr class="table-row">
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $application->user->name }}</p>
                                        <p class="mt-0.5 text-xs break-all text-slate-500">{{ $application->user->email }}</p>
                                    </td>
                                    <td class="table-cell">
                                        <p class="font-medium text-slate-700">{{ $application->job->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">
                                            {{ $application->job->department?->name ?? '—' }}
                                        </p>
                                    </td>
                                    <td class="table-cell w-48">
                                        <div class="flex items-center gap-3">
                                            <span class="w-12 shrink-0 font-mono text-xs font-bold text-slate-900">
                                                {{ $application->score }}
                                            </span>
                                            <div class="min-w-24 flex-1">
                                                <x-progress :value="(float) $application->score" size="sm" />
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-cell">
                                        <x-status-badge :status="$application->status" />
                                    </td>
                                    <td class="table-cell">
                                        <span class="font-mono text-xs font-semibold text-slate-600">
                                            {{ $application->interviewSchedule?->interview_date->format('d M Y') ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="table-cell">
                                        <span class="block max-w-56 text-xs leading-relaxed text-slate-500">
                                            {{ \Illuminate\Support\Str::limit($application->interviewSchedule?->notes ?? '', 80) ?: '—' }}
                                        </span>
                                    </td>
                                    <td class="table-cell text-right">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <x-button href="{{ route('manajer.decisions.rekap', $application) }}" variant="secondary" size="sm">
                                                Rekap Pelamar
                                            </x-button>
                                            <x-button href="{{ route('manajer.interviews.show', $application) }}" size="sm">
                                                Buka Lembar Keputusan
                                            </x-button>
                                        </div>
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
