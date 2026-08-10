# PROMPT ANTIGRAVITY — PHASE 4A
## Real Gate SSO, Secure User Sync Apply, Application Entitlement Enforcement, and Identity Production Hardening

Anda adalah principal Laravel architect, IAM/OIDC engineer, application security engineer, distributed systems engineer, privacy engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash High** atau **Claude Sonnet 4.6 Thinking**.

Tujuan fase ini:

1. menutup Phase 3C2 secara final;
2. mengganti login stub dengan autentikasi Gate SSO nyata;
3. menghubungkan aplikasi ke Gate menggunakan kontrak resmi, bukan FakeGateClient;
4. menerapkan application entitlement: hanya user yang diberi akses ke aplikasi POSKESTREN Health di Gate yang dapat masuk;
5. mengubah dry-run sync menjadi secure apply sync yang idempotent, audited, reversible secara domain, dan memiliki reconciliation;
6. mempertahankan pemisahan Person -> User -> Role/Permission -> Patient;
7. memastikan deactivation/revocation Gate tidak menghapus riwayat medis;
8. memastikan semua human users yang eligible tetap dapat menjadi patient;
9. menjaga local emergency/break-glass account terisolasi dan diaudit bila memang dibutuhkan;
10. berhenti sebelum mengaktifkan Attendance production connector.

Jangan mengubah data medis berdasarkan payload Gate.

---

# 1. WAJIB DIBACA

Baca:

- `AGENTS.md`
- `README.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/01-domain/PERSON-PATIENT-IDENTITY.md`
- `docs/01-domain/BUSINESS-RULES.md`
- `docs/02-workflows/GATE-USER-SYNC.md`
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/NON-FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
- `docs/05-data/DATA-DICTIONARY.md`
- `docs/07-security/GATE-SYNC-SECURITY.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/08-api/INTEGRATION-CONTRACTS.md`
- `docs/09-testing/SECURITY-TESTS.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/10-delivery/PHASE-3C2-CLOSURE.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika path berbeda, gunakan path aktual.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, client secret, private key, access token, refresh token, password, atau credential.
2. Jangan mengaktifkan production Attendance connector.
3. Jangan memakai user/patient nyata pada test.
4. Jangan hard delete Person, User, Patient, audit, atau medical history.
5. Jangan update medical fields dari Gate.
6. Jangan match user berdasarkan nama.
7. Jangan menggunakan email/NIS/NIP sebagai primary internal identity.
8. `gate_user_id` adalah external stable identifier jika kontrak resmi mengonfirmasi.
9. User yang kehilangan akses aplikasi di Gate harus kehilangan login/access, tetapi medical history tetap ada.
10. Role admin tidak menentukan patient eligibility.
11. Jangan otomatis memberi clinical permission hanya karena role dari Gate bernama admin.
12. Semua apply sync harus transaction-safe, idempotent, audited, dan conflict-aware.
13. Jangan membuat mass overwrite tanpa preview/reconciliation.
14. Jangan mengaktifkan webhook production tanpa signature verification.
15. Jangan membuat local bypass login tersembunyi.
16. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 3C2 FINAL CLOSURE

Jalankan read-only:

```bash
pwd
git branch --show-current
git status
git log --oneline -10
php artisan migrate:status
php artisan route:list
```

Verifikasi:

- commit `6d65efe` atau commit final Phase 3C2 ada;
- working tree clean;
- 134 tests baseline atau baseline terbaru lulus;
- attendance integration masih OFF/fake;
- outbox idempotency/retry/dead-letter tetap aktif;
- operational payload privacy tests tetap lulus;
- Graphify up-to-date.

Buat:

`docs/10-delivery/PHASE-3C2-FINAL-CLOSURE.md`

Status:
- `PASSED`
- `FAILED`

Jika Critical regression, berhenti.

---

# 4. TAHAP B — AUDIT AUTHENTICATION CURRENT STATE

Identifikasi:

- route `/login`;
- login stub;
- session guard;
- existing `users` table;
- local password support;
- auth middleware;
- CSRF/session config;
- intended Gate OAuth/OIDC endpoints;
- Passport/OIDC assumptions;
- existing Gate client config;
- fake Gate client bindings.

Buat:

`docs/10-delivery/PHASE-4A-AUTH-PREFLIGHT.md`

Isi:
- current auth path;
- stub behavior;
- fake bindings;
- target auth flow;
- blockers;
- migration impact;
- rollback plan.

Jangan menghapus stub sampai real SSO flow dan tests tersedia.

---

# 5. TAHAP C — GATE OIDC/OAUTH CLIENT

Implementasikan real Gate authentication menggunakan kontrak resmi Gate.

Prefer authorization-code flow.

Komponen:

- `GateOidcClientContract`
- `HttpGateOidcClient`
- `FakeGateOidcClient` untuk test
- DTO claims/userinfo
- state/nonce/PKCE bila kontrak mendukung
- callback validation
- token exchange
- userinfo retrieval
- logout behavior sesuai Gate contract

Jangan mengarang endpoint. Ambil dari config:

```text
GATE_BASE_URL
GATE_CLIENT_ID
GATE_CLIENT_SECRET
GATE_REDIRECT_URI
GATE_SCOPES
GATE_APP_CODE
```

Secret hanya environment.

Jika Gate menyediakan discovery:

- gunakan `.well-known/openid-configuration`;
- validasi issuer;
- validasi audience;
- validasi nonce;
- validasi signature;
- validasi expiry;
- clock skew kecil/configurable.

Jika Gate tidak mendukung OIDC penuh, dokumentasikan contract aktual dan gunakan OAuth2 + userinfo dengan security equivalent.

---

# 6. TAHAP D — APPLICATION ENTITLEMENT

Gate adalah source of truth untuk hak akses user ke aplikasi.

POSKESTREN Health harus memeriksa entitlement aplikasi.

Buat contract/DTO:

- `GateApplicationEntitlementDTO`
- app code contoh configurable: `poskestren-health`

Status:
- `allowed`
- `revoked`
- `suspended`
- `not_assigned`

Login hanya berhasil jika:
- Gate identity valid;
- account active;
- application entitlement `allowed`.

Jika entitlement dicabut:
- session baru ditolak;
- session aktif ditangani sesuai policy (mis. middleware revalidation/short TTL);
- local user tidak dihapus;
- person/patient/history tetap ada;
- audit `application_access_revoked`.

Jangan mengubah medical permissions berdasarkan entitlement aplikasi; entitlement hanya menentukan akses aplikasi.

---

# 7. TAHAP E — LOGIN / CALLBACK FLOW

Flow target:

```text
GET /login
 -> redirect Gate authorization endpoint
 -> Gate authenticates
 -> callback
 -> validate state/nonce/code
 -> exchange token
 -> fetch userinfo/entitlement
 -> resolve Person/User by gate_user_id
 -> apply minimal identity projection
 -> enforce app entitlement
 -> login local session
 -> regenerate session
 -> audit
 -> redirect dashboard
```

Aturan:

- no open redirect;
- state one-time;
- callback replay protection;
- session fixation protection;
- no client role from query;
- no local password login by default unless explicitly approved;
- login failure sanitized;
- Rate limit login/callback where appropriate.

---

# 8. TAHAP F — PERSON/USER PROJECTION ON LOGIN

Pada successful SSO:

Update only authoritative identity fields:

- gate_user_id;
- name;
- username/NIS/NIP sesuai contract;
- email/phone jika authoritative;
- user_type;
- organization attributes;
- source status;
- source version/timestamp/checksum;
- synced_at.

Jangan update:

- patient health profile;
- allergies;
- medical conditions;
- visits;
- roles/permissions klinis lokal kecuali mapping contract explicitly approved.

Patient eligibility:
- human person eligible;
- admin human remains eligible;
- service/bot/admin-only technical account not eligible.

---

# 9. TAHAP G — SECURE APPLY SYNC

Upgrade existing dry-run into apply workflow.

Tetap pertahankan dry-run.

Flow:

```text
Create sync run
 -> fetch all pages
 -> validate
 -> classify
 -> dry-run preview
 -> explicit apply authorization
 -> transaction-safe item apply
 -> reconciliation
 -> final summary
 -> audit
```

Status item:
- new
- matched
- changed
- unchanged
- deactivated
- source_missing
- conflict
- unsupported_type
- duplicate_identifier
- invalid_payload

Apply rules:

## new
Create Person/User projection.
Create Patient only if eligible policy says eager creation; otherwise lazy.
Document decision.

## changed
Update authoritative projection only.

## deactivated
Disable login/access.
Do not delete person/patient/history.

## source_missing
Only after complete source snapshot or explicit tombstone.
Mark `source_missing`, never immediate hard delete.

## conflict
No automatic apply.
Manual resolution required.

## unsupported_type
Quarantine/review.

---

# 10. APPLY IDEMPOTENCY & CONCURRENCY

Require:

- `gate_user_id` unique;
- sync run source version/cursor;
- item source checksum;
- idempotency key;
- row lock on Person/User during apply;
- duplicate webhook/sync event safe;
- MariaDB concurrency tests;
- no duplicate patient;
- no role escalation.

If two apply workers process same user:
- one effective identity mutation;
- deterministic final state;
- no duplicate audit success;
- no duplicate person/user/patient.

---

# 11. RECONCILIATION

Build reconciliation dashboard/report:

Counts:
- source users
- local mapped
- new
- changed
- deactivated
- missing
- conflicts
- unsupported
- failed

Drill-down:
- identity fields only;
- no medical data.

Actions:
- inspect conflict;
- approve mapping;
- reject mapping;
- mark technical account;
- retry item.

All actions audited.

---

# 12. LEGACY IDENTITY MAPPING

If local records predate Gate linkage:

Matching order:
1. exact `gate_user_id`;
2. approved mapping table;
3. NIS/NIP/email candidate for manual review;
4. never name-only auto-merge.

Create `gate_identity_mappings` if needed:

- gate_user_id;
- person_id;
- mapping_method;
- confidence/reference;
- approved_by;
- approved_at;
- status;
- notes;
- audit.

No silent merge.

---

# 13. ROLE / PERMISSION MAPPING

Separate:

- Gate identity/user type;
- Gate application entitlement;
- local clinical roles/permissions.

If Gate provides app role claims:
- create explicit mapping table/config;
- default deny unknown role;
- no arbitrary role string -> permission;
- mapping changes audited;
- clinical permission remains explicit.

Do not let Gate claim `admin` imply `view-medical-record`.

---

# 14. BREAK-GLASS / LOCAL ADMIN

Decide explicitly.

Preferred:
- no normal local login.

If a break-glass account is operationally required:
- explicit config flag;
- disabled by default;
- separate credential rotation;
- MFA if available;
- strong rate limit;
- alert/audit every login;
- cannot masquerade as Gate user;
- cannot bypass clinical Policy;
- documented recovery procedure.

If not approved, do not implement.

---

# 15. LOGOUT / SESSION REVOCATION

Implement:

- Laravel session logout;
- session invalidation;
- CSRF token regeneration;
- optional Gate end-session redirect if contract supports.

Consider entitlement re-check:
- on login;
- on sensitive session refresh;
- configurable TTL;
- optional scheduled revocation sync.

Do not call Gate on every page request unless justified.

---

# 16. GATE USER SYNC HTTP CLIENT

Replace FakeGateClient in non-test environments with `HttpGateClient`.

Requirements:
- auth;
- TLS;
- timeout;
- retry only safe requests;
- pagination;
- rate limit handling;
- schema validation;
- source version;
- correlation ID;
- sanitized logs;
- circuit/failure handling;
- health probe.

Fake remains for tests.

No production credential in repository.

---

# 17. WEBHOOK FOUNDATION (OPTIONAL, DEFAULT OFF)

Only if Gate contract supports it.

Config:
```text
GATE_WEBHOOK_ENABLED=false
```

Require:
- signature;
- timestamp freshness;
- replay protection;
- idempotency;
- IP allowlist only as supplemental;
- schema validation;
- queue/outbox handling;
- no immediate destructive changes.

If contract not available, defer.

---

# 18. AUTHORIZATION UI

Create/update:

- login flow;
- access-denied page;
- app entitlement revoked page;
- Gate connection status;
- sync runs;
- dry-run preview;
- apply confirmation;
- reconciliation;
- conflicts;
- identity detail with Gate source info.

Do not show tokens.

Keep light/dark/system and blue theme.

---

# 19. AUDIT EVENTS

Minimum:

- `GateLoginInitiated`
- `GateLoginSucceeded`
- `GateLoginFailed`
- `GateApplicationAccessDenied`
- `GateApplicationAccessRevoked`
- `GateLogout`
- `GateUserProjectionCreated`
- `GateUserProjectionUpdated`
- `GateUserDeactivated`
- `GateSyncDryRunCreated`
- `GateSyncApplyStarted`
- `GateSyncItemApplied`
- `GateSyncItemFailed`
- `GateSyncCompleted`
- `GateIdentityConflictCreated`
- `GateIdentityConflictResolved`
- `GateRoleMappingChanged`
- `BreakGlassLoginUsed` if applicable

No token/secret in audit.

---

# 20. TEST WAJIB

## SSO
- login redirects to Gate;
- state validation;
- callback replay rejected;
- invalid issuer/audience rejected if OIDC;
- expired token rejected;
- active user + entitlement allowed succeeds;
- active user without entitlement denied;
- revoked entitlement denied;
- session regenerated;
- no query-role escalation;
- logout invalidates session.

## Identity projection
- new user creates one Person/User;
- human admin still patient eligible;
- technical account not patient eligible;
- identity change updates projection;
- health data unchanged;
- deactivation preserves Patient/history.

## Sync apply
- dry-run non-mutative;
- apply new/changed/deactivated;
- source_missing only after full snapshot;
- conflict not auto-applied;
- duplicate run idempotent;
- concurrent apply with MariaDB safe;
- no duplicate Person/User/Patient;
- failed item rollback;
- audit correct.

## Entitlement
- app access required;
- revocation handling;
- entitlement does not grant clinical permission.

## Security
- no secret in log;
- CSRF/state/replay;
- IDOR/admin routes;
- rate limit;
- session fixation;
- open redirect.

## Regression
- all Phase 1-3 tests;
- outbox Attendance remains fake/off;
- medical history unchanged.

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

MariaDB required for concurrency.

---

# 21. GRAPHIFY

Update graph without `--code-only`.

Query:
- `/login -> Gate -> callback -> User`;
- Gate entitlement -> middleware -> access;
- Gate user -> Person -> Patient;
- sync dry-run -> apply;
- deactivation -> User without Patient delete;
- Gate role -> clinical permission leakage;
- duplicate apply paths;
- source_missing logic;
- fake Gate binding in production;
- Attendance production activation leakage;
- secret logging;
- hard delete;
- missing tests.

Update traceability/mapping docs.

---

# 22. DOCUMENTATION

Create:
- `docs/10-delivery/PHASE-4A-CLOSURE.md`
- `docs/08-api/GATE-OIDC-CONTRACT.md`
- `docs/07-security/GATE-SSO-SECURITY.md`
- `docs/02-workflows/GATE-LOGIN-AND-ACCESS.md`

Update:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/01-domain/BUSINESS-RULES.md`
- `docs/02-workflows/GATE-USER-SYNC.md`
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/NON-FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/INTEGRATIONS.md`
- `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
- `docs/05-data/DATA-DICTIONARY.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/GATE-SYNC-SECURITY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
- `docs/08-api/INTEGRATION-CONTRACTS.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Create ADRs for:
- Gate SSO/OIDC flow;
- app entitlement enforcement;
- sync apply semantics;
- legacy identity mapping;
- break-glass account;
- entitlement revalidation TTL.

---

# 23. FEATURE FLAGS

Defaults:

```text
GATE_SSO_ENABLED=false
GATE_SYNC_APPLY_ENABLED=false
GATE_WEBHOOK_ENABLED=false
ATTENDANCE_INTEGRATION_ENABLED=false
ATTENDANCE_INTEGRATION_DRIVER=fake
```

During tests use fake/sandbox.

At final Phase 4A:
- real code path may exist;
- production flags stay OFF until deployment/UAT approval.

Do not activate production automatically.

---

# 24. GIT

Before:

```bash
git status
git log --oneline -5
```

If clean:

```bash
git tag -a phase-3-complete -m "Phase 3 clinical workflows and integration foundation complete"
```

After passing:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "feat(identity): complete Phase 4A Gate SSO and secure sync foundation"
git status
```

Target clean.

---

# 25. OUTPUT AKHIR

Report:

1. Phase 3C2 closure status.
2. Current auth stub removal/replacement.
3. Gate OIDC/OAuth implementation.
4. Entitlement enforcement.
5. Person/User/Patient projection behavior.
6. Secure sync apply architecture.
7. Reconciliation/conflict behavior.
8. Legacy mapping.
9. Role/permission mapping.
10. Break-glass decision.
11. Session/logout behavior.
12. Feature flags.
13. Tests/assertions/skips.
14. MariaDB concurrency.
15. Graphify findings.
16. Remaining production blockers.
17. Git commit/status.
18. GO/NO-GO for Phase 4B production integrations/UAT.

---

# 26. CHECKPOINT WAJIB

Do not proceed to production integration if:

- login stub still provides insecure bypass;
- Gate issuer/audience/state validation incomplete;
- app entitlement not enforced;
- sync apply can duplicate Person/User/Patient;
- deactivation can delete health history;
- Gate role can auto-grant clinical access unexpectedly;
- secrets appear in logs;
- critical tests fail.

If passed:
- commit;
- working tree clean;
- keep production flags OFF;
- stop;
- wait for explicit approval for Phase 4B.
