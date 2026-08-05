---
id: DOC-TECH-STACK
title: "Technology Stack"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Technology Stack

## Application

- Laravel 13.
- PHP 8.3+.
- Livewire 4.
- Blade.
- Tailwind CSS.
- Flux UI.
- Alpine.js hanya untuk interaksi ringan.
- Vite.

## Data dan infrastructure

- MariaDB sebagai database utama.
- Redis untuk cache, lock, queue, dan rate limit pada production.
- Filesystem private untuk lampiran medis.
- Nginx dan PHP-FPM.
- Supervisor/systemd untuk queue worker.
- Cron untuk scheduler.

## Quality

- Pest.
- Laravel Pint.
- PHPStan/Larastan direkomendasikan.
- GitHub Actions atau CI setara.
- Laravel Boost untuk konteks agent.

## Prinsip pemilihan

Livewire dipilih karena aplikasi dominan berupa form, workflow, tabel, dashboard, dan administrasi. Modular monolith dipilih agar deployment sederhana tetapi batas domain tetap jelas.

## Batasan

- Tidak menggunakan SPA penuh pada MVP.
- Tidak membuat microservices sebelum kebutuhan operasional membuktikannya.
- Tidak memakai package permission/audit tanpa review keamanan dan maintenance.
