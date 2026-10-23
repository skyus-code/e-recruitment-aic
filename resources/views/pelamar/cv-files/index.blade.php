<x-layouts.app title="Kelola Berkas Lamaran">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="eyebrow">Pelamar</p>
                <h1 class="mt-1 text-2xl font-extrabold text-slate-900">Kelola Berkas Lamaran</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-500">Kelola CV dan lihat ijazah yang pernah diunggah saat melamar pekerjaan.</p>
            </div>
        </div>

        <x-card>
            <form method="POST" action="{{ route('pelamar.cv-files.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <x-input type="file" name="cv_file" label="Unggah CV (PDF)" required />

                <x-button type="submit">Unggah</x-button>
            </form>
        </x-card>

        <x-card>
            <h2 class="text-lg font-bold text-slate-900">Daftar Berkas</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-semibold tracking-wider text-slate-500 uppercase">
                            <th class="px-4 py-3">Nama Berkas</th>
                            <th class="px-4 py-3">Ukuran</th>
                            <th class="px-4 py-3">Tanggal Unggah</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @foreach ($cvFiles as $file)
                            <tr>
                                <td class="px-4 py-3">{{ $file->file_name }}</td>
                                <td class="px-4 py-3">{{ number_format($file->file_size/1024,1) }} KB</td>
                                <td class="px-4 py-3">{{ $file->uploaded_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <x-button href="{{ route('pelamar.cv-files.preview', $file) }}" variant="secondary" size="sm" target="_blank" rel="noopener">Lihat</x-button>
                                        <x-button href="{{ route('pelamar.cv-files.download', $file) }}" variant="secondary" size="sm">Unduh</x-button>
                                        <form method="POST" action="{{ route('pelamar.cv-files.destroy', $file) }}" onsubmit="return confirm('Yakin ingin menghapus berkas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($degreeFiles as $file)
                            <tr>
                                <td class="px-4 py-3">Ijazah · {{ $file->file_name }}</td>
                                <td class="px-4 py-3">{{ number_format($file->file_size / 1024, 1) }} KB</td>
                                <td class="px-4 py-3">{{ $file->uploaded_at?->translatedFormat('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <x-button href="{{ route('files.ijazah.preview', $file->application) }}" variant="secondary" size="sm" target="_blank" rel="noopener">Lihat</x-button>
                                        <x-button href="{{ route('files.ijazah', $file->application) }}" variant="secondary" size="sm">Unduh</x-button>
                                        <form method="POST" action="{{ route('pelamar.degree-files.destroy', $file) }}" onsubmit="return confirm('Yakin ingin menghapus ijazah ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <x-button type="submit" variant="danger" size="sm">Hapus</x-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @if ($cvFiles->isEmpty() && $degreeFiles->isEmpty())
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada berkas</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
