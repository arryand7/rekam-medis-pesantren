# PROMPT ANTIGRAVITY — PHASE 3C2
## Operational Notifications, Integration Outbox, Absensi Contract, and Reporting Foundation

Anda adalah principal Laravel architect, distributed-systems integration engineer, privacy engineer, application security engineer, reporting architect, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash High** atau **Claude Sonnet 4.6 Thinking**.

Tujuan fase ini:

1. memverifikasi final closure Phase 3C1;
2. membangun event/outbox untuk kebutuhan operasional setelah discharge;
3. membangun notification domain internal yang minimum-necessary;
4. membangun kontrak integrasi ke aplikasi SABIRA Absensi tanpa langsung mengaktifkan production transport;
5. membangun idempotency, retry, dead-letter/reconciliation, dan delivery audit;
6. membangun dashboard operasional dan laporan klinis/manajemen dasar;
7. memastikan tidak ada diagnosis/narasi medis sensitif yang bocor ke Absensi atau recipient operasional;
8. berhenti sebelum production activation connector, WhatsApp/email production, billing, claim, atau analytics AI.

Jangan membuat keputusan klinis otomatis.

---

# 1. DOKUMEN WAJIB DIBACA

Baca terlebih dahulu:

- `AGENTS.md`
- `README.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/01-domain/OPERATIONAL-CONTEXT.md`
- `docs/01-domain/BUSINESS-RULES.md`
- `docs/01-domain/PERSON-PATIENT-IDENTITY.md`
- `docs/01-domain/PATIENT-JOURNEY.md`
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`
- `docs/02-workflows/DISCHARGE-AND-RETURN.md`
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/NON-FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/SYSTEM-ARCHITECTURE.md`
- `docs/04-architecture/MODULE-BOUNDARIES.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/05-data/DOMAIN-MODEL.md`
- `docs/05-data/ENTITY-RELATIONSHIPS.md`
- `docs/05-data/DATA-DICTIONARY.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/08-api/API-CONVENTIONS.md`
- `docs/08-api/INTEGRATION-CONTRACTS.md`
- `docs/09-testing/TEST-STRATEGY.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/10-delivery/PHASE-3C1-CLOSURE.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika path aktual berbeda, gunakan path aktual. Jangan membuat duplikat hanya karena typo.

Tandai requirement yang belum final sebagai `[PERLU DIKONFIRMASI]`.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan memakai data pasien nyata.
3. Jangan menjalankan production deployment.
4. Jangan mengaktifkan konektor production ke Absensi tanpa explicit approval.
5. Jangan mengirim WhatsApp/email production.
6. Jangan mengirim diagnosis, assessment narrative, medication detail, allergy detail, consultation advice, atau audit log ke Absensi.
7. Gunakan minimum-necessary operational payload.
8. Jangan menganggap semua patient adalah santri.
9. Integrasi Absensi hanya dibuat untuk tipe person yang benar-benar didukung kontrak Absensi.
10. Gunakan stable identity mapping dari Gate (`gate_user_id` atau identifier resmi yang disepakati).
11. Jangan match person berdasarkan nama.
12. Jangan menggunakan client-supplied actor, official timestamp, delivery status, atau recipient authority.
13. Semua outbound integration wajib idempotent, retry-safe, auditable, dan reversible secara domain bila dibutuhkan.
14. Jangan menghapus event/outbox yang gagal; gunakan status/retry/dead-letter.
15. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 3C1 FINAL CLOSURE

Lakukan read-only:

```bash
pwd
git branch --show-current
git status
git log --oneline -10
php artisan migrate:status
php artisan route:list
```

Verifikasi:

- commit `260dd6f` atau commit final Phase 3C1 ada;
- working tree clean;
- 111/111 test atau baseline terbaru lulus;
- discharge final immutable;
- visit discharged secara atomik;
- operational handoff minimum-necessary;
- tidak ada external send di Phase 3C1;
- discharge document private;
- Graphify updated.

Buat:

`docs/10-delivery/PHASE-3C1-FINAL-CLOSURE.md`

Status:
- `PASSED`
- `FAILED`

Jika ada Critical regression, berhenti.

---

# 4. TAHAP B — INTEGRATION OUTBOX FOUNDATION

Implementasikan transactional outbox.

Buat `integration_outbox_events`:

- ULID;
- `event_type`;
- `aggregate_type`;
- `aggregate_id`;
- `destination`;
- `payload_snapshot` JSON;
- `payload_version`;
- `idempotency_key`;
- `status`;
- `available_at`;
- `attempt_count`;
- `last_attempt_at` nullable;
- `sent_at` nullable;
- `acknowledged_at` nullable;
- `failed_at` nullable;
- `last_error_code` nullable;
- `last_error_message_sanitized` nullable;
- `correlation_id`;
- `created_by_id` nullable;
- timestamps.

Status:
- `pending`
- `processing`
- `sent`
- `acknowledged`
- `failed`
- `dead_letter`
- `cancelled`

Aturan:

1. outbox dibuat dalam transaction yang sama dengan business event yang relevan;
2. idempotency key unique pada destination + event semantic;
3. worker tidak menghapus row setelah sukses;
4. retry menggunakan exponential/backoff policy configurable;
5. failure log tidak menyimpan secret;
6. dead-letter dapat direview/retry manual dengan permission;
7. no duplicate side effect;
8. server-authoritative timestamps;
9. no direct status manipulation dari request publik.

---

# 5. TAHAP C — OPERATIONAL NOTIFICATION DOMAIN

Bangun domain notification internal terpisah dari transport.

Buat `operational_notifications`:

- ULID;
- patient/person reference;
- visit/discharge reference;
- notification_type;
- recipient_type;
- recipient_reference nullable;
- `payload_snapshot` JSON minimum;
- priority;
- status;
- prepared_by_id;
- prepared_at;
- ready_at nullable;
- delivered_at nullable;
- acknowledged_at nullable;
- acknowledged_by_id nullable;
- cancelled_at nullable;
- cancellation_reason nullable;
- correlation_id;
- lock_version;
- timestamps.

Notification types:
- `health_visit_closed`
- `rest_restriction`
- `limited_activity`
- `follow_up_required`
- `return_to_activity`
- `external_follow_up`
- `operational_attention_required`
- `other`

Recipient types:
- `dorm_supervisor`
- `homeroom_teacher`
- `guardian`
- `patient`
- `staff_supervisor`
- `attendance_system`
- `other`

Tidak ada diagnosis detail.

---

# 6. PRIVACY PAYLOAD PROFILES

Buat explicit payload profiles.

## Dorm supervisor

Boleh:
- identity minimum;
- rest/activity status;
- effective period;
- practical restriction;
- next follow-up;
- emergency escalation instruction bila approved.

Tidak boleh default:
- diagnosis;
- assessment;
- detailed medication;
- external consultation advice;
- full referral narrative.

## Homeroom teacher

Boleh:
- identity;
- fit/unfit/limited school activity;
- date range;
- attendance/accommodation instruction;
- follow-up date if operationally needed.

Tidak boleh:
- diagnosis;
- medicine;
- detailed symptoms;
- clinical notes.

## Guardian

Scope ditandai `[PERLU DIKONFIRMASI]` sesuai kebijakan sekolah dan kewenangan.

## Absensi system

Payload minimum:
- stable person identifier;
- applicable user/student type;
- effective start/end;
- attendance disposition;
- operational reason category;
- source reference;
- version;
- correlation/idempotency key.

Jangan kirim diagnosis.

Buat automated privacy tests yang memastikan forbidden keys tidak ada dalam payload.

---

# 7. TAHAP D — ABSENSI INTEGRATION CONTRACT

Bangun contract/interface, belum production connector.

Interface contoh:

`AttendanceIntegrationContract`

Methods sesuai domain:
- publish health-related attendance disposition;
- revoke/supersede disposition;
- health/status probe;
- query delivery status bila contract mendukung.

Implementasi:
- `FakeAttendanceIntegration`
- optional `LocalAttendanceSandboxIntegration`

Jangan membuat production HTTP call tanpa endpoint/credential/contract resmi.

---

# 8. ABSENSI PAYLOAD CONTRACT

Buat DTO versioned, misalnya:

`AttendanceHealthDispositionDTO`

Field minimum:

- `event_id`;
- `event_version`;
- `gate_user_id` atau stable external person ID;
- `local_patient_reference` opaque bila diperlukan;
- `disposition_type`;
- `effective_from`;
- `effective_until` nullable;
- `activity_scope`;
- `source_system = poskestren_health`;
- `source_visit_reference` opaque;
- `issued_at`;
- `supersedes_event_id` nullable;
- `correlation_id`.

Disposition types contoh:
- `excused_health`
- `limited_activity`
- `rest`
- `return_to_activity`
- `follow_up_external`

Jangan mengarang mapping final Absensi. Dokumentasikan `[PERLU DIKONFIRMASI]`.

---

# 9. IDENTITY RESOLUTION

Gunakan Gate source-of-truth:

1. `Person.gate_user_id`;
2. approved external mapping;
3. jangan gunakan nama;
4. jangan menggunakan email/NIS/NIP sebagai primary integration key kecuali contract resmi menyatakan demikian;
5. unsupported/missing mapping -> integration conflict, bukan silent drop.

Buat `integration_identity_conflicts` bila diperlukan:

- person;
- destination;
- conflict type;
- source identifier snapshot;
- status;
- resolution;
- resolved_by;
- resolved_at;
- audit.

---

# 10. TAHAP E — DELIVERY ENGINE

Buat service/worker untuk outbox.

Behavior:

1. fetch pending event dengan locking;
2. mark processing;
3. resolve adapter;
4. call fake/sandbox transport;
5. store delivery attempt;
6. mark sent/acknowledged;
7. retry transient failure;
8. dead-letter permanent/exhausted failures;
9. audit.

Buat `integration_delivery_attempts`:

- outbox event;
- attempt number;
- destination;
- started_at;
- finished_at;
- result;
- external_reference nullable;
- HTTP/status code nullable;
- sanitized error;
- latency_ms;
- correlation_id.

No secret/raw sensitive payload in logs.

---

# 11. SUPERSEDE / REVOCATION

Ketika discharge/activity restriction berubah melalui amendment:

- jangan edit event lama;
- buat event baru;
- link `supersedes_event_id`;
- downstream disposition harus dapat direvoke/supersede;
- old event remains auditable;
- no duplicate active disposition.

Implementasikan fake downstream behavior dan tests.

---

# 12. TAHAP F — INTERNAL NOTIFICATION INBOX

Buat internal inbox untuk user aplikasi:

`user_notifications` atau equivalent:

- recipient_user;
- type;
- title;
- body/minimum payload;
- source;
- read_at;
- acknowledged_at nullable;
- created_at.

Gunakan untuk:
- petugas follow-up;
- operational handoff pending;
- dead-letter integration;
- overdue follow-up;
- referral/consultation operational alerts.

Tidak ada push external production pada phase ini.

---

# 13. TAHAP G — DASHBOARD OPERASIONAL

Bangun dashboard role-aware.

## Clinical/health team

- visits today;
- waiting assessment;
- under observation;
- referral external;
- follow-up due;
- discharge today;
- pending medication administration;
- integration failures.

## Management

Hanya agregat:
- number of visits;
- observation count;
- referral count;
- common complaint categories jika data/terminology safe;
- average visit duration;
- follow-up completion;
- medicine stock alert summary.

Jangan expose patient-level clinical data pada management dashboard kecuali permission.

## Dorm/homeroom operational view

Hanya minimum necessary:
- who needs rest/limited activity;
- effective period;
- follow-up;
- acknowledgement status.

No diagnosis.

---

# 14. TAHAP H — REPORTING FOUNDATION

Buat report/query services, bukan BI besar.

Reports:
- visit census;
- patient visit history;
- observation census;
- referral census;
- discharge/follow-up;
- medication administration summary;
- pharmacy stock/expiry;
- operational restrictions;
- integration delivery status.

Requirements:
- date filters;
- user type/program filter bila tersedia;
- server-side pagination;
- permission-scoped columns;
- export audit;
- no N+1;
- timezone Asia/Jakarta.

PDF/Excel export boleh hanya jika existing libraries aman; jika belum, foundation/query dahulu.

---

# 15. AUTHORIZATION

Permissions minimum:

- `view-operational-notifications`
- `prepare-operational-notifications`
- `acknowledge-operational-notifications`
- `view-integration-outbox`
- `retry-integration-events`
- `resolve-integration-conflicts`
- `view-attendance-integration-status`
- `manage-attendance-integration-settings`
- `view-clinical-dashboard`
- `view-management-dashboard`
- `view-operational-dashboard`
- `view-health-reports`
- `export-health-reports`

Admin teknis tidak otomatis dapat melihat medical report detail.

Integration config permission tidak otomatis memberi clinical data access.

---

# 16. UI

Pertahankan warna dasar biru muda, light/dark/system.

Buat:
- Notification center;
- Integration outbox monitor;
- Failed/dead-letter review;
- Identity conflict screen;
- Attendance integration status;
- Role-specific dashboards;
- Report center.

Tidak ada button production connector activation kecuali feature flag tetap OFF.

---

# 17. CONFIG / FEATURE FLAGS

Buat config:

```text
ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
```

Jangan mencetak secret.

Rules:
- default fake/off;
- production HTTP adapter belum dibuat atau disabled;
- enabling requires explicit future phase;
- config validation;
- health status UI jelas menunjukkan fake/sandbox/off.

---

# 18. AUDIT EVENTS

Minimal:
- `OperationalNotificationPrepared`
- `OperationalNotificationAcknowledged`
- `IntegrationOutboxEventCreated`
- `IntegrationDeliveryAttempted`
- `IntegrationDeliverySucceeded`
- `IntegrationDeliveryFailed`
- `IntegrationEventDeadLettered`
- `IntegrationEventRetried`
- `IntegrationIdentityConflictCreated`
- `IntegrationIdentityConflictResolved`
- `AttendanceDispositionPrepared`
- `AttendanceDispositionSuperseded`
- `ReportViewed`
- `ReportExported`

Append-only, server time, correlation ID, minimum payload.

---

# 19. TEST WAJIB

## Phase 3C1 regression
- discharge remains immutable;
- operational handoff privacy;
- visit closure atomic;
- follow-up behavior.

## Outbox
- created transactionally;
- unique idempotency;
- retry safe;
- dead-letter;
- no delete on success;
- worker concurrency with MariaDB;
- failed business transaction creates no outbox.

## Privacy
- forbidden clinical keys absent from dorm payload;
- forbidden clinical keys absent from homeroom payload;
- forbidden clinical keys absent from Absensi payload;
- management dashboard no patient-level detail without permission.

## Absensi contract
- fake adapter receives correct DTO;
- missing Gate ID -> conflict;
- duplicate event one effect;
- supersede old disposition;
- disabled feature performs no external call;
- no production URL required.

## Delivery
- transient retry;
- permanent failure dead-letter;
- attempt logs sanitized;
- no secret;
- correlation preserved.

## Authorization
- direct URL 403;
- admin technical cannot read clinical reports automatically;
- integration manager cannot read medical detail automatically.

## Reporting
- permission-scoped fields;
- pagination;
- date/time timezone;
- no N+1 regression where measurable.

Run:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
git diff --check
```

Concurrency uses MariaDB test database.

---

# 20. GRAPHIFY

Update graph without `--code-only`.

Query:
- Discharge -> OperationalNotification;
- Discharge -> Outbox -> AttendanceAdapter;
- Gate identity -> attendance DTO;
- privacy payload path;
- forbidden diagnosis leakage;
- outbox retry/dead-letter;
- idempotency;
- integration settings -> clinical access leakage;
- management dashboard patient data leakage;
- hard delete;
- missing tests.

Perbarui traceability and mapping docs.

---

# 21. DOKUMENTASI

Buat:
- `docs/10-delivery/PHASE-3C2-CLOSURE.md`
- `docs/08-api/ATTENDANCE-INTEGRATION-CONTRACT.md`
- `docs/07-security/OPERATIONAL-DATA-SHARING.md`

Perbarui:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `docs/01-domain/BUSINESS-RULES.md`
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/NON-FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/05-data/ENTITY-RELATIONSHIPS.md`
- `docs/05-data/DATA-DICTIONARY.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/08-api/INTEGRATION-CONTRACTS.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Buat ADR untuk:
- transactional outbox;
- attendance integration contract;
- privacy payload profiles;
- integration idempotency/superseding;
- reporting data access.

---

# 22. GIT

Sebelum mulai:

```bash
git status
git log --oneline -5
```

Jika clean:

```bash
git tag -a phase-3c1-complete -m "Phase 3C1 discharge follow-up and operational handoff complete"
```

Setelah semua lulus:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "feat(integration): complete Phase 3C2 operational outbox and reporting foundation"
git status
```

Target working tree clean.

---

# 23. OUTPUT AKHIR

Berikan:
1. Phase 3C1 closure status.
2. Outbox architecture.
3. Operational notification model.
4. Privacy payload profiles.
5. Attendance integration contract.
6. Identity resolution strategy.
7. Retry/dead-letter/idempotency behavior.
8. Internal notification inbox.
9. Dashboards.
10. Reports.
11. Permissions/Policies.
12. Routes/UI.
13. Tests/assertions/skips.
14. MariaDB concurrency results.
15. Graphify findings.
16. Remaining risks.
17. Feature flag status.
18. Commit.
19. Working tree.
20. GO/NO-GO for production attendance connector phase.

---

# 24. CHECKPOINT WAJIB

Jangan aktifkan production connector jika:
- diagnosis/clinical narrative bocor ke operational payload;
- Gate identity mapping belum stabil;
- idempotency/retry/dead-letter gagal;
- outbox tidak transaction-safe;
- unauthorized dashboard/report access;
- feature flag tidak default OFF;
- critical tests gagal.

Jika lulus:
- commit;
- working tree clean;
- berhenti;
- connector Absensi production tetap OFF/fake;
- jangan membuat WA/email production;
- jangan membuat billing/claim;
- tunggu persetujuan eksplisit pengguna.
