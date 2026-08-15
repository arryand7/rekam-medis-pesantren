---
id: DELIVERY-APPLICATION-IDENTITY-BRANDING-AUDIT
title: "Application Identity & Branding Audit"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Application Identity & Branding Audit

## Baseline

- Starting SHA: `20d3a8b87440c36dc9a7d62d199d8932907ddcfa`
- Branch: `main`
- Version: `0.24.0`
- Environment: local development; staging dan production belum dideploy.
- Working tree pada entry gate: bersih.

## Temuan arsitektur

- Belum ada tabel/model/service pengaturan aplikasi yang persisten.
- `config('app.name')` hanya berasal dari environment dan layout masih mengandung brand hardcoded.
- Permission protected `manage-system-settings` sudah kanonikal, disediakan seeder, dan diberikan secara eksplisit kepada role `admin`; `super_admin` memiliki bypass penuh.
- App shell memakai `resources/views/layouts/app.blade.php`; login memakai guest layout dan `pages/auth/login.blade.php`.
- Default mark saat ini berupa tanda plus/ikon generik inline dan tidak memiliki aset logo/favikon project-owned yang konsisten.
- Tidak ada PWA manifest; PWA dinyatakan `NOT-APPLICABLE` untuk capability ini.
- Disk `public` sudah tersedia di `storage/app/public`; dokumen medis menggunakan disk privat terpisah.
- `AuditLogService` mendukung event administratif append-only dengan payload before/after yang disanitasi.

## Keputusan implementasi

1. Tambahkan single-row `application_identities` dengan migration additive; default tetap dapat dirender tanpa row/database customization.
2. Gunakan `ApplicationIdentityService` sebagai satu-satunya loader/mutator, dengan cache dan invalidasi setelah update/reset.
3. Gunakan `manage-system-settings` untuk menu dan direct URL; tidak membuat permission baru.
4. Simpan upload raster publik pada disk `public` di `branding/`; source SVG default tetap committed di `public/branding/default/`.
5. Tolak uploaded SVG. Format runtime hanya PNG, JPEG, dan WebP dengan MIME/image validation.
6. Integrasikan identity melalui layout components agar Blade tidak melakukan query langsung.
7. Buat mark geometris orisinal bernuansa biru klinis: bulan sabit, bintang kecil, dan pola titik/ubin halus; tanpa lambang kemanusiaan atau logo pihak ketiga.

## Acceptance scope

Identity utama, deskripsi, logo/favikon, preview, footer, reset, audit, cache, fallback, title, header/sidebar shell, login, authorization, upload security, light/dark/system, responsive, public-repository hygiene, dan Graphify termasuk scope. CMS, marketing site, PWA baru, dan deployment tidak termasuk scope.
