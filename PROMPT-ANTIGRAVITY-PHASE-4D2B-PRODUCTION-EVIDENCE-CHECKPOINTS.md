# PROMPT ANTIGRAVITY — PHASE 4D2B
## Production-Server Evidence Checkpoints
## T+6h / T+24h / T+48h / T+72h Stabilization Verification

Gunakan **Claude Opus 4.6 Thinking**.

Proyek: **SABIRA POSKESTREN Health**

Konteks:
- Stabilization start: `2026-08-10 21:53:58 +0700`
- T+1h sudah dilaporkan PASS.
- Status saat ini: `STABILIZATION-IN-PROGRESS`.
- Jangan mulai Phase 5.
- Jangan membuat metrics simulasi.

TUJUAN:
Mengumpulkan **bukti dari server production aktual** untuk checkpoint T+6h, T+24h, T+48h, dan T+72h.

---

# 1. SOURCE-OF-TRUTH RULE

Setiap bukti harus diberi source label:

- `PRODUCTION-SERVER`
- `PRODUCTION-EXTERNAL-PROBE`
- `PRODUCTION-DATABASE`
- `PRODUCTION-LOG`
- `TEST-ENV`
- `LOCAL-DEV`
- `HUMAN-UAT`

Hanya data dengan source `PRODUCTION-*` atau `HUMAN-UAT` yang boleh digunakan untuk final operational acceptance.

Jangan mengubah `LOCAL-DEV` atau `TEST-ENV` menjadi seolah-olah production evidence.

---

# 2. WALL-CLOCK CHECK

Pada awal run, gunakan production server time:

```bash
date
timedatectl
```

Hitung terhadap:

```text
STABILIZATION_START_AT=2026-08-10 21:53:58 +0700
```

Classify:

- `< 6h` -> `WAITING-FOR-T+6H`
- `>= 6h < 24h` -> T+6h eligible
- `>= 24h < 48h` -> T+24h eligible
- `>= 48h < 72h` -> T+48h eligible
- `>= 72h` -> T+72h final eligible

Jangan membuat checkpoint sebelum wall-clock terpenuhi.

---

# 3. PROVE YOU ARE ON THE ACTUAL PRODUCTION SERVER

Sebelum mengambil metrics, catat:

```bash
hostname
uname -a
pwd
readlink -f /var/www/poskestren/current
cd /var/www/poskestren/current
git rev-parse HEAD
git status --short
```

Verifikasi safe runtime config tanpa secret:

```text
APP_ENV=production
APP_DEBUG=false
```

Jika tidak dapat membuktikan bahwa shell adalah production server:
- STOP.
- status `PRODUCTION-EVIDENCE-NOT-AVAILABLE`.

---

# 4. EXTERNAL PROBE MUST NOT USE localhost

Gunakan production hostname:

```bash
curl -skI https://poskestren.sabira.id/
curl -skI https://poskestren.sabira.id/dashboard
curl -skI https://poskestren.sabira.id/patients
curl -skI https://poskestren.sabira.id/visits
curl -skI https://poskestren.sabira.id/reports
curl -skI https://poskestren.sabira.id/users
curl -skI https://poskestren.sabira.id/login
curl -sk https://poskestren.sabira.id/health
curl -sk https://poskestren.sabira.id/health/ready
```

Record as:
`PRODUCTION-EXTERNAL-PROBE`.

Do not substitute `127.0.0.1`.

---

# 5. NGINX / REVERSE-PROXY LOG EVIDENCE

Use actual production access/error logs.

Determine stabilization window counts:

- total requests;
- 2xx;
- 3xx;
- 4xx;
- 5xx;
- 5xx percentage.

Use the actual configured log path.

Examples only:
```bash
nginx -T 2>/dev/null | grep -n "access_log\|error_log"
```

Then query the relevant period.

If request latency is not logged:
- set `LATENCY=NOT-INSTRUMENTED`.

Do NOT invent p50/p95.

If request time exists:
- compute p50/p95/p99 from actual logs or monitoring.

Sanitize URLs/identifiers in documentation.

---

# 6. SECURITY / APPLICATION LOG EVIDENCE

Inspect actual production logs for current checkpoint window:

- HTTP 500;
- OIDC state/nonce failures;
- Gate token failures;
- entitlement denial;
- unauthorized probes;
- path traversal;
- direct private document attempts;
- fatal exceptions.

Do not print raw IP addresses, tokens, user identifiers, or clinical data.

Report aggregate counts/categories only.

---

# 7. GATE SSO AUDIT METRICS

Use production `audit_logs` or actual auth-event store.

Count for stabilization window:

```text
login_attempts
login_success
login_failed
entitlement_denied
invalid_state
invalid_nonce
token_exchange_failed
duplicate_projection_error
deactivated_user_denied
```

If exact event names differ, map documented equivalents.

Do not infer metrics from test suite.

---

# 8. SUPERVISOR / QUEUE PROOF

On production server:

```bash
supervisorctl status
php artisan queue:failed
```

If Supervisor not used, use actual systemd/process-manager command.

Record:
- worker names;
- state;
- uptime/restarts if available;
- failed jobs count.

If queue backend allows:
- pending count;
- oldest pending age.

Do not state "2 workers active" without process evidence.

---

# 9. SCHEDULER PROOF

Verify BOTH schedule definition and OS execution:

```bash
php artisan schedule:list
crontab -l
```

or actual systemd timer commands.

Prove:
- exactly one active production scheduler source;
- execution happened after stabilization start;
- no duplicate scheduler.

If no execution log/heartbeat exists:
- mark `SCHEDULER-EXECUTION-NOT-INSTRUMENTED`.

---

# 10. PRODUCTION DATABASE IDENTITY

Before DB aggregates, prove active DB safely without printing password.

Record:
- DB driver;
- DB host category/local vs remote if safe;
- database name;
- MariaDB version;
- APP_ENV.

Expected production database:
`poskestren_sabira` if that is the documented production DB.

If database identity is ambiguous:
- STOP aggregate claims.

---

# 11. DATA INTEGRITY — READ ONLY

Run actual production aggregate queries.

Required:

```text
duplicate gate_user_id
duplicate patient_number
duplicate referral_number
duplicate active referral
negative medicine stock
orphan referral/version references
orphan discharge/version references
unexpected mass deactivation
failed jobs
duplicate effective outbox delivery
```

Report query/result counts only.

Do not output patient/user rows.

---

# 12. OUTBOX / ATTENDANCE PRODUCTION METRICS

Query production:

- pending;
- processing;
- acknowledged;
- failed;
- dead_letter;
- cancelled;
- oldest pending event age.

Verify effective config:

```text
ATTENDANCE_INTEGRATION_ENABLED=
ATTENDANCE_INTEGRATION_DRIVER=
```

Without secrets.

If enabled, confirm destination is production endpoint.

For one recent acknowledged event, inspect schema/keys only and confirm forbidden clinical keys absent.

Do not output payload values.

---

# 13. PHARMACY

Technical DB check:
- no negative stock;
- ledger/batch reconciliation.

Physical stock:
- requires HUMAN-UAT / physical stock opname sign-off.

If no real stock count:
`PENDING-PHYSICAL-AUDIT`.

Do not change to PASS from software evidence.

---

# 14. BACKUP — ACTUAL ARTIFACT REQUIRED

Find actual production backup generated after go-live/hotfix.

Record:
- timestamp;
- file size;
- SHA-256;
- permission mode;
- DB backup reference;
- private-storage backup reference.

Do not reveal secret contents.

If no actual backup artifact:
`BACKUP-EVIDENCE-MISSING`.

Run:
```bash
sha256sum <backup-file>
```
or equivalent.

---

# 15. RESTORE TEST

Only count PASS if an actual isolated restore was performed.

Required evidence:
- isolated DB name;
- restore timestamp;
- source backup checksum;
- import result;
- migration status;
- basic app bootstrap result.

Never restore over production.

Until done:
`RESTORE-NOT-YET-PROVEN`.

---

# 16. HUMAN UAT EVIDENCE

Do not invent sign-offs.

For each role:

```text
UAT-CLINICAL-01
UAT-PHARMACY-01
UAT-DORM-01
UAT-MANAGEMENT-01
UAT-IT-01
```

Required evidence:
- actual test performed;
- date/time;
- result;
- operator confirmation/sign-off.

If no real human confirmation:
`UAT-PENDING`.

Automated tests do not count as human UAT.

---

# 17. CLINICAL WORKFLOW EVIDENCE

Automated tests are `TEST-ENV`.

Production workflow evidence requires an actual approved workflow or audit of an organically completed case.

Do not create fake medical history in production.

If no real clinical workflow occurred yet:
`PRODUCTION-CLINICAL-WORKFLOW-PENDING`.

This does not make the application unsafe; it means evidence is not yet available.

---

# 18. RESOURCE TELEMETRY

On actual server collect point-in-time:

```bash
uptime
free -h
df -h
ps aux --sort=-%mem | head
```

DB connection count via safe aggregate.

Label:
`POINT-IN-TIME`.

Only report averages if actual historical monitoring exists.

---

# 19. CHECKPOINT DOCUMENTATION

Append to:

`docs/10-delivery/PHASE-4D2-STABILIZATION-EVIDENCE.md`

Each checkpoint must include:

```text
CHECKPOINT:
TIMESTAMP:
ELAPSED_HOURS:
SOURCE:
RUNTIME_SHA:
HEALTH:
READY:
HTTP_5XX:
GATE:
QUEUE:
SCHEDULER:
OUTBOX:
DATA_INTEGRITY:
BACKUP:
SECURITY:
ISSUES:
STATUS:
```

Never pre-fill future checkpoints.

---

# 20. CHECKPOINT DECISION

At T+6h:
- if no Critical issue -> `T+6H-PASS`
- otherwise `PRODUCTION-INCIDENT-OPEN`.

At T+24h:
- if real evidence healthy -> `PRODUCTION-STABLE-PRELIMINARY`.

At T+48h:
- continue preliminary status if healthy.

At T+72h:
Only use:
`PRODUCTION-OPERATIONALLY-ACCEPTED-VERIFIED`

if ALL required production evidence is real and no Critical/High blocker remains.

If UAT/backup/restore evidence remains incomplete:
`PRODUCTION-STABLE-WITH-EVIDENCE-GAPS`.

---

# 21. NO FEATURE WORK

Until T+72h final:
- no Phase 5;
- no WhatsApp;
- no analytics;
- no UI redesign;
- no unrelated refactor.

Only:
- security;
- reliability;
- monitoring;
- config;
- documentation.

---

# 22. FINAL OUTPUT PER RUN

Report:

1. Actual server time.
2. Elapsed stabilization hours.
3. Eligible checkpoint.
4. Production server identity proof.
5. Runtime SHA.
6. External hostname probe.
7. Real HTTP metrics.
8. Gate metrics.
9. Queue evidence.
10. Scheduler evidence.
11. Outbox/Attendance evidence.
12. DB integrity aggregates.
13. Pharmacy DB result.
14. Physical stock status.
15. Backup evidence.
16. Restore evidence.
17. Human UAT evidence.
18. Clinical production workflow evidence.
19. Security log result.
20. Resource telemetry.
21. Evidence gaps.
22. Checkpoint status.
23. Next checkpoint time.

Do not start Phase 5 automatically.
