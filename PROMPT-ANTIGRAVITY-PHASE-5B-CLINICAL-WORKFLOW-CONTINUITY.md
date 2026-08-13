# PROMPT ANTIGRAVITY — PHASE 5B
## Clinical Workflow Continuity & Operational Module Completion
## SABIRA POSKESTREN Health — Local Development

Gunakan **Claude Opus 4.6 Thinking**.

Anda adalah principal Laravel/Livewire product engineer, clinical workflow architect, UX engineer, pharmacy workflow engineer, IAM/security reviewer, QA lead, dan documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

---

# 0. PROJECT TRUTH

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
DEPLOYMENT_STATUS=NOT_DEPLOYED
PRODUCTION_STATUS=NOT_STARTED
CURRENT_BRANCH=master
PHASE_5A_FINAL_COMMIT=931f26a
PHASE_5A_STATUS=PHASE-5A-FINAL-ACCEPTED
```

Jangan melakukan:
- SSH production;
- deployment;
- production telemetry;
- production UAT;
- fake production claims.

Seluruh validasi Phase 5B dilakukan pada local development/test environment.

---

# 1. PHASE 5A BASELINE

Phase 5A telah membuktikan secara nyata:

- hybrid login;
- role-aware sidebar;
- patient directory/search;
- patient context header;
- unified visit workspace;
- visit stage navigation;
- assessment integration;
- medication integration;
- responsive browser verification;
- light/dark/system verification;
- basic accessibility;
- direct-route authorization;
- 216 tests / 874 assertions;
- Pint PASS;
- PHPStan PASS;
- Vite PASS.

Remaining backlog yang relevan:

- Observation belum memiliki full workspace continuity.
- External Consultation belum memiliki full workspace continuity.
- Referral belum memiliki full contextual timeline/step experience.
- Discharge + Follow-Up + Operational Handoff belum terintegrasi penuh ke visit workspace.
- Pharmacy inventory UX masih perlu refinement.
- Management analytics bukan prioritas utama Phase 5B.

---

# 2. PHASE 5B GOAL

Phase 5B harus membuat seluruh episode pelayanan terasa sebagai satu workflow lengkap:

```text
Patient
  ↓
Visit
  ↓
Vitals
  ↓
Assessment
  ↓
Actions
  ↓
Clinical Decision
  ├── Observation
  ├── Medication
  ├── External Consultation
  ├── Referral
  └── Discharge
        ↓
Follow-Up
        ↓
Operational Handoff
```

Target utama:

1. Full Observation Workspace.
2. External Consultation Workspace.
3. Referral Timeline & Action Flow.
4. Discharge / Follow-Up / Handoff continuity.
5. Pharmacy operational UX refinement.
6. Cross-module visit state visibility.
7. Role-aware action availability.
8. Browser verification.
9. Evidence-backed implementation.
10. No documentation-only completion.

---

# 3. EVIDENCE-FIRST RULE

Sebuah fitur hanya boleh disebut `IMPLEMENTED` jika ada source runtime nyata pada:

- `resources/views/**`
- `app/**`
- `routes/**`
- `resources/js/**`
- `resources/css/**`
- `tests/**`

Dokumentasi `.md` bukan implementasi.

Setiap klaim final harus menunjuk ke file source atau test.

---

# 4. PRE-IMPLEMENTATION AUDIT

Sebelum coding, audit source aktual:

```bash
git status
git rev-parse HEAD
php artisan route:list
```

Inspect:

- observation routes/controllers/views/services/policies;
- consultation routes/controllers/views/services/policies;
- referral routes/controllers/views/services/policies;
- discharge/follow-up/handoff routes/controllers/views/services/policies;
- pharmacy routes/controllers/views/services/policies;
- visit workspace components;
- permissions;
- current tests.

Create:

`docs/05-ui/PHASE-5B-BEFORE-IMPLEMENTATION-AUDIT.md`

Classify each module:

- `FULLY-INTEGRATED`
- `PARTIALLY-INTEGRATED`
- `ISOLATED-SCREEN`
- `MISSING-UI`
- `BACKEND-ONLY`
- `BLOCKED`

---

# 5. OBSERVATION WORKSPACE — PRIORITY A

Observation must become a first-class continuation of Visit Workspace.

Target:

```text
Visit
  ↓
Observation
  ├─ Overview
  ├─ Monitoring Records
  ├─ Observation Vitals
  ├─ Clinical Actions
  ├─ Shift Handover
  └─ Outcome
```

Required UI:

## Observation Header
- patient context;
- visit number;
- observation status;
- start time;
- responsible officer;
- duration if safe to derive.

## Observation Timeline
Render chronological operational events:

- started;
- monitoring note;
- vital recorded;
- action recorded;
- handover requested;
- handover acknowledged;
- outcome completed.

Do not invent domain events that do not exist.

## Primary Actions
Depending on permission/state:

- `Tambah Catatan Monitoring`
- `Catat Tanda Vital`
- `Catat Tindakan`
- `Serah Terima Shift`
- `Selesaikan Observasi`

Disable/hide impossible actions based on domain state.

Server-side authorization remains authoritative.

---

# 6. OBSERVATION STATE UX

Display states clearly:

```text
PLANNED
IN_PROGRESS
COMPLETED
CANCELLED
```

or actual enum/status names in the model.

Do not invent status names.

Completed/cancelled episodes must not show active mutation actions.

Add tests for state-based action availability.

---

# 7. EXTERNAL CONSULTATION WORKSPACE — PRIORITY A

Create coherent consultation workflow:

```text
Visit
  ↓
Clinical Summary
  ↓
Clinical Question
  ↓
Transmission
  ↓
External Advice
  ↓
Local Clinical Decision
```

Key UX principle:

**External Advice ≠ Local Clinical Order**

Clearly differentiate:

### External Advice
- source clinician/partner;
- received timestamp;
- advice summary;
- transmission history.

### Local Decision
- responsible local clinician;
- decision;
- timestamp;
- resulting local plan.

Never auto-transform external advice into:
- diagnosis;
- medication order;
- discharge;
- referral decision.

---

# 8. CONSULTATION VERSION / TRANSMISSION UX

If versioned summary/transmission models already exist:

Show:

- version number;
- checksum/integrity state where useful;
- sent timestamp;
- transport status;
- acknowledgement/failure if supported.

Do not expose raw payloads/secrets.

If transport is fake/local only:
label:

`LOCAL-DEVELOPMENT / SIMULATED TRANSPORT`

Do not call it a real external transmission.

---

# 9. REFERRAL WORKSPACE — PRIORITY A

Build a contextual referral experience integrated with Visit Workspace.

Target conceptual timeline:

```text
Referral Created
  ↓
Prepared
  ↓
Transport Planned
  ↓
Departed / In Transit
  ↓
Arrived
  ↓
Clinical Handover
  ↓
Accepted / Declined
  ↓
Return
  ↓
Local Return Review
```

Use actual domain statuses/events only.

Do NOT merge:

- arrival;
- handover;
- acceptance.

They are distinct events.

---

# 10. REFERRAL OVERVIEW

Show:

- referral number;
- destination;
- reason/clinical summary according permission;
- status;
- transport status;
- companion;
- handover;
- destination event;
- return state;
- local review state.

Actions should be grouped by lifecycle and permission.

Example:

```text
Transport
- Atur kendaraan
- Atur pendamping

Handover
- Catat serah terima

Destination
- Catat tiba
- Catat diterima/ditolak

Return
- Catat kepulangan
- Lakukan review lokal
```

Do not expose all actions simultaneously if state makes them invalid.

---

# 11. REFERRAL TIMELINE

Create reusable timeline component if appropriate.

Use semantic markup.

Each timeline event:

- type;
- timestamp;
- actor role if safe;
- status;
- short operational summary.

No raw sensitive payload.

Responsive at mobile width.

---

# 12. DISCHARGE WORKSPACE — PRIORITY A

Integrate discharge into Visit Workspace.

Show:

- readiness;
- draft/final status;
- discharge type;
- destination;
- activity recommendation;
- rest recommendation;
- restriction notes;
- follow-up required;
- follow-up plan;
- operational handoff;
- amendment history.

States:

```text
DRAFT
FINALIZED
AMENDED
ENTERED_IN_ERROR
```

Use actual domain names.

---

# 13. DISCHARGE READINESS UX

If `EvaluateVisitDischargeReadinessAction` exists:

Render readiness as structured checklist.

Examples only if backed by actual rule:

```text
Assessment finalized
Observation completed
Referral resolved
```

Do not invent clinical requirements.

Warnings must distinguish:

- blocker;
- warning;
- informational.

No automatic clinical decision.

---

# 14. FOLLOW-UP WORKSPACE

Integrate:

- follow-up required?
- due date/time;
- responsible role;
- status;
- completed/cancelled;
- notes where authorized.

Dashboard/visit workspace should make overdue/upcoming tasks discoverable.

Do not invent urgency thresholds.

---

# 15. OPERATIONAL HANDOFF UX

Operational handoff must preserve minimum necessary privacy.

Recipient examples:
- dorm supervisor;
- operational staff;
- teacher if authorized by design.

Allowed operational data may include:
- rest/activity restriction;
- follow-up instruction;
- acknowledgement requirement.

Do NOT expose:
- diagnosis;
- detailed clinical narrative;
- medication details;
- allergy list;
- vitals.

Verify DTO/builder and UI both respect privacy.

---

# 16. VISIT WORKSPACE CROSS-MODULE STATUS

Upgrade visit overview so each module card shows real state:

```text
Assessment        FINALIZED
Observation       IN PROGRESS
Medication        2 ACTIVE ORDERS
Consultation      ADVICE RECEIVED
Referral          NOT STARTED
Discharge         DRAFT
Follow-Up         DUE
```

Use actual relations/state.

Avoid N+1 queries.

Eager-load relationships.

---

# 17. NEXT ACTION UX REFINEMENT

The UI next-action hint must use only existing workflow state.

Examples:

- observation active -> `Lanjutkan monitoring observasi`
- external advice received but no local decision -> `Catat keputusan klinis lokal`
- referral returned but no review -> `Lakukan review kepulangan rujukan`
- discharge finalized with follow-up required -> `Pastikan rencana follow-up tercatat`

Do not produce diagnosis or treatment advice.

Add tests for state mapping.

---

# 18. PHARMACY OPERATIONAL UX — PRIORITY B

Audit current:

- medicines;
- inventory;
- batches;
- receipt;
- adjustment;
- movement history.

Improve discoverability:

## Inventory Filters
- medicine
- location
- status
- expiry state
- quarantine/depleted

## Batch Information
- batch number
- expiry
- quantity
- location
- status

## Visual states
- normal
- low stock only if threshold configured
- expiring
- expired
- quarantined
- depleted

Unknown threshold:
`[PERLU DIKONFIRMASI]`

Do not invent reorder policy.

---

# 19. PHARMACY STOCK MOVEMENT SAFETY

UX must clearly distinguish:

```text
Receipt
Adjustment In
Adjustment Out
Medication Administration Issue
Reversal
```

Do not provide generic unrestricted mutation form.

Show append-only history.

For adjustment:
- reason required;
- confirmation;
- permission required.

---

# 20. MEDICATION SAFETY UX

Review medication screen:

- active allergy warning;
- order status;
- administration status;
- batch selection;
- available quantity;
- issue quantity;
- held/refused/missed states;
- reversal/entered-in-error history.

Do not create automated prescribing recommendations.

---

# 21. ROLE-AWARE ACTION MATRIX

Create:

`docs/05-ui/PHASE-5B-ROLE-ACTION-MATRIX.md`

Map:

- clinical;
- pharmacy;
- dorm/operational;
- management;
- technical admin.

For each action:

```text
VIEW
CREATE
UPDATE
FINALIZE
ACKNOWLEDGE
CORRECT
ADMINISTER
EXPORT
```

Verify UI visibility against Policy/Gate.

No role may obtain new authority only because button was added.

---

# 22. EMPTY / LOADING / ERROR STATES

Add meaningful states for:

Observation:
- no active episode;
- no monitoring record.

Consultation:
- no consultation;
- awaiting external advice;
- advice received, local decision pending.

Referral:
- no referral;
- transport pending;
- returned, review pending.

Discharge:
- no discharge draft;
- follow-up not required;
- handoff awaiting acknowledgement.

Pharmacy:
- no batch;
- no movement;
- no inventory match.

No blank tables.

---

# 23. CONFIRMATION DIALOGS

Use confirmations for:

- complete observation;
- acknowledge handover;
- submit external advice/local decision if immutable;
- referral major state transition;
- record return;
- finalize discharge;
- amend discharge;
- stock adjustment;
- medication administration/reversal.

No confirmation for simple navigation.

---

# 24. RESPONSIVE VERIFICATION

Browser verify at:

```text
375x812
768x1024
1024x768
1440x900
```

Must inspect:

- observation timeline;
- consultation workspace;
- referral timeline;
- discharge checklist;
- pharmacy inventory;
- medication workspace.

Record actual results.

Do not infer from Tailwind classes alone.

---

# 25. LIGHT / DARK / SYSTEM

Verify all changed screens in:

- Light
- Dark
- System

Ensure:
- status badge contrast;
- timeline legibility;
- warning severity;
- form controls;
- modal/dialog;
- empty states.

---

# 26. ACCESSIBILITY

Implement:

- semantic headings;
- labels;
- accessible button names;
- keyboard focus;
- timeline semantics;
- status text not color-only;
- modal focus handling;
- proper table headers.

Use wording:
`WCAG 2.1 AA-oriented`

Do not claim certification.

---

# 27. PERFORMANCE / N+1 AUDIT

Because Visit Workspace aggregates many relations:

Audit:

- query count;
- eager loading;
- nested relations;
- dashboards;
- referral timeline;
- observation timeline.

Use Laravel query logging/test where appropriate.

Do not prematurely cache mutable clinical records.

---

# 28. SECURITY / PRIVACY REGRESSION

Verify:

- all mutation routes remain POST/PATCH/DELETE;
- CSRF intact;
- policies enforced;
- direct unauthorized route -> 403;
- technical admin no clinical escalation;
- operational views minimum necessary;
- Attendance DTO still excludes clinical keys;
- private documents still private;
- finalized records immutable except controlled amendment/correction.

---

# 29. TEST SUITE

Add new test suite:

`tests/Feature/Ui/Phase5BClinicalWorkflowContinuityTest.php`

At minimum cover:

## Observation
- patient/visit context rendered;
- state-based actions;
- completed state blocks mutation;
- timeline visible.

## Consultation
- summary/question/advice/local-decision separation;
- unauthorized denied;
- external advice does not mutate local decision automatically.

## Referral
- lifecycle timeline;
- state-based actions;
- return review;
- handover != acceptance.

## Discharge
- readiness;
- finalize;
- follow-up;
- operational handoff minimum necessary.

## Pharmacy
- filters;
- batch status;
- negative stock still prevented;
- adjustment permissions.

## Role/privacy
- dorm cannot see diagnosis;
- management aggregate only;
- technical admin no clinical detail.

Total test count should increase.

---

# 30. MANUAL BROWSER VERIFICATION DOC

Create:

`docs/05-ui/PHASE-5B-VISUAL-VERIFICATION.md`

Record:

```text
MODULE
VIEWPORT
THEME
RESULT
ISSUE
FIX
```

Do not fabricate PASS.

---

# 31. GRAPHIFY

After implementation:

```bash
graphify update .
```

No `--code-only`.

Query:

- visit -> observation
- visit -> consultation
- consultation -> external advice
- consultation -> local decision
- visit -> referral
- referral -> transport
- referral -> handover
- referral -> return
- referral -> local review
- visit -> discharge
- discharge -> follow-up
- discharge -> handoff
- pharmacy -> batch
- pharmacy -> stock movement
- role -> action
- route -> Policy

Identify orphan/dead-end pages.

---

# 32. QUALITY GATE

Run:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

No critical skips.

---

# 33. DOCUMENTATION

Create:

- `docs/05-ui/PHASE-5B-BEFORE-IMPLEMENTATION-AUDIT.md`
- `docs/05-ui/PHASE-5B-ROLE-ACTION-MATRIX.md`
- `docs/05-ui/PHASE-5B-OBSERVATION-WORKSPACE.md`
- `docs/05-ui/PHASE-5B-CONSULTATION-WORKSPACE.md`
- `docs/05-ui/PHASE-5B-REFERRAL-WORKSPACE.md`
- `docs/05-ui/PHASE-5B-DISCHARGE-FOLLOWUP-WORKSPACE.md`
- `docs/05-ui/PHASE-5B-PHARMACY-UX.md`
- `docs/05-ui/PHASE-5B-VISUAL-VERIFICATION.md`
- `docs/09-testing/PHASE-5B-TEST-MATRIX.md`
- `docs/10-delivery/PHASE-5B-CLOSURE.md`

Update:

- `CHANGELOG.md`
- `PROJECT-STATUS.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

---

# 34. CHANGELOG

Add a real development version, for example:

```text
0.20.0 — Phase 5B Clinical Workflow Continuity
```

Only list implemented code.

Never call it production.

---

# 35. DIFF HYGIENE

Before commit:

```bash
git status
git diff --stat
git diff --check
```

Separate:
- source;
- tests;
- docs;
- Graphify-generated churn.

Do not allow generated cache to obscure source summary.

---

# 36. GIT

Suggested commit:

```bash
git add -A
git diff --cached --check
git commit -m "feat(workflow): complete observation referral discharge and pharmacy continuity"
```

Do not commit secrets.

---

# 37. FINAL CLASSIFICATION

Use exactly one:

### `PHASE-5B-COMPLETE`
Priority A workflows fully implemented and browser verified; tests green.

### `PHASE-5B-COMPLETE-WITH-MINOR-BACKLOG`
Core continuity complete; only non-critical Priority B polish remains.

### `PHASE-5B-MANUAL-VERIFICATION-PENDING`
Code/tests complete, browser verification incomplete.

### `PHASE-5B-PARTIAL`
Some major modules still isolated.

### `PHASE-5B-BLOCKED`
Critical security/domain/technical issue.

---

# 38. FINAL REPORT

Report:

1. Starting commit.
2. Pre-implementation audit result.
3. Observation implementation.
4. Observation state/timeline result.
5. Consultation implementation.
6. External advice/local decision separation.
7. Referral implementation.
8. Referral timeline/state actions.
9. Discharge implementation.
10. Follow-up implementation.
11. Operational handoff/privacy result.
12. Visit cross-module status result.
13. Next-action refinement.
14. Pharmacy inventory UX.
15. Pharmacy stock movement safety.
16. Medication safety UX.
17. Role/action matrix.
18. Empty/error/confirmation states.
19. Responsive browser results.
20. Theme results.
21. Accessibility result.
22. Performance/N+1 result.
23. Security/privacy regression.
24. New test count.
25. Total tests/assertions.
26. Pint/PHPStan/Vite.
27. Graphify findings.
28. Source-only diff summary.
29. Remaining backlog.
30. Commit SHA.
31. Final classification.

Do not begin Phase 5C automatically.
