# PROMPT ANTIGRAVITY — PHASE 5B1
## Final Verification, Test Portability, Browser Acceptance & Repository Hygiene
## SABIRA POSKESTREN Health — Local Development

Gunakan **Claude Opus 4.6 Thinking**.

Anda adalah principal Laravel engineer, QA lead, UX verification engineer, repository maintainer, security reviewer, dan documentation curator untuk proyek **SABIRA POSKESTREN Health**.

# 0. PROJECT TRUTH

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
DEPLOYMENT_STATUS=NOT_DEPLOYED
PRODUCTION_STATUS=NOT_STARTED
CURRENT_BRANCH=master
PHASE_5A_FINAL_COMMIT=931f26a
PHASE_5B_IMPLEMENTATION_STATUS=CODE_COMPLETE_PENDING_FINAL_VERIFICATION
LATEST_TEST_BASELINE=223 tests / 930 assertions
```

Phase 5B telah mengimplementasikan Observation workspace, External Consultation, Referral lifecycle/timeline UX, Discharge + Follow-Up + Operational Handoff continuity, Visit Workspace status cards, Next Action Guidance, dan Pharmacy batch expiry UX.

Phase 5B belum boleh dinyatakan final sampai:
1. konfigurasi test portable;
2. browser verification nyata;
3. expiry warning policy diverifikasi/configurable;
4. repository dibersihkan dari file transient/prompt yang tidak perlu;
5. quality gate penuh;
6. final Git commit + clean tree.

Target akhir: `PHASE-5B-COMPLETE`.

Jangan mulai Phase 5C otomatis.

# 1. HARD SAFETY RULES

Jangan:
- deploy atau SSH production;
- menghapus file massal tanpa klasifikasi;
- menghapus source code yang direferensikan aplikasi;
- menghapus migration atau test aktif;
- menghapus dokumentasi arsitektur/runbook/source-of-truth;
- menghapus `.md` hanya karena ekstensi `.md`;
- menjalankan `rm -rf` pada direktori luas;
- mengubah Git history secara destruktif.

Semua penghapusan harus dapat dijelaskan berdasarkan file atau pola yang sangat spesifik.

# STAGE A — BASELINE

## 2. Baseline Git

```bash
git status
git branch --show-current
git rev-parse HEAD
git log -n 5 --oneline
```

Jika working tree tidak bersih, identifikasi perubahan terlebih dahulu dan jangan overwrite pekerjaan user.

# STAGE B — TEST DATABASE PORTABILITY

## 3. Audit `phpunit.xml`

```bash
cat phpunit.xml
rg -n "/Applications/XAMPP|mysql\.sock|DB_SOCKET|8186" \
phpunit.xml .env.example config database tests \
-g '!vendor/**'
```

Repository tidak boleh hardcode absolute path developer seperti:

```text
/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock
```

## 4. Buat test config portable

`phpunit.xml` hanya boleh memuat default portable. Contoh konsep:

```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="mariadb"/>
<env name="DB_DATABASE" value="poskestren_health_test"/>
```

Local Mac boleh menjalankan:

```bash
DB_SOCKET=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock \
APP_ENV=testing php artisan test
```

CI/Linux harus dapat memakai `DB_HOST`, `DB_PORT`, dan credential dari environment/secrets.

Jangan commit secret.

Buat:
`docs/09-testing/TEST-DATABASE-PORTABILITY.md`

# STAGE C — REAL BROWSER VERIFICATION

## 5. Jalankan aplikasi lokal

Gunakan local Laravel + MariaDB + synthetic/demo data. Jangan gunakan data medis nyata bila tidak perlu.

## 6. Verifikasi halaman nyata di browser

Buka:

```text
/login
/dashboard
/patients
/visits/{syntheticVisit}
/observations/{syntheticObservation}
/consultations/{syntheticConsultation}
/referrals/{syntheticReferral}
/visits/{syntheticVisit}/discharge
/pharmacy/inventory
/visits/{syntheticVisit}/medications
```

Jangan infer PASS hanya dari source Blade.

## 7. Observation

Periksa:
- patient context;
- visit stage nav;
- status episode;
- responsible officer;
- monitoring;
- tindakan;
- active vs completed state.

Completed observation harus read-only untuk aksi yang tidak lagi valid.

## 8. Consultation

Secara visual harus jelas membedakan:

```text
SARAN KLINIS EKSTERNAL
```

dan:

```text
KEPUTUSAN KLINIS LOKAL POSKESTREN
```

External advice tidak boleh tampak sebagai automatic local order.

Jika transport masih fake/local, label:
`Simulasi / Local Development`.

## 9. Referral mobile-first

Uji 7-stage lifecycle pada `375x812`.

Jangan memaksa 7 item mengecil hingga tak terbaca. Gunakan horizontal scroll, vertical timeline, atau responsive condensed layout.

Arrival, handover, acceptance tetap event berbeda.

## 10. Discharge + Handoff

Verifikasi:
- readiness;
- draft/final state;
- follow-up;
- restriction;
- operational handoff;
- acknowledgement.

Operational/asrama handoff tidak boleh menampilkan:
- diagnosis;
- clinical narrative;
- medication detail;
- allergy detail;
- vital signs.

Minimum necessary:
- rest/activity restriction;
- operational instruction;
- follow-up instruction;
- acknowledgement.

## 11. Pharmacy

Verifikasi:
- medicine;
- batch;
- expiry;
- quantity;
- location;
- status;
- expired warning;
- expiring-soon;
- quarantine/depleted jika ada;
- mobile overflow.

# STAGE D — EXPIRY WARNING POLICY

## 12. Audit threshold 30 hari

```bash
rg -n "30 day|30 hari|addDays\(30\)|diffInDays|expiry|expir" \
app resources config docs tests \
-g '!vendor/**' \
-g '!node_modules/**'
```

Klasifikasi threshold:
- `CONFIGURED-POLICY`
- `DOCUMENTED-SOP`
- `UI-PRESENTATION-DEFAULT`
- `AI-INTRODUCED-UNCONFIRMED`

Jika belum ada kebijakan resmi, jangan hardcode 30 hari sebagai kebenaran operasional.

Gunakan config, misalnya:

```php
config('pharmacy.expiry_warning_days', 30)
```

dan dokumentasikan:

```text
EXPIRY_WARNING_DAYS=30
[PERLU DIKONFIRMASI]
```

Tambahkan test konfigurasi.

# STAGE E — RESPONSIVE / THEME / ACCESSIBILITY

## 13. Viewport matrix

Verifikasi nyata:

```text
375 x 812
768 x 1024
1024 x 768
1440 x 900
```

untuk:
- Observation
- Consultation
- Referral
- Discharge
- Pharmacy
- Visit overview

Status hanya:
`PASS`, `ISSUE`, `FIXED`, `NOT_VERIFIED`.

## 14. Theme

Uji:
- Light
- Dark
- System

Fokus:
- timeline;
- warning;
- checklist;
- status badge;
- disabled state;
- focus state.

## 15. Accessibility

Uji:
- Tab order;
- visible focus;
- semantic headings;
- button labels;
- timeline tidak color-only;
- form labels;
- modal keyboard usability.

Gunakan wording `WCAG 2.1 AA-oriented`, bukan sertifikasi.

# STAGE F — REPOSITORY HYGIENE

## 16. Tujuan cleanup

Repository harus menyimpan:
- runtime;
- source;
- test;
- architecture;
- requirement;
- SOP;
- security;
- integration contract;
- delivery/runbook;
- maintenance docs.

Repository tidak perlu menyimpan seluruh history prompt AI sekali-pakai.

## 17. Inventory Markdown

```bash
find . -type f -name "*.md" \
-not -path "./vendor/*" \
-not -path "./node_modules/*" \
-not -path "./graphify-out/*" \
-not -path "./storage/*" | sort

git ls-files '*.md' | sort
```

Buat:
`docs/10-delivery/REPOSITORY-HYGIENE-AUDIT.md`

## 18. Cari prompt-only Markdown

Filename candidates:

```bash
find . -type f \( \
-name "PROMPT-*.md" -o \
-name "*ANTIGRAVITY*PROMPT*.md" -o \
-name "*CLAUDE*PROMPT*.md" -o \
-name "*GEMINI*PROMPT*.md" -o \
-name "*PHASE*PROMPT*.md" \
\) \
-not -path "./vendor/*" \
-not -path "./node_modules/*" \
-not -path "./graphify-out/*"
```

Content candidates:

```bash
rg -l \
"Gunakan \*\*Claude|Gunakan Claude|Gunakan \*\*Gemini|Anda adalah principal|PROMPT ANTIGRAVITY|Jangan mulai Phase|FINAL CLASSIFICATION" \
-g '*.md' \
-g '!vendor/**' \
-g '!node_modules/**' \
-g '!graphify-out/**'
```

Search result hanyalah kandidat. Jangan auto-delete.

## 19. Klasifikasi Markdown

Setiap kandidat harus diklasifikasikan:

### `KEEP-CANONICAL`
Contoh:
- README;
- CHANGELOG;
- PROJECT-STATUS;
- AGENTS;
- requirement;
- architecture;
- ADR;
- API/integration contract;
- security;
- SOP/runbook;
- test docs;
- roadmap aktif;
- Graphify docs.

### `EXTRACT-THEN-DELETE`
Prompt berisi keputusan durable unik yang belum ada di canonical docs.

Prosedur:
1. ekstrak keputusan penting;
2. pindahkan ke dokumen canonical;
3. verifikasi informasi tersimpan;
4. baru hapus prompt.

### `DELETE-TRANSIENT`
Prompt sekali pakai untuk Gemini/Claude/Codex/Antigravity tanpa pengetahuan unik.

Contoh pola:
- `PROMPT-ANTIGRAVITY-PHASE-*.md`
- `PROMPT-CLAUDE-*.md`
- `PROMPT-GEMINI-*.md`
- resume prompt
- temporary execution prompt

### `REVIEW-MANUALLY`
Ambigu.

Jangan hapus item `REVIEW-MANUALLY`.

## 20. Prinsip prompt cleanup

**Repository mendokumentasikan sistem, bukan percakapan/instruction history yang dipakai untuk membangunnya.**

Sebelum prompt dihapus, pastikan keputusan durable seperti:
- architecture;
- permission;
- privacy;
- clinical boundary;
- integration contract;
- test requirement;
- deployment constraint

sudah ada dalam canonical documentation.

## 21. Duplicate Markdown

Cari exact duplicate:

```bash
python3 - <<'PY'
from pathlib import Path
from collections import defaultdict
import hashlib

groups = defaultdict(list)

for p in Path('.').rglob('*.md'):
    if {'vendor','node_modules','graphify-out','storage'} & set(p.parts):
        continue
    groups[hashlib.sha256(p.read_bytes()).hexdigest()].append(str(p))

for sha, files in groups.items():
    if len(files) > 1:
        print(sha)
        for f in files:
            print("  ", f)
PY
```

Untuk duplicate:
- keep canonical;
- update references;
- delete duplicate copy.

Jangan menganggap dokumen berbeda sebagai duplicate hanya karena judul mirip.

## 22. Temporary/generated files

Audit:

```bash
git status --short
git ls-files | rg \
"(^|/)(tmp|temp|logs?|screenshots?|artifacts?)/|\.DS_Store$|\.log$|\.tmp$|~$"
```

Audit juga:
- `public/build`
- coverage
- `.phpunit.cache`
- `.phpunit.result.cache`
- `.DS_Store`
- browser screenshot sementara
- local logs
- IDE transient files

Jika generated file tidak perlu dikomit:
- tambahkan `.gitignore` yang presisi;
- `git rm --cached` hanya jika aman.

## 23. Graphify output

Audit:

```bash
git ls-files 'graphify-out/**'
du -sh graphify-out 2>/dev/null || true
```

Klasifikasi:
- `KEEP-TRACKED`
- `GENERATED-SHOULD-BE-IGNORED`
- `PARTIAL-CANONICAL-OUTPUT`
- `REVIEW-MANUALLY`

Jangan menghapus Graphify hanya karena besar.

Jika terbukti reproducible cache dan tidak perlu Git:
1. tambah `.gitignore`;
2. keluarkan dari Git index secara aman;
3. dokumentasikan regenerasi `graphify update .`.

Jika ragu, keep + `REVIEW-MANUALLY`.

## 24. Root directory

```bash
ls -la
```

Root seharusnya hanya berisi file/direktori project-level yang masuk akal.

Prompt-only Markdown umumnya tidak perlu tinggal di root.

## 25. Broken references

Sebelum move/delete:

```bash
rg -n "FILENAME_OR_PATH" . \
-g '!vendor/**' \
-g '!node_modules/**' \
-g '!graphify-out/**'
```

Update references.

Setelah cleanup, pastikan tidak ada broken references ke prompt/file yang dihapus.

## 26. Cleanup report

`REPOSITORY-HYGIENE-AUDIT.md` harus mencatat:

```text
FILE
CLASSIFICATION
ACTION
REASON
KNOWLEDGE_MIGRATED_TO
```

Summary:

```text
PROMPT_FILES_DELETED=
DUPLICATES_DELETED=
TEMP_FILES_REMOVED=
FILES_MOVED=
CANONICAL_DOCS_RETAINED=
REVIEW_MANUALLY=
```

# STAGE G — SECURITY & PRIVACY REGRESSION

## 27. Authorization

Verifikasi:
- guest internal routes -> login;
- unauthorized clinical direct route -> 403;
- technical admin tidak otomatis dapat clinical access;
- operational role minimum necessary;
- finalized state guard tetap;
- POST logout + CSRF;
- direct login throttle tetap;
- Gate OIDC state/nonce tetap.

## 28. Privacy template audit

```bash
rg -n \
"diagnosis|clinical_notes|medication_details|allerg|vital" \
resources/views/pages/discharges \
resources/views/pages/operational \
resources/views/pages/notifications \
2>/dev/null
```

Review context. Jangan menghapus clinical data dari clinical-only page secara membabi buta.

# STAGE H — TESTS

## 29. Adjust/add tests bila perlu

Cover:
- configurable expiry threshold;
- referral lifecycle rendering;
- completed observation lock;
- consultation advice/local-decision separation;
- discharge handoff privacy;
- role/privacy behavior.

Jangan tambah meaningless test hanya untuk menaikkan count.

## 30. Full quality gate

```bash
DB_SOCKET=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock \
APP_ENV=testing php artisan test

./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

Socket hanya diberikan dari command/environment lokal, bukan di-hardcode ke `phpunit.xml`.

# STAGE I — GRAPHIFY

## 31. Update graph

Jika source/docs berubah:

```bash
graphify update .
```

Jangan gunakan `--code-only`.

Pastikan tidak ada node/reference ke prompt yang sudah dihapus bila memang prompt tidak lagi menjadi canonical docs.

# STAGE J — DOCUMENTATION

## 32. Create

Buat:
- `docs/09-testing/TEST-DATABASE-PORTABILITY.md`
- `docs/05-ui/PHASE-5B1-VISUAL-VERIFICATION.md`
- `docs/10-delivery/REPOSITORY-HYGIENE-AUDIT.md`
- `docs/10-delivery/PHASE-5B1-FINAL-CLOSURE.md`

Update:
- `CHANGELOG.md`
- `PROJECT-STATUS.md`
- `plans/KNOWN-ISSUES.md`
- `.gitignore` jika justified
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

**Jangan membuat atau menyimpan prompt `.md` baru di repository.**
Prompt Phase 5B1 adalah execution artifact sementara, bukan project documentation.

# STAGE K — DIFF HYGIENE

## 33. Review

```bash
git status --short
git diff --stat
git diff --check
```

Source-only:

```bash
git diff --stat -- \
'app/**' \
'resources/**' \
'routes/**' \
'config/**' \
'tests/**' \
'phpunit.xml' \
'.gitignore'
```

Pisahkan docs dan Graphify churn dalam laporan.

# STAGE L — FINAL GIT CLOSURE

## 34. Commit

Sebelum commit:

```bash
git add -A
git diff --cached --check
git status --short
```

Review deletion list file-per-file.

Lalu:

```bash
git commit -m "chore(phase-5b): finalize workflow verification and clean repository artifacts"
```

Verifikasi:

```bash
git rev-parse HEAD
git status
git log -n 3 --oneline
```

Expected:
`working tree clean`.

# 35. FINAL CLASSIFICATION

Gunakan tepat satu:

### `PHASE-5B-COMPLETE`
Browser verification nyata, test config portable, expiry policy handled, repository hygiene selesai, tests/quality gate hijau, final commit clean.

### `PHASE-5B-COMPLETE-WITH-MANUAL-CLEANUP-REVIEW`
Core verification PASS tetapi ada file ambigu yang tetap `REVIEW-MANUALLY`.

### `PHASE-5B-MANUAL-VERIFICATION-PENDING`
Browser verification belum lengkap.

### `PHASE-5B-REPOSITORY-HYGIENE-BLOCKED`
Cleanup tidak dapat menentukan canonical/transient secara aman.

### `PHASE-5B-BLOCKED`
Ada issue workflow/security/test kritis.

# 36. FINAL REPORT

Laporkan:

1. Starting SHA.
2. Test portability audit.
3. `phpunit.xml` portability fix.
4. Observation browser result.
5. Consultation browser result.
6. Referral desktop result.
7. Referral mobile result.
8. Discharge browser result.
9. Operational handoff privacy result.
10. Pharmacy browser result.
11. 375px result.
12. 768px result.
13. 1024px result.
14. 1440px result.
15. Light/Dark/System result.
16. Accessibility result.
17. Expiry warning policy result.
18. Markdown files inventoried.
19. Prompt-only files found.
20. Prompt-only files deleted.
21. Durable knowledge migrated.
22. Duplicate docs deleted.
23. Temporary/generated files cleaned.
24. Graphify tracking decision.
25. `.gitignore` changes.
26. Broken-link/reference check.
27. Security/privacy regression result.
28. New/adjusted tests.
29. Total tests/assertions.
30. Pint/PHPStan/Vite result.
31. Source-only diff summary.
32. Documentation diff summary.
33. Deleted-file summary.
34. Remaining manual-review files.
35. Final commit SHA.
36. Working tree status.
37. Final classification.

Jangan mulai Phase 5C otomatis.
