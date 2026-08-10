---
id: DOC-INTEGRATIONS
title: "Arsitektur Integrasi Eksternal POSKESTREN Health"
status: active
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Arsitektur Integrasi Eksternal POSKESTREN Health

## 1. Integrasi SABIRA Gate (IAM & Identity Provider)

- **Tujuan**: Single Sign-On (SSO) OIDC, sinkronisasi identitas autoritatif, dan penegakan hak akses aplikasi (*application entitlement*).
- **Protokol**: OAuth2 Authorization Code Flow dengan state/nonce CSRF/replay protection.
- **Driver Implementasi**:
  - `FakeGateOidcClient` & `FakeGateClientService` (Testing/Development)
  - `HttpGateOidcClient` & `HttpGateClient` (Staging/Production)
- **Data Autoritatif**: `gate_user_id`, nama lengkap, NIK, NIS/NIP, email, nomor HP, tipe pengguna, status sumber, checksum.
- **Larangan**: Nol mutasi data rekam medis, diagnosa, resep, atau catatan klinis dari payload Gate.

## 2. Integrasi SABIRA Absensi (Attendance System)

- **Tujuan**: Mengirim disposisi absensi santri terkait kesehatan (izin sakit, istirahat asrama, pembatasan KBM, kembali beraktivitas).
- **Driver Implementasi**:
  - `FakeAttendanceIntegration` (Testing)
  - `HttpAttendanceSandboxIntegration` (Staging / Sandbox)
  - `AttendanceIntegrationContract` (Kontrak Antarmuka)
- **Transport**: Pola Transactional Integration Outbox (`integration_outbox_events`) dengan locking MariaDB `lockForUpdate()`, idempotensi unik, dan retry backoff eksponensial.
- **Standar Privasi (*Minimum Necessary*)**:
  - *Allowed*: `event_id`, `gate_user_id`, `disposition_type`, `effective_from`, `effective_until`, `activity_scope`, `source_visit_reference`.
  - *Forbidden*: `diagnosis`, `icd10`, `complaint`, `vital_signs`, `medications`, `allergies`, `assessment`, `clinical_notes`. (Ditegakkan oleh runtime validation `assertPayloadCompliant`).

## 3. Mitra Layanan Kesehatan (Puskesmas / Rumah Sakit)

- **Tujuan**: Konsultasi klinis jarak jauh dan rujukan darurat/terjadwal.
- **Mekanisme**: Pencatatan terstruktur `ClinicalConsultation` dan `Referral` lokal, berkas ringkasan terenkripsi privat, dan penyerahan resmi dengan audit unduhan.

## 4. Prinsip Arsitektur Integrasi

1. **Transactional Outbox**: Event outbox ditulis dalam database transaction yang sama dengan mutasi bisnis.
2. **Idempotency**: Setiap event memiliki `idempotency_key` unik.
3. **Correlation Tracking**: Setiap request membawa `X-Poskestren-Event-Id` dan `correlation_id`.
4. **Dead-Letter Recovery**: Event yang gagal melebihi batas maksimal dipindahkan ke status `dead_letter` dengan opsi retry manual oleh petugas berizin.
5. **Observability**: Probe status kesehatan endpoint tersedia di `/integration/attendance/status` dan `/health`.
