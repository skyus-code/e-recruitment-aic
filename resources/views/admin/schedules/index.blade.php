<x-layouts.app title="Jadwal Wawancara">
    @php
        $dayLabels = [
            'Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu',
            'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu',
        ];
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Jadwal Wawancara']"
            flow="MODULE 04 · SCHED · ORDER: interview_date DESC"
        />

        <x-section-header
            eyebrow="MODULE 04 · SCHED"
            title="Jadwal Wawancara & Microteaching"
            subtitle="Seluruh jadwal wawancara dan microteaching yang dijadwalkan tim HRD untuk kandidat yang lolos seleksi berkas."
        >
            <x-slot name="actions">
                <x-badge variant="amber">MENUNGGU JADWAL: {{ $pending }}</x-badge>
            </x-slot>
        </x-section-header>

        @if ($schedules->isEmpty())
            <x-empty-state
                title="Belum ada jadwal wawancara"
                description="Belum ada jadwal wawancara yang tercatat. Jadwalkan wawancara dari halaman Detail Screening untuk kandidat yang lolos seleksi berkas."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('admin.screening.index') }}" variant="secondary">Ke Seleksi Berkas</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <x-card padding="p-0" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">Tanggal</th>
                                <th class="table-head">Jam</th>
                                <th class="table-head">Pelamar</th>
                                <th class="table-head">Formasi</th>
                                <th class="table-head">Pewawancara</th>
                                <th class="table-head">Lokasi / Tautan</th>
                                <th class="table-head">Status</th>
                                <th class="table-head">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($schedules as $schedule)
                                <tr class="table-row">
                                    <td class="table-cell">
                                        <p class="font-mono text-xs font-semibold text-blue-900 uppercase">
                                            {{ $dayLabels[$schedule->interview_date->format('D')] ?? $schedule->interview_date->format('D') }}
                                        </p>
                                        <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                            {{ $schedule->interview_date->format('d M Y') }}
                                        </p>
                                    </td>
                                    <td class="table-cell font-mono text-xs font-bold text-slate-900">
                                        {{ \Illuminate\Support\Carbon::parse($schedule->interview_time)->format('H:i') }}
                                    </td>
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $schedule->application->user->name }}</p>
                                        <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">{{ $schedule->application->user->email }}</p>
                                    </td>
                                    <td class="table-cell">
                                        <p class="text-slate-800">{{ $schedule->application->job->title }}</p>
                                        <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                            {{ $schedule->application->job->department?->name ?? 'Departemen' }}
                                        </p>
                                    </td>
                                    <td class="table-cell text-slate-700">{{ $schedule->interviewer_name ?? '—' }}</td>
                                    <td class="table-cell">
                                        @if ($schedule->interview_type === 'online')
                                            <a
                                                href="{{ $schedule->interview_location }}"
                                                target="_blank"
                                                rel="noopener"
                                                class="inline-flex max-w-56 items-center gap-1.5 truncate text-xs font-semibold text-blue-900 underline decoration-blue-200 transition-colors duration-200 hover:decoration-blue-900"
                                            >
                                                <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                                                {{ $schedule->interview_location }}
                                            </a>
                                        @else
                                            <span class="inline-flex max-w-56 items-center gap-1.5 truncate text-xs font-semibold text-slate-700">
                                                {{ $schedule->interview_location }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="table-cell">
                                        <x-status-badge :status="$schedule->application->status" />
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button href="{{ route('admin.screening.show', $schedule->application) }}" variant="secondary" size="sm">
                                                Detail
                                            </x-button>

                                            <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Batalkan jadwal wawancara ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Batalkan</x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-4">
                    <p class="text-sm text-slate-500">Menampilkan {{ $schedules->firstItem() }}–{{ $schedules->lastItem() }} dari {{ $schedules->total() }} jadwal. Gunakan kontrol halaman untuk melihat seluruh jadwal.</p>
                    {{ $schedules->links() }}
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>
