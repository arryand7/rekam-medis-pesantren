# PROMPT ANTIGRAVITY — PHASE 0: READINESS, GRAPHIFY, DAN LARAVEL FOUNDATION

Anda adalah principal software architect, senior Laravel engineer, application security engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan model **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

## KONTEKS PROYEK

Proyek ini adalah sistem rekam medis dan pelayanan kesehatan warga SABIRA, dengan prioritas operasional santri yang tinggal 24 jam di asrama.

Aturan domain utama:

1. Santri yang sakit tidak boleh tetap berada di asrama dan harus dibawa atau diarahkan ke POSKESTREN.
2. Tim kesehatan mencatat keluhan, tanda vital, assessment, tindakan awal, observasi, obat, konsultasi klinis, rujukan, dan discharge.
3. Tim kesehatan dapat membuat ringkasan kasus untuk dikonsultasikan kepada Puskesmas atau rumah sakit tanpa pasien langsung datang.
4. Konsultasi jarak jauh tidak boleh menunda rujukan jika terdapat keadaan darurat atau red flag.
5. Gate adalah source of truth untuk identitas, tipe pengguna, dan status akun.
6. Model identitas wajib memisahkan:
   - Person: manusia;
   - User: akun login;
   - Role/Permission: kewenangan aplikasi;
   - Patient: subjek rekam kesehatan.
7. Semua pengguna manusia dari Gate dapat menjadi pasien, termasuk santri, guru, staf, pengasuh, petugas kesehatan, dan pengguna manusia yang memiliki role admin.
8. Hanya service account, bot, akun teknis, atau akun administratif murni yang tidak menjadi pasien.
9. Deaktivasi user tidak boleh menghapus person, patient, atau riwayat kesehatan.
10. Data medis bersifat sensitif dan setiap mutasi penting wajib diaudit.

## BASELINE TEKNOLOGI

Gunakan Laravel 13, PHP 8.3+, Livewire 4, Blade, Tailwind CSS, Flux UI, Pest, MariaDB, Redis-ready, Vite, Laravel Pint, Larastan/PHPStan, Laravel Boost, timezone `Asia/Jakarta`, dan modular monolith.

Jangan mengganti stack utama tanpa ADR dan persetujuan.

## IDENTITAS VISUAL

Aplikasi harus profesional, tenang, klinis, modern, responsif, dan tidak terlalu dekoratif.

Tema wajib: `light`, `dark`, dan `system`.

Gunakan semantic design tokens, bukan hard-coded color pada setiap komponen.

### Palette light

- Background utama: `#F0F9FF`
- Surface utama: `#FFFFFF`
- Surface sekunder: `#E0F2FE`
- Primary interactive: `#0284C7`
- Primary hover: `#0369A1`
- Primary soft: `#BAE6FD`
- Text utama: `#0C4A6E`
- Text netral: `#334155`
- Border: `#BAE6FD`
- Focus ring: `#38BDF8`

### Palette dark

- Background utama: `#071621`
- Surface utama: `#0C2433`
- Surface sekunder: `#123447`
- Primary interactive: `#38BDF8`
- Primary hover: `#7DD3FC`
- Text utama: `#E0F2FE`
- Text netral: `#CBD5E1`
- Border: `#1E4B63`
- Focus ring: `#7DD3FC`

Warna status harus memakai semantic token `success`, `warning`, `danger`, dan `info`. Warna tidak boleh menjadi satu-satunya indikator status.

Print view dan dokumen PDF selalu menggunakan tema light.

## ATURAN KERJA WAJIB

1. Baca `AGENTS.md` terlebih dahulu.
2. Baca `README.md`, `PROJECT-STATUS.md`, `FILE-MANIFEST.md`, dan `UPDATE-SUMMARY.md`.
3. Baca seluruh dokumen di folder `docs/` dan `plans/` sesuai urutan dalam `docs/README.md`.
4. Jangan mengarang SOP klinis, diagnosis, dosis, kewenangan tenaga kesehatan, aturan consent, atau regulasi.
5. Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`.
6. Jangan menjalankan command destruktif.
7. Jangan memakai data pasien nyata untuk development, seeder, screenshot, atau test.
8. Jangan menampilkan secret atau isi `.env`.
9. Jangan menjalankan production deployment.
10. Jangan mengubah code sebelum preflight dan readiness review selesai.
11. Gunakan Graphify sebagai sumber navigasi setelah graph tersedia.
12. Jangan menggunakan Graphify `--code-only`, karena Markdown adalah sumber kebenaran domain.
13. Seluruh pekerjaan harus memiliki bukti test, audit perubahan, dan dokumentasi.
14. Jangan menyelesaikan seluruh aplikasi dalam satu iterasi.
15. Berhenti pada checkpoint yang ditentukan dan tunggu persetujuan manusia.

# TAHAP A — REPOSITORY PREFLIGHT

Lakukan pemeriksaan read-only:

1. Tampilkan current working directory.
2. Tampilkan struktur direktori maksimal tiga level.
3. Periksa status Git.
4. Periksa keberadaan `artisan`, `composer.json`, `package.json`, `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`, `tests/`, `docs/`, dan `plans/`.
5. Hitung jumlah file Markdown dan bandingkan dengan `FILE-MANIFEST.md`.
6. Periksa apakah dokumentasi berada pada path yang benar dan tidak hanya di-flatten ke root.
7. Periksa versi PHP, Composer, Node.js, npm, Git, MariaDB/MySQL client, `uv`, dan `graphify`.
8. Jangan menginstal atau mengubah apa pun pada tahap ini.

Buat laporan `docs/10-delivery/ENVIRONMENT-PREFLIGHT.md` berisi environment, file/folder yang hilang, dokumentasi yang salah lokasi, dependency yang belum tersedia, risiko, rekomendasi, dan status `READY`, `READY-WITH-BLOCKERS`, atau `NOT-READY`.

Jika dokumentasi tidak lengkap atau directory structure tidak sesuai manifest:

- jangan mengarang isi file;
- jangan memindahkan file tanpa rencana;
- tuliskan recovery plan yang presisi;
- tandai sebagai blocker;
- berhenti sebelum coding.

# TAHAP B — GRAPHIFY

Jika dokumentasi lengkap dan Graphify tersedia:

1. Gunakan skill Graphify yang sudah terpasang.
2. Bangun knowledge graph seluruh repository dengan mode deep.
3. Sertakan source code, Markdown, SQL/migration, konfigurasi, dan test.
4. Jangan gunakan `--code-only`.
5. Pastikan tersedia:
   - `graphify-out/graph.html`;
   - `graphify-out/GRAPH_REPORT.md`;
   - `graphify-out/graph.json`.
6. Query graph untuk:
   - Gate -> Person -> User -> Patient;
   - patient eligibility;
   - konsultasi klinis;
   - consultation dan referral;
   - access control;
   - requirement tanpa test;
   - business rule tanpa implementation target;
   - risiko hard delete;
   - kemungkinan client-supplied actor, timestamp, atau status.
7. Catat hasil di `docs/10-delivery/GRAPHIFY-BASELINE-REVIEW.md`.

Jika Graphify belum dapat dijalankan, tuliskan exact command yang dibutuhkan tetapi jangan mengarang hasil graph.

# TAHAP C — READINESS REVIEW

Periksa:

1. Konsistensi domain.
2. Person/User/Role/Patient separation.
3. Gate source-of-truth dan field ownership.
4. Idempotency dan reconciliation.
5. Medical visit lifecycle.
6. Observation lifecycle.
7. Medication and stock transaction.
8. Clinical consultation lifecycle.
9. Emergency referral guard.
10. External advice attribution.
11. Privacy dan minimum necessary.
12. Access-control matrix.
13. Auditability.
14. Data model dan database constraints.
15. Theme light/dark/system.
16. Test strategy.
17. Deployment dan backup.
18. Traceability Graphify.

Perbarui:

- `docs/10-delivery/READINESS-REVIEW.md`;
- `plans/KNOWN-ISSUES.md`;
- `PROJECT-STATUS.md`.

Klasifikasikan temuan sebagai Critical, High, Medium, atau Low.

Jangan lanjut ke bootstrap jika ada blocker Critical yang membuat desain inti tidak aman atau ambigu.

# TAHAP D — LARAVEL FOUNDATION

Hanya lakukan jika dokumentasi cukup, tidak ada blocker Critical yang melarang bootstrap, direktori/Git aman, dan seluruh dokumentasi dapat dipertahankan.

Jika skeleton Laravel belum tersedia:

1. Buat rencana bootstrap tanpa menghapus dokumentasi.
2. Gunakan temporary directory lalu merge secara terkontrol jika root tidak kosong.
3. Sebelum command, tampilkan rencana dan file terdampak.
4. Jangan overwrite dokumentasi.
5. Jangan menghapus `.agents/` atau skill Graphify.

Implementasikan hanya fondasi:

- Laravel 13;
- Livewire starter kit;
- authentication baseline;
- Pest;
- Pint;
- Larastan/PHPStan;
- Laravel Boost;
- MariaDB development melalui `.env.example`;
- timezone dan locale Indonesia;
- app shell;
- semantic theme tokens;
- light/dark/system theme;
- theme preference persistence;
- responsive sidebar;
- top navigation;
- empty dashboard shell;
- health endpoint;
- CI baseline;
- test baseline.

Jangan membuat modul rekam medis, migration klinis, Gate sync, consultation, obat, observasi, atau rujukan pada tahap ini.

# REQUIREMENT THEME FOUNDATION

Implementasikan:

- theme applied before first paint;
- default `system`;
- local preference sebelum login;
- account preference setelah login;
- accessible switcher;
- keyboard support;
- focus state;
- print always light;
- no theme flicker;
- automated preference test;
- screenshot/artifact light dan dark pada desktop dan mobile.

Gunakan semantic tokens:

- `--background`;
- `--surface`;
- `--surface-muted`;
- `--foreground`;
- `--foreground-muted`;
- `--border`;
- `--primary`;
- `--primary-hover`;
- `--primary-soft`;
- `--focus-ring`;
- `--success`;
- `--warning`;
- `--danger`;
- `--info`.

# VERIFIKASI

Jalankan dan laporkan:

- `php artisan test`;
- formatter check;
- static analysis;
- frontend build;
- route list;
- migration status pada database development/test;
- browser smoke test;
- theme light/dark/system;
- responsive desktop/mobile;
- unauthorized route smoke test.

Jangan menyatakan berhasil jika command gagal.

# DOKUMENTASI SETELAH IMPLEMENTASI

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Kemudian update Graphify graph.

# OUTPUT AKHIR

Berikan:

1. Executive summary.
2. Preflight findings.
3. Graphify status.
4. Readiness findings.
5. File dibuat/diubah.
6. Command dijalankan.
7. Test dan hasil aktual.
8. Screenshot/artifact light dan dark.
9. Risiko dan blocker.
10. Exact next recommended phase.
11. Git diff summary.

# CHECKPOINT WAJIB

- Setelah tahap A, B, dan C, berhenti jika ada blocker Critical atau dokumentasi tidak lengkap.
- Jika aman, lanjutkan hanya ke Laravel Foundation.
- Setelah Laravel Foundation selesai dan diverifikasi, berhenti.
- Jangan mulai Phase Identity/Gate atau modul medis sampai saya memberikan persetujuan eksplisit.
