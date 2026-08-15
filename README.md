---
id: DOC-ROOT-README
title: "SABIRA POSKESTREN Health"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# SABIRA POSKESTREN Health

SABIRA POSKESTREN Health adalah aplikasi Laravel untuk mendukung pencatatan layanan kesehatan warga pesantren: penerimaan kunjungan, assessment, observasi, farmasi, konsultasi eksternal, rujukan, kepulangan, tindak lanjut, dashboard, dan pelaporan berbasis peran.

> Status: local pre-staging/rehearsal. Repositori ini belum merupakan bukti deployment staging atau production. Integrasi Gate, Attendance, TLS/proxy, dan browser acceptance nyata masih harus diverifikasi pada environment tujuan.

## Prinsip keamanan dan privasi

- Gate adalah sumber kebenaran identitas; authorization tetap ditegakkan server-side melalui Policy/Gate.
- Data medis, identitas pasien, credential, dump database, log runtime, dan ekspor privat tidak boleh dimasukkan ke Git atau public issue.
- Fixture bawaan menggunakan data sintetis. Jangan memakai data pasien nyata untuk demo atau pengujian publik.
- Rekam medis yang disahkan bersifat append-only/amendment-aware dan perubahan penting menghasilkan audit trail.
- Aplikasi tidak menghasilkan diagnosis, resep, atau keputusan klinis otomatis berbasis AI.

Jika menemukan kerentanan, ikuti [SECURITY.md](SECURITY.md). Jangan mengirim secret, token, atau data medis melalui public issue.

## Stack dan kebutuhan lokal

- PHP 8.3+
- Composer 2
- Node.js/npm yang kompatibel dengan lockfile
- MariaDB
- Laravel 13, Livewire 4, Tailwind CSS, Flux UI, Pest, dan Vite

## Instalasi pengembangan

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Siapkan database lokal yang terisolasi, isi hanya nilai lokal di `.env`, lalu jalankan:

```bash
php artisan migrate
npm ci
npm run build
php artisan serve
```

Jangan menyalin credential staging/production ke `.env.example`, source code, fixture, dokumentasi, log, atau issue tracker. Integrasi eksternal default memakai endpoint `.invalid` dan tetap nonaktif sampai dikonfigurasi secara eksplisit.

## Quality gate

Gunakan database test yang terisolasi. Contoh portabel:

```bash
APP_ENV=testing DB_DATABASE=poskestren_health_test php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
composer validate --strict
composer audit
npm audit
git diff --check
```

Detail strategi dan portabilitas database terdapat di [TEST-STRATEGY.md](docs/09-testing/TEST-STRATEGY.md) dan [TEST-DATABASE-PORTABILITY.md](docs/09-testing/TEST-DATABASE-PORTABILITY.md).

## Dokumentasi utama

Mulai dari [AGENTS.md](AGENTS.md), [project brief](docs/00-project/PROJECT-BRIEF.md), [business rules](docs/01-domain/BUSINESS-RULES.md), [module boundaries](docs/04-architecture/MODULE-BOUNDARIES.md), [access-control matrix](docs/07-security/ACCESS-CONTROL-MATRIX.md), dan [implementation plan](docs/10-delivery/IMPLEMENTATION-PLAN.md).

Gate publikasi repositori didokumentasikan dalam [PUBLIC-GITHUB-RELEASE-GATE.md](docs/10-delivery/PUBLIC-GITHUB-RELEASE-GATE.md). Canonical Graphify outputs boleh di-track, sedangkan cache AST tetap diabaikan sesuai [Graphify version-control policy](docs/12-graphify/GRAPHIFY-VERSION-CONTROL-POLICY.md).

## Lisensi

Belum ada lisensi open-source yang dipilih pemilik proyek. Tanpa file `LICENSE`, hak penggunaan, penyalinan, modifikasi, dan distribusi tidak diberikan secara otomatis. Keputusan lisensi harus dibuat pemilik sebelum mengumumkan proyek sebagai open source.
