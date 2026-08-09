---
id: DOC-GRAPHIFY-INSTALL
title: "Instalasi Graphify pada Proyek"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-05
---

# Instalasi Graphify pada Proyek

## Konsep instalasi

Package Graphify diinstal secara global dan terisolasi melalui `uv`. Skill assistant dipasang pada scope proyek dari root repository.

Package PyPI bernama `graphifyy` dengan dua huruf `y`, sedangkan command yang digunakan adalah `graphify`.

## 1. Install uv pada macOS

Perintah ini dapat dijalankan dari direktori mana pun:

```bash
brew install uv
uv --version
```

## 2. Install Graphify CLI

```bash
uv tool install graphifyy
uv tool update-shell
```

Tutup dan buka terminal baru, lalu verifikasi:

```bash
graphify --version
```

Python 3.10 atau lebih baru diperlukan oleh Graphify.

## 3. Masuk ke root proyek

```bash
cd /path/ke/poskestren-health
pwd
git status
```

Pastikan direktori berisi `AGENTS.md`, `docs/`, `app/`, dan `composer.json`.

## 4. Register skill project-scoped

### Codex

```bash
graphify codex install --project
```

Di Codex, skill dipanggil menggunakan:

```text
$graphify .
```

### Gemini CLI

```bash
graphify gemini install --project
```

Di Gemini:

```text
/graphify .
```

### Antigravity

```bash
graphify antigravity install --project
```

### Lebih dari satu assistant

Jalankan subcommand masing-masing agar konfigurasi berada di repo dan dapat direview tim.

## 5. Build graph pertama

Jangan gunakan `--code-only`, karena dokumentasi Markdown adalah bagian penting domain aplikasi ini.

Codex:

```text
$graphify . --mode deep
```

Gemini/assistant lain:

```text
/graphify . --mode deep
```

Output:

```text
graphify-out/
├── graph.html
├── GRAPH_REPORT.md
└── graph.json
```

## 6. Query awal

```bash
graphify query "bagaimana Gate user dipetakan menjadi person dan patient?"
graphify query "aturan apa yang mencegah admin manusia kehilangan patient eligibility?"
graphify query "bagaimana clinical consultation terhubung ke visit dan referral?"
graphify path "SyncGateUsersAction" "patients"
graphify explain "ClinicalConsultation"
```

## 7. Menjaga graph tetap baru

Dari assistant:

```text
$graphify . --update
```

atau:

```text
/graphify . --update
```

Otomatis pada perubahan:

```bash
graphify watch .
```

Otomatis setelah commit:

```bash
graphify hook install
```

## 8. Kebijakan repository

Pilih salah satu:

### Shared graph
Commit `graphify-out/` agar semua anggota tim menerima snapshot yang sama.

### Local graph
Tambahkan `graphify-out/` ke `.gitignore` dan setiap developer membangun sendiri.

Untuk proyek ini, shared graph direkomendasikan setelah ukuran file dan kebijakan privasi diperiksa. Jangan masukkan export yang berisi secret atau data pasien nyata.

## Troubleshooting

Jika `graphify` tidak ditemukan:

```bash
uv tool update-shell
```

Buka terminal baru.

Jika skill tidak muncul, ulangi install dari root repo:

```bash
graphify codex install --project
graphify gemini install --project
```

Lalu mulai ulang assistant.
