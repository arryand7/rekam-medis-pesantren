---
id: DOC-REPOSITORY-HYGIENE-POLICY
title: "Kebijakan Kebersihan Repositori (Repository Hygiene Policy)"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-13
---

# Kebijakan Kebersihan Repositori (Repository Hygiene Policy)

Dokumen ini adalah aturan permanen tata kelola dan kebersihan repositori untuk seluruh pengembang, kontributor, dan AI Coding Agent.

---

## 1. Berkas yang Wajib Berada di Git (Tracked in Git)

Berikut adalah artefak permanen yang sah untuk disimpan dalam kontrol versi:

- **Source Code**: Controller, Model, Service, Action, DTO, Request, Policy, Event, Listener, View Blade, Assets CSS/JS.
- **Konfigurasi Aplikasi & Template**: `config/*.php`, `.env.example`, `composer.json`, `package.json`, `phpunit.xml`, `phpstan.neon`, `vite.config.js`.
- **Database Schema & Data**: Migrations, Seeders, Factories.
- **Automated Tests**: Unit, Feature, Architecture, Integration, UAT test cases (`tests/**`).
- **Dokumentasi Kanonikal**:
  - Proyek & Domain: `docs/00-project/`, `docs/01-domain/`
  - Workflow & Proses: `docs/02-workflows/`
  - Arsitektur & Boundary: `docs/04-architecture/`
  - Data Dictionary & UI: `docs/05-data/`, `docs/05-ui/`
  - Integrasi: `docs/06-integration/`, `docs/08-api/`
  - Keamanan & Akses: `docs/07-security/`
  - Pengujian: `docs/09-testing/`
  - Delivery & Release: `docs/10-delivery/`
  - Keputusan Arsitektur: `docs/11-decisions/` (ADR)
  - Graphify Knowledge Graph: `docs/12-graphify/` (kebijakan khusus di bawah)
- **Status & Panduan**: `README.md`, `CHANGELOG.md`, `PROJECT-STATUS.md`, `AGENTS.md`, `CONTRIBUTING.md`, `FILE-MANIFEST.md`, dan `BOOTSTRAP-CHECKLIST.md`.
- **Graphify Canonical Deliverables**: `graphify-out/graph.json`, `graphify-out/graph.html`, `graphify-out/GRAPH_REPORT.md`, `graphify-out/manifest.json`, `graphify-out/.graphify_labels.json*`, `graphify-out/.graphify_root`, dated snapshots.

---

## 2. Berkas yang DILARANG Berada di Git (Not Belong in Git)

Berkas berikut berstatus transien, privat, atau hasil komputasi ulang yang tidak boleh di-commit:

- **AI Execution Prompts**: Berkas `PROMPT-*.md`, instruksi eksekusi satu kali Gemini/Claude/Codex/Antigravity, prompt resume/handoff.
- **Tangkapan Layar & Rekaman Transien**: Screenshot browser manual/otomatis, video webp rekaman pengujian lokal, artefak `.tempmediaStorage`.
- **Operating System Metadata**: `.DS_Store`, `Thumbs.db`, desktop.ini.
- **Runtime Logs**: `storage/logs/*.log`, `laravel.log`.
- **Local Cache & Build Caches**:
  - `storage/framework/cache/**`
  - `storage/framework/sessions/**`
  - `storage/framework/views/**`
  - `.phpunit.cache/`, `.phpunit.result.cache`
  - `coverage/`, `.phpstan.cache/`
- **Graphify AST Extraction Cache**: `graphify-out/cache/` (100% reproducible via `graphify update .`).
- **IDE State & Local Config**: `.idea/`, `.vscode/` (kecuali shared workspace settings yang disepakati), `*.sublime-*`.
- **Secrets & Machine-Local Environments**: `.env`, `.env.local`, file kredensial atau private key.
- **Temporary Dumps & Scratch Scripts**: SQL dumps lokal, scratch scripts eksperimental non-tooling.

---

## 3. Aturan Khusus AI Prompt (AI Prompt Rule)

> [!IMPORTANT]
> **AI execution prompts adalah artefak transien (one-time instructions).**
> Seluruh AI execution prompts **TIDAK BOLEH** menetap di dalam repositori git setelah tahap eksekusi selesai.

### Prosedur Penanganan Keputusan AI
1. Jika suatu prompt AI melahirkan keputusan arsitektural, keamanan, privasi, klinis, pengujian, integrasi, atau delivery yang berharga (*durable knowledge*), keputusan tersebut **wajib diekstraksi ke dokumen kanonikal** yang sesuai sebelum prompt dihapus.
2. Setelah ekstraksi dipastikan lengkap, file prompt AI dihapus dari working tree dan git index (`git rm`).
3. Repositori bersih dari file prompt AI pada setiap milestone atau release commit (`PROMPT_FILES_RETAINED = 0`).

---

## 4. Pemetaan Tujuan Pengetahuan Kanonikal (Canonical Knowledge Destinations)

| Kategori Pengetahuan | Direktori Kanonikal |
|---|---|
| Keputusan Arsitektur & Tech Stack | `docs/04-architecture/`, `docs/11-decisions/` (ADR) |
| Aturan Bisnis & Konsep Domain | `docs/01-domain/` |
| Alur Kerja Operasional & Klinis | `docs/02-workflows/` |
| Desain Antarmuka & Verifikasi UI | `docs/05-ui/` |
| Data Model & Dictionary | `docs/05-data/` |
| Kontrak Integrasi & API | `docs/06-integration/`, `docs/08-api/` |
| Tata Kelola Keamanan, Akses & Privasi | `docs/07-security/` |
| Strategi Pengujian & QA | `docs/09-testing/` |
| Delivery, Audit Rilis & Verifikasi Tahap | `docs/10-delivery/` |
| Knowledge Graph & Graphify Policy | `docs/12-graphify/` |

---

## 5. Bukti Pengujian Lingkungan Lokal vs Produksi

- Bukti pengujian yang dijalankan di `localhost`, `127.0.0.1`, macOS Darwin, atau SQLite memory adalah **bukti validasi pengembangan lokal (Local Development Rehearsal)**.
- Dilarang mengklaim bukti eksekusi lokal sebagai bukti kesiapan server produksi fisik aktual sebelum server produksi riil di-deploy dan divalidasi.
