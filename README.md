# LajuNalar

Platform latihan **Speed Test / Agility Test Tahap 3 PCPM**. Frontend menggunakan React, Redux Toolkit, dan Tailwind CSS; backend menggunakan Laravel, Filament, Sanctum, Socialite, serta dokumentasi REST otomatis melalui Scramble.

> LajuNalar adalah platform latihan independen dan tidak berafiliasi dengan Bank Indonesia.

## Fitur

- Login pengguna dan admin hanya melalui Google OAuth.
- Paket angka hilang, huruf ganda, atau campuran; jumlah soal dan durasi dapat diatur admin.
- Alur ujian satu arah: Enter/tombol lanjut, tanpa kembali atau lompat nomor.
- Timer berbasis waktu server dan auto-submit saat habis.
- Jawaban disimpan bertahap dalam batch maksimum 25, bukan 500 request atau 500 insert terpisah.
- Antrean jawaban sementara disimpan di `localStorage`, sehingga lebih tahan terhadap koneksi singkat yang terputus.
- Hasil dan pembahasan seluruh nomor, termasuk jawaban benar, salah, tidak dijawab, dan waktu respons.
- Dashboard pengguna dengan riwayat yang diringkas per paket.
- Panel Filament interaktif untuk paket, soal, generator, pengerjaan, laporan soal, pengguna, serta statistik.
- Generator admin dengan progres `122/500` melalui queue worker.
- Dokumentasi OpenAPI interaktif oleh Dedoc Scramble.

## Menjalankan di Laragon

Project ditujukan untuk folder `D:\laragon\www\lajunalar`.

1. Buat database MySQL bernama `lajunalar`.
2. Salin `.env.example` menjadi `.env`, lalu isi koneksi database.
3. Isi Google OAuth dan email admin pada `.env`:

```env
APP_URL=http://lajunalar.test
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
ADMIN_EMAILS=email.google.anda@gmail.com
```

Di Google Cloud Console, tambahkan URI callback persis seperti `GOOGLE_REDIRECT_URI`.

4. Jalankan setup:

```powershell
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run build
```

Seeder membuat tiga paket siap latihan, masing-masing berisi 500 soal: campuran, angka, dan huruf.

5. Untuk pengembangan, jalankan:

```powershell
composer run dev
```

Jika menjalankan layanan secara terpisah, queue worker wajib aktif agar generator menampilkan progres:

```powershell
php artisan queue:work --tries=3
npm run dev
```

Panel admin tersedia di `/admin`. Pengguna yang email Google-nya tercantum di `ADMIN_EMAILS` otomatis memperoleh peran administrator.

## Dokumentasi REST API

Scramble membangun dokumentasi dari route, Form Request, resource, dan tipe PHP secara otomatis:

- UI interaktif: `/docs/api`
- OpenAPI JSON: `/docs/api.json`

Dokumentasi hanya terbuka pada environment lokal atau untuk pengguna berperan admin.

Endpoint utama berada di prefix `/api/v1`:

| Method | Endpoint | Kegunaan |
|---|---|---|
| GET | `/me` | Profil sesi aktif |
| GET | `/dashboard` | Statistik dan riwayat per paket |
| GET | `/quizzes` | Daftar paket aktif |
| GET | `/quizzes/{uuid}` | Detail dan riwayat paket |
| POST | `/quizzes/{uuid}/attempts` | Memulai atau melanjutkan attempt |
| GET | `/attempts/{uuid}` | Soal attempt tanpa kunci jawaban |
| POST | `/attempts/{uuid}/answers/batch` | Menyimpan 1–25 jawaban berurutan |
| POST | `/attempts/{uuid}/finish` | Mengakhiri dan menghitung hasil |
| GET | `/attempts/{uuid}/result` | Hasil dan pembahasan lengkap |
| POST | `/questions/{uuid}/reports` | Melaporkan soal |

## Strategi penyimpanan 500 soal

Satu pengerjaan tetap menghasilkan maksimal 500 baris jawaban—itu wajar dan berguna untuk pembahasan—tetapi penulisannya dibuat efisien:

1. Browser menampung jawaban singkat di memori dan `localStorage`.
2. Jawaban dikirim tiap 10 jawaban atau sekitar 4 detik.
3. API menerima maksimum 25 jawaban per request dan melakukan satu bulk insert di dalam transaksi.
4. `quiz_attempts` hanya menyimpan ringkasan dan pointer posisi; dashboard membaca tabel agregat `user_quiz_stats`, bukan menghitung ulang seluruh jawaban.
5. Soal dan jawaban pada attempt menyimpan snapshot, sehingga regenerasi bank soal tidak merusak riwayat lama.

Dengan pola ini, satu attempt 500 soal biasanya hanya membutuhkan sekitar 50 request ringan, bukan 500 request individual.

## Verifikasi

```powershell
php artisan test
npm run build
vendor\bin\pint --test
```
