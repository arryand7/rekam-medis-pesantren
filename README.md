---
id: DOC-ROOT-README
title: "SABIRA POSKESTREN Health"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# SABIRA POSKESTREN Health

Sistem rekam medis dan pelayanan kesehatan warga SABIRA, dengan prioritas operasional santri berasrama selama 24 jam.

Seluruh santri tinggal di asrama. Santri yang diketahui sakit tidak diperbolehkan tetap berada di asrama tanpa penanganan. Santri harus dibawa atau diarahkan ke POSKESTREN untuk didata, diperiksa, mendapatkan tindakan pertama, lalu diputuskan apakah dapat kembali beraktivitas, perlu diobservasi di ruang kesehatan, atau harus dirujuk ke fasilitas kesehatan.

## Sasaran sistem

- Menyatukan rekam medis seluruh pengguna manusia yang memenuhi syarat sebagai pasien, termasuk santri, guru, dan staf.
- Menertibkan alur penerimaan, pemeriksaan, observasi, pemberian obat, konsultasi klinis jarak jauh, rujukan, dan pemulangan.
- Memberikan informasi operasional yang tepat kepada petugas kesehatan, pengasuh, wali kelas, manajemen, dan wali santri sesuai kewenangan.
- Menyediakan audit trail untuk setiap perubahan data medis.
- Menyediakan dokumentasi yang dapat dipetakan oleh Graphify dari aturan bisnis sampai implementasi dan pengujian.

## Baseline teknis

- Laravel 13
- PHP 8.3 atau lebih baru
- Livewire 4
- Tailwind CSS
- Flux UI
- Pest
- MariaDB
- Redis-ready untuk queue, cache, dan lock
- Vite
- Tema `light`, `dark`, dan `system`
- Zona waktu aplikasi `Asia/Jakarta`

## Status

Proyek berada pada local pre-staging readiness dengan klasifikasi `PHASE-5D-PRE-STAGING-READY-WITH-MANUAL-ITEMS`. Aplikasi belum pernah dideploy ke staging/production; Gate, Attendance, TLS/proxy dan browser acceptance nyata masih menunggu verifikasi environment. Belum ada keputusan final mengenai SOP medis yang ditandai perlu konfirmasi.

## Mulai membaca

1. [AGENTS.md](AGENTS.md)
2. [docs/00-project/PROJECT-BRIEF.md](docs/00-project/PROJECT-BRIEF.md)
3. [docs/00-project/MVP-SCOPE.md](docs/00-project/MVP-SCOPE.md)
4. [docs/01-domain/OPERATIONAL-CONTEXT.md](docs/01-domain/OPERATIONAL-CONTEXT.md)
5. [docs/01-domain/BUSINESS-RULES.md](docs/01-domain/BUSINESS-RULES.md)
6. [docs/01-domain/PERSON-PATIENT-IDENTITY.md](docs/01-domain/PERSON-PATIENT-IDENTITY.md)
7. [docs/02-workflows/GATE-USER-SYNC.md](docs/02-workflows/GATE-USER-SYNC.md)
8. [docs/02-workflows/REMOTE-CLINICAL-CONSULTATION.md](docs/02-workflows/REMOTE-CLINICAL-CONSULTATION.md)
9. [docs/04-architecture/TECH-STACK.md](docs/04-architecture/TECH-STACK.md)
10. [docs/07-security/ACCESS-CONTROL-MATRIX.md](docs/07-security/ACCESS-CONTROL-MATRIX.md)
11. [docs/10-delivery/IMPLEMENTATION-PLAN.md](docs/10-delivery/IMPLEMENTATION-PLAN.md)
12. [docs/12-graphify/GRAPHIFY-INSTALLATION.md](docs/12-graphify/GRAPHIFY-INSTALLATION.md)
13. [docs/12-graphify/AI-HANDOFF.md](docs/12-graphify/AI-HANDOFF.md)

## Model identitas dan pasien

Gate adalah sumber kebenaran identitas, tipe pengguna, dan status akun. Aplikasi menyimpan proyeksi lokal yang aman untuk kebutuhan operasional.

Identitas manusia, akun login, role aplikasi, dan profil pasien dipisahkan:

- `person` merepresentasikan manusia;
- `user` merepresentasikan akun login;
- role/permission menentukan akses;
- `patient` merepresentasikan subjek rekam medis.

Semua `person` dapat menjadi pasien. Akun teknis atau administratif murni tidak memiliki profil pasien. Pengguna manusia yang juga memiliki permission admin tetap dapat menjadi pasien.

## Prinsip penting

- Dokumentasi domain adalah sumber kebenaran untuk perilaku sistem.
- Data medis tidak boleh diubah atau dihapus tanpa jejak.
- UI tidak menjadi pengganti authorization di server.
- Status medis dan disposisi tidak boleh ditentukan oleh client.
- Sistem tahap awal tidak memberikan diagnosis otomatis berbasis AI.
- Implementasi dilakukan per fase dan wajib disertai test serta pembaruan dokumentasi.
