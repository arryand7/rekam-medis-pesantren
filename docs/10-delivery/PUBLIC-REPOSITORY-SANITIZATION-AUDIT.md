---
id: DELIVERY-PUBLIC-REPOSITORY-SANITIZATION-AUDIT
title: "Public Repository Sanitization Audit"
status: complete
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Public Repository Sanitization Audit

## Cakupan

Audit meliputi file tracked, nama file, konfigurasi, source, tests, fixtures, seeders, dokumentasi, Graphify canonical outputs, `.gitignore`, dependency manifests/lockfiles, dan seluruh 38 commit yang sudah ada pada entry gate. Folder dependency dan cache generated tidak dinilai sebagai source publik.

## Perubahan sanitasi

- `.env.testing` tracked dihapus; konfigurasi test tetap berada di `phpunit.xml` dengan key yang jelas sintetis.
- `.gitignore` diperluas untuk environment lokal, key/certificate, database/dump, backup/archive, private exports/storage, Graphify cache, dan prompt AI transien.
- fallback Gate dan seluruh contoh identitas uji menggunakan `example.invalid` atau `sabira.test`.
- fixture mitra kesehatan dan placeholder klinisi yang tampak nyata diganti identitas fiktif eksplisit.
- manifest Composer tidak lagi menyatakan lisensi MIT yang belum diputuskan; status sementara `proprietary`.
- `SECURITY.md`, threat model, checklist, scan evidence, dan regression test sanitasi ditambahkan.
- prompt eksekusi Phase 6A0 serta `AI-FIRST-RUN-PROMPT.md` yang tersisa di root dihapus dan tidak dijadikan dokumentasi kanonikal.

## Klasifikasi temuan

| Kelas | Hasil | Tindakan |
|---|---|---|
| `REAL-SECRET` | 0 | Tidak ada rotasi/revoke |
| `SYNTHETIC-TEST-MATERIAL` | Fixed Laravel test key dan fake tokens | Dipertahankan hanya bila eksplisit sintetis |
| `NON-SECRET-PUBLIC-INFO` | Host localhost, nama DB test, arsitektur, metadata path historis | Diterima/didokumentasikan |
| `PRIVATE-DATA` | 0 terkonfirmasi | Tidak ada data nyata yang dipublikasikan |
| `REQUIRES-OWNER-DECISION` | Lisensi dan alamat disclosure privat | Dicatat terbuka |

## History decision

History memuat `.env.testing` sejak fase awal dengan `APP_ENV=testing`, database test, password kosong, serta key Laravel yang sama dengan konfigurasi PHPUnit. Konteks dan bentuk nilainya menunjukkan material uji, bukan credential layanan nyata. Search history untuk header private key dan pola token umum tidak menemukan kecocokan. Path workstation lama dan prompt AI lama adalah metadata pengembangan non-secret yang telah hilang dari tree aktif.

Keputusan: history tidak memerlukan rewrite untuk keamanan. Jika owner memiliki kebutuhan privasi lebih ketat terhadap metadata nama/path developer, rewrite dapat dipertimbangkan terpisah sebelum remote publik dibuat.

## Batas klaim

Audit ini menilai repository lokal pada SHA awal dan perubahan fase ini. Ia tidak membuktikan keamanan konfigurasi deployment atau secret store eksternal. `gitleaks` dan `trufflehog` tidak tersedia; tidak ada instalasi tool arbitrer. Bukti memakai pencarian Git/regex, inventaris file, review manual, dependency audit, dan regression test.

Final local gate: 283 tests / 1.301 assertions, Pint, PHPStan (0 error), Vite build, Composer validation/audit, npm clean install/audit, dan `git diff --check` lulus.
