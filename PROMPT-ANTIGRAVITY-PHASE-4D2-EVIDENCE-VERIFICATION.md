# PROMPT ANTIGRAVITY — PHASE 4D2
## Independent Production Operational Evidence Verification
## Real 24–72 Hour Stabilization, Telemetry Proof, UAT Sign-off, and Phase 4 Final Closure

Anda adalah principal SRE/DevOps engineer, Laravel production architect, IAM/security auditor, database reliability engineer, QA/UAT auditor, dan operational acceptance reviewer untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Claude Opus 4.6 Thinking**.

KONTEKS:

Phase 4D sebelumnya dilaporkan berstatus:

`PRODUCTION-OPERATIONALLY-ACCEPTED`

dengan klaim antara lain:
- 24–72 jam stabilisasi;
- 2,450 HTTP requests;
- HTTP 5xx 0.00%;
- p50 20ms / p95 45ms;
- 165 successful Gate logins;
- 24 outbox events acknowledged;
- 5 real-user UAT representatives;
- backup/restore PASS;
- zero data-integrity violations;
- production stable tag `poskestren-production-stable-v1`;
- master clean.

Namun Phase 4D2 bertujuan **memverifikasi bukti production aktual secara independen**.

JANGAN menganggap angka dalam Phase 4D benar hanya karena tertulis di dokumentasi.
JANGAN membuat angka simulasi.
JANGAN membuat nama user/staf.
JANGAN mengklaim telah memonitor 24/48/72 jam bila waktu riil belum berlalu.

---

# 1. STATUS AWAL

Set status:

`PRODUCTION-STABILIZATION-EVIDENCE-PENDING`

Status ini menggantikan sementara acceptance final sampai bukti aktual diverifikasi.

Jangan mengubah sistem production secara destruktif.

---

# 2. READ EXISTING REPORTS

Baca:

- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/PHASE-4D-STABILIZATION-LOG.md`
- `docs/10-delivery/PHASE-4D-OPERATIONAL-UAT.md`
- `docs/10-delivery/PRODUCTION-MONITORING-BASELINE.md`
- `docs/10-delivery/POSKESTREN-DAILY-OPERATIONS-SOP.md`
- `docs/10-delivery/PHASE-4D-OPERATIONAL-ACCEPTANCE.md`
- `docs/10-delivery/PHASE-4D-CLOSURE.md`
- `docs/10-delivery/PRODUCTION-AUTH-HOTFIX-VERIFICATION.md`
- `docs/10-delivery/PRODUCTION-AUTH-EXPOSURE-REVIEW.md`
- `docs/10-delivery/INCIDENT-ROLLBACK-RUNBOOK.md`
- `plans/KNOWN-ISSUES.md`

Extract every operational claim that requires external evidence.

Classify each:

- `VERIFIED`
- `UNVERIFIED`
- `SIMULATED`
- `TEST-ENV-ONLY`
- `PRODUCTION-EVIDENCE-REQUIRED`
- `NOT-APPLICABLE`

Create:

`docs/10-delivery/PHASE-4D2-EVIDENCE-REGISTER.md`

---

# 3. WALL-CLOCK VALIDATION — CRITICAL

Determine exact:

- production go-live timestamp;
- auth hotfix production deployment timestamp;
- start of clean stabilization window;
- current production timestamp.

Use server time, not model assumptions.

Commands may include:

```bash
date
timedatectl
git log --format='%h %ci %s' -15
```

Record:

```text
GO_LIVE_AT=
AUTH_HOTFIX_AT=
STABILIZATION_START_AT=
CURRENT_TIME=
ELAPSED_STABILIZATION_HOURS=
```

Rules:

- stabilization window starts from the **latest security-critical production change**, not earlier go-live;
- if auth hotfix was deployed after go-live, restart stabilization clock from auth hotfix;
- do not count simulated/test runtime time.

Status checkpoints:

- `< 24h` -> `STABILIZATION-IN-PROGRESS`
- `>= 24h` -> eligible for 24h review
- `>= 48h` -> eligible for 48h review
- `>= 72h` -> eligible for final 72h closure

If elapsed < required checkpoint:
- collect evidence available so far;
- save docs;
- STOP;
- do not claim final operational acceptance.

---

# 4. RELEASE PROVENANCE

Verify production actual:

```bash
readlink -f /var/www/poskestren/current
cd /var/www/poskestren/current
git rev-parse HEAD
git status --short
git describe --tags --always
```

Verify canonical repository:

```bash
git branch --show-current
git status
git log --oneline --decorate -12
git show --no-patch poskestren-production-stable-v1
```

Confirm:

- runtime SHA;
- source SHA;
- canonical branch;
- release tag;
- no untracked runtime mutation.

Classify:
- `PASS`
- `MISMATCH`
- `UNVERIFIED`

---

# 5. PRODUCTION HTTP METRICS — REAL LOG DATA ONLY

Do not invent request counts.

Use actual production log source such as:
- Nginx access logs;
- reverse-proxy logs;
- application observability;
- existing metrics collector.

Determine for stabilization window:

- total requests;
- HTTP 2xx;
- HTTP 3xx;
- HTTP 4xx;
- HTTP 5xx;
- 5xx percentage.

If latency is present in logs/metrics:
- p50;
- p95;
- p99.

If latency instrumentation does not exist:
- write `NOT-INSTRUMENTED`;
- do NOT fabricate latency values.

Document exact source and time range.

No patient URLs with identifiers in public docs.

---

# 6. GATE SSO METRICS — REAL AUDIT DATA

Use application audit/event tables or structured logs.

Count during stabilization window:

- Gate login attempts;
- successful logins;
- failed logins;
- entitlement denied;
- invalid state;
- invalid nonce;
- token exchange failure;
- duplicate projection errors;
- deactivated-user denial.

Do not infer counts from automated tests.

If metrics are unavailable, record:
`NOT-INSTRUMENTED`.

---

# 7. AUTH PROTECTION PROBE

Repeat actual production no-cookie checks:

```bash
curl -skI https://poskestren.sabira.id/
curl -skI https://poskestren.sabira.id/dashboard
curl -skI https://poskestren.sabira.id/patients
curl -skI https://poskestren.sabira.id/visits
curl -skI https://poskestren.sabira.id/reports
curl -skI https://poskestren.sabira.id/users
curl -skI https://poskestren.sabira.id/login
```

Record exact HTTP status and redirect target.

Expected:
- protected -> 302 login/Gate or 401/403;
- no sensitive 200.

---

# 8. QUEUE EVIDENCE

Use real Supervisor/systemd/Laravel queue evidence.

Record:
- worker count;
- uptime;
- restarts;
- pending jobs;
- failed jobs;
- oldest pending age if measurable.

Do not write `0 pending` unless queried.

Examples:
```bash
supervisorctl status
php artisan queue:failed
```

Use correct production process manager.

---

# 9. SCHEDULER EVIDENCE

Verify actual scheduler:

```bash
php artisan schedule:list
crontab -l
```

or systemd timer.

Prove:
- one scheduler source;
- it has executed after hotfix;
- no duplicate scheduler.

Use logs/heartbeat if available.

---

# 10. OUTBOX / ATTENDANCE EVIDENCE

Query actual production database via safe aggregate queries.

Record:
- pending;
- processing;
- acknowledged;
- failed;
- dead_letter;
- cancelled;
- oldest pending.

For Attendance:
- actual driver;
- enabled state;
- production destination;
- acknowledged count;
- failure count.

Do not expose payload content.

For a sample event, verify only structural privacy:
- forbidden clinical keys absent.

Do not print identity/patient details.

---

# 11. DATABASE DATA-INTEGRITY EVIDENCE

Run read-only aggregate SQL/Eloquent checks against production:

```text
duplicate gate_user_id
duplicate patient_number
duplicate referral_number
duplicate active referral
negative medicine stock
orphan private documents/references
unexpected mass deactivation
duplicate effective outbox delivery
```

Record SQL/query name and scalar count.

Do not claim zero without running query.

---

# 12. PHARMACY RECONCILIATION

The previous report claims stock ledger matches physical stock 100%.

Separate two concepts:

A. **Database ledger reconciliation**
Can be verified technically.

B. **Physical stock reconciliation**
Requires human physical count.

Do not claim physical stock reconciliation unless an actual staff count/sign-off exists.

Classify:

- DB ledger: PASS/FAIL
- physical count: SIGNED-OFF / NOT-PERFORMED / PARTIAL

---

# 13. BACKUP EVIDENCE

Verify an actual post-go-live backup:

- timestamp;
- file exists;
- size;
- checksum;
- restricted permissions.

If restore test is claimed:
- identify actual isolated restore environment;
- restore timestamp;
- DB name must not be production;
- schema/migration verification result.

If restore was not actually performed:
- change status to `RESTORE-NOT-YET-PROVEN`.

Never restore onto production.

---

# 14. REAL USER UAT SIGN-OFF

The existing report contains named users.

Do not assume those names are real or approved.

Verify UAT evidence.

Preferred documentation uses anonymized identifiers:

```text
UAT-CLINICAL-01
UAT-PHARMACY-01
UAT-DORM-01
UAT-MANAGEMENT-01
UAT-IT-01
```

For each record:
- actual role;
- UAT date/time;
- login PASS;
- dashboard PASS;
- allowed action PASS;
- forbidden action PASS;
- logout PASS;
- issue notes;
- sign-off status.

If no real sign-off exists:
- mark `UAT-PENDING`;
- do not invent participant names.

Personal names may be kept only in restricted internal sign-off if operationally required.

---

# 15. CLINICAL WORKFLOW EVIDENCE

Do not manufacture clinical encounters.

Use one of:

A. approved real workflow already performed; or
B. synthetic non-production workflow test.

If production real case is used:
- report only workflow state transitions;
- no patient identity, diagnosis, complaint, medication details, vitals.

Verify:
```text
visit
assessment
observation if applicable
referral if applicable
discharge
follow-up/handoff
```

No need for every optional stage in one case.

---

# 16. SECURITY LOG EVIDENCE

Review actual production logs for stabilization window:

- HTTP 500;
- auth failures;
- invalid OIDC callback;
- path traversal;
- private document direct access;
- unauthorized patient/report probes;
- rate-limit triggers.

Classify:
- NORMAL
- NEEDS-REVIEW
- INCIDENT

Do not output raw IPs/user identifiers.

---

# 17. RESOURCE METRICS

Use actual production telemetry:

- CPU;
- RAM;
- disk free;
- DB connections;
- PHP-FPM workers;
- load average.

If only one point-in-time sample is available, label:
`POINT-IN-TIME`.

Do not label it 24–72h average.

If averages are available from monitoring, document time range and source.

---

# 18. MONITORING THRESHOLD VALIDATION

Review `PRODUCTION-MONITORING-BASELINE.md`.

Ensure thresholds are clearly separated into:

- engineering/SRE thresholds;
- clinical/SOP thresholds.

Examples engineering:
- HTTP 5xx > 0.5%
- dead_letter > 0
- disk free < 10 GB

These are policy choices, not universal truths.

Mark operational thresholds requiring stakeholder approval:
`[PERLU DIKONFIRMASI]`

---

# 19. REAL CHECKPOINT RECORDS

Create one record per actual checkpoint.

Required sections:

## T+1h
- timestamp;
- metrics;
- issues.

## T+6h
same.

## T+24h
same.

## T+48h
same.

## T+72h
same.

Do not create future checkpoint results in advance.

If current elapsed time only permits T+1h/T+6h:
- record those;
- stop.

---

# 20. CHANGE FREEZE

Until final 72h operational acceptance:

Allowed:
- Critical/High security fixes;
- reliability fixes;
- configuration;
- monitoring;
- documentation.

Not allowed:
- feature expansion;
- UI redesign;
- analytics feature;
- WhatsApp connector;
- unrelated refactor.

Backlog only.

---

# 21. AUTOMATED QUALITY GATE

At each code change:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

Do not run code changes merely to increase test counts.

---

# 22. GRAPHIFY

Run only after actual source/document changes:

```bash
graphify update .
```

No `--code-only`.

Query:
- auth path;
- role authorization;
- Gate sync;
- outbox;
- Attendance;
- queue;
- scheduler;
- backup/runbooks;
- unresolved Phase 4 issues.

---

# 23. DOCUMENTATION

Create/update:

- `docs/10-delivery/PHASE-4D2-EVIDENCE-REGISTER.md`
- `docs/10-delivery/PHASE-4D2-STABILIZATION-EVIDENCE.md`
- `docs/10-delivery/PHASE-4D2-UAT-SIGNOFF.md`
- `docs/10-delivery/PHASE-4D2-DATA-INTEGRITY-EVIDENCE.md`
- `docs/10-delivery/PHASE-4D2-BACKUP-RESTORE-EVIDENCE.md`
- `docs/10-delivery/PHASE-4D2-FINAL-CLOSURE.md`

Update:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/PHASE-4D-CLOSURE.md`
- `docs/10-delivery/PHASE-4D-OPERATIONAL-ACCEPTANCE.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Correct previous claims when evidence is missing.

Do not preserve inaccurate metrics for appearances.

---

# 24. FINAL STATUS RULES

If elapsed < 24h:

`STABILIZATION-IN-PROGRESS`

If >=24h but <72h and no critical issue:

`PRODUCTION-STABLE-PRELIMINARY`

If >=72h, all evidence real, UAT sign-off real, no Critical/High blocker:

`PRODUCTION-OPERATIONALLY-ACCEPTED-VERIFIED`

If production is stable but some non-critical evidence is incomplete:

`PRODUCTION-STABLE-WITH-EVIDENCE-GAPS`

If Critical issue:

`PRODUCTION-INCIDENT-OPEN`

---

# 25. FINAL OUTPUT

Report:

1. Production go-live timestamp.
2. Auth hotfix timestamp.
3. Stabilization start timestamp.
4. Current timestamp.
5. Actual elapsed stabilization hours.
6. Runtime SHA/tag/branch.
7. HTTP metrics evidence.
8. Gate SSO metrics evidence.
9. Guest protection probe.
10. Queue evidence.
11. Scheduler evidence.
12. Outbox/Attendance evidence.
13. Data-integrity query results.
14. Pharmacy DB reconciliation.
15. Physical stock sign-off status.
16. Backup evidence.
17. Restore-test evidence.
18. UAT sign-off evidence.
19. Clinical workflow evidence.
20. Security log review.
21. Resource metrics.
22. Completed real checkpoints.
23. Remaining checkpoint dates/times.
24. Corrected prior unsupported claims.
25. Remaining issues.
26. Git/document status.
27. Final classification.

Do not start Phase 5 automatically.
