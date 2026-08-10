---
id: DOC-API-ATTENDANCE-INTEGRATION-CONTRACT
title: "Kontrak Integrasi Disposisi Kesehatan SABIRA Absensi"
status: active
last_updated: 2026-08-10
owner: "Ryand Arifriantoni"
---


# Kontrak Integrasi Disposisi Kehadiran (Attendance Health Disposition Contract)

## 1. Ikhtisar Arsitektur

Integrasi antara POSKESTREN dan SABIRA Absensi beroperasi secara asinkron searah menggunakan pola **Transactional Outbox**. POSKESTREN mempublikasikan event disposisi kesehatan saat status kunjungan difinalisasi atau diamandemen, dan sistem absensi mengonsumsinya untuk menyesuaikan status presensi santri atau staf.

```
[ POSKESTREN Domain ]
         │ (DB Transaction)
         ▼
[ integration_outbox_events ]
         │ (Asynchronous Worker / Outbox Dispatcher)
         ▼
[ AttendanceIntegrationContract ] ──> [ Fake / Real Attendance HTTP API ]
```

## 2. Profil Privasi & Larangan Data Sensitif

Penerima data absensi **DILARANG KERAS** menerima data diagnosis medis atau rincian klinis. Payload disposisi hanya mencakup parameter kehadiran minimum:

### A. Field yang Diperbolehkan
- `event_id`: Identifikasi unik event integrasi (ULID).
- `event_version`: Versi skema (integer).
- `gate_user_id`: Identifikasi terpusat person dari Gate (Authoritative).
- `disposition_type`: `rest`, `limited_activity`, `return_to_activity`, `excused_health`.
- `effective_from`: Waktu mulai berlaku (ISO-8601 UTC/WIB).
- `effective_until`: Waktu akhir berlaku (ISO-8601 UTC/WIB, nullable).
- `activity_scope`: `all_activities`, `sports_only`, `academic_only`, `boarding_only`.
- `source_visit_reference`: Nomor referensi kunjungan (bukan catatan medis).
- `supersedes_event_id`: ID event sebelumnya yang digantikan (nullable).

### B. Kunci Klinis Terlarang (Runtime Blocked)
`diagnosis`, `working_diagnosis`, `icd10`, `clinical_summary`, `assessment_summary`, `examination_findings`, `history_current_illness`, `medications`, `allergies`, `vital_signs`, `internal_notes`.

## 3. Spesifikasi DTO (`AttendanceHealthDispositionDTO`)

```json
{
  "event_id": "01KZM91VABC1234567890DEFGH",
  "event_version": 1,
  "gate_user_id": "GATE-USR-8821",
  "disposition_type": "rest",
  "effective_from": "2026-08-09T08:00:00+07:00",
  "effective_until": "2026-08-10T08:00:00+07:00",
  "activity_scope": "all_activities",
  "source_visit_reference": "VIS-20260809-001",
  "issued_at": "2026-08-09T08:00:00+07:00",
  "supersedes_event_id": null,
  "correlation_id": "01KZM91VXYZ123456789012345",
  "metadata": {
    "operational_reason_category": "health_care",
    "restriction_category": "bed_rest"
  }
}
```

## 4. Konfigurasi Lingkungan (`config/integration.php`)

```env
ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
ATTENDANCE_INTEGRATION_ENDPOINT=null
ATTENDANCE_INTEGRATION_TIMEOUT=5
ATTENDANCE_INTEGRATION_MAX_RETRIES=5
ATTENDANCE_INTEGRATION_RETRY_BACKOFF=60
```
