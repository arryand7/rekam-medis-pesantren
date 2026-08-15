---
id: DOC-LOGGING-PRIVACY-GUIDELINES
title: "Logging Privacy Guidelines"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Logging Privacy Guidelines

## Boleh dicatat

Event code, internal record ID/ULID, timestamp server, actor ID, HTTP status class, integration attempt/status, exception class dan correlation ID.

## Dilarang di log aplikasi biasa

Password, auth code, access/refresh/ID token, client/API secret, state/nonce mentah, cookie/session ID, database credential, response body upstream, SOAP/diagnosis, alergi, medication detail, dokumen medis, NIK dan kontak lengkap.

## Aturan implementasi

- Gunakan `AuditLogService` untuk perubahan domain; sanitizer wajib rekursif.
- Pesan exception eksternal diganti kategori generik; detail diagnosis hanya pada kanal incident terbatas setelah disensor.
- Jangan memasukkan request/response penuh ke context logger.
- Tetapkan rotation, least-privilege access, retention dan alerting. Log audit append-only tidak boleh digunakan sebagai tempat payload rahasia.
- Sebelum membagikan bukti, cari token, credential, absolute local path dan identifier pasien.
