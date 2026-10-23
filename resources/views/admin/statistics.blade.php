<x-layouts.app title="Statistik Rekrutmen">
    @php
        $funnelLabels = $funnel->pluck('label')->all();
        $funnelValues = $funnel->pluck('value')->map(fn ($v) => (int) $v)->all();
        $bucketLabels = array_keys($buckets);
        $bucketValues = array_map(fn ($v) => (int) $v, array_values($buckets));
        $monthlyLabels = $monthly->pluck('label')->all();
        $monthlyValues = $monthly->pluck('value')->map(fn ($v) => (int) $v)->all();
        $maxDeptApps = max(1, (int) collect($perDepartment)->max('applications'));
        $bucketColors = ['#DC2626', '#D97706', '#64748B', '#0EA5E9', '#059669'];
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Statistik Rekrutmen']"
            flow="MODULE 05 · DATA · AGGREGATE: RecruitmentMetrics"
        />

        <x-section-header
            eyebrow="MODULE 05 · DATA"
            title="Statistik & Evaluasi Rekrutmen"
            subtitle="Agregat seluruh aktivitas rekrutmen: pipeline, distribusi skor NLP, tren pendaftaran, dan analisis kata kunci."
        />

        {{-- Ringkasan --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 xl:grid-cols-5">
            <x-stat-card label="Total Lamaran" value="{{ $summary['total'] }}" accent="blue" meta="total aplikasi masuk" />
            <x-stat-card label="Sudah Discoring" value="{{ $summary['scored'] }}" accent="emerald" meta="diproses pipeline NLP" />
            <x-stat-card label="Rata-Rata Skor" value="{{ number_format($summary['average'], 1) }}" accent="amber" meta="skor 0–100" />
            <x-stat-card label="Skor Tertinggi" value="{{ number_format($summary['highest'], 1) }}" accent="blue" meta="best match kandidat" />
            <x-stat-card label="Keyword Terdaftar" value="{{ $summary['keywords'] }}" accent="slate" meta="NLP DICTIONARY" />
        </div>

        {{-- Charts --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Funnel --}}
            <x-card>
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Pipeline</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Funnel Tahapan Seleksi</h2>
                        <p class="mt-1 text-sm text-slate-500">Jumlah lamaran pada setiap status alur seleksi.</p>
                    </div>
                    <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">CHART · BAR</span>
                </div>
                <div class="relative mt-5 h-72">
                    <canvas id="funnelChart" aria-label="Funnel tahapan seleksi" role="img"></canvas>
                </div>
            </x-card>

            {{-- Distribusi skor --}}
            <x-card>
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Distribusi</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Distribusi Skor NLP</h2>
                        <p class="mt-1 text-sm text-slate-500">Sebaran skor kesesuaian kandidat dalam 5 interval.</p>
                    </div>
                    <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">BUCKET · 0–100</span>
                </div>
                <div class="relative mt-5 h-72">
                    <canvas id="scoreChart" aria-label="Distribusi skor NLP" role="img"></canvas>
                </div>
            </x-card>

            {{-- Tren bulanan --}}
            <x-card>
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Tren</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Tren Pendaftaran (6 Bulan)</h2>
                        <p class="mt-1 text-sm text-slate-500">Jumlah lamaran per bulan selama enam bulan terakhir.</p>
                    </div>
                    <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">CHART · LINE</span>
                </div>
                <div class="relative mt-5 h-72">
                    <canvas id="trendChart" aria-label="Tren pendaftaran 6 bulan" role="img"></canvas>
                </div>
            </x-card>

            {{-- Per departemen --}}
            <x-card padding="p-0" class="overflow-hidden">
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <div>
                        <p class="eyebrow">Departemen</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Per Departemen</h2>
                        <p class="mt-1 text-sm text-slate-500">Jumlah lowongan dan lamaran per departemen.</p>
                    </div>
                    <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TABLE · departments</span>
                </div>

                @if ($perDepartment->isEmpty())
                    <div class="px-6 py-10 text-center">
                        <p class="text-sm text-slate-500">Belum ada departemen terdaftar.</p>
                    </div>
                @else
                    <div class="px-6 py-5">
                        <ul class="space-y-4">
                            @foreach ($perDepartment as $dept)
                                <li class="space-y-1.5">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="truncate text-sm font-semibold text-slate-800">{{ $dept['name'] }}</span>
                                        <span class="shrink-0 font-mono text-[11px] font-bold text-slate-900">
                                            {{ $dept['applications'] }} lamaran · {{ $dept['jobs'] }} lowongan
                                        </span>
                                    </div>
                                    <x-progress size="sm" :value="$dept['applications']" :max="$maxDeptApps" />
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Analisis kata kunci --}}
        <x-card padding="p-0" class="overflow-hidden">
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="eyebrow">Keyword Analytics</p>
                    <h2 class="mt-1 text-lg font-bold text-slate-900">Tingkat Kecocokan Kata Kunci</h2>
                    <p class="mt-1 text-sm text-slate-500">Seberapa sering tiap kata kunci cocok dengan CV pelamar yang discoring.</p>
                </div>
                <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block">TOP 8 KEYWORD</span>
            </div>

            @if ($keywordAnalysis->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-500">
                        Belum ada analisis kata kunci. Analisis muncul setelah minimal satu screening NLP dijalankan.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">Kata Kunci</th>
                                <th class="table-head">Kategori</th>
                                <th class="table-head">Dipakai</th>
                                <th class="table-head">Tingkat Cocok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($keywordAnalysis as $kw)
                                <tr class="table-row">
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $kw['keyword'] }}</p>
                                    </td>
                                    <td class="table-cell">
                                        <x-badge variant="slate">{{ $kw['category'] }}</x-badge>
                                    </td>
                                    <td class="table-cell">
                                        <span class="font-mono text-xs font-bold text-slate-900">{{ $kw['uses'] }}</span>
                                        <span class="text-slate-400">× dipakai</span>
                                    </td>
                                    <td class="table-cell">
                                        <div class="w-44">
                                            <div class="flex items-center justify-between gap-2">
                                                <x-badge :variant="$kw['rate'] >= 70 ? 'emerald' : ($kw['rate'] >= 40 ? 'amber' : 'red')">
                                                    {{ $kw['rate'] }}%
                                                </x-badge>
                                                <span class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">rate</span>
                                            </div>
                                            <x-progress size="sm" :value="$kw['rate']" class="mt-1.5" />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) {
                return;
            }

            const BRAND = ['#1E3A8A', '#059669', '#D97706', '#DC2626', '#64748B', '#0EA5E9'];

            // Funnel
            const funnelEl = document.getElementById('funnelChart');
            if (funnelEl) {
                new Chart(funnelEl, {
                    type: 'bar',
                    data: {
                        labels: @json($funnelLabels),
                        datasets: [{
                            data: @json($funnelValues),
                            backgroundColor: '#1E3A8A',
                            borderRadius: 6,
                            barPercentage: 0.55,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (ctx) => ' ' + ctx.parsed.x + ' lamaran' } },
                        },
                        scales: {
                            x: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                            y: { reverse: true, grid: { display: false } },
                        },
                    },
                });
            }

            // Distribusi skor
            const scoreEl = document.getElementById('scoreChart');
            if (scoreEl) {
                new Chart(scoreEl, {
                    type: 'bar',
                    data: {
                        labels: @json($bucketLabels),
                        datasets: [{
                            data: @json($bucketValues),
                            backgroundColor: @json($bucketColors),
                            borderRadius: 6,
                            barPercentage: 0.6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (ctx) => ' ' + ctx.parsed.y + ' kandidat' } },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                        },
                    },
                });
            }

            // Tren bulanan
            const trendEl = document.getElementById('trendChart');
            if (trendEl) {
                new Chart(trendEl, {
                    type: 'line',
                    data: {
                        labels: @json($monthlyLabels),
                        datasets: [{
                            data: @json($monthlyValues),
                            borderColor: '#1E3A8A',
                            backgroundColor: 'rgba(30, 58, 138, 0.12)',
                            fill: true,
                            tension: 0.35,
                            pointRadius: 3,
                            pointBackgroundColor: '#1E3A8A',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            borderWidth: 2.5,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: (ctx) => ' ' + ctx.parsed.y + ' lamaran' } },
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: { beginAtZero: true, grid: { color: '#F1F5F9' } },
                        },
                    },
                });
            }
        });
    </script>
</x-layouts.app>