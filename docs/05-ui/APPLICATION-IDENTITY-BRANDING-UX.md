---
id: UI-APPLICATION-IDENTITY-BRANDING
title: "Application Identity & Branding UX"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Application Identity & Branding UX

## Struktur layar

Layar `Administrasi & Sistem > Identitas Aplikasi` terdiri dari Identitas Utama, Deskripsi & Footer, Logo & Ikon, Preview, tombol simpan, dan area reset terkonfirmasi. Form menggunakan semantic theme primitives yang sama dengan UI klinis agar label, hint, border, placeholder, dan status tetap terbaca pada light, dark, dan system mode.

## Perilaku visual

- Header, sidebar, login, title HTML, footer, dan favicon membaca identity terpusat.
- Preview memperlihatkan logo pada surface terang dan gelap, ringkasan header, serta favicon tanpa memaksa recolor terhadap upload pengguna.
- Logo dark kustom bersifat opsional. Fallback berurutan: logo dark kustom, logo utama kustom, lalu logo dark default.
- Layout grid turun menjadi satu kolom pada mobile dan tidak menggunakan fixed content width yang menimbulkan overflow halaman.
- Label native, `aria-describedby`, focus ring, alt text, petunjuk format/ukuran, dan konfirmasi reset tersedia untuk penggunaan keyboard dan pembaca layar.

## Identitas default

Identitas default memakai biru klinis dan indigo dengan mark geometris orisinal berupa bulan sabit, bintang kecil, dan cue perawatan berbentuk buku/daun. Bentuk dibuat langsung dari primitive SVG project-owned, bukan logo sekolah, organisasi, pemerintah, atau lambang kemanusiaan.

Actual institution logo tidak disertakan dalam repository publik. Super Admin dapat mengunggahnya sebagai aset runtime setelah memperoleh persetujuan publikasi yang tepat.
