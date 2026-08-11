# PROMPT ANTIGRAVITY — PHASE 5A2
## Visual Browser Verification, Diff Hygiene Audit, and Final UX Acceptance
## SABIRA POSKESTREN Health — Local Development

Gunakan **Claude Opus 4.6 Thinking**.

KONTEKS:

Phase 5A1 telah menghasilkan implementasi runtime nyata dengan commit:

`18ff7c2`
`feat(ux): implement evidence-backed patient and visit workflow experience`

Perubahan yang dilaporkan:
- Blade components baru
- role-aware sidebar
- patient search
- patient context header
- visit workspace
- visit stage navigation
- UX state improvements
- 11 UI workflow tests baru
- 216 tests / 874 assertions PASS

Namun sebelum lanjut Phase 5B, masih ada dua gap yang harus ditutup:

1. Klaim responsive/manual browser verification belum memiliki bukti eksekusi browser yang jelas.
2. Diff Phase 5A1 sangat besar:
   `26604 insertions(+), 19397 deletions(-)`
   sehingga perlu audit apakah itu perubahan substantif atau churn formatting/line-ending/generated files.

Target final Phase 5A:

`PHASE-5A-FINAL-ACCEPTED`

Jangan masuk Phase 5B sebelum dua gap ini selesai.

---

# 1. PROJECT TRUTH

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
DEPLOYMENT=NOT_DEPLOYED
PRODUCTION=NOT_STARTED
CURRENT_PHASE=5A2
```

Tidak ada production validation pada fase ini.

---

# 2. AUDIT COMMIT 18ff7c2

Run:

```bash
git status
git show --stat --oneline 18ff7c2
git show --numstat --format= 18ff7c2
git diff --name-status 18ff7c2^..18ff7c2
git diff --stat 18ff7c2^..18ff7c2
```

Categorize every changed file:

- runtime source
- Blade/UI
- test
- docs
- Graphify/generated
- formatting-only
- suspicious/unexpected

Create:

`docs/05-ui/PHASE-5A2-DIFF-HYGIENE-AUDIT.md`

---

# 3. DETECT LINE-ENDING / FORMAT CHURN

Check suspicious files:

```bash
git diff --ignore-space-at-eol --stat 18ff7c2^..18ff7c2
git diff -w --stat 18ff7c2^..18ff7c2
```

Also inspect:

```bash
git diff --check 18ff7c2^..18ff7c2
```

Determine whether large insert/delete numbers are caused by:
- CRLF/LF conversion;
- trailing whitespace cleanup;
- generated Graphify outputs;
- document regeneration;
- actual source changes.

If runtime files were fully rewritten unintentionally:
- restore/reapply minimal changes safely.

Do not rewrite history destructively.

---

# 4. SOURCE CHANGE SUMMARY

Produce actual source-only summary excluding:

```text
docs/**
graphify-out/**
vendor/**
node_modules/**
public/build/**
```

Use:

```bash
git diff --stat 18ff7c2^..18ff7c2 -- \
'app/**' \
'resources/**' \
'routes/**' \
'tests/**'
```

Report:

```text
SOURCE_FILES_CHANGED=
SOURCE_INSERTIONS=
SOURCE_DELETIONS=
DOC_FILES_CHANGED=
GENERATED_FILES_CHANGED=
```

The final report must distinguish source code from documentation/generated churn.

---

# 5. START LOCAL APP FOR BROWSER VERIFICATION

Use the project's normal local development flow.

Ensure:
- local MariaDB ready;
- migrations current;
- assets built/dev server available;
- no production settings.

Use synthetic/test accounts and synthetic patient data.

Do not enter real medical data unnecessarily.

---

# 6. REAL BROWSER WALKTHROUGH

Actually open the application in a browser.

Do not infer visual behavior from Blade source.

Verify:

## Login
- `/login`
- identifier label
- password show/hide
- remember me
- Gate SSO button
- validation state
- dark/light

## Dashboard
- correct role menu
- sidebar
- topbar user info
- logout

## Patient
- `/patients`
- search
- reset
- empty state
- result list
- quick visit action

## Patient Detail
- patient context header
- allergy banner
- no overflow

## Visit Workspace
- patient context
- visit header
- stage navigation
- next-action banner
- clinical cards
- empty states

## Assessment
- context header
- stage navigation
- SOAP form

## Medication
- context header
- stage navigation
- medication list/form

If observation/referral/discharge integration is only partial:
- mark it PARTIAL.
- do not claim COMPLETE.

---

# 7. VIEWPORT VERIFICATION

Test actual rendered UI at:

```text
375 x 812
768 x 1024
1024 x 768
1440 x 900
```

For each viewport verify:

- no horizontal page overflow;
- sidebar behavior;
- stage navigation;
- patient context wrapping;
- buttons reachable;
- forms fit;
- tables behave;
- modal/dialog fit;
- topbar usable.

Record each as:

- PASS
- ISSUE
- FIXED
- NOT-VERIFIED

Create:

`docs/05-ui/PHASE-5A2-VISUAL-VERIFICATION.md`

Do not mark PASS unless actually rendered.

---

# 8. LIGHT / DARK / SYSTEM

For changed screens verify:

```text
Light
Dark
System
```

Check:
- text contrast;
- cards;
- borders;
- form fields;
- alert banners;
- stage nav;
- hover/focus;
- disabled states.

Do not claim WCAG certification.

Use wording:
`WCAG-oriented visual verification`.

---

# 9. ACCESSIBILITY MANUAL CHECK

Actually verify basic keyboard behavior:

- Tab navigation
- visible focus
- password toggle keyboard accessible
- menu controls accessible
- buttons have visible labels
- stage nav understandable without color
- modal focus behavior if modal present

If not testable:
`MANUAL-A11Y-VERIFICATION-PENDING`.

---

# 10. SECURITY REGRESSION

Ensure visual changes did not bypass server authority.

Verify:

- guest -> login
- unauthorized direct clinical route -> 403
- technical admin does not receive clinical data
- POST logout + CSRF
- direct login throttle intact
- Gate state/nonce intact
- no GET mutation

---

# 11. VERIFY PATIENT SEARCH QUERY

Review implementation for:
- safe wildcard handling;
- escaped/parameterized query;
- relation query eager loading;
- pagination preservation;
- no uncontrolled full-table query if search empty;
- no excessive PII.

Add/adjust test if edge case found.

---

# 12. VERIFY NEXT-ACTION UI

Audit exact `MedicalVisit` statuses.

The UI-only next-action banner must map only to actual valid domain states.

Do not display nonexistent state labels.

Do not turn next-action text into clinical advice.

Add tests for supported states.

---

# 13. VERIFY ROLE MENU PERMISSIONS

Audit every `@can` / `@canany` ability in sidebar.

Confirm each ability exists in actual permissions/policies.

No typo such as:
- ability never seeded;
- ability name different from Policy;
- menu permanently hidden;
- menu accidentally exposed.

Add tests per representative role where useful.

---

# 14. FIX ONLY VERIFIED ISSUES

If browser/diff audit finds issues:
- fix them;
- add tests where appropriate.

Avoid unrelated UI redesign.

Priority:
1. broken workflow
2. broken mobile layout
3. authorization/menu mismatch
4. accessibility blocker
5. theme inconsistency
6. cosmetic polish

---

# 15. FULL QUALITY GATE

After any fix:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

No skipped critical tests.

---

# 16. GRAPHIFY

Only if source/docs changed:

```bash
graphify update .
```

No `--code-only`.

---

# 17. UPDATE DOCUMENTATION

Update:

- `docs/05-ui/PHASE-5A1-MANUAL-UI-VERIFICATION.md`
- `docs/05-ui/PHASE-5A1-IMPLEMENTATION-EVIDENCE-REGISTER.md`
- `docs/10-delivery/PHASE-5A-CLOSURE.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `plans/KNOWN-ISSUES.md`

Create:

- `docs/05-ui/PHASE-5A2-DIFF-HYGIENE-AUDIT.md`
- `docs/05-ui/PHASE-5A2-VISUAL-VERIFICATION.md`
- `docs/10-delivery/PHASE-5A2-FINAL-ACCEPTANCE.md`

---

# 18. GIT

Before commit:

```bash
git status
git diff --stat
git diff --check
```

Only commit actual Phase 5A2 fixes/docs.

Suggested:

```bash
git add -A
git diff --cached --check
git commit -m "fix(ux): complete visual verification and diff hygiene for phase 5a"
```

---

# 19. FINAL CLASSIFICATION

Use one:

### `PHASE-5A-FINAL-ACCEPTED`
- source implementation confirmed;
- browser verification completed;
- responsive core screens PASS;
- no critical diff anomaly;
- tests green.

### `PHASE-5A-ACCEPTED-WITH-MINOR-UX-BACKLOG`
- core experience verified;
- only non-critical cosmetic/secondary issues remain.

### `PHASE-5A-MANUAL-VERIFICATION-PENDING`
- code/test good but actual browser verification not completed.

### `PHASE-5A-DIFF-HYGIENE-ISSUE`
- suspicious large/unintentional diff requires cleanup.

### `PHASE-5A-BLOCKED`
- critical workflow/security issue.

---

# 20. FINAL REPORT

Report:

1. Commit 18ff7c2 diff classification.
2. Source vs docs/generated statistics.
3. Line-ending/format churn result.
4. Browser runtime used.
5. Login visual result.
6. Dashboard/navigation visual result.
7. Patient search result.
8. Patient context result.
9. Visit workspace result.
10. Assessment result.
11. Medication result.
12. Observation/referral/discharge integration status.
13. 375px result.
14. 768px result.
15. 1024px result.
16. 1440px result.
17. Light/dark/system result.
18. Keyboard/accessibility result.
19. Security regression result.
20. Next-action domain-state audit.
21. Permission/menu audit.
22. Fixes applied.
23. Test count/assertions.
24. Pint/PHPStan/Vite.
25. Remaining UX backlog.
26. Commit SHA.
27. Final classification.

Do not begin Phase 5B automatically.
