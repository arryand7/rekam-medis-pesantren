# PROMPT ANTIGRAVITY — RESUME PHASE 4A WITH CLAUDE OPUS 4.6 (THINKING)
## Safe Handoff from Interrupted Gemini 3.6 Flash Execution
## Real Gate SSO, Secure Sync Apply, Application Entitlement, Identity Production Hardening

Anda adalah principal Laravel architect, IAM/OIDC security engineer, application security engineer, distributed systems engineer, MariaDB concurrency reviewer, privacy engineer, dan technical documentation auditor.

Model yang digunakan: **Claude Opus 4.6 (Thinking)**.

Proyek: **SABIRA POSKESTREN Health**
Owner: **Ryand Arifriantoni**
Fase aktif: **PHASE 4A — Real Gate SSO, Secure User Sync Apply, Application Entitlement Enforcement, and Identity Production Hardening**

Eksekusi Phase 4A sebelumnya dijalankan oleh Gemini 3.6 Flash High tetapi berhenti karena usage limit setelah sebagian perubahan diterapkan.

TUGAS UTAMA ANDA:
**LANJUTKAN dari state repository saat ini. JANGAN mengulang Phase 4A dari awal.**

Spesifikasi sumber utama:
- `PROMPT-ANTIGRAVITY-PHASE-4A.md` jika tersedia di project/local workspace;
- dokumentasi existing project;
- implementation aktual yang sudah dibuat Gemini;
- acceptance criteria Phase 4A yang dijabarkan di bawah.

---

# 1. PRINSIP HANDOFF WAJIB

1. Jangan menolak/reject perubahan parsial Gemini.
2. Jangan menjalankan:
   - `git reset --hard`
   - `git clean -fd`
   - `git checkout -- .`
   - `git restore .`
   - `git stash drop`
3. Jangan menghapus file parsial hanya karena belum selesai.
4. Jangan membuat ulang migration/model/service/controller yang sudah ada sebelum inspeksi.
5. Jangan membuat migration duplikat dengan tujuan schema yang sama.
6. Jangan mengubah migration yang sudah pernah applied.
7. Migration yang belum applied boleh diperbaiki setelah statusnya dibuktikan.
8. Jangan mengarang hasil implementasi Gemini.
9. Jangan mempercayai komentar/TODO sebagai bukti implementasi.
10. Jangan menampilkan `.env`, OAuth secret, client secret, tokens, private keys, password, atau credential.
11. Jangan mengaktifkan production integration secara otomatis.
12. Jangan mengubah atau menghapus medical history.
13. Jangan mengubah health profile dari payload Gate.
14. Jangan lanjut ke Phase 4B.
15. Jangan menyatakan Phase 4A selesai sebelum actual tests lulus.

---

# 2. RECOVERY PREFLIGHT — READ ONLY FIRST

Sebelum menulis kode apa pun, jalankan:

```bash
pwd
git branch --show-current
git status --short
git status
git diff --stat
git diff --check
git diff --name-status
git log --oneline -12
```

Kemudian:

```bash
php artisan about
php artisan migrate:status
php artisan route:list
```

Temukan file terkait Phase 4A:

```bash
find app config database routes resources tests docs -maxdepth 6 \
  \( \
    -iname '*gate*' -o \
    -iname '*oidc*' -o \
    -iname '*oauth*' -o \
    -iname '*auth*' -o \
    -iname '*entitlement*' -o \
    -iname '*identity*' -o \
    -iname '*sync*' \
  \) -print
```

Periksa khusus:

- staged files;
- unstaged files;
- untracked files;
- partial migrations;
- partial controllers;
- Gate HTTP/OIDC clients;
- login/callback routes;
- middleware;
- sync apply code;
- entitlement code;
- feature flags;
- test files;
- auth stub;
- fake Gate binding;
- generated files;
- accidental secret exposure.

Jangan cetak isi `.env`.

---

# 3. RECOVERY CHECKPOINT

Setelah preflight memastikan tidak ada secret/generated dependency yang terikut:

Jika working tree berisi perubahan parsial yang valid, buat branch recovery:

```bash
git switch -c resume/phase-4a-claude-opus
```

Jika branch tersebut sudah ada atau current branch memang branch recovery, jangan membuat duplikat.

Review staging:

```bash
git status --short
git diff --check
```

Buat WIP checkpoint hanya jika aman:

```bash
git add -A
git diff --cached --check
git commit -m "wip(identity): checkpoint interrupted Phase 4A implementation"
```

Jangan commit:
- `.env`;
- credentials;
- generated dependency folders;
- temporary tokens;
- runtime caches.

Jangan rewrite/squash history.

---

# 4. RECONSTRUCT ACTUAL PHASE 4A STATE

Baca seluruh dokumen yang relevan:

- `AGENTS.md`
- `README.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `PROMPT-ANTIGRAVITY-PHASE-4A.md` jika tersedia
- `docs/01-domain/PERSON-PATIENT-IDENTITY.md`
- `docs/02-workflows/GATE-USER-SYNC.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
- `docs/07-security/GATE-SYNC-SECURITY.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/10-delivery/PHASE-3C2-CLOSURE.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Kemudian audit semua file parsial yang ditinggalkan Gemini.

Buat:

`docs/10-delivery/PHASE-4A-RESUME-STATE.md`

Gunakan klasifikasi:

- `COMPLETE`
- `PARTIAL`
- `NOT_STARTED`
- `BLOCKED`
- `NEEDS_CORRECTION`

Dokumen wajib menjelaskan:

1. current Git commit/branch;
2. WIP checkpoint commit;
3. daftar file yang diwarisi dari Gemini;
4. migration existing/applied/not-applied;
5. current auth flow;
6. status login stub;
7. status Gate OIDC/OAuth client;
8. status entitlement;
9. status Person/User projection;
10. status sync dry-run;
11. status sync apply;
12. status reconciliation/conflict;
13. status tests;
14. status feature flags;
15. security defects;
16. continuation plan.

Jangan coding lanjutan sebelum reconstruction selesai.

---

# 5. REVIEW SECURITY-KRITIS IMPLEMENTATION GEMINI

Audit setiap implementasi yang sudah ada.

## 5.1 Login and callback

Periksa:

- `/login`;
- Gate redirect;
- callback route;
- `state`;
- nonce bila OIDC;
- PKCE bila dipakai;
- callback replay;
- redirect target;
- session regeneration;
- rate limiting;
- error handling.

Pastikan:

- tidak ada auto-login stub;
- tidak ada query `?user_id=...`;
- tidak ada query `?role=admin`;
- tidak ada open redirect;
- tidak ada client-controlled actor;
- token tidak ditulis ke log/session secara tidak aman.

## 5.2 OIDC/OAuth validation

Jika OIDC:

- issuer;
- audience;
- signature;
- expiration;
- nonce;
- state;
- discovery/JWKS handling;
- clock skew.

Jika Gate implementation aktual adalah OAuth2 + userinfo dan bukan OIDC penuh:

- jangan memaksakan OIDC palsu;
- dokumentasikan contract aktual;
- gunakan security equivalent.

Jangan mengarang endpoint.

## 5.3 Entitlement

Periksa bahwa login memerlukan application entitlement POSKESTREN Health.

Entitlement hanya menentukan:
- boleh/tidak boleh mengakses aplikasi.

Entitlement TIDAK otomatis menentukan:
- clinical role;
- clinical permission;
- patient eligibility.

## 5.4 Fake/real bindings

Pastikan:

- tests menggunakan fake;
- non-test code mempunyai real HTTP implementation;
- feature flags OFF secara default;
- fake tidak tersambung diam-diam pada environment production.

---

# 6. MIGRATION SAFETY

Sebelum mengedit migration:

```bash
php artisan migrate:status
```

Aturan:

1. Jika migration Phase 4A belum applied:
   - boleh diperbaiki setelah review;
   - jangan membuat duplicate table.
2. Jika sudah applied:
   - jangan edit migration lama;
   - buat corrective migration baru.
3. Jangan `migrate:fresh` pada database development/production.
4. Gunakan `poskestren_health_test` untuk verification.
5. Jangan hard delete Person/User/Patient/history.

Periksa constraint:

- unique `gate_user_id`;
- mapping uniqueness;
- sync idempotency;
- run/item uniqueness;
- entitlement references;
- conflict references;
- no destructive cascade to Patient/history.

---

# 7. TARGET ARCHITECTURE — GATE SSO

Phase 4A target:

```text
GET /login
    ↓
Gate authorization endpoint
    ↓
User authenticates at Gate
    ↓
Callback
    ↓
Validate state / nonce / code
    ↓
Exchange token
    ↓
Validate identity
    ↓
Fetch userinfo
    ↓
Fetch/check application entitlement
    ↓
Resolve Person by gate_user_id
    ↓
Project identity to Person/User
    ↓
Regenerate Laravel session
    ↓
Login
    ↓
Dashboard
```

Jangan gunakan name matching.

---

# 8. GATE CLIENTS

Target components:

- `GateOidcClientContract`
- real `HttpGateOidcClient`
- `FakeGateOidcClient`
- `GateClientContract`
- real `HttpGateClient`
- existing Fake client retained for tests
- identity/user DTO
- application entitlement DTO
- sync result DTO

Configuration only:

```text
GATE_BASE_URL
GATE_CLIENT_ID
GATE_CLIENT_SECRET
GATE_REDIRECT_URI
GATE_SCOPES
GATE_APP_CODE
```

Never commit values/secrets.

---

# 9. APPLICATION ENTITLEMENT

Required statuses:

- `allowed`
- `revoked`
- `suspended`
- `not_assigned`

Rules:

1. Valid Gate login + no entitlement -> access denied.
2. Revoked -> access denied.
3. Suspended -> access denied.
4. User/Person/Patient remains stored.
5. Medical history remains.
6. Clinical permissions remain local and separate.
7. Unknown entitlement -> default deny.
8. Audit all denial/revocation.

If Gate actual API uses different status names, map them explicitly via DTO/config. Do not scatter raw strings.

---

# 10. PERSON / USER / PATIENT PROJECTION

On successful Gate identity resolution:

Only authoritative identity projection may be updated:

- `gate_user_id`;
- name;
- NIS/NIP/username according to Gate contract;
- email;
- phone if authoritative;
- user type;
- organizational attributes;
- source status;
- source version/checksum;
- synced_at.

Never update from Gate:

- allergies;
- medical conditions;
- health profile;
- medical visits;
- observations;
- medication;
- referrals;
- discharge;
- medical history.

Rules:

- human user may be Patient;
- human admin may still be Patient;
- technical/service/bot account is not Patient eligible;
- account deactivation does not remove Person/Patient/history.

---

# 11. SECURE SYNC APPLY

Existing dry-run must remain.

Target:

```text
Gate source
   ↓
Fetch complete pages
   ↓
Validate
   ↓
Classify
   ↓
Dry-run preview
   ↓
Explicit authorized Apply
   ↓
Per-item transaction
   ↓
Reconciliation
   ↓
Final audit/report
```

Statuses:

- `new`
- `matched`
- `changed`
- `unchanged`
- `deactivated`
- `source_missing`
- `conflict`
- `unsupported_type`
- `duplicate_identifier`
- `invalid_payload`

Rules:

### new
Create Person/User projection once.

### changed
Only authoritative source fields.

### unchanged
No unnecessary writes.

### deactivated
Disable login/access.
Keep history.

### source_missing
Only actionable after a verified COMPLETE source snapshot.
Never infer from partial API failure.

### conflict
Never auto-merge.

### duplicate_identifier
Manual resolution.

### invalid_payload
Quarantine/fail item safely.

---

# 12. SYNC RUN / ITEM TRACEABILITY

If not already present, implement structures similar to:

`gate_sync_runs`

- id;
- mode dry_run/apply;
- source snapshot/version;
- started_by;
- started_at;
- completed_at;
- status;
- counts;
- cursor/page metadata;
- full_snapshot_confirmed;
- correlation ID.

`gate_sync_items`

- run;
- gate_user_id;
- person reference nullable;
- classification;
- source checksum;
- proposed changes JSON safe;
- apply status;
- error code/message sanitized;
- applied_at/by;
- idempotency key;
- timestamps.

Do not store token/secret.

---

# 13. IDEMPOTENCY AND MARIA DB CONCURRENCY

Must prove with MariaDB:

1. two workers apply same Gate user;
2. only one effective Person/User mutation;
3. no duplicate Person;
4. no duplicate User;
5. no duplicate Patient;
6. no duplicate successful audit;
7. duplicate sync run/item safe;
8. deactivation concurrent with projection update deterministic;
9. transaction failure rolls back local identity change.

Use row locks/unique constraints.

Do not claim concurrency safety from SQLite.

---

# 14. LEGACY MAPPING

Existing local Person records may lack Gate linkage.

Matching order:

1. exact gate_user_id;
2. approved mapping;
3. NIS/NIP/email only as manual-review candidate;
4. never auto-match name.

If needed create `gate_identity_mappings`.

Fields:

- gate_user_id;
- person_id;
- mapping_method;
- status;
- approved_by;
- approved_at;
- notes;
- source checksum/reference.

Manual actions require Policy + audit.

---

# 15. RECONCILIATION AND CONFLICT UI

Provide:

- sync runs;
- preview;
- apply confirmation;
- counts;
- new;
- changed;
- deactivated;
- source missing;
- conflicts;
- unsupported;
- invalid;
- failed.

Actions:

- inspect;
- approve mapping;
- reject mapping;
- mark technical account;
- retry item.

Identity data only.
No medical data.

---

# 16. LOCAL ROLE / PERMISSION MAPPING

Maintain strict separation:

```text
Gate Identity
Gate User Type
Gate Application Entitlement
             ↓
        Local User
             ↓
Local Role / Permission
```

If Gate sends role claims:

- explicit mapping only;
- unknown -> deny/no local role;
- no arbitrary dynamic role creation;
- no `admin` -> clinical superuser shortcut;
- mapping changes audited.

---

# 17. AUTH STUB REMOVAL

Do NOT remove the old login stub prematurely.

Safe sequence:

1. real Gate redirect route works;
2. callback tests pass;
3. entitlement tests pass;
4. session creation tests pass;
5. guest redirect tests pass;
6. then remove/replace stub.

After completion verify:

- no insecure stub;
- `/login` triggers safe Gate login flow or safe disabled state if feature flag OFF;
- no authentication bypass.

If `GATE_SSO_ENABLED=false`, provide explicit safe disabled/setup page, NOT an auto-login stub.

---

# 18. FEATURE FLAGS

Defaults must remain:

```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
GATE_WEBHOOK_ENABLED=false

ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
```

Tests may override.

Do not automatically turn production flags ON.

---

# 19. BREAK-GLASS DECISION

Do not invent hidden local login.

If docs/stakeholder do not approve break-glass:
- do not implement;
- record `[PERLU DIKONFIRMASI]`.

If existing implementation was already created by Gemini:
- audit it aggressively;
- disable by default;
- require separate explicit flag;
- audit every login;
- no Gate impersonation;
- no Policy bypass.

---

# 20. LOGOUT / SESSION

Verify:

- `Auth::logout()`;
- session invalidation;
- CSRF regeneration;
- optional Gate end-session only if supported;
- no token leak.

Entitlement revalidation strategy:
- login;
- configurable TTL/session refresh;
- periodic sync/revocation mechanism.

Do not call Gate on every page unless explicitly justified.

---

# 21. WEBHOOK

Default OFF.

Only implement if actual Gate contract exists.

Require:

- signature verification;
- timestamp freshness;
- replay protection;
- event idempotency;
- schema validation;
- sanitized logs;
- queue/outbox;
- no destructive action from unverified event.

If Gate webhook contract unavailable:
- leave deferred;
- document blocker.

---

# 22. CONTROLLER / POLICY / REQUEST REVIEW

All mutation routes must:

- use dedicated Controllers;
- use Form Requests;
- call `authorize()` / `can:`;
- reject actor/time/status from payload;
- prevent IDOR;
- prevent mass assignment;
- rate-limit security-sensitive routes.

Zero insecure auth/sync mutation closures.

---

# 23. TEST REQUIREMENTS

Do not simply preserve old test count.

## SSO tests

- `/login` safe redirect to Gate when enabled;
- disabled mode safe;
- state required;
- invalid state rejected;
- replay rejected;
- invalid code fails safely;
- OIDC issuer/audience/signature/expiry/nonce validation where applicable;
- entitlement allowed succeeds;
- not_assigned/revoked/suspended denied;
- session regenerated;
- query role escalation rejected;
- open redirect rejected;
- logout invalidates session.

## Projection

- one Person/User for Gate user;
- identity update safe;
- health data untouched;
- human admin patient eligibility preserved;
- technical account not patient eligible;
- deactivated user cannot login;
- deactivation preserves Patient/history.

## Sync dry-run

- zero identity mutation;
- classification correct;
- incomplete snapshot cannot trigger source_missing deactivation.

## Sync apply

- new;
- changed;
- unchanged;
- deactivated;
- conflict;
- invalid;
- unsupported;
- duplicate;
- source_missing full snapshot only;
- idempotency;
- rollback;
- audit.

## MariaDB concurrency

- same user concurrent apply;
- no duplicate Person/User/Patient;
- deterministic final state;
- no duplicate success audit.

## Authorization

- non-authorized sync apply -> 403;
- technical admin without required identity permission -> 403 where appropriate;
- clinical permissions not derived from entitlement.

## Regression

- Phase 1–3 tests;
- Attendance integration remains OFF/fake;
- medical records unaffected.

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

MariaDB concurrency tests must run, not skip, before Phase 4A final closure.

---

# 24. GRAPHIFY

After implementation stabilizes:

Update graph without `--code-only`.

Query:

- `/login -> Gate -> callback -> local session`;
- callback state/nonce validation;
- Gate entitlement -> application access;
- Gate user -> Person -> User -> Patient;
- deactivation -> no Patient delete;
- dry-run -> apply;
- source_missing full snapshot guard;
- concurrent sync paths;
- Gate role -> local clinical permission leakage;
- fake Gate binding in non-test environment;
- login stub remnants;
- local auth bypass;
- secret logging;
- Attendance production activation leakage;
- hard delete paths;
- requirements without tests.

Update documentation mapping.

---

# 25. DOCUMENTATION

Create/update:

- `docs/10-delivery/PHASE-4A-RESUME-STATE.md`
- `docs/10-delivery/PHASE-4A-AUTH-PREFLIGHT.md`
- `docs/10-delivery/PHASE-4A-CLOSURE.md`
- `docs/08-api/GATE-OIDC-CONTRACT.md`
- `docs/07-security/GATE-SSO-SECURITY.md`
- `docs/02-workflows/GATE-LOGIN-AND-ACCESS.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/02-workflows/GATE-USER-SYNC.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/GATE-SYNC-SECURITY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/08-api/INTEGRATION-CONTRACTS.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

`PHASE-4A-CLOSURE.md` must distinguish:

- inherited Gemini work;
- Gemini work corrected by Claude;
- features completed by Claude;
- unresolved blockers.

---

# 26. FINAL SECURITY REVIEW

Before final commit explicitly verify:

### Authentication
- no stub bypass;
- no open redirect;
- state/nonce/replay controls;
- session regeneration;
- entitlement enforcement.

### Identity
- `gate_user_id` stable unique;
- no name auto-merge;
- no health mutation;
- deactivation non-destructive.

### Sync
- dry-run safe;
- apply authorized;
- idempotent;
- concurrent safe;
- complete-snapshot guard.

### Authorization
- entitlement != clinical permission;
- Gate role cannot silently become medical superuser.

### Secrets
- no secret/token in Git;
- no secret/token in logs/audit.

### Integrations
- Attendance production still OFF/fake.
- Gate production flags remain OFF unless explicitly instructed otherwise.

---

# 27. FINAL GIT CHECKPOINT

If all verification passes:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "feat(identity): complete Phase 4A Gate SSO and secure sync foundation"
git status
```

Keep WIP checkpoint commit in history.

Do not squash/rebase automatically.

Target:
- working tree clean.

---

# 28. FINAL OUTPUT

Report:

1. Recovery/preflight result.
2. WIP checkpoint commit.
3. Files inherited from Gemini.
4. Incomplete/unsafe Gemini implementation found.
5. Corrections made by Claude.
6. New implementation completed by Claude.
7. Migration status.
8. Gate OAuth/OIDC flow.
9. Application entitlement enforcement.
10. Login stub final status.
11. Person/User/Patient projection.
12. Sync dry-run and apply.
13. Reconciliation/conflict handling.
14. Legacy identity mapping.
15. Role/permission mapping.
16. Feature flags.
17. Session/logout.
18. Break-glass decision.
19. Tests/assertions/skips.
20. MariaDB concurrency evidence.
21. Graphify findings.
22. Remaining production blockers.
23. Final commit.
24. Working tree.
25. GO/NO-GO for Phase 4B.

---

# 29. MANDATORY STOP CONDITIONS

STOP and report blocker if:

- migration state cannot be safely determined;
- an applied migration would need destructive modification;
- secret/token is found in tracked files;
- insecure auth stub remains unavoidable;
- Gate contract required for validation is unknown and cannot be inferred safely;
- entitlement cannot be validated;
- sync apply can duplicate Person/User/Patient;
- source_missing can deactivate users from partial snapshot;
- Gate deactivation can delete Patient/history;
- Gate role can automatically grant clinical access;
- MariaDB concurrency tests fail or remain skipped;
- critical security tests fail.

If Phase 4A passes:

- final commit;
- working tree clean;
- production flags remain OFF;
- do NOT start Phase 4B;
- wait for explicit user approval.
