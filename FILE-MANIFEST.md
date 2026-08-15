---
id: DOC-FILE-MANIFEST
title: "Manifest File Dokumentasi"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Manifest File

Manifest ini mendeskripsikan kelompok file kanonikal tanpa menyimpan daftar statis yang cepat usang. Inventaris exact untuk suatu commit selalu diperoleh dengan `git ls-files`; file ignored dan untracked bukan bagian dari rilis.

## Root kanonikal

- `AGENTS.md` — kontrak coding agent.
- `README.md` — orientasi publik, instalasi, keamanan, dan lisensi.
- `PROJECT-STATUS.md` dan `CHANGELOG.md` — status serta riwayat perubahan.
- `SECURITY.md` — kanal dan aturan pelaporan kerentanan.
- `CONTRIBUTING.md` dan `BOOTSTRAP-CHECKLIST.md` — panduan contributor/bootstrap.
- `plans/` — backlog, sprint, dan keputusan yang masih terbuka.

Prompt eksekusi AI bukan dokumentasi kanonikal dan tidak boleh di-track.

## Dokumentasi

`docs/00-project` sampai `docs/12-graphify` mengelompokkan konteks proyek, domain, workflow, requirements, architecture, data, UI, security, API, testing, delivery, ADR, dan Graphify. Indeks naratif tersedia di `docs/README.md`; hubungan dokumen dengan implementasi tersedia di `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

## Implementasi dan verifikasi

- `app/`, `config/`, `database/`, `resources/`, dan `routes/` — source aplikasi.
- `tests/` — unit, feature, security, UAT, concurrency, dan UI contract tests.
- `composer.json`/`composer.lock` serta `package.json`/`package-lock.json` — dependency manifests.
- `.env.example` — satu-satunya environment template yang boleh tracked.
- `graphify-out/` — canonical graph outputs; cache tetap ignored.

## Inventaris rilis

Gunakan sebelum commit/publikasi:

```bash
git ls-files
git status --short
git diff --cached --name-status
```

Larangan dan klasifikasi file rinci terdapat di `docs/10-delivery/REPOSITORY-HYGIENE-POLICY.md` dan `docs/10-delivery/GITHUB-PUBLIC-REPOSITORY-CHECKLIST.md`.
