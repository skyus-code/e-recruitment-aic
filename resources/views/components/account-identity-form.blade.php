@props(['user'])

<x-card>
    <div class="border-b border-slate-100 pb-4">
        <p class="eyebrow">Identitas Akun</p>
        <h2 class="mt-1 text-lg font-bold text-slate-900">Username &amp; Email</h2>
        <p class="mt-1 text-sm text-slate-500">Username digunakan sebagai identitas akun. Email baru perlu diverifikasi sebelum digunakan untuk melamar.</p>
    </div>

    <form method="POST" action="{{ route('account.username.update') }}" class="mt-5 space-y-4">
        @csrf
        @method('PUT')
        <x-input name="username" label="Username" :value="old('username', $user->username ?? $user->name)" maxlength="30" autocomplete="username" required />
        <p class="-mt-2 text-xs text-slate-500">Gunakan nama yang ingin ditampilkan di bagian atas halaman.</p>
        <x-button type="submit" variant="secondary">Simpan</x-button>
    </form>

    <form method="POST" action="{{ route('account.email.update') }}" class="mt-6 space-y-4 border-t border-dashed border-slate-200 pt-5">
        @csrf
        @method('PUT')
        <x-input type="email" name="email" label="Alamat Email Baru" :value="old('email', $user->email)" autocomplete="email" required />
        <x-input type="password" name="current_password" label="Kata Sandi Saat Ini" autocomplete="current-password" required />
        <p class="-mt-2 text-xs text-slate-500">Email baru langsung aktif dan status verifikasinya direset. Kode verifikasi lama akan dibatalkan.</p>
        <x-button type="submit" variant="primary">Simpan Email</x-button>
    </form>
</x-card>
