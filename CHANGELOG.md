---
id: DOC-CHANGELOG
title: "Changelog"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Changelog

Semua perubahan penting proyek dicatat di file ini.

## [Unreleased]

## [0.3.0] — 2026-08-05

### Added

- Inisialisasi repositori Git dan bootstrap fondasi Laravel 13, Livewire 4, Pest 4, Larastan, dan Vite.
- Implementasi sistem tema 3-mode (`light`, `dark`, `system`) dengan semantic design tokens (`--background`, `--surface`, `--primary`, dll.).
- Script anti-flicker tema yang dieksekusi sebelum *first paint*.
- Komponen switcher tema yang aksebel dan responsif dengan dukungan keyboard serta status persistence (`localStorage`).
- Layout App Shell modern dengan sidebar responsif, topbar, dan footer.
- Dashboard shell kosong dengan kartu statistik poskestren dan pengumuman SOP pelayanan santri sakit.
- Endpoint kesehatan `/health` yang mengembalikan status sistem JSON (DB, Cache, Storage).
- Pengujian otomatis Pest untuk Dashboard, HealthCheck, dan Theme Preference.
- Laporan preflight (`ENVIRONMENT-PREFLIGHT.md`), baseline graphify (`GRAPHIFY-BASELINE-REVIEW.md`), dan readiness review (`READINESS-REVIEW.md`).

### Fixed

- Membersihkan 6 file Markdown duplikat pada root directory yang sudah ada di folder `docs/`.

- Definisi awal domain pelayanan kesehatan santri berasrama.
- Rancangan MVP, arsitektur, keamanan, UI/UX, data, API, testing, delivery, ADR, dan Graphify.
- Dukungan tema light, dark, dan system pada requirement.
- Instruksi kerja AI melalui `AGENTS.md`.

### Changed

- Belum ada.

### Fixed

- Belum ada.

### Security

- Menetapkan requirement audit trail dan larangan hard delete catatan medis.

## [0.2.0] — 2026-08-05

### Added

- Model identitas `person -> user -> patient`.
- Kelayakan pasien untuk seluruh pengguna manusia dari Gate.
- Workflow sinkronisasi Gate yang idempotent dan dapat direkonsiliasi.
- Workflow konsultasi klinis jarak jauh ke Puskesmas/rumah sakit.
- Clinical consultation summary dan external clinical advice.
- Security governance untuk pertukaran data konsultasi.
- Panduan instalasi Graphify untuk macOS, Codex, Gemini, dan Antigravity.
- ADR pemisahan person/patient dan konsultasi klinis jarak jauh.

### Changed

- Scope aplikasi diperluas dari rekam medis santri menjadi rekam medis warga SABIRA dengan aturan operasional khusus santri berasrama.
- Admin dipisahkan sebagai permission; hanya akun administratif/teknis murni yang tidak menjadi pasien.
- Gate ditegaskan sebagai source of truth identitas dan tipe pengguna.
