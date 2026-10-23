# E-Recruitment AIC

Portal rekrutmen Ar-Rahman Islamic Center untuk mengelola lowongan, lamaran, seleksi kandidat, dan proses wawancara.

## Fitur

- Halaman publik untuk melihat lowongan kerja.
- Pendaftaran, login, reset kata sandi, verifikasi email, dan login sosial dengan Google, LinkedIn, atau Facebook.
- Profil pelamar, data pendidikan dan pengalaman, keterampilan, serta unggah dan unduh CV dan ijazah.
- Pengajuan lamaran dan pemantauan status serta hasil analisis kandidat.
- Panel admin untuk mengelola lowongan, departemen, kata kunci seleksi, lamaran, jadwal wawancara, dan statistik.
- Panel manajer untuk meninjau dossier kandidat, mengisi penilaian wawancara, dan memberikan keputusan.

## Teknologi

- PHP 8.3+
- Laravel 13
- MySQL
- Node.js dan npm untuk Vite, Tailwind CSS, dan aset frontend
- Laravel Socialite serta SocialiteProviders LinkedIn

## Persyaratan

- PHP 8.3 atau lebih baru beserta ekstensi yang dibutuhkan Laravel.
- Composer.
- MySQL.
- Node.js dan npm.

## Menjalankan secara lokal

1. Clone repository dan masuk ke folder project.

   ```bash
   git clone https://github.com/skyus-code/e-recruitment-aic.git
   cd e-recruitment-aic
   ```

2. Pasang dependency PHP dan buat file konfigurasi lokal.

   ```bash
   composer install
   ```

   ```powershell
   Copy-Item .env.example .env
   ```

3. Edit `.env` lokal. Atur koneksi database MySQL, lalu isi konfigurasi email jika ingin mengirim email verifikasi atau reset kata sandi. Untuk login sosial, masukkan kredensial provider dan URL callback yang didaftarkan pada dashboard provider.

   ```env
   GOOGLE_CLIENT_ID=
   GOOGLE_CLIENT_SECRET=
   GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

   LINKEDIN_CLIENT_ID=
   LINKEDIN_CLIENT_SECRET=
   LINKEDIN_REDIRECT_URI=http://localhost:8000/auth/linkedin/callback

   FACEBOOK_CLIENT_ID=
   FACEBOOK_CLIENT_SECRET=
   FACEBOOK_REDIRECT_URI=https://your-public-domain/auth/facebook/callback
   ```

   Jangan commit `.env` atau membagikan client secret. Untuk Facebook, gunakan HTTPS dan pastikan callback URL sama persis dengan URL di Meta Developer Dashboard.

4. Buat application key, jalankan migrasi dan seeder, lalu build aset frontend.

   ```bash
   php artisan key:generate
   php artisan migrate --seed
   npm install
   npm run build
   ```

5. Jalankan aplikasi dan Vite.

   ```bash
   composer run dev
   ```

   Buka alamat lokal yang ditampilkan oleh server, biasanya `http://localhost:8000`.

## Pengujian

Jalankan test suite dengan:

```bash
php artisan test
```

## Catatan konfigurasi

- Callback OAuth lokal tersedia di `/auth/google/callback`, `/auth/linkedin/callback`, dan `/auth/facebook/callback`.
- Setiap provider perlu memiliki App ID/client ID, secret, dan redirect URI yang sesuai di dashboard providernya.
- File yang diunggah pengguna disimpan melalui Laravel filesystem. Untuk deployment, gunakan storage persisten dan jangan mengandalkan filesystem sementara.
- Sesuaikan konfigurasi email sebelum menguji alur verifikasi email dan reset kata sandi.
