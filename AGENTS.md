---
id: DOC-AGENTS
title: "Instruksi Wajib untuk AI Coding Agent"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Instruksi Wajib untuk AI Coding Agent

Dokumen ini adalah kontrak kerja bagi Codex, Gemini, Claude, atau coding agent lain yang dijalankan dari root repositori.

## Urutan baca wajib

Sebelum mengubah kode, baca:

1. `README.md`
2. `PROJECT-STATUS.md`
3. `docs/00-project/PROJECT-BRIEF.md`
4. `docs/00-project/MVP-SCOPE.md`
5. `docs/01-domain/BUSINESS-RULES.md`
6. Workflow yang berkaitan dengan tugas.
7. `docs/04-architecture/TECH-STACK.md`
8. `docs/04-architecture/MODULE-BOUNDARIES.md`
9. `docs/05-data/DATA-DICTIONARY.md`
10. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
11. `docs/09-testing/TEST-STRATEGY.md`
12. `docs/10-delivery/IMPLEMENTATION-PLAN.md`
13. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

## Aturan kerja

1. Jangan mulai coding sebelum memahami requirement dan acceptance criteria tugas.
2. Kerjakan satu fase atau satu capability yang jelas dalam satu iterasi.
3. Jika dokumentasi bertentangan, hentikan perubahan pada bagian yang konflik dan catat di `plans/KNOWN-ISSUES.md`.
4. Jangan mengarang SOP medis, dosis, diagnosis, kewenangan petugas, atau aturan komunikasi wali.
5. Gunakan waktu server sebagai waktu resmi untuk kejadian medis.
6. Semua operasi multi-tabel penting harus memakai database transaction.
7. Controller dan komponen Livewire harus tipis.
8. Business logic ditempatkan pada Action atau Service.
9. Authorization wajib menggunakan Policy atau Gate server-side.
10. Validation wajib dilakukan di server.
11. Gunakan enum atau state object untuk lifecycle penting.
12. Event domain digunakan untuk side effect seperti notifikasi dan audit.
13. Jangan menaruh data medis sensitif di log aplikasi biasa.
14. Setiap perubahan rekam medis wajib menghasilkan audit trail.
15. Catatan medis yang sudah disahkan tidak boleh hard delete.
16. Buat test sebelum menyatakan pekerjaan selesai.
17. Perbarui dokumentasi yang terdampak.
18. Perbarui `PROJECT-STATUS.md` dan `CHANGELOG.md`.
19. Jangan mengubah teknologi utama tanpa ADR baru.
20. Jangan melakukan refactor luas yang tidak diminta.

## Baseline arsitektur

- Modular monolith.
- Laravel 13, PHP 8.3+.
- Livewire 4, Tailwind CSS, Flux UI.
- Pest untuk feature, unit, dan architecture tests.
- UUID/ULID untuk identifier publik dan entitas medis.
- MariaDB sebagai database utama.
- Redis opsional pada development dan direkomendasikan pada production.
- Queue untuk notifikasi dan proses non-kritis.
- Policies untuk authorization.
- Form Request atau validasi Livewire untuk input.
- Action classes untuk use case.
- Domain events untuk kejadian penting.
- Audit log append-only.

## Larangan destruktif

Jangan menjalankan tindakan berikut kecuali database test terisolasi:

- `php artisan migrate:fresh`
- `php artisan db:wipe`
- `DROP DATABASE`
- menghapus migration yang sudah diterapkan
- menghapus data medis untuk memperbaiki test
- mengubah production credential
- menonaktifkan Policy untuk melewati kegagalan
- melakukan force push
- menjalankan command deployment tanpa instruksi eksplisit

## Definition of Done ringkas

Tugas selesai hanya jika:

- Requirement dan acceptance criteria terpenuhi.
- Authorization dan validation diterapkan.
- Transaction boundary benar.
- Audit event tersedia bila diperlukan.
- Feature test dan security test terkait lulus.
- Light dan dark theme diuji untuk UI baru.
- Dokumentasi serta traceability diperbarui.
- Tidak ada data sensitif bocor ke log atau response.
- `PROJECT-STATUS.md` mencerminkan keadaan aktual.

## Format laporan setelah perubahan

Laporkan:

1. Tujuan tugas.
2. File yang diubah.
3. Migration atau perubahan schema.
4. Route/API baru.
5. Policy dan authorization.
6. Test yang dibuat dan hasilnya.
7. Dokumentasi yang diperbarui.
8. Risiko dan pekerjaan lanjutan.

## Aturan identitas Gate dan pasien

1. Gate adalah source of truth untuk identitas, tipe pengguna, dan status akun.
2. Jangan mengizinkan form lokal mengubah field authoritative dari Gate.
3. Sinkronisasi harus idempotent dan memiliki audit serta reconciliation report.
4. Deaktivasi akun tidak boleh menghapus person, patient profile, atau riwayat kesehatan.
5. Pisahkan `person`, `user`, role/permission, dan `patient`.
6. Permission admin tidak menentukan apakah seorang manusia boleh menjadi pasien.
7. Hanya akun teknis/administratif murni yang tidak memiliki profil pasien.
8. Jangan memakai email, NIS, atau NIP sebagai primary key internal.
9. Simpan `gate_user_id`, source version/timestamp, checksum, dan waktu sinkronisasi.
10. Konflik identitas tidak boleh diselesaikan dengan overwrite diam-diam.

## Aturan konsultasi klinis jarak jauh

1. Ringkasan konsultasi harus berasal dari data assessment yang dapat ditelusuri.
2. Identitas fasilitas dan tenaga penerima/penjawab harus dicatat.
3. Respons eksternal disimpan sebagai `external_clinical_advice`, bukan ditulis seolah-olah assessment lokal.
4. Konsultasi jarak jauh tidak boleh menunda rujukan darurat.
5. Pengiriman data harus melalui kanal yang disetujui, minimum necessary, dan diaudit.
6. AI tidak boleh menghasilkan diagnosis, resep, atau keputusan klinis otomatis.
