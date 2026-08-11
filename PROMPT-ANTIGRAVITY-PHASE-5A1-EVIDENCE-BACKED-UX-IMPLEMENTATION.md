# PROMPT ANTIGRAVITY — PHASE 5A1
## Evidence-Backed UX Implementation Audit & Core Workflow Code Completion
## SABIRA POSKESTREN Health — Local Development

Gunakan **Claude Opus 4.6 Thinking**.

Anda adalah principal Laravel/Livewire product engineer, UX architect, health-information-system workflow engineer, security reviewer, dan QA lead untuk proyek **SABIRA POSKESTREN Health**.

---

# 0. SOURCE OF TRUTH

Current project reality:

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
DEPLOYMENT_STATUS=NOT_DEPLOYED
PRODUCTION_STATUS=NOT_STARTED
CURRENT_BASELINE=v0.19.2+
CURRENT_BRANCH=master
LATEST_PHASE_5A_COMMIT=6a65330
```

Phase 5A sebelumnya melaporkan:

`PHASE-5A-COMPLETE`

tetapi transcript eksekusi menunjukkan mayoritas perubahan Phase 5A terjadi pada dokumentasi, bukan source UI/runtime.

Karena itu:

**JANGAN menerima laporan Phase 5A sebelumnya sebagai bukti bahwa UX sudah diimplementasikan.**

Tujuan Phase 5A1 adalah:

1. membandingkan klaim Phase 5A dengan source code nyata;
2. mengklasifikasikan setiap klaim;
3. mengimplementasikan gap UX kode yang benar-benar belum ada;
4. memprioritaskan core operational journey;
5. menambahkan regression tests yang membuktikan implementasi;
6. mengoreksi dokumentasi agar sesuai kenyataan.

---

# 1. HARD RULE — DOCUMENTATION IS NOT IMPLEMENTATION

Sebuah fitur UX hanya boleh disebut `IMPLEMENTED` jika ada bukti source code nyata pada:

- `resources/views/**`
- `app/Livewire/**`
- `app/Http/**`
- `resources/js/**`
- `resources/css/**`
- component classes/views
- route/controller integration
- automated tests

Dokumentasi `.md` saja tidak dihitung.

Automated test lama yang tidak menguji UX baru juga tidak dihitung sebagai bukti implementasi Phase 5A.

---

# 2. FIRST: AUDIT THE ACTUAL PHASE 5A COMMIT

Run:

```bash
git status
git rev-parse HEAD
git show --stat --oneline 6a65330
git diff --name-status 6a65330^..6a65330
git diff --stat 6a65330^..6a65330
```

Classify commit:

- `CODE-AND-DOCS`
- `DOCS-ONLY`
- `MOSTLY-DOCS`
- `MIXED`

If no runtime/UI source files changed, explicitly state:

`PHASE-5A-PREVIOUS-IMPLEMENTATION-CLAIMS-NOT-PROVEN`

Do not hide this result.

---

# 3. CLAIM-BY-CLAIM EVIDENCE REGISTER

Read:

- `docs/05-ui/PHASE-5A-UX-AUDIT.md`
- `docs/05-ui/PHASE-5A-VISIT-WORKSPACE.md`
- `docs/05-ui/PHASE-5A-ROLE-NAVIGATION-MATRIX.md`
- `docs/05-ui/PHASE-5A-RESPONSIVE-ACCESSIBILITY-AUDIT.md`
- `docs/10-delivery/PHASE-5A-CLOSURE.md`
- `CHANGELOG.md`

Create:

`docs/05-ui/PHASE-5A1-IMPLEMENTATION-EVIDENCE-REGISTER.md`

For every prior claim, classify:

- `EXISTING-BEFORE-PHASE-5A`
- `IMPLEMENTED-IN-CODE`
- `DOC-ONLY`
- `PARTIALLY-IMPLEMENTED`
- `NOT-IMPLEMENTED`
- `NEEDS-MANUAL-UI-VERIFICATION`

At minimum audit claims:

- hybrid login UX
- role-aware dashboard
- patient smart search
- patient context header
- sticky context header
- unified visit workspace
- visit stage navigation
- vital-sign units
- assessment draft/final UI
- observation timeline
- pharmacy stock/expiry visual states
- medication administration batch UX
- consultation separation
- referral stepper/timeline
- discharge/follow-up continuity
- empty states
- success feedback
- confirmation dialogs
- 375/768/1024/1440 responsive behavior
- WCAG 2.1 AA claims
- theme consistency

Do not mark PASS without locating code or performing a real UI/manual browser verification.

---

# 4. SCOPE CONTROL

Do not try to redesign the whole application in one uncontrolled patch.

Phase 5A1 implementation priority:

## Priority A — MUST COMPLETE
1. Login usability
2. Role-aware dashboard entry/navigation
3. Patient search/list usability
4. Patient context header
5. Unified Visit Workspace
6. Visit stage navigation
7. Empty/error/success states for core workflow
8. Mobile/responsive usability for core workflow
9. Accessibility for core workflow

## Priority B — COMPLETE IF SAFE
10. Observation workspace continuity
11. Medication workspace continuity
12. Referral/discharge continuity

## Priority C — BACKLOG IF LARGE
13. Full pharmacy redesign
14. Advanced management reports UX
15. patient/self-service portal
16. analytics
17. WhatsApp

Do not mark Phase 5A1 blocked just because Priority C is deferred.

---

# 5. MANUAL APPLICATION WALKTHROUGH BEFORE CODING

Run local app safely.

Use existing local development method.

Inspect in browser at minimum:

```text
/login
/dashboard
/patients
/visits
```

Then inspect a representative visit workspace using synthetic/test data.

Do not create unnecessary real personal/medical data.

For each page capture:

- current layout
- primary action
- dead ends
- missing navigation
- confusing labels
- mobile issues
- permission issues
- empty states

Create:

`docs/05-ui/PHASE-5A1-BEFORE-IMPLEMENTATION-AUDIT.md`

---

# 6. LOGIN UX — VERIFY, THEN FIX

Current v0.19.2 includes direct credentials + Gate SSO.

Verify actual code and browser behavior.

Required UI:

### Identifier
Label should make clear accepted identity options:

`Email / Username / NIK / NIS / NIP`

But avoid unsafe account enumeration.

### Password
- show/hide
- autocomplete behavior appropriate
- validation
- error message

### Remember Me
- clear label
- correct backend binding

### Gate
Secondary action:

`Masuk dengan SABIRA Gate SSO`

### States
- invalid credentials
- inactive account
- throttled
- Gate unavailable/disabled if applicable

Security must remain:
- session regeneration
- rate limit
- audit
- CSRF
- logout invalidate

Do not weaken security for UX.

---

# 7. ROLE-AWARE NAVIGATION — ACTUAL CODE

Inspect the canonical app shell/sidebar.

Implement menu visibility based on local permission.

Required behavior:

### Clinical
Show relevant:
- Dashboard
- Patients
- Visits
- Observations
- Medication
- Referral
- Follow-Up

### Pharmacy
Show:
- Pharmacy dashboard/inventory
- Medicine
- Batch
- Stock movements
- medication tasks where authorized

### Operational/Dorm
Show only minimum-necessary operational views.

### Management
Show aggregate dashboard/reporting.

### Technical Admin
Show:
- users
- roles
- Gate sync
- audit
- integrations/system

No clinical menu without clinical permission.

Menu visibility is UX only.
Policies remain server authority.

Add tests for direct-route 403.

---

# 8. PATIENT LIST & SEARCH — ACTUAL IMPLEMENTATION

Inspect actual patient routes/controller/view.

Implement/verify:

- search by name
- NIS/NIP
- patient number/MRN
- query preservation
- pagination
- clear/reset
- no-result message
- loading state if async
- responsive table/card behavior

Do not expose excessive PII.

Primary action:
`Buka Pasien`

Authorized users may get:
`Daftarkan Kunjungan`

---

# 9. PATIENT CONTEXT COMPONENT

Create a reusable Blade/Livewire component if none exists.

Example responsibility:

```text
resources/views/components/patient-context-header.blade.php
```

or existing design-system equivalent.

Show only appropriate context:

- name
- patient/MRN number
- NIS/NIP if authorized/appropriate
- type/status
- visit state
- active allergy indicator

Do NOT globally dump:
- diagnosis
- address
- contact
- detailed medical history

Use on:

- visit detail
- assessment
- observation
- medication
- consultation
- referral
- discharge

The component should remain consistent across screens.

---

# 10. UNIFIED VISIT WORKSPACE — CORE DELIVERABLE

This is the primary Phase 5A1 implementation.

Inspect current:

```text
/visits/{visit}
```

and all child routes.

Build one consistent Visit Workspace shell.

Suggested component/layout:

```text
VisitWorkspaceLayout
 ├─ PatientContextHeader
 ├─ VisitHeader
 ├─ StageNavigation
 └─ PageContent
```

Do not create unnecessary abstraction if Blade components are enough.

---

# 11. VISIT HEADER

Display:

- visit number
- arrival timestamp
- visit status
- responsible staff if existing
- current operational state

No excessive clinical content in header.

---

# 12. VISIT STAGE NAVIGATION

Target stages:

```text
Overview
Intake
Vital Signs
Assessment
Actions
Observation
Medication
Consultation
Referral
Discharge
Follow-Up
```

Rules:

- only show stages user may access;
- clearly indicate current stage;
- indicate completed stage where domain supports it;
- indicate unavailable stage safely;
- status not color-only;
- mobile usable;
- direct URLs still Policy protected.

Do not alter backend state machine merely to make navigation appear complete.

---

# 13. VISIT OVERVIEW

Create/upgrade a visit overview page that summarizes workflow state.

Sections:

- visit information
- latest vitals summary if authorized
- assessment status
- observation status
- medication status
- consultation status
- referral status
- discharge status
- follow-up status

Use links/actions to continue workflow.

Do not expose information user cannot access.

This page should answer:

`Apa yang sudah selesai dan apa langkah berikutnya?`

---

# 14. NEXT ACTION ENGINE — UI ONLY

Do not invent new clinical decision logic.

Based only on existing domain state, provide a safe UI hint such as:

```text
Langkah berikutnya tersedia:
- Catat tanda vital
- Lengkapi pengkajian
- Lanjutkan observasi
- Finalisasi kepulangan
```

Do NOT recommend diagnosis/treatment.

If domain state cannot determine an action:
- omit suggestion.

---

# 15. EMPTY / SUCCESS / ERROR STATES

Implement reusable consistency where practical.

Required core empty states:

```text
Belum ada tanda vital.
Belum ada pengkajian.
Belum ada tindakan.
Belum ada episode observasi.
Belum ada instruksi obat.
Belum ada konsultasi.
Belum ada rujukan.
Belum ada rencana kepulangan.
```

Success feedback examples:

```text
Kunjungan berhasil dibuat.
Tanda vital berhasil disimpan.
Draft pengkajian tersimpan.
Pengkajian berhasil difinalisasi.
```

Error messages:
- no stack trace
- no SQL
- no secret
- useful recovery hint

---

# 16. CONFIRMATIONS

Use confirmation dialog for high-impact actions:

- finalize assessment
- administer medication
- cancel visit
- entered-in-error
- finalize discharge
- important referral transition
- stock correction

Do not add confirmation for ordinary navigation.

Server-side authorization/state validation remains required.

---

# 17. RESPONSIVE CORE WORKFLOW

Actually inspect and implement at:

```text
375px
768px
1024px
1440px
```

Priority screens:

- login
- app shell
- patient list
- visit overview
- assessment
- observation
- medication
- referral
- discharge

Fix actual issues.

For mobile:
- sidebar collapses
- patient header wraps correctly
- visit stage nav scrolls or transforms appropriately
- action buttons remain reachable
- forms do not overflow
- tables use horizontal scroll/cards as appropriate

Do not state "optimal" without inspecting.

---

# 18. ACCESSIBILITY CORE WORKFLOW

Audit actual markup.

Implement:

- input labels
- error `aria-describedby` where appropriate
- button text/accessibility names
- focus-visible state
- keyboard navigation
- modal focus behavior where supported
- no icon-only meaning
- status text + color
- semantic heading order

Do not claim formal WCAG certification.

Use wording:

`WCAG 2.1 AA-oriented implementation`

unless a full compliance audit actually occurred.

---

# 19. THEME

Verify actual Light/Dark/System behavior on changed pages.

Use semantic tokens.

Do not introduce per-page random colors.

Check:
- hover
- border
- input
- badge
- focus
- modal
- table
- empty state
- mobile navigation

---

# 20. OBSERVATION CONTINUITY — PRIORITY B

If observation screens are currently isolated, integrate them with Visit Workspace.

Show:
- status
- responsible officer
- latest monitoring
- handover
- outcome

Actions:
- add monitoring
- add vital if existing route
- handover
- complete

Respect Policy/state machine.

---

# 21. MEDICATION CONTINUITY — PRIORITY B

Within Visit Workspace show:
- active orders
- status
- allergy warning
- scheduled/administered state
- stock availability

Link to existing medication workspace.

Do not rewrite PharmacyService/MedicationService unless a real bug is found.

---

# 22. REFERRAL / DISCHARGE CONTINUITY — PRIORITY B

Create coherent navigation/timeline using existing state.

Referral should distinguish:
- prepared
- transport
- handover
- destination
- return
- local review

Discharge:
- draft
- readiness
- finalized
- amended
- follow-up
- operational handoff

Do not merge domain events.

---

# 23. DO NOT FAKE VISUAL FEATURES

If something cannot be implemented safely in this phase, classify it in backlog.

Do not write docs saying:
- "sticky"
- "stepper"
- "responsive"
- "accessible"
- "timeline"
- "unified workspace"

unless the source code actually contains the implementation.

---

# 24. TESTS — NEW PHASE 5A1 TESTS REQUIRED

Do not finish with only the previous 205 tests.

Add meaningful tests for new code.

Create/update:

`tests/Feature/Ui/Phase5A1CoreWorkflowUxTest.php`

or appropriate structure.

Test:

## Auth
- login page content/options
- direct login still secure
- Gate option exists
- guest protected

## Navigation
- clinical sees correct entries
- technical admin does not see clinical menu
- unauthorized direct route remains 403

## Patient
- search by supported fields
- no-result state
- pagination/query preservation

## Visit Workspace
- correct patient context
- stages rendered by permission
- current/completed status where applicable
- restricted stage hidden/denied

## State UX
- empty states
- success flash where applicable
- finalized mutation restrictions unchanged

Avoid brittle CSS-only assertions.

The final test count must increase if actual behavior was added.

---

# 25. BROWSER / MANUAL VERIFICATION

Automated HTTP tests cannot prove visual responsiveness.

Create:

`docs/05-ui/PHASE-5A1-MANUAL-UI-VERIFICATION.md`

Record actual verification for:

```text
375
768
1024
1440
```

For each:
- PASS
- ISSUE
- FIXED
- NOT-VERIFIED

Do not fabricate screenshots or manual results.

If Antigravity cannot perform browser visual inspection:
- mark `MANUAL-VERIFICATION-REQUIRED`.

---

# 26. QUALITY GATE

Run:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

If code style fails:
```bash
./vendor/bin/pint
```

then repeat.

No critical skipped tests.

---

# 27. GRAPHIFY

After code implementation:

```bash
graphify update .
```

Never `--code-only`.

Query:

- patient -> visit workspace
- visit workspace -> assessment
- visit workspace -> observation
- visit workspace -> medication
- visit workspace -> referral
- visit workspace -> discharge
- role -> navigation
- menu -> Policy
- UI component reuse
- orphan user-facing views

---

# 28. DOCUMENTATION CORRECTION

Update Phase 5A docs to distinguish:

- existing capability
- audited capability
- newly implemented Phase 5A1 capability
- manual verification pending

Correct `PHASE-5A-CLOSURE.md`.

Do not erase historical report; add correction.

Example:

```text
Phase 5A documentation audit completed.
Phase 5A1 provides evidence-backed runtime UI implementation.
```

---

# 29. CHANGELOG

Add a new version entry, e.g.:

```text
0.19.3 — Phase 5A1 Core UX Implementation
```

Only list features actually implemented.

Do not call it production.

---

# 30. GIT

Before commit:

```bash
git status
git diff --stat
git diff --check
```

Verify there are actual source/runtime changes.

Expected to see some of:

```text
resources/views/**
app/**
resources/js/**
resources/css/**
tests/**
```

If only docs changed:
- DO NOT classify Phase 5A1 complete.

Commit suggestion:

```bash
git add -A
git diff --cached --check
git commit -m "feat(ux): implement evidence-backed patient and visit workflow experience"
```

---

# 31. FINAL CLASSIFICATION

Use exactly one:

### `PHASE-5A1-COMPLETE`
Core UX implemented in source code, tests added, manual verification adequately completed or clearly documented.

### `PHASE-5A1-COMPLETE-WITH-MANUAL-VERIFICATION-PENDING`
Code implemented and automated tests pass, but visual browser verification remains.

### `PHASE-5A1-PARTIAL`
Only some core workflow implementation completed.

### `PHASE-5A1-DOCS-ONLY-BLOCKED`
Agent failed to make runtime/source changes.

### `PHASE-5A1-BLOCKED`
Critical technical/security/domain blocker.

---

# 32. FINAL OUTPUT

Report:

1. Phase 5A previous commit audit.
2. Code-vs-document classification.
3. Claims proven existing before Phase 5A.
4. Claims that were documentation-only.
5. Source files actually changed in Phase 5A1.
6. Login UX implementation.
7. Role navigation implementation.
8. Patient search implementation.
9. Patient context component.
10. Visit Workspace implementation.
11. Visit stage navigation.
12. Visit overview/next-action UX.
13. Empty/success/error states.
14. Responsive code changes.
15. Accessibility changes.
16. Observation continuity.
17. Medication continuity.
18. Referral/discharge continuity.
19. New tests added.
20. Total tests/assertions.
21. Manual browser verification.
22. Pint/PHPStan/Vite.
23. Graphify.
24. Remaining Priority B/C backlog.
25. Git diff summary.
26. Commit SHA.
27. Final classification.

Do not begin Phase 5B automatically.
