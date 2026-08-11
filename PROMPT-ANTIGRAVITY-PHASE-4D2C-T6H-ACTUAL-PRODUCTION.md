# PROMPT ANTIGRAVITY — PHASE 4D2C
## T+6h ACTUAL PRODUCTION SERVER VERIFICATION
## No Local-Workstation Evidence Substitution

Gunakan **Claude Opus 4.6 Thinking**.

Proyek: **SABIRA POSKESTREN Health**

Konteks:
- Stabilization start: `2026-08-10 21:53:58 +0700`
- T+6h eligible sejak `2026-08-11 03:53:58 +0700`
- Previous evidence collection was executed on:
  - `Darwin 25.5.0 arm64`
  - local workstation project directory
  - local MariaDB port `8186`
  - localhost Laravel runtime
- Production hostname `https://poskestren.sabira.id` was not reachable from that workstation.

Therefore:
**DO NOT LABEL WORKSTATION OUTPUT AS PRODUCTION EVIDENCE.**

Target status:
- `T+6H-PASS` only with actual production-server evidence.
- otherwise `PRODUCTION-EVIDENCE-NOT-AVAILABLE`.

## 1. HARD SOURCE-OF-TRUTH RULE

A result may be labeled `PRODUCTION-SERVER` only when command execution is proven to occur on the actual production host.

A result may be labeled `PRODUCTION-DATABASE` only when connection is proven to be the database used by the active production runtime.

A local database named `poskestren_sabira` does NOT automatically make it production.

Allowed labels:
- `PRODUCTION-SERVER`
- `PRODUCTION-DATABASE`
- `PRODUCTION-LOG`
- `PRODUCTION-EXTERNAL-PROBE`
- `HUMAN-UAT`
- `LOCAL-DEV`
- `TEST-ENV`
- `UNVERIFIED`

Never relabel local evidence.

## 2. WALL CLOCK

At start of run:
```bash
date
```

Calculate from:
```text
STABILIZATION_START_AT=2026-08-10 21:53:58 +0700
```

Record actual elapsed hours.

If elapsed >= 6h:
- T+6h eligible.

Do not backfill future T+24/T+48/T+72 checkpoints.

## 3. CONNECT TO ACTUAL PRODUCTION SERVER

Use the project's approved operational access path, e.g.:
```bash
ssh <production-host>
```

or the existing SABIRA deployment/ops mechanism if that is the official server-access route.

Do not invent credentials.

If production server cannot be accessed:
- STOP.
- final status `PRODUCTION-EVIDENCE-NOT-AVAILABLE`.
- list exactly which evidence remains unavailable.

Do not continue using local workstation as substitute.

## 4. PROVE PRODUCTION HOST IDENTITY

On the actual production server execute:
```bash
date
hostname
hostnamectl 2>/dev/null || true
uname -a
pwd
```

Expected production is Linux according existing deployment documentation.

Also:
```bash
readlink -f /var/www/poskestren/current
cd /var/www/poskestren/current
git rev-parse HEAD
git status --short
```

If actual deploy path differs, use documented path.

Record:
```text
HOSTNAME=
OS=
ACTIVE_RELEASE=
RUNTIME_SHA=
WORKTREE_STATUS=
```

Only after this proof may further results be labeled `PRODUCTION-SERVER`.

## 5. VERIFY EFFECTIVE PRODUCTION CONFIG SAFELY

Do not print secrets.

Use Laravel/Tinker to print only safe keys:
```text
APP_ENV
APP_DEBUG
APP_URL
GATE_SSO_ENABLED
GATE_CLIENT_DRIVER
GATE_SYNC_APPLY_ENABLED
ATTENDANCE_INTEGRATION_ENABLED
ATTENDANCE_INTEGRATION_DRIVER
BREAK_GLASS_ENABLED
```

Expected:
- APP_ENV=production
- APP_DEBUG=false
- Gate production flags according approved live state
- no fake driver if integration is declared live
- break glass false unless explicitly approved.

## 6. PROVE DATABASE IS THE ACTIVE PRODUCTION DATABASE

From the active production release:

Use safe Laravel config/DB query to record:
```text
DB_DRIVER
DB_HOST_CATEGORY
DB_PORT
DB_DATABASE
DB_VERSION
```

Do not print username/password.

Then verify the Laravel process is using that database.

Only then label aggregate queries:
`PRODUCTION-DATABASE`.

If app points to a different DB than expected:
- STOP;
- status `PRODUCTION-DATABASE-MISMATCH`.

## 7. ACTUAL EXTERNAL PROBE

From production server and, if possible, from a separate external client:
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

Expected:
- protected guest routes -> 302/401/403;
- `/login` safe public entry;
- health/ready 200.

Do not use localhost as production external proof.

## 8. NGINX / WEB SERVER PROOF

On production:
```bash
systemctl status nginx --no-pager
nginx -T 2>/dev/null | grep -n "server_name\|access_log\|error_log"
```

or actual web server equivalent.

Verify production vhost includes:
`poskestren.sabira.id`.

Record actual access/error log paths.

## 9. REAL HTTP METRICS FOR T+0 TO CURRENT

Use production access logs.

Calculate:
- total requests;
- 2xx;
- 3xx;
- 4xx;
- 5xx;
- 5xx rate.

Time range:
from `2026-08-10 21:53:58 +0700`
to current server time.

If access logs do not contain latency:
`LATENCY=NOT-INSTRUMENTED`.

If they contain request time:
calculate p50/p95/p99.

Do not fabricate metrics.

## 10. AUTH / SECURITY LOG METRICS

Use production Laravel logs/audit DB.

Aggregate:
- Gate login success;
- Gate login failure;
- entitlement denied;
- invalid state;
- invalid nonce;
- token failure;
- duplicate identity projection;
- unauthorized route probes;
- HTTP 500/fatal exception;
- private-document access denial.

No raw PII, IP, tokens, or PHI in report.

## 11. QUEUE WORKER — REAL PROCESS PROOF

Production server:
```bash
supervisorctl status
```

or actual process manager:
```bash
systemctl list-units --type=service | grep -i queue
```

Then:
```bash
php artisan queue:failed
```

Record:
- worker count/names;
- state;
- failed jobs;
- restarts if available.

Do not claim pending queue count unless backend can prove it.

## 12. SCHEDULER — REAL OS PROOF

On production:
```bash
php artisan schedule:list
crontab -l
```

Also inspect system-wide cron if applicable:
```bash
grep -R "schedule:run" /etc/cron* 2>/dev/null
```

or systemd timers.

Verify:
- exactly one execution source;
- command references active production release or stable symlink;
- execution occurred after auth hotfix if logs/heartbeat available.

If execution proof unavailable:
`SCHEDULER-EXECUTION-NOT-INSTRUMENTED`.

## 13. PRODUCTION DATABASE INTEGRITY QUERIES

Run read-only aggregate queries only after production DB identity is proven.

Required:
```text
duplicate gate_user_id
duplicate patient_number
duplicate referral_number
duplicate active referral
negative medicine stock
orphan referral/version references
orphan discharge/version references
deactivated user count
total user count
failed jobs
outbox by status
```

Do not expose record values.

For "unexpected mass deactivation":
- do not equate `deactivated_count=0` automatically with correctness;
- compare with Gate sync expectations if available.

## 14. OUTBOX / ATTENDANCE

From production DB/config:

Record:
- integration enabled?
- driver?
- pending;
- processing;
- acknowledged;
- failed;
- dead_letter;
- oldest pending age.

If Attendance declared live:
- production driver must not be fake/sandbox unless intentionally designed.
- verify destination host safely (host only, no credential/query).
- verify schema keys for one event without values.
- forbidden clinical keys must be absent.

## 15. BACKUP ACTUAL ARTIFACT

Do not cite runbook as backup evidence.

Find an actual post-hotfix production backup artifact.

Record:
- created timestamp;
- file size;
- SHA-256;
- permission mode;
- DB dump reference;
- private-storage backup reference.

If none:
`BACKUP-EVIDENCE-MISSING`.

Do not print backup contents.

## 16. RESTORE TEST

Keep:
`RESTORE-NOT-YET-PROVEN`

unless an actual isolated restore is executed and evidenced.

Do not run restore merely because this checkpoint asks for evidence unless it fits approved maintenance procedure.

## 17. RESOURCE TELEMETRY — ACTUAL PRODUCTION ONLY

On production:
```bash
uptime
free -h
df -h
ps aux --sort=-%mem | head -n 20
```

Optionally DB connection count.

Label this:
`POINT-IN-TIME`.

Do not call it an average unless monitoring history exists.

## 18. HUMAN UAT

Do not reinterpret automated tests as HUMAN-UAT.

If actual human sign-off documentation exists:
- cite anonymized role ID and timestamp.

Otherwise:
`UAT-PENDING`.

## 19. CLINICAL WORKFLOW

If no real production clinical transaction has occurred:
`PRODUCTION-CLINICAL-WORKFLOW-PENDING`.

Automated tests remain `TEST-ENV`.

Do not fabricate a patient encounter.

## 20. CORRECT PREVIOUS MISLABELS

Update Phase 4D2 documentation so that:
- workstation Darwin evidence -> `LOCAL-DEV`
- local MariaDB evidence -> `LOCAL-DEV` unless proven production DB used remotely
- localhost curl -> `LOCAL-DEV`
- `schedule:list` alone -> not proof of production cron
- runbook alone -> not backup artifact
- automated tests -> not production workflow/UAT

Correct historical docs transparently.

## 21. T+6H CHECKPOINT DECISION

`T+6H-PASS` requires at minimum:
- actual production server identity proven;
- production runtime SHA known;
- health/ready pass on real hostname;
- guest auth protection pass;
- no critical 5xx/security errors;
- production DB integrity no critical anomaly;
- queue process healthy;
- scheduler configuration valid;
- no dead-letter critical integration issue.

Evidence gaps such as physical stock or restore drill may remain tracked and do not automatically fail T+6h.

If actual production server cannot be reached:
`PRODUCTION-EVIDENCE-NOT-AVAILABLE`

If critical issue:
`PRODUCTION-INCIDENT-OPEN`

## 22. DOCUMENTATION

Append/create:
- `docs/10-delivery/PHASE-4D2B-PRODUCTION-SERVER-PROOF.md`
- `docs/10-delivery/PHASE-4D2-STABILIZATION-EVIDENCE.md`
- `docs/10-delivery/PHASE-4D2-EVIDENCE-REGISTER.md`

Update previous source labels.

Do not pre-fill T+24h or later.

## 23. FINAL OUTPUT

Report:
1. Current actual time.
2. Elapsed hours.
3. Production connection method.
4. Actual production hostname/OS.
5. Active release path.
6. Runtime SHA.
7. Effective safe config.
8. Active production DB identity.
9. External production probes.
10. Web-server status.
11. HTTP request/error metrics.
12. Gate/auth metrics.
13. Queue process proof.
14. Scheduler OS proof.
15. DB integrity counts.
16. Outbox/Attendance production metrics.
17. Backup artifact evidence.
18. Restore status.
19. Production resource telemetry.
20. Human UAT evidence.
21. Clinical workflow evidence.
22. Corrected local-vs-production labels.
23. Remaining evidence gaps.
24. T+6h checkpoint status.
25. Next checkpoint time.

Do not start Phase 5.
