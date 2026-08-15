---
id: DOC-THEME
title: "Light, Dark, dan System Theme"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Light, Dark, dan System Theme

## Mode

- `light`
- `dark`
- `system`

## Aturan

- Default pengguna baru: `system`.
- Sebelum login: simpan preferensi lokal.
- Setelah login: preferensi akun menjadi sumber utama.
- Terapkan class/theme sebelum render untuk mencegah flicker.
- Semua komponen, chart, table, modal, dropdown, toast, form, dan skeleton diuji pada light/dark.
- Print selalu light.
- Email dan dokumen ekspor tidak mengikuti dark mode.

## Implementasi

Gunakan semantic CSS variables yang dipetakan ke Tailwind/Flux. Jangan hard-code warna pada setiap komponen.

## Kontras

- Teks normal, termasuk secondary/muted/placeholder/disabled, ditargetkan minimal rasio kontras WCAG AA 4.5:1 terhadap surface tempat teks ditampilkan.
- `foreground-tertiary` hanya untuk metadata non-kritis dan tetap memenuhi target teks normal pada surface utama.
- Warning tidak hanya menggunakan warna kuning.
- Red flag medis harus tetap jelas tanpa menyilaukan.
- Focus ring wajib terlihat pada dua tema.
- Status surface memakai pasangan token `*-bg` dan `*-text`; jangan memasangkan background soft dengan teks palet yang sama-sama terang.
- Mode `system` memakai set token light atau dark yang sama berdasarkan preferensi sistem, sehingga komponen tidak mempunyai cabang warna sendiri.

## Persistence

Preference: `theme = light|dark|system`. Perubahan theme tidak memerlukan reload.
