<x-layouts.app title="Pengaturan Akun">
    <div class="space-y-6">
        <x-trace
            :items="[$user->role->label(), 'Pengaturan Akun']"
            flow=" · · "
        />

        <x-section-header
            eyebrow="Pengaturan Akun"
            title="Pengaturan Akun & Keamanan"
            subtitle="Kelola status email, verifikasi alamat email, dan kata sandi akun Anda. Perubahan kata sandi berlaku untuk seluruh perangkat saat Anda masuk kembali."
        />

        @if (session('email_changed'))
            <x-alert variant="success" title="Email berhasil diubah">
                Email baru sudah tersimpan dan belum terverifikasi.
                <a href="{{ route('verification.notice') }}" class="ml-1 font-semibold underline">Verifikasi Sekarang</a>
                <span class="ml-1">atau pilih</span>
                <a href="{{ route('password.edit') }}" class="font-semibold underline">Nanti Saja</a>
                <span>— verifikasi dapat dilakukan kapan saja melalui Pengaturan Akun.</span>
            </x-alert>
        @endif

        <x-account-identity-form :user="$user" />

        <div class="grid gap-6 lg:grid-cols-[1fr_1.4fr] lg:items-start">
            {{-- Ringkasan akun --}}
            <x-card>
                <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <p class="eyebrow">Akun</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900">Ringkasan Akun</h2>
                        <p class="mt-1 text-sm text-slate-500">Identitas yang dipakai untuk masuk ke sistem.</p>
                    </div>
                    <span class="hidden font-mono text-[10px] tracking-wider text-slate-400 uppercase sm:block"> · </span>
                </div>

                <div class="mt-5 flex items-center gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-900 ring-2 ring-blue-700">
                        {{ $user->initials() }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate font-heading text-sm font-bold text-slate-900">{{ $user->username ?: $user->name }}</p>
                        <p class="truncate text-sm text-slate-500">{{ $user->email }}</p>
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-1 gap-y-4 border-t border-slate-100 pt-5">
                    <div>
                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Peran</dt>
                        <dd class="mt-1">
                            <x-badge variant="blue">{{ $user->role->label() }}</x-badge>
                        </dd>
                    </div>

                    <div>
                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Status Email</dt>
                        <dd class="mt-1">
                            @if ($user->hasVerifiedEmail())
                                <x-badge variant="emerald">Terverifikasi · {{ $user->email_verified_at->format('d F Y') }}</x-badge>
                            @else
                                <x-badge variant="amber">Belum Terverifikasi · verifikasi melalui kode email</x-badge>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="font-mono text-[10px] tracking-wider text-slate-400 uppercase">Login Sosial Tertaut</dt>
                        <dd class="mt-1 flex flex-wrap gap-2">
                            @forelse ($linkedProviders as $provider)
                                <x-badge variant="slate">{{ $provider }}</x-badge>
                            @empty
                                <span class="text-sm text-slate-500">Belum ada provider tertaut.</span>
                            @endforelse
                        </dd>
                    </div>
                </dl>

                @if ($user->role->value === 'pelamar')
                    <div class="mt-5 border-t border-dashed border-slate-200 pt-5">
                        <x-button href="{{ route('pelamar.profile.edit') }}" variant="secondary" size="sm">
                            Kelola Profil &amp; Master Data
                        </x-button>
                    </div>
                @endif
            </x-card>

            <div class="space-y-6">
                {{-- Status email & verifikasi --}}
                <x-email-status
                    :user="$user"
                    :resend-cooldown="$resendCooldown"
                    :expires-in-minutes="$expiresInMinutes"
                />

                {{-- Form ganti kata sandi --}}
                <x-password-form :user="$user" :linked-providers="$linkedProviders" />
            </div>
        </div>
    </div>
</x-layouts.app>
