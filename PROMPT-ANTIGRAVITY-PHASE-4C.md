# PROMPT ANTIGRAVITY — PHASE 4C
## Production Deployment Hardening, Controlled Cutover, Rollback, Observability, and Go-Live Validation

Anda adalah principal Laravel production architect, DevOps/SRE engineer, IAM/OIDC security engineer, database reliability engineer, application security engineer, incident-response engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Claude Opus 4.6 Thinking**, **Claude Sonnet 4.6 Thinking**, atau model reasoning setara.

Baseline tervalidasi:
- Phase 4B: `PRODUCTION-READY-STAGING-VALIDATED`
- 173 tests passed, 659 assertions, 0 failures, 0 skipped
- MariaDB concurrency PASSED
- Gate staging SSO PASSED
- Secure Gate sync apply PASSED
- Attendance sandbox PASSED
- Outbox retry/dead-letter PASSED
- Privacy payload PASSED
- Production feature flags masih OFF
- Git baseline Phase 4B: `dd5798f`

Tujuan Phase 4C:
1. menutup Phase 4B secara final;
2. mengaudit environment production;
3. memastikan deployment profile, secrets, permissions, private storage, queue, scheduler, cache/session, logging, backup, health checks, dan rollback siap;
4. melakukan production database preflight dan migration safety review;
5. membangun candidate/release yang dapat di-rollback secara atomik;
6. mengaktifkan Gate SSO production secara bertahap hanya setelah contract production terbukti;
7. mengaktifkan Gate sync apply production setelah dry-run/reconciliation aman;
8. mengaktifkan Attendance production connector hanya setelah contract, privacy, idempotency, retry, dan probe production lulus;
9. menjalankan production smoke/UAT dan rollback drill;
10. berhenti setelah production validation. Jangan menambah fitur baru.

---

# 1. ATURAN KESELAMATAN MUTLAK

1. Jangan menampilkan `.env`, password, token, client secret, private key, OAuth token, DB credential, atau secret lain.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, destructive reset, hard delete, atau force push.
3. Jangan menjalankan migration sebelum backup tervalidasi.
4. Jangan mengaktifkan Gate production sebelum issuer/client/redirect/entitlement contract diverifikasi.
5. Jangan mengaktifkan Attendance production sebelum endpoint/contract/idempotency/privacy probe diverifikasi.
6. Jangan mengaktifkan seluruh feature flag sekaligus.
7. Aktivasi feature harus bertahap dan reversible.
8. Jangan mengaktifkan Gate webhook production kecuali signature/replay contract resmi dan sudah diuji.
9. Jangan membuat synthetic patient pada production tanpa approval eksplisit.
10. Jangan mengirim diagnosis/narasi klinis ke Absensi.
11. Jangan mematikan Policy, CSRF, rate limit, audit, atau session security untuk mempermudah deployment.
12. Jika Critical blocker ditemukan, STOP sebelum cutover.

---

# 2. PHASE 4B FINAL CLOSURE

Jalankan read-only:

```bash
pwd
git branch --show-current
git status
git log --oneline -12
php artisan migrate:status
php artisan route:list
```

Verifikasi:
- commit `dd5798f` tersedia;
- working tree clean;
- Phase 4B closure PASSED;
- production flags OFF;
- sandbox/staging URLs tidak hardcoded;
- no secret in repository;
- Graphify updated.

Buat `docs/10-delivery/PHASE-4B-FINAL-CLOSURE.md` dengan status `PASSED` atau `FAILED`.

Stop jika FAILED.

---

# 3. PRODUCTION PREFLIGHT — READ ONLY FIRST

Audit production tanpa write dahulu.

Dokumentasikan:
- OS/version;
- PHP CLI/FPM;
- Composer;
- Node/npm build strategy;
- MariaDB/MySQL;
- Redis;
- queue driver;
- session/cache driver;
- private storage permissions;
- web server/reverse proxy;
- TLS;
- trusted proxy;
- deploy path;
- current runtime SHA;
- DB migration state;
- backup location;
- free disk/RAM;
- PHP-FPM/queue process manager;
- scheduler;
- log rotation;
- health checks.

Buat `docs/10-delivery/PHASE-4C-PRODUCTION-PREFLIGHT.md`.

Status setiap item:
- READY
- NEEDS_FIX
- BLOCKED

Tidak ada cutover sebelum semua Critical item READY.

---

# 4. DEPLOYMENT PROFILE / MANIFEST

Buat deployment profile tanpa secret yang berisi minimal:

```text
application_name
environment
repository
branch
expected_commit
release_path
shared_storage_path
php_version
php_fpm_service
web_server
database
queue_driver
queue_service
scheduler_strategy
health_endpoint
backup_path
rollback_strategy
gate_feature_flags
attendance_feature_flags
```

Jika server sudah memakai deployment engine/agent, integrasikan dengan mekanisme existing dan jangan membuat pipeline paralel yang bertentangan.

Deployment harus dapat melaporkan:
- candidate SHA;
- runtime SHA before;
- runtime SHA after;
- backup path;
- release path;
- health result;
- rollback result.

---

# 5. PRODUCTION CONFIG / SECRET AUDIT

Verifikasi keberadaan config tanpa mencetak nilai.

Laravel:
```text
APP_ENV=production
APP_DEBUG=false
APP_URL
APP_KEY exists
SESSION_SECURE_COOKIE=true
```

Gate initial state:
```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
GATE_WEBHOOK_ENABLED=false
```

Attendance initial state:
```text
ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
```

Break-glass:
```text
BREAK_GLASS_ENABLED=false
```

Verify required production Gate/Attendance endpoint and credential variables exist without revealing values.

---

# 6. PRIVATE STORAGE HARDENING

Verify:
- referral documents private;
- discharge documents private;
- return external documents private;
- private paths not under public disk;
- `storage:link` does not expose private documents;
- web server cannot direct-serve private files;
- download requires Controller + Policy;
- backup includes private document storage securely.

Test guessed direct URLs -> 403/404.

---

# 7. PRODUCTION DATABASE PRE-MIGRATION AUDIT

Run read-only:

```bash
php artisan migrate:status
```

Verify production DB identity unambiguously.

Check:
- InnoDB;
- charset/collation;
- isolation level;
- existing migration drift;
- indexes/unique constraints for `gate_user_id`, `patient_number`, `referral_number`, outbox idempotency, handoff idempotency, referral returns, version uniqueness.

If DB identity is ambiguous, STOP.

---

# 8. PRE-CUTOVER BACKUP

Before any migration/cutover create validated backups:
1. database logical backup;
2. current application release/source;
3. environment/secret config backup stored securely outside repository;
4. Nginx/web server config;
5. PHP-FPM config relevant to app;
6. queue/scheduler service config;
7. private storage backup/manifest.

Verify:
- backup exists;
- non-zero;
- DB dump readable/listable;
- checksum recorded;
- permissions restricted;
- restore command documented.

Buat `docs/10-delivery/PHASE-4C-BACKUP-AND-ROLLBACK.md`.

No cutover if backup invalid.

---

# 9. IMMUTABLE RELEASE / CANDIDATE

Prefer atomic release structure:

```text
releases/<timestamp-or-sha>/
current -> releases/<active>
shared/storage
shared/.env
```

If existing deployment engine uses another safe atomic strategy, preserve it.

Build candidate:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
```

Cache only if compatible:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Verify candidate matches expected Git SHA.

---

# 10. CANDIDATE QUALITY GATE

Before packaging/deployment run full checks in safe environment:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
git diff --check
```

No skipped critical tests.

---

# 11. MIGRATION REVIEW

Review every pending migration manually for:
- table lock risk;
- full table scan;
- index creation;
- non-null changes;
- defaults;
- FK changes;
- destructive drops;
- irreversible changes.

Classify:
- SAFE_ONLINE
- REQUIRES_MAINTENANCE
- BLOCKED

Do not run BLOCKED migration.

---

# 12. CONTROLLED MIGRATION

Only after backup + migration review PASS:

```bash
php artisan migrate --force
php artisan migrate:status
```

If migration fails:
- STOP;
- do not enable Gate/Attendance;
- follow rollback/restore plan.

---

# 13. QUEUE WORKER

Configure production queue under server-standard process manager.

Verify:
- autostart/restart;
- correct app user;
- correct working directory;
- environment secure;
- timeout/tries/backoff;
- memory limit;
- logs rotated;
- failed jobs visible.

Run one safe internal/non-PHI job and verify completion.

---

# 14. SCHEDULER

Run:

```bash
php artisan schedule:list
```

Ensure exactly one production scheduler source, e.g. cron/systemd timer.

Verify scheduled commands are idempotent and visible operationally.

---

# 15. CACHE / SESSION / REDIS

Verify actual production drivers.

Gate OAuth redirect requires persistent session.

Check:
- Redis connectivity if used;
- prefixes/isolation;
- session persistence;
- queue isolation;
- cache behavior;
- restart behavior.

---

# 16. WEB SERVER / TLS / PROXY

Verify:
- HTTPS valid;
- HTTP -> HTTPS;
- correct host;
- trusted proxies;
- `X-Forwarded-Proto` handled;
- Secure/HttpOnly/SameSite cookies;
- exact Gate callback URL;
- `.env`, `.git`, private storage, hidden files inaccessible;
- PHP-FPM version/socket correct;
- no directory listing;
- upload limits appropriate.

Run external smoke checks.

---

# 17. HEALTH / READINESS ENDPOINTS

Verify/create safe endpoints.

`/health` may expose only:
- status;
- version/SHA;
- environment label;
- time.

`/health/ready` may check:
- DB;
- cache/Redis;
- queue heartbeat;
- writable private storage;
- Gate/Attendance dependency status as safe non-secret state.

Do not expose stack traces, credentials, internal paths, or PHI.

---

# 18. OBSERVABILITY

Verify structured logs/correlation for:
- request;
- Gate login failures;
- entitlement denial;
- Gate sync run/item;
- outbox event/delivery;
- dead-letter;
- queue failures;
- critical referral/discharge mutations.

No token/secret/PHI.

Document operational checks for:
- HTTP 5xx;
- queue failures;
- dead-letter count;
- SSO failures;
- sync failures;
- Attendance failures;
- DB failures;
- disk usage.

---

# 19. PRODUCTION FEATURE ACTIVATION — STRICT ORDER

Do not enable everything at once.

## Step 1 — Core app deployed, integrations OFF

```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
ATTENDANCE_INTEGRATION_ENABLED=false
```

Smoke test app, DB, queue, scheduler, documents, Policies.

## Step 2 — Gate production connectivity probe

With SSO still OFF verify:
- discovery/JWKS or actual Gate contract;
- TLS;
- client configuration;
- redirect URI;
- entitlement endpoint.

## Step 3 — Gate SSO activation

Only if Step 2 PASS:

```text
GATE_SSO_ENABLED=true
GATE_CLIENT_DRIVER=http
```

Reload config safely.

Test approved production account:
- login;
- entitlement;
- session;
- logout.

Failure -> disable SSO immediately and revert config cache.

## Step 4 — Gate sync apply activation

Only after SSO stable:

```text
GATE_SYNC_APPLY_ENABLED=true
```

First run dry-run only.

Review counts:
- source;
- new;
- matched;
- changed;
- unchanged;
- deactivated;
- source_missing;
- conflicts;
- unsupported;
- invalid.

STOP if unexpected mass `source_missing`, deactivation, duplicate identifiers, or abnormal source count.

Only then run explicitly approved apply.

## Step 5 — Attendance production probe

Keep integration disabled.

Probe actual production endpoint/auth/contract safely.

## Step 6 — Attendance production activation

Only if contract and privacy checks PASS:

```text
ATTENDANCE_INTEGRATION_ENABLED=true
ATTENDANCE_INTEGRATION_DRIVER=http
```

Send one approved minimum-necessary operational disposition.

Verify:
- one downstream effect;
- acknowledgement;
- idempotency;
- privacy;
- audit.

If uncertain, keep Attendance OFF and classify core app separately.

---

# 20. GATE PRODUCTION VALIDATION

Use only approved accounts.

Test:
- allowed entitlement;
- no entitlement if safe test identity exists;
- logout;
- callback replay;
- invalid state.

Do not destructively revoke real staff just to test.

---

# 21. FIRST PRODUCTION SYNC

Before APPLY, capture dry-run report.

Require human review before first full apply.

Invariant:
- incomplete/failed snapshot must never trigger `source_missing` deactivation.

After apply verify:
- no duplicate Person/User/Patient;
- no medical data changes from Gate;
- deactivation preserved history;
- audit/reconciliation complete.

---

# 22. ATTENDANCE PRIVACY VERIFICATION

Before first production send validate serialized payload.

Allowed only:
- stable Gate identity;
- operational disposition;
- effective period;
- activity scope;
- opaque source reference;
- event version;
- correlation/idempotency.

Forbidden:
- diagnosis;
- ICD;
- complaints;
- vitals;
- medication;
- allergy;
- assessment;
- referral narrative;
- consultation advice;
- audit.

Runtime validator must reject forbidden keys.

---

# 23. POST-CUTOVER SMOKE/UAT

Verify:
- `/`;
- `/health`;
- `/login`;
- Gate callback if enabled;
- dashboard;
- authorization/direct URL;
- technical admin denied clinical detail;
- private document authorized download;
- queue;
- scheduler;
- Gate sync status;
- outbox monitor;
- reports;
- Attendance status if enabled.

Avoid unnecessary real clinical record creation.

---

# 24. DATABASE / DATA POST-CUTOVER VALIDATION

Check:
- migrations clean;
- failed jobs;
- audit writes;
- no duplicate identities;
- no negative stock;
- outbox consistency;
- no unexpected mass deactivation;
- no orphan private documents.

---

# 25. ROLLBACK DRILL

Prove rollback before final GO.

Application rollback:
- atomically switch to previous release/deployment-engine equivalent;
- restart PHP-FPM/queue;
- health check.

Feature rollback:
```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
ATTENDANCE_INTEGRATION_ENABLED=false
```

Rebuild config cache safely.

Database:
- do not blindly `migrate:rollback` after production data writes;
- document forward-fix or restore strategy;
- validated backup must be available.

Record drill evidence.

---

# 26. FAILURE MATRIX

Document response for:
- Gate unavailable;
- Gate token endpoint unavailable;
- entitlement unavailable;
- DB unavailable;
- Redis unavailable;
- queue stopped;
- Attendance unavailable;
- disk full;
- private storage unwritable;
- migration failure;
- bad release;
- high HTTP 500.

For each document:
- user impact;
- detection/alert;
- automatic retry;
- manual action;
- rollback trigger.

---

# 27. SECURITY POSTURE

Verify production:
- `APP_DEBUG=false`;
- no debug toolbar/dev route;
- no test-only login;
- no fake Gate driver while SSO enabled;
- break-glass disabled unless explicitly approved;
- no private document public exposure;
- no secret committed;
- session security;
- CSRF;
- rate limiting;
- Policy enforcement;
- append-only audit.

---

# 28. PERFORMANCE BASELINE

Capture production-safe baseline for:
- `/login`/home;
- dashboard;
- patient list;
- report query;
- DB query counts where practical;
- queue latency;
- outbox processing latency.

Do not destructively load-test production.

---

# 29. BACKUP / RESTORE READINESS

Verify:
- DB backup schedule;
- private document backup;
- release retention;
- restricted/encrypted backup storage;
- documented restore procedure;
- retention policy `[PERLU DIKONFIRMASI]` if not approved.

If safe, restore latest backup to an isolated test environment and verify application boot/migration consistency.

Never restore over production for testing.

---

# 30. GRAPHIFY

Update without `--code-only`.

Query:
- production login -> Gate -> callback;
- entitlement path;
- sync apply/source_missing guard;
- feature flags;
- Attendance production adapter;
- private storage exposure;
- queue/outbox;
- health endpoints;
- debug/test route leakage;
- fake driver in production;
- hardcoded secret/URL;
- hard delete path;
- missing production tests.

Update mapping docs.

---

# 31. DOCUMENTATION

Create:
- `docs/10-delivery/PHASE-4C-PRODUCTION-PREFLIGHT.md`
- `docs/10-delivery/PHASE-4C-BACKUP-AND-ROLLBACK.md`
- `docs/10-delivery/PHASE-4C-DEPLOYMENT-RUNBOOK.md`
- `docs/10-delivery/PHASE-4C-PRODUCTION-UAT.md`
- `docs/10-delivery/PHASE-4C-CLOSURE.md`
- `docs/10-delivery/PRODUCTION-GO-LIVE-CHECKLIST.md`
- `docs/10-delivery/INCIDENT-ROLLBACK-RUNBOOK.md`

Update:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/07-security/GATE-SSO-SECURITY.md`
- `docs/07-security/OPERATIONAL-DATA-SHARING.md`
- `docs/08-api/GATE-OIDC-CONTRACT.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/08-api/ATTENDANCE-INTEGRATION-CONTRACT.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

---

# 32. FULL VALIDATION

Before final GO:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
git diff --check
```

Production smoke/UAT reported separately.

---

# 33. GIT / RELEASE CHECKPOINT

Before Phase 4C changes:

```bash
git status
git log --oneline -8
git tag -a phase-4b-complete -m "Phase 4B staging integration and UAT complete"
```

Commit code/docs hardening only, never secret config:

```bash
git add -A
git diff --cached --check
git commit -m "chore(production): complete Phase 4C deployment hardening and go-live runbook"
```

Record runtime release SHA separately if server deployment has no source change.

---

# 34. FINAL STATUS MODEL

Use exactly one:

### `PRODUCTION-READY-NOT-CUTOVER`
Hardening passes, but production cutover not explicitly authorized/performed.

### `PRODUCTION-CUTOVER-PASSED`
Production deployed and approved integrations enabled; smoke/UAT passes.

### `PRODUCTION-CUTOVER-PARTIAL`
Core application production healthy, but optional integration such as Attendance intentionally remains OFF/deferred.

### `NO-GO`
Critical blocker exists.

Do not call optional Attendance deferral a total application failure.

---

# 35. FINAL OUTPUT

Report:
1. Phase 4B final closure.
2. Candidate SHA and runtime SHA before/after.
3. Production environment preflight.
4. Backup validation and backup reference.
5. Migration review/result.
6. Release/cutover strategy.
7. Queue worker status.
8. Scheduler status.
9. Cache/session/Redis status.
10. Reverse proxy/TLS/session result.
11. Private storage result.
12. Health/readiness result.
13. Gate production probe.
14. Gate SSO activation result.
15. First production sync dry-run/apply result.
16. Attendance production probe/activation result.
17. Privacy verification.
18. Production smoke/UAT.
19. Rollback drill.
20. Observability.
21. Performance baseline.
22. Backup/restore readiness.
23. Final feature flags.
24. Full tests/assertions/skips.
25. Graphify findings.
26. Remaining risks/blockers.
27. Git/release status.
28. Final GO/NO-GO classification.

---

# 36. MANDATORY STOP CONDITIONS

STOP before cutover if:
- production DB identity ambiguous;
- backup invalid/missing;
- migration destructive/unreviewed;
- rollback unavailable;
- `APP_DEBUG=true`;
- private documents public;
- Gate production contract/redirect/entitlement not verified;
- source_missing can mass-deactivate from partial snapshot;
- Gate sync can duplicate identity;
- Gate role can escalate clinical permission;
- required queue/scheduler unavailable;
- secret exposed;
- critical test fails.

STOP Attendance activation if:
- endpoint/contract not verified;
- forbidden clinical field appears;
- idempotency fails;
- retry/dead-letter fails.

If hardening succeeds but explicit production cutover approval has not been given:
- classify `PRODUCTION-READY-NOT-CUTOVER`;
- STOP;
- do not invent approval.
