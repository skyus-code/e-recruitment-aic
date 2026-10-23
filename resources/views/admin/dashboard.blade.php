<x-layouts.app title="Overview">
    @php
        $palette = ['#1E3A8A', '#059669', '#D97706', '#DC2626', '#64748B', '#0EA5E9'];
        $maxJobs = max(1, (int) $topJobs->max('applications_count'));
        $scoreLabel = is_numeric($averageScore) ? number_format($averageScore, 1) : '0,0';
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Overview']"
            flow="PIPELINE: AdminController → RecruitmentMetricsService → Cache::sync"
        />

        <x-section-header
            eyebrow="MODULE 01"
            title="Overview & Monitoring Rekrutmen"
            subtitle="Ringkasan eksekutif alur seleksi guru & tenaga kependidikan Ar-Rahman Islamic Center."
        >
            <x-slot name="actions">
                <x-button href="{{ route('admin.screening.index') }}" variant="secondary">Ke Seleksi NLP</x-button>
                <x-button href="{{ route('admin.jobs.index') }}">Kelola Lowongan</x-button>
            </x-slot>
        </x-section-header>

        {{-- Baris statistik --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <x-stat-card
                label="Total Lamaran"
                value="{{ $stats['total'] }}"
                accent="blue"
                meta="{{ $stats['formasi'] }} formasi terbuka"
            />

            <x-stat-card
                label="Diterima"
                value="{{ $stats['hired'] }}"
                accent="emerald"
                meta="dari {{ $stats['total'] }} lamaran"
            />

            <x-stat-card
                label="Ditolak"
                value="{{ $stats['rejected'] }}"
                accent="red"
                meta="seleksi berkas & wawancara"
            />

            <x-stat-card
                label="Rata-Rata Skor"
                value="{{ $scoreLabel }}"
                accent="amber"
                caption="{{ $screened }} kandidat sudah discoring"
                meta="NLP_ENGINE: V2.4"
            />
        </div>

        {{-- Baris kedua: chart + antrian + peringkat --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Kolom kiri (2 kolom) --}}
            <div class="flex flex-col gap-6 lg:col-span-2">
                {{-- Distribusi Status Lamaran --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Pipeline Status</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Distribusi Status Lamaran</h2>
                            <p class="mt-1 text-sm text-slate-500">Proporsi lamaran pada setiap tahap alur seleksi.</p>
                        </div>
                        <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">
                            N = {{ array_sum($chartData['values']) }}
                        </span>
                    </div>

                    <div class="mt-5 flex flex-col gap-6 sm:flex-row sm:items-center">
                        <div class="relative h-52 w-full shrink-0 sm:w-52">
                            <canvas id="statusChart" aria-label="Distribusi status lamaran" role="img"></canvas>
                        </div>

                        <ul class="w-full space-y-2.5">
                            @foreach ($chartData['labels'] as $i => $label)
                                <li class="flex items-center justify-between gap-3">
                                    <span class="flex min-w-0 items-center gap-2.5 text-sm text-slate-600">
                                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $palette[$i % count($palette)] }}"></span>
                                        <span class="truncate">{{ $label }}</span>
                                    </span>
                                    <span class="shrink-0 font-mono text-sm font-bold text-slate-900">{{ $chartData['values'][$i] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-card>

                {{-- Menunggu Screening --}}
                <x-card padding="p-0" class="overflow-hidden">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                        <div>
                            <p class="eyebrow">Antrian</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Menunggu Screening</h2>
                            <p class="mt-1 text-sm text-slate-500">Lamaran baru yang belum diproses oleh pipeline NLP.</p>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $pendingScreening->count() }} antrean
                        </span>
                    </div>

                    @if ($pendingScreening->isEmpty())
                        <div class="px-6 py-10 text-center">
                            <p class="text-sm text-slate-500">Tidak ada lamaran yang menunggu screening. Semua antrean sudah diproses.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr>
                                        <th class="table-head">Pelamar</th>
                                        <th class="table-head">Formasi</th>
                                        <th class="table-head">Tanggal</th>
                                        <th class="table-head">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingScreening as $a)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <p class="font-semibold text-slate-900">@if (! $a->admin_seen_at)<span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-xs font-black text-amber-700" aria-label="Lamaran baru">!</span>@endif{{ $a->user->name }}</p>
                                                <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">{{ $a->user->email }}</p>
                                            </td>
                                            <td class="table-cell">
                                                <p class="text-slate-800">{{ $a->job->title }}</p>
                                                <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">{{ $a->job->department?->name ?? 'Departemen' }}</p>
                                            </td>
                                            <td class="table-cell font-mono text-xs text-slate-500">
                                                {{ $a->applied_at?->format('d M Y') ?? '—' }}
                                            </td>
                                            <td class="table-cell">
                                                <x-button href="{{ route('admin.screening.show', $a) }}" size="sm" variant="secondary">Proses</x-button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Kolom kanan (1 kolom) --}}
            <div class="flex flex-col gap-6">
                {{-- Peringkat Skor Tertinggi --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Leaderboard</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Peringkat Skor Tertinggi</h2>
                            <p class="mt-1 text-sm text-slate-500">Kandidat dengan skor kesesuaian NLP terbaik.</p>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $topCandidates->count() }} kandidat
                        </span>
                    </div>

                    @if ($topCandidates->isEmpty())
                        <p class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            Belum ada kandidat yang discoring. Jalankan seleksi NLP dari menu Seleksi Berkas.
                        </p>
                    @else
                        <ul class="mt-4 space-y-2.5">
                            @foreach ($topCandidates as $a)
                                <li>
                                    <a href="{{ route('admin.screening.show', $a) }}" class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 transition-all duration-200 ease-in-out hover:border-blue-100 hover:bg-blue-50/50 hover:shadow-sm">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-100 font-mono text-xs font-bold text-blue-900">
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-sm font-semibold text-slate-900">{{ $a->user->name }}</span>
                                            <span class="block truncate text-xs text-slate-500">{{ $a->job->title }}</span>
                                        </span>
                                        <x-badge variant="solid-blue">Skor {{ number_format((float) $a->score, 1) }}</x-badge>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>

                {{-- Formasi Terpopuler --}}
                <x-card>
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div>
                            <p class="eyebrow">Top Job</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-900">Formasi Terpopuler</h2>
                            <p class="mt-1 text-sm text-slate-500">Lowongan dengan jumlah pelamar terbanyak.</p>
                        </div>
                        <span class="font-mono text-[10px] font-semibold tracking-wider text-slate-400 uppercase">
                            {{ $topJobs->count() }} formasi
                        </span>
                    </div>

                    @if ($topJobs->isEmpty())
                        <p class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                            Belum ada lowongan terdaftar.
                        </p>
                    @else
                        <ul class="mt-5 space-y-4">
                            @foreach ($topJobs as $job)
                                <li class="space-y-1.5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="truncate text-sm font-semibold text-slate-800">{{ $job->title }}</span>
                                        <span class="shrink-0 font-mono text-[11px] font-bold text-slate-900">{{ $job->applications_count }} lamaran</span>
                                    </div>
                                    <x-progress size="sm" :value="$job->applications_count" :max="$maxJobs" />
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('statusChart');
            if (!el || !window.Chart) {
                return;
            }

            new Chart(el, {
                type: 'doughnut',
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        data: @json($chartData['values']),
                        backgroundColor: @json($palette),
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: { position: 'bottom', display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return ' ' + context.label + ': ' + context.parsed + ' lamaran';
                                },
                            },
                        },
                    },
                },
            });
        });
    </script>
</x-layouts.app>
