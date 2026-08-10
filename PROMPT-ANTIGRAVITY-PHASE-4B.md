# PROMPT ANTIGRAVITY — PHASE 4B
## Staging Integration, End-to-End UAT, Gate SSO Activation, Secure Sync Apply, and Attendance Sandbox

Anda adalah principal Laravel architect, IAM/OIDC engineer, distributed-systems integration engineer, application security engineer, DevOps engineer, QA/UAT architect, privacy engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Claude Opus 4.6 Thinking**, **Claude Sonnet 4.6 Thinking**, atau **Gemini 3.6 Flash High**.

Tujuan Phase 4B:

1. menutup Phase 4A secara final;
2. mengaktifkan Gate SSO hanya pada environment staging;
3. mengaktifkan secure Gate sync apply hanya pada staging;
4. menguji application entitlement nyata dari Gate;
5. menghubungkan Attendance integration ke sandbox/staging Absensi, bukan production;
6. menjalankan end-to-end UAT untuk identity, clinical workflow, discharge, operational outbox, dan attendance disposition;
7. membuktikan rollback, retry, idempotency, observability, dan failure handling;
8. memastikan tidak ada data klinis sensitif keluar ke Absensi;
9. memastikan production flags tetap OFF;
10. berhenti sebelum production rollout.

---

# 1. WAJIB DIBACA

Baca:

- `AGENTS.md`
- `README.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/PHASE-4A-CLOSURE.md`
- `docs/10-delivery/PHASE-4A-RESUME-STATE.md`
- `docs/08-api/GATE-OIDC-CONTRACT.md`
- `docs/07-security/GATE-SSO-SECURITY.md`
- `docs/02-workflows/GATE-LOGIN-AND-ACCESS.md`
- `docs/02-workflows/GATE-USER-SYNC.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/08-api/ATTENDANCE-INTEGRATION-CONTRACT.md`
- `docs/07-security/OPERATIONAL-DATA-SHARING.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Gunakan path aktual bila berbeda.

---

# 2. SAFETY / ENVIRONMENT RULES

1. Jangan mengaktifkan feature flag production.
2. Jangan mengubah production `.env`.
3. Jangan menampilkan secret/token/password.
4. Gunakan staging environment terpisah.
5. Gunakan staging/sandbox Gate client dan Absensi endpoint.
6. Jangan mengirim data pasien nyata jika synthetic/UAT account cukup.
7. Bila UAT menggunakan account nyata, gunakan minimum necessary dan approval eksplisit.
8. Jangan menjalankan destructive migration.
9. Jangan `migrate:fresh` pada staging.
10. Jangan hard delete Person/User/Patient/history.
11. Jangan menyalin production DB penuh ke staging tanpa sanitization.
12. Jangan mengaktifkan webhook production.
13. Production Attendance connector tetap OFF.
14. Jangan menutup Phase 4B jika rollback/failure path belum diuji.

---

# 3. PHASE 4A FINAL SANITY

Jalankan:

```bash
pwd
git branch --show-current
git status
git log --oneline -12
php artisan migrate:status
php artisan route:list
```

Verifikasi:

- commit `991776c` atau commit Phase 4A final ada;
- working tree clean;
- Phase 4A tests baseline lulus;
- production flags OFF;
- no login stub bypass;
- entitlement enforced;
- sync apply idempotent/concurrent-safe;
- deactivation non-destructive;
- medical data untouched by Gate;
- Graphify updated.

Buat:

`docs/10-delivery/PHASE-4A-FINAL-CLOSURE.md`

Status:
- `PASSED`
- `FAILED`

Stop jika FAILED.

---

# 4. PATIENT NUMBER COLLISION HARDENING

Audit perubahan:

```php
substr($id, -10)
```

Pastikan `patient_number` tetap mempunyai:

- database unique constraint;
- collision retry strategy;
- deterministic failure handling;
- MariaDB concurrent creation test.

Jangan mengandalkan probabilitas ULID suffix saja.

Jika collision handling belum ada, perbaiki sebelum staging UAT.

Test minimal:
- 1000 synthetic patient creations;
- forced collision simulation;
- concurrent creation;
- exact one unique patient_number per patient.

---

# 5. STAGING ENVIRONMENT PRECHECK

Dokumentasikan:

- app URL staging;
- HTTPS status;
- reverse proxy;
- trusted proxy settings;
- session domain;
- secure cookie;
- SameSite;
- CSRF;
- queue driver;
- scheduler;
- cache/session backend;
- DB host;
- Gate staging URL;
- Absensi staging/sandbox URL.

Jangan mencetak secret.

Buat:

`docs/10-delivery/PHASE-4B-STAGING-PREFLIGHT.md`

Status per item:
- READY
- NEEDS_FIX
- BLOCKED

---

# 6. STAGING GATE SSO ACTIVATION

Hanya pada staging:

```text
GATE_SSO_ENABLED=true
GATE_CLIENT_DRIVER=http
```

Production tetap:

```text
GATE_SSO_ENABLED=false
```

Verifikasi real flow:

```text
POSKESTREN staging /login
 -> Gate staging authorization
 -> callback
 -> state/nonce validation
 -> token exchange
 -> userinfo
 -> app entitlement
 -> Person/User projection
 -> Laravel session
 -> dashboard
```

Test account categories:

- human staff allowed;
- human teacher allowed;
- student/santri allowed jika app entitlement diberikan;
- technical account denied/handled;
- user no entitlement denied;
- revoked entitlement denied;
- suspended user denied.

Tidak perlu memakai user production bila synthetic Gate staging users tersedia.

---

# 7. OIDC/OAUTH STAGING VERIFICATION

Verifikasi nyata terhadap Gate staging:

- issuer;
- discovery endpoint bila ada;
- JWKS;
- audience;
- callback URL;
- HTTPS;
- state;
- nonce;
- PKCE bila digunakan;
- token expiration;
- clock skew;
- logout behavior;
- invalid/replayed callback.

Buat report:

`docs/10-delivery/PHASE-4B-GATE-SSO-UAT.md`

Jangan simpan token.

---

# 8. APPLICATION ENTITLEMENT UAT

Test matrix:

| Gate Account | Account Active | POSKESTREN Entitlement | Expected |
|---|---|---|---|
| Human staff | yes | allowed | login |
| Human teacher | yes | allowed | login |
| Human user | yes | not_assigned | deny |
| Human user | yes | revoked | deny |
| Human user | suspended | allowed | deny |
| Technical/service | yes | allowed | app-access according policy, not patient eligible |

Pastikan:
- entitlement != clinical permission;
- local policy remains authoritative for medical modules;
- revocation does not delete history.

---

# 9. STAGING SYNC APPLY ACTIVATION

Only staging:

```text
GATE_SYNC_APPLY_ENABLED=true
```

Production remains false.

Run:

1. dry-run;
2. inspect preview;
3. explicit authorized apply;
4. reconciliation.

Test:
- new;
- changed;
- unchanged;
- deactivated;
- conflict;
- duplicate identifier;
- invalid;
- unsupported;
- source_missing only on complete snapshot.

Verify counts before/after.

No medical mutation.

---

# 10. LIVE-LIKE SYNC FAILURE TEST

Simulate:

- Gate timeout;
- page 2 failure;
- malformed payload;
- duplicate page;
- rate limit;
- 500;
- incomplete snapshot;
- reconnect/retry.

Expected:
- no false source_missing deactivation;
- retry safe;
- no duplicate Person/User/Patient;
- failed run clearly marked;
- audit consistent;
- reconciliation available.

---

# 11. ABSENSI STAGING/SANDBOX CONTRACT ACTIVATION

Do NOT use production Absensi.

Create/enable:

```text
ATTENDANCE_INTEGRATION_ENABLED=true
ATTENDANCE_INTEGRATION_DRIVER=sandbox
```

Only on staging.

Production stays:
```text
ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
```

If no sandbox adapter exists, implement `HttpAttendanceSandboxIntegration` or equivalent using config.

No hardcoded URL/secret.

---

# 12. ABSENSI PAYLOAD UAT

Verify exact payload:

Allowed:
- stable Gate user ID;
- disposition type;
- effective date range;
- activity scope;
- source reference;
- event version;
- idempotency/correlation ID.

Forbidden:
- diagnosis;
- ICD;
- complaint narrative;
- vital signs;
- medication;
- allergy;
- assessment;
- referral narrative;
- consultation advice;
- audit log.

Add runtime schema validation plus test against actual serialized payload.

---

# 13. END-TO-END UAT SCENARIOS

Run synthetic scenarios.

## Scenario A — Simple Visit -> Rest

```text
Gate login
 -> patient selected/resolved
 -> visit
 -> assessment
 -> discharge rest
 -> operational handoff
 -> outbox
 -> Absensi sandbox disposition
 -> acknowledgement
```

Verify:
- one downstream effect;
- no sensitive payload;
- audit chain.

## Scenario B — Observation -> Return to Activity

```text
visit
 -> assessment
 -> observation
 -> monitoring
 -> completed
 -> discharge
 -> return_to_activity
 -> attendance sandbox update
```

## Scenario C — Referral

```text
visit
 -> assessment
 -> referral
 -> return
 -> local review
 -> discharge
 -> attendance disposition
```

## Scenario D — Amendment

```text
discharge disposition A
 -> already sent
 -> discharge/activity amendment
 -> superseding outbox event
 -> downstream old disposition superseded
```

No destructive overwrite.

## Scenario E — Gate Revocation

```text
user logged in
 -> Gate entitlement revoked
 -> revalidation/sync
 -> access denied
 -> medical history intact
```

---

# 14. OUTBOX / DELIVERY FAILURE UAT

Test sandbox:

- endpoint timeout;
- 429;
- 500;
- malformed response;
- duplicate request;
- delayed acknowledgement;
- permanent 4xx;
- service unavailable.

Verify:
- retry/backoff;
- no duplicate effect;
- dead-letter after policy;
- manual retry authorized;
- sanitized logs;
- no secrets;
- correlation preserved.

---

# 15. OBSERVABILITY

Implement/verify:

- structured application logs;
- correlation IDs;
- sync run IDs;
- outbox event IDs;
- delivery attempt IDs;
- login failure category;
- entitlement denial category;
- queue failure visibility;
- scheduled command visibility.

No sensitive payload/token.

Create operational health page/report:

- Gate connectivity;
- Gate discovery/JWKS;
- sync last success;
- queue status;
- outbox pending/failed;
- Attendance sandbox connectivity;
- dead-letter count.

Permission restricted.

---

# 16. QUEUE AND SCHEDULER

Verify staging:

- queue worker;
- scheduler;
- retries;
- failed jobs;
- restart behavior.

Do not rely on synchronous request processing for outbound integration if outbox design expects worker.

Test worker restart:
- event pending before restart;
- worker restarted;
- event eventually processed exactly once.

---

# 17. SESSION / REVERSE PROXY HARDENING

Verify staging HTTPS:

- `APP_URL=https://...`;
- trusted proxy configuration;
- `Secure` cookies;
- `HttpOnly`;
- SameSite appropriate for Gate redirect;
- CSRF intact;
- callback URL exact;
- forwarded proto handled correctly;
- no mixed-content redirects.

Test external browser login, not only feature test.

---

# 18. UAT ROLE MATRIX

Test representative roles:

- clinical staff;
- pharmacy staff;
- admin technical;
- management;
- operational dorm;
- homeroom;
- ordinary allowed user.

Verify:
- menus;
- direct URL;
- clinical permissions;
- dashboard visibility;
- reports;
- integration monitor.

Technical admin must not automatically read clinical detail.

---

# 19. REPORT / PRIVACY UAT

Verify management report:
- aggregate only unless explicitly authorized.

Operational dashboard:
- no diagnosis.

Export:
- permission;
- audit;
- minimum necessary.

Test user attempts to manipulate query/filter to access unauthorized record.

---

# 20. UAT EVIDENCE

Create:

`docs/10-delivery/PHASE-4B-UAT-EVIDENCE.md`

Include:
- scenario;
- test account type;
- environment;
- expected;
- actual;
- screenshots reference;
- request correlation ID;
- no secrets;
- pass/fail.

Do not embed real patient PHI.

---

# 21. SECURITY TESTS

Run/verify:

- open redirect;
- callback replay;
- invalid state;
- entitlement bypass;
- IDOR;
- privilege escalation;
- Gate role -> clinical role leakage;
- sync apply unauthorized;
- outbox retry unauthorized;
- report data leakage;
- attendance payload leakage;
- path traversal;
- session fixation.

---

# 22. FULL TEST SUITE

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

MariaDB concurrency tests must run, not skip.

Also run staging smoke tests separately and report.

---

# 23. GRAPHIFY

Update graph without `--code-only`.

Query:
- Gate staging login path;
- entitlement enforcement path;
- sync apply source_missing guard;
- Person/User/Patient duplicate paths;
- Discharge -> Outbox -> Absensi sandbox;
- superseding attendance event;
- privacy forbidden payload;
- queue/retry/dead-letter path;
- technical admin -> clinical data leakage;
- production connector accidental activation;
- hardcoded staging URL/secret;
- missing tests.

Update docs mapping.

---

# 24. FEATURE FLAG GUARANTEE

Document exact environment matrix:

| Feature | Testing | Staging | Production |
|---|---|---|---|
| Gate SSO | fake/true | real/true | false |
| Gate sync apply | test/true | true | false |
| Gate webhook | false | false unless explicitly tested | false |
| Attendance | fake | sandbox | fake/off |
| Break glass | false | false unless explicitly approved | false |

Production config must remain untouched.

---

# 25. DOCUMENTATION

Create/update:

- `docs/10-delivery/PHASE-4A-FINAL-CLOSURE.md`
- `docs/10-delivery/PHASE-4B-STAGING-PREFLIGHT.md`
- `docs/10-delivery/PHASE-4B-GATE-SSO-UAT.md`
- `docs/10-delivery/PHASE-4B-UAT-EVIDENCE.md`
- `docs/10-delivery/PHASE-4B-CLOSURE.md`
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

# 26. GIT

Before:

```bash
git status
git log --oneline -8
```

If clean:

```bash
git tag -a phase-4a-complete -m "Phase 4A Gate SSO and secure sync foundation complete"
```

After all code/docs/test changes:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "test(staging): complete Phase 4B Gate and attendance integration UAT"
git status
```

Target clean.

---

# 27. FINAL OUTPUT

Report:

1. Phase 4A final closure.
2. Patient number collision hardening result.
3. Staging preflight.
4. Gate staging connection result.
5. Real SSO flow result.
6. Entitlement matrix result.
7. Sync dry-run/apply result.
8. Incomplete snapshot/failure behavior.
9. Attendance sandbox result.
10. End-to-end UAT scenarios.
11. Outbox failure/retry/dead-letter result.
12. Privacy payload verification.
13. Queue/scheduler result.
14. Reverse proxy/session result.
15. Role matrix result.
16. Full tests/assertions/skips.
17. MariaDB concurrency.
18. Graphify findings.
19. Remaining blockers.
20. Production flag verification.
21. Git commit/status.
22. GO/NO-GO for Phase 4C deployment hardening.

---

# 28. STOP CONDITIONS

NO-GO if:

- patient_number uniqueness relies only on ULID suffix probability;
- real Gate staging flow cannot validate identity/entitlement;
- source_missing can trigger after partial snapshot;
- sync apply creates duplicate Person/User/Patient;
- Gate role escalates clinical permissions;
- sensitive clinical data reaches Absensi;
- sandbox idempotency/retry/dead-letter fails;
- production flags are modified;
- queue/scheduler cannot reliably process outbox;
- critical security/UAT tests fail.

If PASSED:
- commit;
- working tree clean;
- production remains OFF;
- stop;
- wait for explicit approval for Phase 4C.
