<x-layouts.app title="Kelola Keyword">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="eyebrow">Master & Settings</p>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Kelola Keyword Pencocokan</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                    Kelola keyword untuk meningkatkan akurasi pencocokan CV dengan lowongan.
                </p>
            </div>
            <x-button href="{{ route('admin.keywords.create') }}" icon="plus">
                Tambah Keyword
            </x-button>
        </div>

        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            <th class="px-4 py-3">Lowongan</th>
                            <th class="px-4 py-3">Keyword</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Bobot</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse ($keywords as $keyword)
                            <tr>
                                <td class="px-4 py-3">{{ $keyword->job?->title ?? '-' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $keyword->keyword }}</td>
                                <td class="px-4 py-3">{{ $keyword->category ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $keyword->weight }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-button href="{{ route('admin.keywords.edit', $keyword) }}" variant="ghost" size="sm">Edit</x-button>
                                        <form method="POST" action="{{ route('admin.keywords.destroy', $keyword) }}" onsubmit="return confirm('Hapus keyword ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada keyword</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $keywords->links() }}</div>
        </x-card>
    </div>
</x-layouts.app>