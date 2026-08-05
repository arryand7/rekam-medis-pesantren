---
id: DOC-THEME
title: "Light, Dark, dan System Theme"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
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

- Teks normal harus terbaca jelas.
- Warning tidak hanya menggunakan warna kuning.
- Red flag medis harus tetap jelas tanpa menyilaukan.
- Focus ring wajib terlihat pada dua tema.

## Persistence

Preference: `theme = light|dark|system`. Perubahan theme tidak memerlukan reload.
