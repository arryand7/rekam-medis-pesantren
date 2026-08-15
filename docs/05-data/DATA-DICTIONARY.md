---
id: DOC-DATA-DICTIONARY
title: "Data Dictionary"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Data Dictionary

## ENT-STUDENT — `students`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | Primary key |
| external_id | string | ID sumber |
| nis | string | Unique bila tersedia |
| name | string | Nama resmi |
| gender | string/null | Dari sumber resmi |
| class_name | string/null | Snapshot/sync |
| dorm_name | string/null | Snapshot/sync |
| active | boolean | Status sumber |
| synced_at | timestamp/null | Waktu sinkron |

## ENT-HEALTH-PROFILE — `student_health_profiles`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | PK |
| student_id | ULID | Unique FK |
| blood_type | string/null | Terkontrol |
| emergency_notes | text/null | Sangat sensitif |
| updated_by | ULID | Actor |

## ENT-MEDICAL-VISIT — `medical_visits`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | PK |
| visit_number | string | Unique, server-generated |
| student_id | ULID | FK |
| status | string | Enum |
| arrived_at | timestamp | Server time |
| chief_complaint | text | Wajib |
| source_type | string/null | Pelapor/pengantar |
| source_name | string/null | Sesuai kebutuhan |
| assigned_to | ULID/null | Petugas |
| created_by | ULID | Actor |
| finalized_at | timestamp/null | Saat final |
| lock_version | integer | Optimistic locking |

## ENT-VITAL — `vital_signs`

Field umum: `visit_id`, `recorded_at`, `temperature_c`, `systolic`, `diastolic`, `pulse_bpm`, `respiratory_rate`, `spo2_percent`, `weight_kg`, `height_cm`, `notes`, `recorded_by`.

Gunakan decimal yang sesuai. Jangan menyimpan nilai vital sebagai string gabungan.

## ENT-ASSESSMENT — `assessments`

Field: `visit_id`, `history`, `findings`, `assessment_text`, `diagnosis_code/null`, `disposition`, `status`, `authored_by`, `finalized_at`, `version`.

## ENT-OBSERVATION — `observation_episodes`

Field: `visit_id`, `reason`, `started_at`, `ended_at`, `location`, `responsible_user_id`, `outcome`, `status`.

## ENT-MEDICATION-ADMIN — `medication_administrations`

Field: `visit_id`, `medicine_id`, `medicine_batch_id/null`, `dose_value`, `dose_unit`, `route`, `scheduled_at/null`, `administered_at/null`, `status`, `administered_by/null`, `notes`.

## ENT-REFERRAL — `referrals`

Field: `visit_id`, `urgency`, `reason`, `facility_name`, `departed_at/null`, `returned_at/null`, `companion`, `transport`, `status`, `created_by`.

## ENT-AUDIT — `audit_logs`

Field: `actor_id`, `action`, `subject_type`, `subject_id`, `before_json`, `after_json`, `reason`, `ip_address`, `user_agent`, `correlation_id`, `created_at`.

## ENT-APPLICATION-IDENTITY — `application_identities`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | Primary key |
| singleton | boolean | Unique guard untuk satu identity aktif |
| application_name | string(120) | Nama aplikasi publik |
| application_short_name | string(50) | Nama ringkas untuk area sempit |
| institution_name | string(160) | Nama institusi tampilan |
| tagline | string(160) | Tagline publik |
| description | text/null | Deskripsi ringkas, bukan data klinis |
| footer_text | string(255)/null | Identitas footer |
| logo_path | string/null | Path raster publik relatif terhadap public disk |
| logo_dark_path | string/null | Path raster dark opsional |
| favicon_path | string/null | Path raster favicon publik |
| created_at, updated_at | timestamp | Server time |

Binary tidak disimpan di database. Tanpa row, aplikasi memakai source defaults dari `config/branding.php`.

## ENT-SSO-CONFIGURATION — `sso_configurations`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | Primary key |
| singleton | boolean | Unique guard untuk satu konfigurasi aktif |
| sso_enabled | boolean | Aktivasi login/callback, default false |
| driver | string(20) | `fake` atau `http` |
| base_url | string(500) | URL dasar provider Gate |
| client_id | string(255) | OAuth client identifier |
| client_secret | encrypted text/null | Ciphertext Laravel; hidden dari serialisasi/UI/audit |
| redirect_uri | string(500) | Callback canonical `/auth/gate/callback` |
| scopes | string(500) | Space-delimited OIDC scopes; wajib memuat `openid` |
| app_code | string(120) | Kode entitlement aplikasi |
| http_timeout, retry_attempts, retry_backoff_ms | integer | Batas keandalan koneksi |
| entitlement_ttl_seconds | integer | TTL revalidasi entitlement |
| created_at, updated_at | timestamp | Waktu server |

Tanpa row, aplikasi memakai fallback source-controlled yang nonaktif, fake, dan tidak memiliki secret. Secret memerlukan `APP_KEY` aplikasi untuk enkripsi/dekripsi, tetapi tidak memerlukan variabel `.env` khusus Gate.

## Catatan

Tabel alergi, kondisi, diagnosis, tindakan, file, notifikasi, dan role-permission dirinci saat desain migration per fase.

## ENT-PERSON — `persons`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | Internal stable ID |
| gate_user_id | string | Unique source ID |
| primary_identifier | string/null | NIS/NIP/identifier |
| name | string | Authoritative |
| email | string/null | Authoritative bila disepakati |
| phone | string/null | Authoritative bila disepakati |
| user_type | string | Dari Gate |
| source_status | string | active/inactive/etc |
| source_updated_at | timestamp/null | Dari Gate |
| source_checksum | string/null | Deteksi perubahan |
| synced_at | timestamp | Sinkronisasi terakhir |

## ENT-USER — `users`

Mewakili login. Field: `person_id`, `gate_user_id`, `username`, `auth_status`, `last_login_at`, `disabled_at`.

## ENT-PATIENT — `patients`

| Field | Tipe | Catatan |
|---|---|---|
| id | ULID | Patient ID internal |
| person_id | ULID | Unique FK |
| patient_number | string | Unique |
| eligibility_status | string | eligible/ineligible/review |
| created_reason | string | sync/first_visit/manual_review |
| first_seen_at | timestamp/null | Pelayanan pertama |

## ENT-CLINICAL-CONSULTATION — `clinical_consultations`

Field: `visit_id`, `status`, `purpose`, `question`, `recipient_facility_id`, `recipient_name`, `channel`, `urgency`, `created_by`, `finalized_at`, `sent_at`, `completed_at`.

## ENT-CONSULTATION-VERSION — `consultation_versions`

Field: `consultation_id`, `version`, `summary_json`, `document_path`, `checksum`, `authored_by`, `finalized_at`, `supersedes_id`.

## ENT-EXTERNAL-ADVICE — `external_clinical_advices`

Field: `consultation_id`, `facility_name`, `clinician_name`, `clinician_role`, `clinician_identifier`, `responded_at`, `channel`, `advice`, `limitations`, `verification_status`, `recorded_by`.
