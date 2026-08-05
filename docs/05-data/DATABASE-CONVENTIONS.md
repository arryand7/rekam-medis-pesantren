---
id: DOC-DB-CONVENTIONS
title: "Konvensi Database"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Konvensi Database

- Nama tabel plural snake_case.
- Foreign key `<entity>_id`.
- Primary key ULID untuk entitas bisnis utama.
- Timestamps UTC di database; tampilkan `Asia/Jakarta`.
- Decimal untuk dosis, berat, tinggi, dan nilai presisi.
- Enum PHP dengan kolom string.
- Index pada foreign key, status, waktu, identifier, dan pencarian.
- Unique constraint untuk invariant, bukan hanya validasi aplikasi.
- Soft delete hanya jika alasan domain jelas; bukan pengganti versioning.
- JSON hanya untuk metadata fleksibel, bukan field inti.
- Migration append-only setelah diterapkan.
- Gunakan transaction dan row lock untuk stok serta kunjungan aktif.
- Hindari cascade delete pada rekam medis.
- File disimpan di storage private; database menyimpan metadata dan checksum.

## Identity constraints

- `persons.gate_user_id` unique.
- `patients.person_id` unique.
- Tidak menggunakan role sebagai patient discriminator.
- Sinkronisasi menyimpan source timestamp/checksum.
- Legacy match candidates disimpan untuk review, bukan auto-merge.

## Consultation constraints

- Version number unique per consultation.
- Final version immutable.
- Advice menyimpan attribution fields.
- Attachment memiliki checksum dan private disk path.
