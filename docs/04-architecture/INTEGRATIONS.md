---
id: DOC-INTEGRATIONS
title: "Rencana Integrasi"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Rencana Integrasi

## Gate

Tujuan: SSO, identitas pengguna, dan akses aplikasi.

Data minimum:
- external user ID,
- username/NIS/NIP,
- nama,
- email,
- status aktif,
- tipe pengguna,
- hak akses aplikasi.

## SSS

Tujuan: sumber data santri, kelas, angkatan, kamar/asrama, dan wali.

## Absensi

Tujuan: mengirim status izin sakit atau pembatasan aktivitas. Tidak mengirim diagnosis lengkap.

## Notification channel

WhatsApp/email/internal notification pada fase yang disetujui.

## Prinsip kontrak

- Versioned API.
- Idempotency key.
- Signed/authenticated request.
- Timeout dan retry.
- Dead-letter/manual retry.
- Correlation ID.
- Data minimization.
- Audit.
- Rekonsiliasi berkala.

## [PERLU DIKONFIRMASI]

Endpoint, payload, source of truth, dan conflict resolution tiap integrasi.

## Gate user projection contract

Gate menyediakan ID stabil, detail pengguna, tipe, status, dan source timestamp/version. POSKESTREN Health menyimpan mapping serta reconciliation.

## Puskesmas/rumah sakit

Tahap awal dapat menggunakan secure document handoff melalui kanal resmi. Integrasi API hanya dibuat setelah ada kontrak, authentication, consent basis, dan data protection agreement yang jelas.

## Anti-pattern

- Sinkronisasi berdasarkan nama saja.
- Menimpa rekam medis karena perubahan role.
- Memberikan akses database langsung kepada mitra.
- Mengirim seluruh rekam medis melalui pesan pribadi.
