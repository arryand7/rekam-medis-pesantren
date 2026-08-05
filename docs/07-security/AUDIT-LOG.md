---
id: DOC-AUDIT-LOG
title: "Audit Log"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Audit Log

## Event wajib

- Login/logout gagal dan sukses sesuai kebijakan.
- Melihat rekam medis sangat sensitif.
- Membuat/mengubah/finalisasi kunjungan.
- Addendum/void.
- Pemberian atau pembatalan obat.
- Perubahan stok.
- Membuat/mengubah rujukan.
- Download/print/export.
- Perubahan role dan permission.
- Sinkronisasi identitas.
- Akses break-glass.

## Field

Actor, action, target, time, IP, user-agent, correlation ID, before, after, reason, source channel, success/failure.

## Proteksi

- Append-only.
- Tidak dapat dihapus dari UI.
- Akses dibatasi.
- Before/after disanitasi agar secret tidak tersimpan.
- Retensi ditetapkan.
- Alert untuk pola mencurigakan.

## Break-glass

Akses darurat harus memiliki alasan, masa berlaku pendek, dan review.

## Event tambahan

- Gate sync preview/apply.
- Identity conflict resolution.
- Patient profile creation/merge.
- Consultation draft/finalize/send/download.
- Recipient change.
- External advice recorded/verified.
- Consultation superseded by referral.
