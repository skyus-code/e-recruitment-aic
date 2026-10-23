<x-layouts.app title="Jadwal & Wawancara">
    <div class="space-y-6">
        <x-trace :items="['Pelamar', 'Jadwal Wawancara']" flow="HRD → WAWANCARA → MICROTEACHING → KEPUTUSAN" />

        <x-section-header
            eyebrow="Portal Pelamar"
            title="Jadwal Wawancara & Microteaching"
            subtitle="Seluruh agenda wawancara yang ditetapkan tim HRD untuk lamaran Anda. Pastikan hadir tepat waktu dan siapkan berkas asli saat sesi berlangsung."
        >
            <x-slot name="actions">
                <x-button href="{{ route('pelamar.applications.index') }}" variant="secondary" size="sm">
                    Lamaran Saya
                </x-button>
            </x-slot>
        </x-section-header>

        @if ($schedules->isEmpty())
            <x-empty-state
                title="Belum ada jadwal wawancara"
                description="Jadwal akan muncul di sini setelah tim HRD menetapkan tahap wawancara untuk lamaran Anda."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('pelamar.applications.index') }}" variant="primary">
                        Lihat Lamaran Saya
                    </x-button>
                </x-slot>
            </x-empty-state>
        @else
            <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-400 uppercase">
                {{ $schedules->count() }} jadwal terjadwal
            </p>

            <div class="space-y-4">
                @foreach ($schedules as $schedule)
                    @php
                        $scheduleStatus = match ($schedule->status) {
                            'completed' => ['label' => 'Selesai', 'variant' => 'emerald'],
                            'cancelled' => ['label' => 'Dibatalkan', 'variant' => 'red'],
                            default => ['label' => 'Terjadwal', 'variant' => 'blue'],
                        };
                    @endphp

                    <x-card class="card-hover">
                        <div class="flex flex-col gap-5 md:flex-row md:items-start">
                            {{-- Blok tanggal --}}
                            <div class="flex shrink-0 items-center gap-4 md:w-32 md:flex-col md:gap-0 md:rounded-xl md:border md:border-blue-100 md:bg-blue-50 md:px-4 md:py-4 md:text-center">
                                <div class="hidden md:block">
                                    <span class="block font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                        {{ $schedule->interview_date->translatedFormat('l') }}
                                    </span>
                                </div>

                                <span class="font-heading text-3xl leading-none font-extrabold text-blue-900 md:mt-1.5 md:text-4xl">
                                    {{ $schedule->interview_date->format('d') }}
                                </span>

                                <span class="block font-mono text-[10px] font-semibold tracking-widest text-blue-800 uppercase md:mt-1.5">
                                    {{ $schedule->interview_date->translatedFormat('F') }}
                                </span>

                                <span class="font-mono text-[11px] font-semibold text-slate-400 md:hidden">
                                    {{ $schedule->interview_date->translatedFormat('l') }}
                                </span>
                            </div>

                            {{-- Isi --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase">
                                            {{ $schedule->application->job->department?->name ?? 'Departemen' }}
                                        </p>
                                        <h3 class="mt-1.5 font-heading text-lg leading-snug font-bold text-slate-900">
                                            {{ $schedule->application->job->title }}
                                        </h3>
                                    </div>

                                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                                        <x-status-badge :status="$schedule->application->status" />
                                        <x-badge :variant="$scheduleStatus['variant']">{{ $scheduleStatus['label'] }}</x-badge>
                                    </div>
                                </div>

                                <dl class="mt-4 grid gap-3 sm:grid-cols-3">
                                    <div>
                                        <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Waktu</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ \Illuminate\Support\Str::of((string) $schedule->interview_time)->substr(0, 5) }} WIB
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Narasumber</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-900">
                                            {{ $schedule->interviewer_name ?? 'Belum ditentukan' }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="font-mono text-[10px] tracking-widest text-slate-400 uppercase">Lokasi Sesi</dt>
                                        <dd class="mt-1">
                                            @if ($schedule->interview_type === 'online')
                                                <a
                                                    href="{{ $schedule->interview_location }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-900 transition-all duration-200 ease-in-out hover:text-blue-700 hover:underline"
                                                >
                                                    Buka tautan
                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V16.5m-13.5-3L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                                </a>
                                            @else
                                                <span class="text-sm font-semibold text-slate-900">{{ $schedule->interview_location }}</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                @if ($schedule->notes)
                                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <p class="font-mono text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Catatan</p>
                                        <div class="mt-1.5 text-sm leading-relaxed text-slate-600">
                                            {!! nl2br(e($schedule->notes)) !!}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Aksi --}}
                            <div class="flex shrink-0 flex-col gap-2.5 md:w-40 md:items-end">
                                <x-button href="{{ route('files.cv', $schedule->application) }}" variant="secondary" size="sm" class="w-full md:w-auto">
                                    Unduh CV
                                </x-button>
                                <x-button href="{{ route('pelamar.applications.show', $schedule->application) }}" variant="ghost" size="sm" class="w-full md:w-auto">
                                    Detail Lamaran
                                </x-button>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
