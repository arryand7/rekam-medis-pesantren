# PROMPT ANTIGRAVITY — PHASE 4D
## Post-Go-Live Stabilization, Operational Acceptance, Security Watch, Data Quality, and Production Baseline

Anda adalah principal Laravel production architect, SRE/DevOps engineer, health-information-system operations engineer, IAM/security engineer, database reliability engineer, QA/UAT lead, dan operational acceptance auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Claude Opus 4.6 Thinking** atau **Claude Sonnet 4.6 Thinking**.

STATUS AWAL:

- Core application live production.
- Gate SSO production live.
- Authentication hotfix classification: `AUTH-HOTFIX-PRODUCTION-VERIFIED`.
- Guest production routes tanpa cookie mengarah ke `/login`.
- Role-aware dashboard tervalidasi.
- `Gate::before()` hanya allow exact local permission dan defer ke Policy.
- Full suite terakhir: 198 tests, 796 assertions, 0 failures, 0 skipped.
- Production feature:
  - `GATE_SSO_ENABLED=true`
  - `GATE_CLIENT_DRIVER=http`
  - `BREAK_GLASS_ENABLED=false`
- Reported active runtime SHA: `338451f`.
- Reported atomic release build reference: `58e6205`.
- Git branch reported: `resume/phase-4a-claude-opus`.

TUJUAN PHASE 4D:

1. menormalkan provenance release/Git setelah security incident;
2. melakukan production stabilization watch 24–72 jam;
3. melakukan operational UAT dengan user nyata yang berwenang;
4. memvalidasi Gate SSO, role, session, sync, Attendance, queue, scheduler, outbox, private storage, backup, dan data integrity dari traffic production aktual;
5. membangun operational acceptance checklist;
6. menetapkan baseline monitoring dan incident thresholds;
7. memastikan SOP harian POSKESTREN siap;
8. menutup Phase 4 sebagai `PRODUCTION-OPERATIONALLY-ACCEPTED`;
9. tidak menambah fitur klinis baru pada fase ini.

---

# 1. SAFETY RULES

1. Jangan menampilkan `.env`, password, token, OAuth secret, private key, DB credential.
2. Jangan menggunakan data pasien untuk test jika synthetic/approved scenario cukup.
3. Jangan destructive reset.
4. Jangan `migrate:fresh`, `db:wipe`, DROP, hard delete.
5. Jangan force push.
6. Jangan mengubah role/entitlement user nyata hanya untuk test tanpa approval.
7. Jangan mematikan auth/Policy/rate-limit untuk troubleshooting.
8. Jangan mengirim clinical payload ke Absensi.
9. Jangan menganggap satu snapshot monitoring cukup untuk operational acceptance.
10. Jika Critical security/data-integrity issue ditemukan, STOP normal acceptance dan buka incident.

---

# 2. PHASE AUTH HOTFIX FINAL CLOSURE

Baca:

- `docs/10-delivery/PRODUCTION-AUTH-RUNTIME-INCIDENT.md`
- `docs/10-delivery/PRODUCTION-AUTH-RUNTIME-VERIFICATION.md`
- `docs/10-delivery/PRODUCTION-AUTH-HOTFIX-ROLLOUT.md`
- `docs/10-delivery/PRODUCTION-AUTH-HOTFIX-VERIFICATION.md`
- `docs/10-delivery/PRODUCTION-AUTH-EXPOSURE-REVIEW.md`
- `docs/10-delivery/PHASE-4C2-FINAL-STATUS.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `plans/KNOWN-ISSUES.md`

Verify current production still passes:

```bash
curl -skI https://poskestren.sabira.id/
curl -skI https://poskestren.sabira.id/dashboard
curl -skI https://poskestren.sabira.id/patients
curl -skI https://poskestren.sabira.id/login
```

Expected:
- protected -> 302 login/Gate;
- `/login` safe entry.

---

# 3. RELEASE PROVENANCE RECONCILIATION — HIGH PRIORITY

The previous report contains:

```text
Atomic release build reference: 58e6205
Active runtime SHA: 338451f
Branch: resume/phase-4a-claude-opus
```

Do not assume this is harmless.

Determine exact relationship:

```bash
git show --no-patch --oneline 58e6205
git show --no-patch --oneline 338451f
git merge-base --is-ancestor 58e6205 338451f
git diff --name-status 58e6205..338451f
git log --oneline --decorate --graph -15
```

On production runtime:

```bash
readlink -f /var/www/poskestren/current
cd /var/www/poskestren/current
git rev-parse HEAD
git status --short
```

Classify:

- `RUNTIME-SHA-CONSISTENT`
- `DOCS-ONLY-DESCENDANT`
- `RUNTIME-SHA-MISMATCH`
- `UNTRACKED-RUNTIME-MUTATION`

Required:
- deployed source must be traceable to a committed SHA;
- working tree clean;
- no code edited directly inside active production release.

If `338451f` is merely documentation descendant of code release, document precisely.
If runtime source differs unexpectedly, STOP and reconcile.

---

# 4. NORMALIZE GIT / RELEASE BRANCH

Production should not permanently depend on an abandoned recovery branch without an explicit decision.

Inspect whether:

```text
resume/phase-4a-claude-opus
```

contains commits not merged into canonical production branch (`master`/`main` according repository).

Do not blindly merge.

Create comparison:

```bash
git log --oneline master..resume/phase-4a-claude-opus
git diff --stat master...resume/phase-4a-claude-opus
```

or actual canonical branch.

If hotfix commits are not on canonical branch:

1. verify clean history;
2. merge using normal Git process;
3. rerun quality gates;
4. tag production verified commit.

Recommended tag after confirmation:

```text
poskestren-production-auth-verified-20260810
```

Do not tag an ambiguous SHA.

---

# 5. LOGOUT ROUTE REVIEW

Previous route matrix lists `/logout` among public routes.

Audit exact route and middleware.

Preferred:
- logout is POST;
- CSRF protected;
- authenticated session expected;
- guest request does not cause side effects;
- no GET logout CSRF weakness.

If currently intentionally public but POST+CSRF-safe, document rationale.
If GET logout or CSRF bypass exists, harden it.

Add tests:
- guest cannot exploit logout route;
- CSRF behavior correct;
- authenticated logout invalidates session.

---

# 6. 24–72 HOUR STABILIZATION WINDOW

Establish a production stabilization period.

Recommended checkpoints:
- T+1 hour;
- T+6 hours;
- T+24 hours;
- T+48 hours;
- T+72 hours.

At each checkpoint collect non-sensitive metrics:

## HTTP
- request count;
- HTTP 4xx;
- HTTP 5xx;
- p50/p95 response time if available.

## Gate SSO
- successful logins;
- failed logins by safe category;
- entitlement denies;
- callback/state/nonce failures;
- duplicate projection errors.

## Queue
- active workers;
- pending jobs;
- failed jobs;
- retry count.

## Outbox
- pending;
- processing;
- acknowledged;
- failed;
- dead_letter;
- oldest pending age.

## Database
- connections;
- slow query indicators;
- lock/deadlock count if available;
- disk size/growth.

## Storage
- free disk;
- private storage writable;
- backup size trend.

No PHI in metrics.

Create:
`docs/10-delivery/PHASE-4D-STABILIZATION-LOG.md`

---

# 7. AUTHENTICATION / AUTHORIZATION WATCH

During stabilization actively verify:

- guest cannot access protected resources;
- technical admin remains blocked from clinical data;
- dorm/homeroom only operational minimum necessary;
- management only aggregate data;
- clinical staff accesses intended workflows;
- ordinary entitled user does not default to admin;
- revoked/no-entitlement access denied.

Audit actual 403/302 patterns for anomalies.

Do not treat repeated unauthorized probing as harmless if abnormal.

---

# 8. REAL USER OPERATIONAL UAT

Use a small controlled group of actual authorized operators.

Suggested representatives:

- 1 health/clinical staff;
- 1 pharmacy staff;
- 1 dorm supervisor;
- 1 management user;
- 1 technical admin.

Do not require all roles if organization does not actually have them.

For each record:

- Gate login;
- dashboard destination;
- allowed menu;
- forbidden menu;
- representative workflow;
- logout;
- usability issue;
- permission issue.

No unnecessary medical data entry.

Create:
`docs/10-delivery/PHASE-4D-OPERATIONAL-UAT.md`

Status:
- PASS
- PASS-WITH-FOLLOW-UP
- FAIL

---

# 9. CLINICAL WORKFLOW PRODUCTION-SAFE ACCEPTANCE

Verify representative workflow without generating fake production medical history unnecessarily.

Where approved, use actual operational transaction under supervision.

Validate:

- patient lookup;
- visit intake;
- assessment;
- vitals;
- observation if naturally needed;
- medicine ordering/admin if naturally needed;
- referral if naturally needed;
- discharge;
- follow-up;
- operational handoff.

Do not manufacture a referral/emergency merely for test.

Use existing completed real workflow for audit where possible.

---

# 10. PHARMACY / STOCK INTEGRITY

Read-only/invariant checks:

- negative stock = 0;
- ledger reconciliation;
- expired/quarantined batch excluded;
- medication administration maps to stock movement;
- reversal consistent;
- no duplicate stock issue.

Create production-safe reconciliation summary without patient data.

---

# 11. IDENTITY / GATE SYNC DATA QUALITY

Review production sync runs:

- source total;
- mapped;
- new;
- changed;
- deactivated;
- source_missing;
- conflicts;
- invalid;
- unsupported.

Look for:
- unusual mass deactivation;
- duplicate `gate_user_id`;
- Person/User duplicates;
- duplicate Patient;
- unmapped users;
- stale identity.

Do not auto-fix conflicts without authorization.

---

# 12. ATTENDANCE PRODUCTION ACCEPTANCE

If Attendance is production-enabled:

Verify recent events:

- payload privacy;
- idempotency;
- ack;
- supersede/revoke behavior;
- delivery latency;
- retry;
- dead-letter.

Ensure forbidden clinical fields remain absent.

If Attendance is still OFF:
- document intentional state;
- do not enable merely for Phase 4D.

---

# 13. FOLLOW-UP / OPERATIONAL HANDOFF ACCEPTANCE

Verify:

- due follow-ups appear;
- overdue logic works;
- internal handoff recipient sees minimum necessary;
- acknowledgements work;
- no clinical narrative leak;
- cancelled/amended handoff history retained.

---

# 14. REPORTING / MANAGEMENT PRIVACY ACCEPTANCE

Verify management dashboard/report:
- aggregates only unless explicit permission;
- no patient drill-down by default;
- date filters correct;
- Asia/Jakarta timezone;
- exports audited;
- direct URL protections.

---

# 15. BACKUP OPERATIONAL VERIFICATION

Verify at least one post-go-live backup job or manual backup:

- DB backup timestamp;
- non-zero;
- checksum;
- private document backup;
- restricted permission;
- retention policy.

Do not expose backup contents.

Preferred:
restore latest production backup into an isolated validation database/environment.

Verify:
- dump imports;
- migration status;
- app can boot;
- private documents manifest matches.

Never restore onto production.

---

# 16. QUEUE / SCHEDULER RESILIENCE

Verify:

- Supervisor/systemd worker active;
- scheduler fires once;
- no duplicate scheduler;
- failed jobs visible;
- worker restart recovers pending job;
- outbox resumes;
- no duplicate downstream effect.

Use safe event/test mechanism.

---

# 17. RESOURCE / PERFORMANCE BASELINE

Capture:

- CPU;
- RAM;
- disk;
- DB connections;
- queue latency;
- dashboard p50/p95 if available;
- report query duration;
- top slow queries without PHI.

Avoid destructive load test.

Define warning thresholds.

---

# 18. SECURITY LOG REVIEW

Review:

- failed auth;
- invalid OIDC callback;
- state/nonce failure;
- 403 spikes;
- path traversal attempts;
- direct private document attempts;
- suspicious `/patients`, `/reports`, `/users` probes;
- repeated rate-limit hits.

Sanitize reporting.

Classify if escalation needed.

---

# 19. DATA INTEGRITY CHECKPOINT

Read-only production assertions:

```text
duplicate gate_user_id = 0
duplicate patient_number = 0
duplicate referral_number = 0
duplicate active referral invariant = 0
negative medicine stock = 0
orphan private documents = 0
unexpected mass deactivation = 0
outbox duplicate effective delivery = 0
```

If non-zero:
- investigate;
- do not silently repair;
- create incident/change record.

---

# 20. DATABASE / INDEX / QUERY REVIEW

Use actual production query patterns.

Check:
- N+1 regressions;
- missing indexes;
- long-running report queries;
- lock contention;
- deadlocks;
- table growth.

Any index addition must be separately reviewed for production safety.

Do not optimize blindly.

---

# 21. ERROR BUDGET / OPERATIONAL THRESHOLDS

Define initial operational thresholds, configurable to environment.

Examples:
- HTTP 5xx rate;
- failed Gate login rate;
- queue failed jobs;
- outbox dead-letter count;
- oldest pending outbox age;
- disk free percentage;
- DB connection saturation;
- backup age.

Do not invent clinical thresholds.

Create:
`docs/10-delivery/PRODUCTION-MONITORING-BASELINE.md`

---

# 22. DAILY POSKESTREN OPERATIONS SOP

Create operational SOP covering:

## Start of day
- health/readiness;
- queue;
- scheduler;
- failed jobs;
- Gate connectivity;
- outbox/dead-letter;
- follow-up due;
- medicine stock alerts.

## During operations
- patient intake;
- medical visit;
- observation;
- medication;
- referral;
- discharge;
- privacy;
- correction workflow.

## End of day
- unfinished visits;
- observation still active;
- pending referral;
- pending handoff;
- failed integration;
- backup status.

Create:
`docs/10-delivery/POSKESTREN-DAILY-OPERATIONS-SOP.md`

Do not invent medical SOP content; mark `[PERLU DIKONFIRMASI]`.

---

# 23. INCIDENT ESCALATION SOP

Create concise matrix:

- Gate unavailable;
- application unavailable;
- DB unavailable;
- queue failed;
- Attendance unavailable;
- storage full;
- suspicious auth access;
- suspected medical-data exposure;
- backup failure.

For each:
- severity;
- immediate containment;
- owner;
- escalation;
- rollback/fallback.

Do not invent named personnel unless documented.

---

# 24. TRAINING / USER ACCEPTANCE

Create role-based training checklist:

- health staff;
- pharmacy;
- dorm;
- management;
- technical admin.

Include:
- login/logout;
- permissions;
- core workflow;
- privacy;
- correction;
- escalation.

Record acceptance/sign-off without sensitive personal information.

---

# 25. PRODUCTION CHANGE FREEZE

During Phase 4D stabilization:

Avoid unrelated feature development/deployment.

Only allow:
- Critical/High bug/security fixes;
- operational configuration fixes;
- documentation;
- monitoring.

If a feature request emerges, add to Phase 5 backlog, not current production stabilization.

---

# 26. AUTOMATED TEST / CI RELEASE GATE

Ensure CI/release gate always includes:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
```

Mandatory regression suites:
- auth runtime protection;
- Gate SSO;
- role isolation;
- sync concurrency;
- medical workflow;
- pharmacy;
- referral;
- discharge;
- outbox/privacy.

Do not deploy if Critical suite fails/skips.

---

# 27. GRAPHIFY

Run:

```bash
graphify update .
```

No `--code-only`.

Query:
- production auth path;
- role dashboards;
- clinical permissions;
- private documents;
- Gate sync;
- Attendance;
- outbox;
- queue;
- scheduler;
- backup docs/code mapping;
- production runtime branch/provenance;
- remaining TODO/known issues;
- missing operational tests.

Update mapping.

---

# 28. DOCUMENTATION

Create:

- `docs/10-delivery/PHASE-4D-STABILIZATION-LOG.md`
- `docs/10-delivery/PHASE-4D-OPERATIONAL-UAT.md`
- `docs/10-delivery/PRODUCTION-MONITORING-BASELINE.md`
- `docs/10-delivery/POSKESTREN-DAILY-OPERATIONS-SOP.md`
- `docs/10-delivery/PHASE-4D-OPERATIONAL-ACCEPTANCE.md`
- `docs/10-delivery/PHASE-4D-CLOSURE.md`

Update:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `docs/10-delivery/INCIDENT-ROLLBACK-RUNBOOK.md`
- security docs
- integration docs
- test matrix
- known issues
- Graphify mapping.

---

# 29. FINAL QUALITY GATE

Run on test environment:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

No critical skips.

Production validation remains read-only or normal approved operations.

---

# 30. GIT / RELEASE NORMALIZATION

After provenance and canonical branch are reconciled:

- ensure hotfix + docs exist on canonical production branch;
- tag only the verified production commit;
- do not force rewrite history.

Suggested tag:
`poskestren-production-stable-v1`

Only create if exact runtime/code commit is unambiguous.

---

# 31. FINAL STATUS

Use one:

### `PRODUCTION-OPERATIONALLY-ACCEPTED`
24–72h stabilization acceptable, user UAT pass, backups and operations verified, no Critical blocker.

### `PRODUCTION-STABLE-WITH-FOLLOW-UP`
Core stable but non-critical operational issues remain.

### `PRODUCTION-INCIDENT-OPEN`
Critical security/data/reliability issue exists.

### `PRODUCTION-ROLLBACK-REQUIRED`
System should not remain on current release.

---

# 32. FINAL OUTPUT

Report:

1. Auth hotfix final closure.
2. Runtime/release SHA provenance result.
3. Canonical Git branch normalization.
4. Logout route review.
5. Stabilization window metrics.
6. Gate SSO production metrics.
7. Role/permission acceptance.
8. Operational user UAT.
9. Clinical workflow acceptance.
10. Pharmacy reconciliation.
11. Identity sync quality.
12. Attendance integration acceptance.
13. Follow-up/handoff acceptance.
14. Reporting/privacy acceptance.
15. Backup/restore result.
16. Queue/scheduler resilience.
17. Resource/performance baseline.
18. Security log review.
19. Data integrity assertions.
20. Monitoring thresholds.
21. Daily operations SOP.
22. Incident SOP.
23. Training/sign-off.
24. Full tests.
25. Graphify findings.
26. Remaining issues/backlog.
27. Git/tag/runtime status.
28. Final classification.

Do not start Phase 5 automatically.
