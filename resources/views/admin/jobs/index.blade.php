<x-layouts.app title="Kelola Lowongan">
    @php
        $chipBase = 'inline-flex items-center gap-1.5 rounded-full px-3 py-1 font-mono text-[10px] font-semibold tracking-widest uppercase ring-1 ring-inset transition-all duration-200 ease-in-out';
    @endphp

    <div class="space-y-6">
        <x-trace
            :items="['Admin', 'Kelola Lowongan']"
            flow="ROUTE: admin.jobs.index · STATUS: {{ $status ?: 'all' }}"
        />

        <x-section-header
            eyebrow="MODULE 02"
            title="Manajemen Lowongan & Bobot Kata Kunci NLP"
            subtitle="Kelola formasi yang dibuka, pantau jumlah pelamar, dan konfigurasikan bobot kata kunci yang dipakai pipeline scoring NLP."
        >
            <x-slot name="actions">
                <x-button href="{{ route('admin.jobs.create') }}">+ Tambah Lowongan Baru</x-button>
            </x-slot>
        </x-section-header>

        {{-- Filter & ringkasan --}}
        <x-card>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.jobs.index') }}" @class([$chipBase, 'bg-blue-900 text-white ring-blue-900' => $status === '', 'bg-white text-blue-900 ring-blue-200 hover:bg-blue-100' => $status !== ''])>
                        Semua ({{ $counts['total'] }})
                    </a>
                    <a href="{{ route('admin.jobs.index', ['status' => 'open']) }}" @class([$chipBase, 'bg-blue-900 text-white ring-blue-900' => $status === 'open', 'bg-white text-blue-900 ring-blue-200 hover:bg-blue-100' => $status !== 'open'])>
                        Aktif Dibuka ({{ $counts['open'] }})
                    </a>
                    <a href="{{ route('admin.jobs.index', ['status' => 'closed']) }}" @class([$chipBase, 'bg-blue-900 text-white ring-blue-900' => $status === 'closed', 'bg-white text-blue-900 ring-blue-200 hover:bg-blue-100' => $status !== 'closed'])>
                        Ditutup ({{ $counts['closed'] }})
                    </a>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 font-mono text-[10px] font-semibold tracking-widest text-blue-900 uppercase ring-1 ring-inset ring-blue-200">
                        Keyword NLP ({{ $counts['keywords'] }})
                    </span>
                </div>

                <form method="GET" action="{{ route('admin.jobs.index') }}" class="flex flex-wrap items-end gap-3">
                    <div class="w-48">
                        <x-select
                            name="status"
                            label="Filter Status"
                            :options="['' => 'Semua Status', 'open' => 'Dibuka', 'closed' => 'Ditutup']"
                            :selected="$status"
                        />
                    </div>
                    <x-button type="submit" variant="secondary" size="sm">Terapkan</x-button>

                    @if ($status !== '')
                        <x-button href="{{ route('admin.jobs.index') }}" variant="ghost" size="sm">Reset</x-button>
                    @endif
                </form>
            </div>
        </x-card>

        @if ($jobs->isEmpty())
            <x-empty-state
                title="Belum ada lowongan"
                description="Belum ada formasi yang terdaftar pada filter ini. Tambahkan lowongan baru untuk mulai menerima lamaran."
            >
                <x-slot name="actions">
                    <x-button href="{{ route('admin.jobs.create') }}">+ Tambah Lowongan Baru</x-button>
                </x-slot>
            </x-empty-state>
        @else
            <x-card padding="p-0" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th class="table-head">#</th>
                                <th class="table-head">Formasi</th>
                                <th class="table-head">Tipe</th>
                                <th class="table-head">Lokasi</th>
                                <th class="table-head">Gaji</th>
                                <th class="table-head">Status</th>
                                <th class="table-head">Pelamar</th>
                                <th class="table-head">Keyword</th>
                                <th class="table-head">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jobs as $job)
                                <tr class="table-row">
                                    <td class="table-cell font-mono text-xs text-slate-400">
                                        {{ $jobs->firstItem() + $loop->index }}
                                    </td>
                                    <td class="table-cell">
                                        <p class="font-semibold text-slate-900">{{ $job->title }}</p>
                                        <p class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">
                                            {{ $job->department?->name ?? 'Departemen' }}
                                        </p>
                                    </td>
                                    <td class="table-cell">
                                        <x-badge variant="blue">{{ $job->job_type->label() }}</x-badge>
                                    </td>
                                    <td class="table-cell text-slate-600">{{ $job->location ?? '—' }}</td>
                                    <td class="table-cell text-xs text-slate-600">{{ $job->salaryRange() ?? '—' }}</td>
                                    <td class="table-cell">
                                        <x-badge :variant="$job->status->value === 'open' ? 'emerald' : 'red'">
                                            {{ $job->status->label() }}
                                        </x-badge>
                                    </td>
                                    <td class="table-cell">
                                        <span class="font-mono text-xs font-bold text-slate-900">{{ $job->applications_count }}</span>
                                        <span class="text-slate-400"> lamaran</span>
                                    </td>
                                    <td class="table-cell">
                                        <span class="font-mono text-xs font-bold text-slate-900">{{ $job->keywords_count }}</span>
                                        <span class="text-slate-400"> kata</span>
                                    </td>
                                    <td class="table-cell">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-button href="{{ route('admin.jobs.show', $job) }}" variant="secondary" size="sm">View</x-button>
                                            <x-button href="{{ route('admin.jobs.edit', $job) }}" variant="secondary" size="sm">Edit</x-button>

                                            <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Hapus lowongan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-4 py-4">
                    {{ $jobs->links() }}
                </div>
            </x-card>
        @endif
    </div>
</x-layouts.app>