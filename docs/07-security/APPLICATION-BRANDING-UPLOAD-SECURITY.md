---
id: SECURITY-APPLICATION-BRANDING-UPLOAD
title: "Application Branding Upload Security"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Application Branding Upload Security

## Batas kepercayaan

Branding adalah aset publik dan disimpan di disk `public` pada `storage/app/public/branding/`. Lokasi ini sengaja dipisahkan dari referral document, clinical report, dan medical upload yang memakai storage privat. Branding tidak boleh memuat data pasien, credential, kontak privat, atau metadata internal.

## Kontrol upload

- Akses form dan endpoint dilindungi `manage-system-settings` di menu, controller, dan Form Request.
- Format runtime terbatas pada PNG, JPEG, dan WebP. Logo maksimal 2 MB; favicon maksimal 1 MB.
- Validasi menggabungkan extension allowlist, MIME, Laravel image validation, dan pemeriksaan struktur gambar melalui `getimagesize`.
- SVG upload ditolak karena dapat membawa active/scriptable content. Hanya SVG default yang dibuat dan direview sebagai source project yang di-commit.
- Nama asli tidak dipercaya; penyimpanan memakai UUID dan extension hasil MIME yang diizinkan.
- Penggantian dilakukan dengan store baru, transaction referensi database, cache invalidation, lalu cleanup aset lama. File baru dibersihkan jika transaction gagal.
- Reset hanya menghapus path runtime yang direferensikan; aset default committed tidak pernah menjadi target deletion.

## Audit dan logging

Event `APPLICATION_IDENTITY_UPDATED`, `APPLICATION_LOGO_UPDATED`, `APPLICATION_FAVICON_UPDATED`, dan `APPLICATION_IDENTITY_RESET` menyimpan actor, field/path aman yang berubah, before/after text, dan waktu melalui audit log. Binary, base64, serta isi file tidak dicatat.
