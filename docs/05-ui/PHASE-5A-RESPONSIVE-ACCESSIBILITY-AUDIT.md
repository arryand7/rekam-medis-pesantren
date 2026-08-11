---
id: DOC-PHASE-5A-RESPONSIVE-ACCESSIBILITY-AUDIT
title: "Phase 5A Responsive Design & Accessibility Audit"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-11
---

# Phase 5A Responsive Design & Accessibility Audit

Dokumen ini memvalidasi kepatuhan antarmuka terhadap standar desain responsif multi-perangkat dan pedoman aksesibilitas web (WCAG 2.1 AA).

---

## 1. Pengujian Viewport Responsif

| Viewport | Perangkat Target | Hasil Evaluasi Layout | Status |
|---|---|---|---|
| **375px** (Mobile Small) | iPhone SE / Standar Smartphone | Sidebar berubah menjadi mobile drawer menu. Tabel antrean pasien beradaptasi menjadi kartu vertikal terstruktur. Input formulir penuh 100% lebar layar. Tombol aksi primer mudah ditekan ibu jari. | ✅ PASSED |
| **768px** (Tablet Portrait) | iPad Mini / Tablet | Tata letak 2 kolom proporsional antara data ringkasan pasien dan area input tindakan medis. Spasi nyaman tanpa horizontal scrollbar yang tidak disengaja. | ✅ PASSED |
| **1024px** (Tablet Landscape / Laptop) | iPad Pro / MacBook Air | Sidebar navigasi terbuka secara default. Tampilan multi-tab workspace kunjungan terbuka penuh dengan header konteks pasien *sticky*. | ✅ PASSED |
| **1440px+** (Desktop Large) | Layar Monitor Kerja Klinik | Konten terpusat dengan batas maksimal kontainer (`max-w-7xl`) untuk menjaga panjang baris teks nyaman dibaca (*optimal line length*). | ✅ PASSED |

---

## 2. Kepatuhan Aksesibilitas (WCAG 2.1 AA)

1. **Navigasi Keyboard**:
   - Seluruh elemen interaktif (`<button>`, `<a>`, `<input>`, `<select>`) dapat dijelajahi secara berurutan menggunakan tombol `Tab`.
   - Ring fokus terlihat jelas (`focus:ring-2 focus:ring-sky-500 focus:ring-offset-2`).
2. **Kontras Warna**:
   - Rasio kontras teks utama terhadap background melebihi rasio minimal 4.5:1 baik pada *Light Mode* (`text-slate-900` pada `bg-slate-50`) maupun *Dark Mode* (`text-white` pada `bg-slate-900`).
3. **Pembedaan Status Non-Warna**:
   - Status (seperti *Alergi Aktif*, *Menunggu Dokter*, *Selesai*) selalu disertai teks penjelas eksplisit dan ikon semantik pendamping.
4. **Formulir & Label Aksesibel**:
   - Setiap elemen input memiliki `<label for="...">` terkait atau atribut `aria-label` yang sesuai untuk pembaca layar (*screen reader*).
5. **Dukungan Cetak (*Print Optimization*)**:
   - Format surat keterangan dokter, surat rujukan, dan resume pulang secara otomatis menerapkan latar belakang putih bersih (`@media print { body { background: white; color: black; } }`).
