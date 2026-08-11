# PROMPT ANTIGRAVITY — PHASE 5A
## Documentation Truth Normalization + Application UX & Workflow Completion
## SABIRA POSKESTREN Health v0.19.2 Development Baseline

Gunakan **Claude Opus 4.6 Thinking**.

Anda adalah principal Laravel architect, health-information-system product engineer, UX/workflow architect, IAM/security engineer, QA lead, dan documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

---

# 0. ABSOLUTE PROJECT TRUTH — WAJIB

Ini adalah **source of truth baru** dan mengoreksi seluruh asumsi sebelumnya:

- Aplikasi **BELUM PERNAH dideploy ke server production**.
- Aplikasi saat ini masih berjalan dan dikembangkan di **laptop macOS developer**.
- Tidak ada production runtime POSKESTREN yang aktif.
- Tidak ada production database POSKESTREN yang aktif.
- Tidak ada Nginx/Supervisor/Cron production yang boleh diklaim sebagai bukti runtime aplikasi.
- Tidak ada production UAT, production stabilization 24–72h, production backup, production traffic, atau production telemetry yang benar-benar terjadi.
- Seluruh hasil automated tests, localhost curl, MariaDB lokal, dan runtime `php artisan serve` adalah **development/test evidence**.
- Semua dokumentasi lama yang menyatakan `PRODUCTION-CUTOVER-PASSED`, `AUTH-HOTFIX-PRODUCTION-VERIFIED`, `PRODUCTION-OPERATIONALLY-ACCEPTED`, atau production stabilization harus dinormalisasi menjadi **pre-production / rehearsal / readiness documentation**.
- Jangan menghapus pekerjaan teknis yang valid; koreksi hanya klaim status lingkungan dan kejadian operasionalnya.
- Jangan melakukan SSH, deployment, atau pencarian server production pada fase ini.
- Jangan mengarang production host, production IP, production metrics, user UAT nyata, atau backup artifact.

Status proyek aktual:

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
DEPLOYMENT_STATUS=NOT_DEPLOYED
PRODUCTION_STATUS=NOT_STARTED
CURRENT_FUNCTIONAL_VERSION=0.19.2
```

---

# 1. CURRENT FUNCTIONAL BASELINE — v0.19.2

Gunakan `CHANGELOG.md` aktual sebagai sumber utama.

Baseline fitur yang sudah tersedia antara lain:

## Authentication / Identity
- Person -> User -> Patient
- local Role & Permission
- Gate SSO OIDC foundation
- application entitlement
- Gate sync dry-run/apply/reconciliation
- Direct Credentials Login
- login via email / username / NIK / NIS / NIP
- password verification dengan `Hash::check()`
- remember me
- brute-force rate limiting 5 attempts/minute
- hybrid login UI
- opsi "Masuk dengan SABIRA Gate SSO"
- audit login
- inactive user rejection

## Patient / Clinical
- patient profile
- health profile
- allergy
- medical conditions
- emergency contacts
- medical visit intake
- active visit guard
- vital signs
- clinical assessment
- clinical actions
- disposition
- observation
- observation monitoring
- handover
- medication order
- medication administration
- stock issue/reversal
- consultation
- referral
- referral transport/companion/handover/return
- discharge
- follow-up
- activity restriction
- operational handoff

## Pharmacy
- medicine master
- stock locations
- medicine batches
- expiry/quarantine
- append-only stock ledger
- receipt
- adjustment
- reversal
- negative stock guard

## Integration / Operations
- Gate integration contracts
- Attendance integration contract
- privacy/minimum-necessary DTO
- outbox
- retry/dead-letter
- notifications
- role-aware dashboards
- health reports
- `/health`
- `/health/ready`

## UI foundation
- Blade
- Livewire
- Tailwind
- Flux UI
- responsive app shell
- light/dark/system theme
- semantic design tokens

Jangan mengimplementasikan ulang modul yang sudah ada sebelum membuktikan gap aktual.

---

# 2. PHASE 5A GOAL

Tujuan Phase 5A adalah membuat aplikasi terasa seperti **satu sistem operasional yang runtut**, bukan sekumpulan modul terpisah.

Fokus:

1. Documentation Truth Normalization.
2. Full UX inventory.
3. Role-aware navigation.
4. Patient discovery & context.
5. End-to-end visit workspace.
6. Clinical workflow continuity.
7. Pharmacy workflow usability.
8. Referral/consultation/discharge continuity.
9. Action discoverability.
10. Validation/error/empty/loading states.
11. Responsive/mobile usability.
12. Accessibility.
13. Theme consistency.
14. Security-safe UI.
15. Regression tests.
16. Updated Graphify map.
17. Phase 5A closure.

Tidak ada deployment production pada phase ini.

---

# STAGE 0 — DOCUMENTATION TRUTH NORMALIZATION

## 3. AUDIT FALSE PRODUCTION CLAIMS

Search seluruh repository:

```bash
rg -n \
"PRODUCTION-CUTOVER-PASSED|PRODUCTION-OPERATIONALLY-ACCEPTED|AUTH-HOTFIX-PRODUCTION-VERIFIED|production live|resmi live|go-live|24.?72|T\\+1h|T\\+6h|T\\+24h|T\\+48h|T\\+72h|production stabilization|production traffic|production UAT|production backup" \
. \
-g '!vendor/**' \
-g '!node_modules/**' \
-g '!storage/**' \
-g '!bootstrap/cache/**' \
-g '!graphify-out/**'
```

Create:

`docs/10-delivery/ENVIRONMENT-TRUTH-CORRECTION.md`

For each claim classify:

- `VALID-DESIGN`
- `VALID-LOCAL-TEST`
- `VALID-PREPRODUCTION-REHEARSAL`
- `INVALID-PRODUCTION-CLAIM`
- `FUTURE-PRODUCTION-RUNBOOK`

Do not delete valid runbooks.

---

# 4. NORMALIZE STATUS TERMINOLOGY

Apply terminology consistently.

Examples:

```text
PRODUCTION-CUTOVER-PASSED
->
PRE-PRODUCTION-CUTOVER-REHEARSAL-PASSED
```

```text
AUTH-HOTFIX-PRODUCTION-VERIFIED
->
AUTH-HOTFIX-LOCAL-RUNTIME-VERIFIED
```

```text
PRODUCTION-OPERATIONALLY-ACCEPTED
->
PRE-PRODUCTION-OPERATIONAL-READINESS-VALIDATED
```

```text
24–72h production stabilization
->
planned production stabilization procedure
```

```text
production UAT
->
local/staging UAT specification
```

Only rename claims where appropriate. Preserve historical context with a note such as:

```text
[CORRECTED 2026-08-11]
Previous wording implied a real production deployment.
The application had not yet been deployed.
This artifact is retained as a pre-production rehearsal/readiness record.
```

Do not rewrite Git history.

---

# 5. CHANGELOG NORMALIZATION

Update `CHANGELOG.md`.

Do NOT erase versions.

For versions such as `0.17.x`, `0.18.x`, `0.19.0`, `0.19.1`:

- preserve implemented code/features;
- correct only environment/status wording;
- clearly distinguish:
  - code implemented;
  - automated test passed;
  - deployment rehearsal;
  - planned production operations;
  - NOT actually deployed.

Add an explicit note near the top:

```markdown
> Environment note:
> Sampai versi 0.19.2 aplikasi masih berada pada local development/pre-production.
> Istilah production pada dokumen historis sebelum koreksi ini merujuk pada
> rehearsal/readiness validation, bukan deployment server production aktual.
```

Do not alter v0.19.2 functionality unless needed by Phase 5A.

---

# 6. PROJECT STATUS NORMALIZATION

Update:

- `PROJECT-STATUS.md`
- `READINESS-REVIEW.md`
- `KNOWN-ISSUES.md`
- Phase 4 closure documents
- deployment documents where needed

Canonical project state should read approximately:

```text
Application Development: ACTIVE
Current Version: 0.19.2+
Environment: LOCAL DEVELOPMENT
Production Deployment: NOT STARTED
Production Server Validation: NOT APPLICABLE YET
Staging Deployment: PENDING
Gate Real Environment Validation: PENDING
Attendance Real Environment Validation: PENDING
```

---

# STAGE 1 — APPLICATION UX INVENTORY

# 7. INVENTORY ALL USER-FACING ROUTES

Run:

```bash
php artisan route:list -v
```

Create:

`docs/05-ui/PHASE-5A-ROUTE-AND-SCREEN-INVENTORY.md`

For every user-facing route capture:

- route;
- method;
- controller;
- middleware;
- role/permission;
- Blade/Livewire view;
- page purpose;
- primary user;
- primary action;
- secondary action;
- parent workflow;
- current UX issue;
- missing state;
- responsive state.

Group by:

- Auth
- Dashboard
- Patient
- Visit
- Clinical
- Observation
- Pharmacy
- Medication
- Consultation
- Referral
- Discharge
- Follow-up
- Operational
- Reporting
- Gate/Admin

---

# 8. INVENTORY NAVIGATION

Audit:

- sidebar
- topbar
- breadcrumbs
- page titles
- section headings
- mobile navigation
- active menu state
- role-based menu visibility
- direct URL security

Create role menu matrix:

```text
ROLE
  -> Dashboard
  -> Patients
  -> Visits
  -> Clinical
  -> Observation
  -> Pharmacy
  -> Referral
  -> Reports
  -> Admin
```

Menu hiding is not authorization.
Policies/middleware remain authoritative.

---

# STAGE 2 — ROLE-AWARE APPLICATION ENTRY

# 9. LOGIN UX AUDIT

Current v0.19.2 has hybrid login.

Audit:

## Direct Credentials
- identifier label must explain accepted values:
  - email
  - username
  - NIK
  - NIS/NIP
- password
- show/hide
- remember me
- validation
- rate limit feedback
- inactive account feedback
- invalid credentials feedback

Do not reveal whether a specific identifier exists if that enables enumeration beyond existing security policy.

## Gate SSO
- clear secondary action:
  `Masuk dengan SABIRA Gate`
- explain that Gate login uses SABIRA account
- preserve intended redirect
- no confusing double-login flow

## Login status
Ensure:
- authenticated -> role dashboard
- guest -> login
- logout -> login
- no auto-admin fallback

---

# 10. DASHBOARD ROLE EXPERIENCE

Audit `DashboardController` and all dashboard views.

Role destinations should be intentionally differentiated:

### Clinical staff
Needs:
- active visits
- patients waiting
- observation
- follow-up due
- pending referrals
- medication requiring attention

### Pharmacy
Needs:
- low stock
- expiring batches
- medicine administration/stock tasks
- recent movements

### Dorm / Operational
Needs only minimum-necessary:
- activity restriction
- rest status
- follow-up instruction
- acknowledgement tasks

No diagnosis/vitals/medication details.

### Management
Needs aggregate:
- visit volume
- referral counts
- observation counts
- high-level trends
- stock summary

No individual clinical details by default.

### Technical Admin
Needs:
- users
- roles/permissions
- Gate sync
- audit
- integrations/system status

Must not auto-get clinical data.

### General / Patient-facing user
If no patient portal is currently implemented:
- do NOT invent full portal automatically;
- provide safe minimal landing or permission-based 403.
- document as future backlog.

---

# STAGE 3 — PATIENT DISCOVERY & CONTEXT

# 11. PATIENT SEARCH EXPERIENCE

Audit patient listing/search.

Support discoverability by appropriate existing fields such as:

- name
- NIS/NIP
- patient number/MRN
- Gate identity where safe

Do not expose excessive PII in list pages.

Need:

- search input
- filters
- pagination
- clear reset
- empty state
- no-results state
- loading state if async
- mobile responsive list/cards

---

# 12. PATIENT CONTEXT HEADER

Create/reuse a consistent patient context component on clinical pages.

Show minimum necessary operational identity:

- name
- patient number
- NIS/NIP if applicable
- user/person type
- current visit status
- relevant alerts such as allergy indicator

Do NOT show excessive data globally.

Provide context actions:

```text
Patient Profile
Active Visit
New Visit
History
```

subject to permission.

---

# STAGE 4 — VISIT WORKSPACE

# 13. END-TO-END VISIT NAVIGATION

A medical visit should feel like one workspace.

Target conceptual flow:

```text
Patient
  ↓
Visit Intake
  ↓
Vital Signs
  ↓
Clinical Assessment
  ↓
Clinical Actions
  ↓
Disposition
  ├─ Observation
  ├─ Medication
  ├─ Consultation
  ├─ Referral
  └─ Discharge
       ↓
Follow-Up / Operational Handoff
```

Audit whether users currently must return to unrelated menu pages between stages.

If yes, implement a unified **Visit Workspace** navigation shell.

---

# 14. VISIT WORKSPACE UI

For `/visits/{visit}` and related routes, provide:

## Header
- patient identity
- visit number
- arrival time
- visit status
- responsible staff if existing

## Progress / Stage navigation

Example:

```text
Intake
Vitals
Assessment
Actions
Observation
Medication
Consultation
Referral
Discharge
Follow-up
```

Stages should:
- reflect current state;
- disable impossible actions;
- mark complete/current/pending;
- not rely solely on color.

Do NOT change domain state machine just for UI convenience.

---

# 15. ACTION PRIORITY

Each screen should have:

- exactly one obvious primary action where possible;
- secondary actions visually subordinate;
- destructive actions separated;
- confirmation for significant state transition;
- status explanation before irreversible/finalizing actions.

Examples:
- Finalize Assessment
- Start Observation
- Administer Medication
- Create Referral
- Finalize Discharge

Never make destructive/corrective actions the default primary button.

---

# STAGE 5 — CLINICAL WORKFLOW UX

# 16. VITAL SIGNS

Audit form:

- units visible
- ranges explained carefully
- field grouping
- required/optional clarity
- validation messages near field
- save/final state
- previous vitals context if supported

Do not invent medical decision thresholds.
Existing technical validation may remain.
Clinical thresholds not documented -> `[PERLU DIKONFIRMASI]`.

---

# 17. CLINICAL ASSESSMENT

Improve workflow:

- structured sections
- draft vs finalized clearly visible
- save draft
- finalization confirmation
- addendum history
- entered-in-error/correction visibility
- allergy alert context
- clinical actions linkage

No accidental editing of finalized records.

---

# 18. OBSERVATION

Observation workspace should make visible:

- status
- start time
- responsible officer
- periodic notes
- periodic vitals
- actions
- handover
- outcome

Need obvious:
- Add Monitoring Record
- Add Vital
- Handover
- Complete Observation

Respect existing state machine.

---

# STAGE 6 — PHARMACY & MEDICATION UX

# 19. PHARMACY WORKSPACE

Audit:

- medicine master
- inventory
- batch
- receipt
- adjustment
- movement history

Improve:

- low stock state
- expiring soon state
- expired/quarantined state
- batch quantity visibility
- unit visibility
- filters
- medicine search
- movement audit trail

Do not invent reorder threshold unless configured.
Mark unknown operational threshold `[PERLU DIKONFIRMASI]`.

---

# 20. MEDICATION ADMINISTRATION

Within visit workspace, show:

- active order
- schedule/status
- allergy warning
- batch availability
- administered/held/refused/missed/cancelled
- audit history

For stock issue:
- selected batch
- available quantity
- amount to issue
- confirmation

Do not expose stock mutation as generic free-form adjustment.

---

# STAGE 7 — CONSULTATION / REFERRAL / DISCHARGE CONTINUITY

# 21. CONSULTATION

UX should make clear:

```text
Clinical Summary
Question to External Clinician
Transmission
External Advice
Local Decision
```

External advice must not look like an automatic local order.

---

# 22. REFERRAL

Referral workspace should group:

- referral status
- destination
- transport
- companion
- handover
- destination status
- return
- local return review

Create timeline/status representation if helpful.

Do not collapse handover and acceptance into one event.

---

# 23. DISCHARGE

Discharge workspace:

- readiness
- discharge type
- activity recommendation
- restrictions
- follow-up
- internal handoff
- document

Need clear:
- Draft
- Finalized
- Amended
- Entered in Error

Finalization should require confirmation.

---

# STAGE 8 — FEEDBACK STATES & CONSISTENCY

# 24. EMPTY STATES

Every major list needs meaningful empty state.

Examples:

```text
Belum ada kunjungan aktif.
Belum ada catatan observasi.
Belum ada obat pada katalog.
Belum ada rujukan.
```

Do not show blank tables.

---

# 25. ERROR STATES

Standardize:

- validation error
- authorization 403
- not found 404
- domain conflict 409 if used
- rate limit 429
- server failure 500

User-facing text should be clear but not leak internals.

---

# 26. SUCCESS FEEDBACK

After significant mutation, provide clear feedback:

- visit created
- vitals saved
- assessment finalized
- observation started
- medication administered
- referral updated
- discharge finalized

Avoid ambiguous silent navigation.

---

# 27. CONFIRMATIONS

Require confirmation for:

- finalize assessment
- administer medication
- stock adjustment
- referral state transitions where significant
- discharge finalization
- entered-in-error
- cancellation
- manual reconciliation approval

Do not add confirmation to trivial navigation.

---

# STAGE 9 — RESPONSIVE / ACCESSIBILITY / THEME

# 28. RESPONSIVE AUDIT

Required viewports:

```text
375px
768px
1024px
1440px
```

Audit:

- sidebar
- forms
- tables
- cards
- workspace tabs
- modal/dialog
- action buttons
- sticky headers
- overflow

For mobile:
- tables may become cards or horizontal scroll when appropriate;
- primary action remains reachable.

---

# 29. ACCESSIBILITY

Ensure:

- keyboard navigation
- visible focus
- labels associated with inputs
- buttons have accessible names
- icons not sole meaning
- status not color-only
- modal focus handling
- sufficient contrast
- dark/light/system all usable

Do not remove semantic HTML for visual convenience.

---

# 30. THEME CONSISTENCY

Use existing semantic tokens.

Light target family:
- background `#F0F9FF`
- surface `#FFFFFF`
- primary `#0284C7`
- text `#0C4A6E`

Dark target family:
- background `#071621`
- surface `#0C2433`
- primary `#38BDF8`
- text `#E0F2FE`

Do not hardcode random colors per page.
Status colors may remain semantic.

Print mode always light.

---

# STAGE 10 — SECURITY UX REVIEW

# 31. UI SECURITY

Verify:

- menu visibility follows permissions;
- server Policy still authoritative;
- direct URL unauthorized -> 403;
- no clinical info in unauthorized dashboard;
- no hidden field trusted for server-authoritative data;
- no user-supplied role escalation;
- no patient identifiers in unsafe logs;
- CSRF on all mutations;
- no GET mutations;
- rate limit for direct credentials login remains active;
- Gate login state/nonce remains intact.

---

# 32. LOCAL DIRECT LOGIN SECURITY

Because v0.19.2 adds direct credentials login:

Audit:

- identifier normalization
- password verification
- remember token behavior
- inactive account rejection
- audit log
- throttle
- session regeneration after login
- logout invalidation
- no timing/user enumeration regression where practical
- no plaintext password log

Add tests as needed.

---

# STAGE 11 — TESTING

# 33. FEATURE TEST MATRIX

Create/update:

`docs/09-testing/PHASE-5A-UX-WORKFLOW-TEST-MATRIX.md`

Test at minimum:

## Auth
- guest -> login
- direct login email
- username
- NIK
- NIS/NIP
- invalid password
- inactive user
- rate limit
- Gate redirect
- logout

## Dashboard
- clinical
- pharmacy
- operational
- management
- technical admin
- minimal user

## Patient
- search
- permission
- no result

## Visit
- intake
- active visit
- workspace navigation
- assessment
- observation
- medication
- referral
- discharge

## UI safety
- unauthorized direct route
- finalized record editing blocked
- destructive confirmations server-side authority unchanged

Do not write brittle tests for exact CSS class unless necessary.

---

# 34. QUALITY GATE

Run:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

If Pint test fails due formatting:
- run Pint;
- repeat full quality gate.

No skipped critical tests.

---

# STAGE 12 — GRAPHIFY

# 35. GRAPHIFY UPDATE

Run:

```bash
graphify update .
```

Never use `--code-only`.

Query:

- login -> dashboard
- role -> menu
- role -> dashboard
- patient -> visit
- visit -> assessment
- visit -> observation
- visit -> medication
- visit -> consultation
- visit -> referral
- visit -> discharge
- discharge -> follow-up
- discharge -> handoff
- pharmacy -> stock ledger
- Gate -> identity
- Attendance -> outbox
- UI route -> Policy

Identify:
- orphan screens
- routes without discoverable navigation
- duplicate workflows
- missing UI links
- dead-end pages

---

# STAGE 13 — DOCUMENTATION

# 36. CREATE

Create:

- `docs/10-delivery/ENVIRONMENT-TRUTH-CORRECTION.md`
- `docs/05-ui/PHASE-5A-ROUTE-AND-SCREEN-INVENTORY.md`
- `docs/05-ui/PHASE-5A-ROLE-NAVIGATION-MATRIX.md`
- `docs/05-ui/PHASE-5A-UX-AUDIT.md`
- `docs/05-ui/PHASE-5A-VISIT-WORKSPACE.md`
- `docs/05-ui/PHASE-5A-RESPONSIVE-ACCESSIBILITY-AUDIT.md`
- `docs/09-testing/PHASE-5A-UX-WORKFLOW-TEST-MATRIX.md`
- `docs/10-delivery/PHASE-5A-CLOSURE.md`

Update:

- `CHANGELOG.md`
- `PROJECT-STATUS.md`
- `READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

---

# STAGE 14 — CHANGE CONTROL

# 37. DO NOT DO IN PHASE 5A

Do not:

- deploy production;
- create production server configs;
- perform fake 24–72h monitoring;
- invent UAT users;
- add WhatsApp integration;
- add advanced analytics;
- redesign database domain unnecessarily;
- replace Gate architecture;
- replace Laravel stack;
- refactor stable services without UX requirement;
- add clinical SOP rules without documentation.

Unknown clinical rule:
`[PERLU DIKONFIRMASI]`

---

# STAGE 15 — GIT

# 38. GIT CHECKPOINT

Before final commit:

```bash
git status
git diff --check
```

Review diff carefully.

Commit suggestion:

```bash
git add -A
git diff --cached --check
git commit -m "feat(phase-5a): normalize pre-production status and complete application workflow UX"
git status
```

Do not commit secrets.

---

# 39. FINAL CLASSIFICATION

Use exactly one:

### `PHASE-5A-COMPLETE`
Documentation truth corrected, core UX workflow continuous, major dead ends resolved, tests green.

### `PHASE-5A-COMPLETE-WITH-BACKLOG`
Main workflow usable but non-critical UX backlog remains.

### `PHASE-5A-BLOCKED`
Critical functional/security/domain issue prevents completion.

---

# 40. FINAL OUTPUT

Report:

1. Environment truth correction summary.
2. Documents corrected from false production wording.
3. Current actual project status.
4. Current version/commit.
5. Route/screen inventory count.
6. Role navigation findings.
7. Login UX result.
8. Dashboard role UX result.
9. Patient discovery result.
10. Patient context component result.
11. Visit workspace result.
12. Vital/assessment UX result.
13. Observation UX result.
14. Pharmacy UX result.
15. Medication UX result.
16. Consultation UX result.
17. Referral UX result.
18. Discharge/follow-up UX result.
19. Empty/error/success state result.
20. Responsive result.
21. Accessibility result.
22. Theme consistency result.
23. Security UX result.
24. Direct login security result.
25. Tests and assertions.
26. Pint/PHPStan/Vite result.
27. Graphify findings.
28. Remaining UX backlog.
29. Git status.
30. Final classification.

Do not begin Phase 5B automatically.
