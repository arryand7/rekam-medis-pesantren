# PROMPT ANTIGRAVITY — PHASE 4C2
## Controlled Production Cutover, Canary Activation, Post-Go-Live Validation, and Rollback Guard

Anda adalah principal Laravel production architect, SRE/DevOps engineer, IAM/OIDC security engineer, database reliability engineer, incident-response engineer, dan production change manager untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Claude Opus 4.6 Thinking** atau **Claude Sonnet 4.6 Thinking**.

Status awal:

- Phase 4C: `PRODUCTION-READY-NOT-CUTOVER`
- Candidate SHA: `ee7d4aa`
- Phase 4B baseline SHA: `dd5798f`
- Full tests: 174 passed, 664 assertions, 0 failed, 0 skipped
- Production flags currently OFF
- Backup/rollback/deployment runbooks available
- Working tree clean

TUJUAN FASE INI:
Melakukan **controlled production cutover** secara bertahap, dengan backup tervalidasi, atomic release, feature activation satu-per-satu, canary validation, dan rollback guard.

---

# 0. MANDATORY AUTHORIZATION GUARD

JANGAN MELAKUKAN WRITE ATAU CUTOVER PRODUCTION sampai pengguna secara eksplisit memberikan frasa:

`SETUJUI CUTOVER PRODUCTION POSKESTREN`

Jika frasa tersebut TIDAK terdapat pada instruksi pengguna yang sedang aktif:

- lakukan read-only preflight bila memungkinkan;
- siapkan execution plan;
- status akhir harus `AWAITING-PRODUCTION-AUTHORIZATION`;
- JANGAN deploy;
- JANGAN migrate;
- JANGAN mengubah `.env`;
- JANGAN restart service production;
- JANGAN mengaktifkan feature flag.

Jangan menganggap laporan Phase 4C sebelumnya sebagai otorisasi.

---

# 1. READ REQUIRED RUNBOOKS

Baca:

- `AGENTS.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/PHASE-4C-PRODUCTION-PREFLIGHT.md`
- `docs/10-delivery/PHASE-4C-BACKUP-AND-ROLLBACK.md`
- `docs/10-delivery/PHASE-4C-DEPLOYMENT-RUNBOOK.md`
- `docs/10-delivery/PHASE-4C-PRODUCTION-UAT.md`
- `docs/10-delivery/PHASE-4C-CLOSURE.md`
- `docs/10-delivery/PRODUCTION-GO-LIVE-CHECKLIST.md`
- `docs/10-delivery/INCIDENT-ROLLBACK-RUNBOOK.md`
- `docs/08-api/GATE-OIDC-CONTRACT.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/08-api/ATTENDANCE-INTEGRATION-CONTRACT.md`
- `docs/07-security/GATE-SSO-SECURITY.md`
- `docs/07-security/OPERATIONAL-DATA-SHARING.md`
- `plans/KNOWN-ISSUES.md`

Jika path berbeda, gunakan path aktual.

---

# 2. ABSOLUTE SAFETY RULES

1. Jangan tampilkan credential, secret, token, private key, `.env`, atau database password.
2. Jangan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, atau destructive reset.
3. Jangan force push.
4. Jangan hard delete Person/User/Patient/history.
5. Jangan mengaktifkan semua integration flag sekaligus.
6. Jangan mengaktifkan Attendance sebelum Gate SSO dan Gate sync stabil.
7. Jangan menggunakan pasien nyata untuk smoke test bila synthetic/approved account tersedia.
8. Jangan mengubah entitlement user nyata secara destruktif hanya untuk testing.
9. Jangan mengirim diagnosis/ICD/keluhan/vitals/medication/allergy/assessment ke Absensi.
10. Jangan melakukan rollback database dengan `migrate:rollback` secara buta.
11. Jangan deploy candidate SHA yang berbeda dari expected tanpa STOP dan review.
12. Jangan lanjut jika backup belum tervalidasi.
13. Jangan lanjut jika runtime SHA tidak dapat diidentifikasi.
14. Jangan lanjut jika health/readiness sebelum cutover tidak hijau.

---

# 3. FINAL PRE-CUTOVER READ-ONLY CHECK

Jalankan read-only:

```bash
pwd
git status
git log --oneline -8
git rev-parse HEAD
php artisan about
php artisan migrate:status
php artisan route:list
php artisan schedule:list
```

Pada server production, identifikasi tanpa mengekspos secret:

- current runtime SHA;
- current release path;
- expected candidate SHA;
- current symlink target;
- database identity;
- pending migration count;
- queue worker status;
- scheduler status;
- PHP-FPM status;
- web server status;
- disk free;
- private storage writable;
- `/health`;
- `/health/ready`.

Expected candidate:

```text
ee7d4aa
```

Jika candidate SHA berbeda:
- STOP;
- status `NO-GO-SHA-MISMATCH`.

---

# 4. PRE-CUTOVER BACKUP — FRESH SNAPSHOT REQUIRED

Walaupun Phase 4C sudah memiliki backup protocol, ambil backup FRESH tepat sebelum cutover.

Backup:

1. MariaDB logical dump;
2. current release/source reference;
3. `.env` secure backup;
4. Nginx config;
5. PHP-FPM config;
6. Supervisor/queue config;
7. cron/scheduler config;
8. private storage backup/manifest.

Verify:

- exists;
- non-zero;
- checksum SHA-256;
- permissions restricted;
- DB dump readable/listable;
- restore command available.

Record:

```text
PRE_CUTOVER_BACKUP_TIMESTAMP
PRE_CUTOVER_DB_BACKUP_REF
PRE_CUTOVER_PRIVATE_STORAGE_BACKUP_REF
PRE_CUTOVER_CONFIG_BACKUP_REF
PRE_CUTOVER_RUNTIME_SHA
```

Do NOT print secret path content.

If backup validation fails:
- STOP;
- `NO-GO-BACKUP`.

---

# 5. MAINTENANCE / ZERO-DOWNTIME DECISION

Determine from pending migrations:

- zero-downtime safe;
- short maintenance required.

If no destructive/long-lock migration:
- prefer atomic release without maintenance or minimal maintenance.

If migration requires maintenance:
- enable maintenance page;
- allow health/admin bypass only if safe;
- record start time.

Do not invent zero downtime if schema operation can block.

---

# 6. BUILD / INSTALL CANDIDATE RELEASE

Create immutable release:

```text
/var/www/poskestren/releases/<release-id>
```

Expected source SHA:

```text
ee7d4aa
```

Use secure shared resources:

- shared `.env`;
- shared `storage`;
- private storage;
- required runtime directories.

Build/install:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

Then application caches as compatible:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Verify:

```bash
php artisan about
php artisan route:list
```

Do not switch `current` yet.

---

# 7. CANDIDATE PRE-SWITCH HEALTH

Before symlink switch:

- bootstrap Laravel;
- validate config;
- validate private storage;
- validate DB connectivity using safe command;
- verify expected migrations;
- verify no production integration flags accidentally ON in candidate.

Expected initial flags:

```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
GATE_WEBHOOK_ENABLED=false
ATTENDANCE_INTEGRATION_ENABLED=false
BREAK_GLASS_ENABLED=false
```

Gate/Attendance driver config may exist, but feature must remain disabled.

---

# 8. DATABASE MIGRATION

Only after fresh backup PASS.

If pending migrations:

```bash
php artisan migrate --force
php artisan migrate:status
```

If no pending migrations:
- record `NO-PENDING-MIGRATIONS`.

If migration fails:
- do not switch release;
- STOP;
- classify `NO-GO-MIGRATION`;
- use forward fix or restore procedure according runbook.

---

# 9. ATOMIC RELEASE SWITCH

Switch atomically:

```text
current -> releases/<new-release>
```

Then:

- reload PHP-FPM;
- restart/reload queue workers safely (`queue:restart` or process-manager strategy);
- verify scheduler target uses `current`;
- do not duplicate scheduler.

Record:

```text
RUNTIME_SHA_AFTER
ACTIVE_RELEASE_PATH
CUTOVER_TIMESTAMP
```

Immediately run:

```text
GET /health
GET /health/ready
```

If either fails:
- immediate application rollback;
- STOP.

---

# 10. CORE APPLICATION CANARY — ALL INTEGRATIONS OFF

Before enabling Gate:

Verify production core:

- `/`;
- `/health`;
- `/health/ready`;
- route boot;
- database;
- cache/session;
- private storage;
- queue;
- scheduler;
- unauthorized route 403;
- no debug output;
- APP_DEBUG=false.

Check logs for:

- HTTP 500;
- DB errors;
- queue failures;
- permission errors;
- missing assets.

If core unhealthy:
- rollback application release immediately.

---

# 11. GATE PRODUCTION CONNECTIVITY PROBE

Keep SSO OFF.

Probe:

- HTTPS;
- OIDC discovery;
- issuer;
- authorization endpoint;
- token endpoint;
- userinfo;
- JWKS;
- app entitlement endpoint/claim contract;
- redirect URI registration.

No login token stored/logged.

If Gate production contract does not match documented Phase 4A/4B behavior:
- leave SSO OFF;
- classify `PRODUCTION-CUTOVER-PARTIAL`;
- core app may remain deployed if healthy.

---

# 12. ENABLE GATE SSO — CANARY ONLY

Only after Gate probe PASS.

Set:

```text
GATE_SSO_ENABLED=true
GATE_CLIENT_DRIVER=http
```

Then rebuild config cache safely.

Use approved production canary identity.

Test:

1. `/login`;
2. redirect to Gate;
3. callback;
4. state/nonce validation;
5. entitlement allowed;
6. Person/User projection;
7. session regeneration;
8. dashboard;
9. logout.

Verify:
- no secret/token in log;
- no duplicate Person/User/Patient;
- clinical role remains local.

If failed:
- set `GATE_SSO_ENABLED=false`;
- rebuild config;
- preserve core deployment;
- classify integration failure.

---

# 13. GATE ENTITLEMENT CANARY

Use safe/approved account states if available.

Verify at minimum:

- allowed -> login;
- not assigned -> deny.

Only test revoked/suspended production states if safe synthetic/test accounts exist.

Never revoke a real staff user merely for validation.

---

# 14. ENABLE GATE SYNC APPLY — DRY-RUN FIRST

Only if SSO canary stable.

Set:

```text
GATE_SYNC_APPLY_ENABLED=true
```

Do NOT apply yet.

Run dry-run.

Capture:

```text
SOURCE_TOTAL
NEW
MATCHED
CHANGED
UNCHANGED
DEACTIVATED
SOURCE_MISSING
CONFLICTS
DUPLICATE_IDENTIFIER
INVALID_PAYLOAD
UNSUPPORTED
```

Mandatory sanity checks:

- source total plausible;
- `source_missing` not abnormally high;
- deactivated count plausible;
- no mass conflict;
- no incomplete snapshot;
- Gate pagination complete.

If abnormal:
- disable sync apply;
- STOP sync activation;
- core+SSO may remain live.

---

# 15. FIRST PRODUCTION SYNC APPLY — HUMAN REVIEW GATE

Do not auto-apply merely because dry-run command succeeded.

Before apply, explicitly display summary without sensitive data and require a second operator/user confirmation phrase:

`SETUJUI APPLY SYNC GATE PRODUCTION`

If this phrase is absent:
- stop after dry-run;
- leave `GATE_SYNC_APPLY_ENABLED=true` or return to false according safest runbook;
- status `AWAITING-SYNC-APPLY-AUTHORIZATION`.

If authorized:
- apply;
- reconcile;
- validate no duplicate Person/User/Patient;
- validate deactivation non-destructive;
- validate audit.

---

# 16. ATTENDANCE PRODUCTION PROBE — KEEP DISABLED

Keep:

```text
ATTENDANCE_INTEGRATION_ENABLED=false
```

Probe production endpoint:

- TLS;
- auth contract;
- API version;
- health/status;
- request schema;
- idempotency behavior;
- acknowledgement behavior.

Do not send patient/clinical event yet.

If endpoint/contract differs from staging:
- keep OFF;
- document blocker;
- core+Gate may remain production.

---

# 17. ATTENDANCE FIRST CANARY — SEPARATE APPROVAL REQUIRED

Before enabling Attendance production, require:

`SETUJUI AKTIVASI ABSENSI PRODUCTION`

If absent:
- keep OFF;
- classify core deployment accordingly.

If authorized and probe PASS:

```text
ATTENDANCE_INTEGRATION_ENABLED=true
ATTENDANCE_INTEGRATION_DRIVER=http
```

Use one approved minimum-necessary canary event.

Before transmission, serialize and verify forbidden keys absent:

- diagnosis;
- ICD;
- complaint;
- vitals;
- medication;
- allergy;
- assessment;
- referral narrative;
- consultation advice;
- audit data.

Verify:
- one event;
- one downstream effect;
- idempotency;
- acknowledgement;
- audit/correlation.

If failure:
- turn Attendance OFF;
- do not rollback healthy core/Gate automatically.

---

# 18. POST-CUTOVER UAT

Run production-safe validation:

## Core
- health;
- ready;
- dashboard;
- assets;
- authorization.

## Identity
- Gate login;
- entitlement;
- logout;
- no duplicate projection.

## Clinical
Use only approved/synthetic safe path.
Avoid creating unnecessary patient records.

## Private docs
- authorized download;
- unauthorized direct URL denied.

## Queue
- worker alive;
- one safe queued job.

## Scheduler
- scheduler heartbeat.

## Outbox
- pending/failed/dead-letter visibility.

## Reports
- permission-scoped.

---

# 19. LOG / OBSERVABILITY WATCH WINDOW

Observe for a defined stabilization window.

Recommended minimum operational window:
- 15–30 minutes immediately post-cutover,
or according local production procedure.

Monitor:

- 5xx;
- PHP exceptions;
- DB errors;
- Gate failures;
- entitlement denials;
- duplicate identity;
- queue failures;
- failed jobs;
- outbox dead-letter;
- disk write failure;
- session errors;
- unauthorized access spikes.

No PHI/token logs.

---

# 20. DATA INTEGRITY POST-CUTOVER

Check:

- migration status;
- duplicate gate_user_id = 0;
- duplicate patient_number = 0;
- duplicate referral_number = 0;
- duplicate active referrals invariant;
- negative medicine stock = 0;
- orphan private document references = 0;
- unexpected mass deactivation = 0;
- outbox duplicate effective deliveries = 0.

Do not expose patient details in report.

---

# 21. ROLLBACK TRIGGERS

Immediate release rollback if:

- `/health` or `/health/ready` fails after switch;
- sustained HTTP 500;
- application boot error;
- migration incompatibility;
- private storage inaccessible;
- core authorization broken;
- severe data corruption risk.

Feature-only rollback if:

- Gate SSO fails but core healthy;
- sync apply anomaly;
- Attendance failure.

Feature rollback:

```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
ATTENDANCE_INTEGRATION_ENABLED=false
```

Application rollback:
- atomic switch to previous release;
- reload FPM;
- restart queue;
- health check.

Database restore:
- only when required by validated incident runbook;
- never blind rollback.

---

# 22. ROLLBACK VALIDATION

If no failure occurs, still verify rollback capability:

- previous release exists;
- symlink switch command valid;
- feature flags reversible;
- fresh backup available;
- restore command documented.

Do not intentionally corrupt production to test rollback.

---

# 23. FINAL FEATURE STATE REPORT

Record exact final state without secrets:

```text
GATE_SSO_ENABLED=
GATE_SYNC_APPLY_ENABLED=
GATE_WEBHOOK_ENABLED=
ATTENDANCE_INTEGRATION_ENABLED=
ATTENDANCE_INTEGRATION_DRIVER=
BREAK_GLASS_ENABLED=
```

Also:

```text
RUNTIME_SHA=
PREVIOUS_RUNTIME_SHA=
ACTIVE_RELEASE=
BACKUP_REFERENCE=
```

---

# 24. DOCUMENTATION

Create:

- `docs/10-delivery/PHASE-4C2-CUTOVER-EXECUTION.md`
- `docs/10-delivery/PHASE-4C2-POST-CUTOVER-UAT.md`
- `docs/10-delivery/PHASE-4C2-FINAL-STATUS.md`

Update:

- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `docs/10-delivery/PRODUCTION-GO-LIVE-CHECKLIST.md`
- `docs/10-delivery/INCIDENT-ROLLBACK-RUNBOOK.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Do not store secrets in documentation.

---

# 25. GRAPHIFY

After code/config documentation changes:

Update without `--code-only`.

Query:

- production login -> Gate;
- feature flag gate;
- Gate sync apply;
- Attendance production adapter;
- private storage;
- health/readiness;
- queue/outbox;
- production fake-driver leakage;
- debug/test route;
- hardcoded secret;
- hard delete;
- missing production guard.

---

# 26. GIT / RELEASE RECORD

If repository docs/code changed:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "ops(production): record controlled POSKESTREN production cutover"
git status
```

Do not commit environment secrets.

Server runtime SHA and Git docs SHA may differ; report both accurately.

---

# 27. FINAL CLASSIFICATION

Use exactly one:

### `AWAITING-PRODUCTION-AUTHORIZATION`
No explicit cutover approval phrase.

### `PRODUCTION-CUTOVER-PASSED`
Core production deployment and all explicitly approved integrations pass.

### `PRODUCTION-CUTOVER-PARTIAL`
Core deployed successfully, but one or more optional integrations intentionally remain OFF or were rolled back.

### `NO-GO`
Critical core blocker.

### `ROLLED-BACK`
Cutover attempted but rollback was required.

---

# 28. FINAL OUTPUT

Report:

1. Authorization phrase received/not received.
2. Runtime SHA before.
3. Candidate SHA.
4. Fresh backup validation.
5. Migration result.
6. Atomic release result.
7. Runtime SHA after.
8. Health/ready result.
9. Queue/scheduler result.
10. Core smoke result.
11. Gate production probe.
12. Gate SSO result.
13. Gate entitlement result.
14. Sync dry-run counts.
15. Sync apply authorization/result.
16. Attendance production probe.
17. Attendance activation authorization/result.
18. Privacy payload result.
19. Post-cutover UAT.
20. Integrity checks.
21. Observability stabilization result.
22. Rollback readiness/result.
23. Final feature flags.
24. Documentation.
25. Git/release status.
26. Remaining risks.
27. Final classification.

Do not continue beyond Phase 4C2.
