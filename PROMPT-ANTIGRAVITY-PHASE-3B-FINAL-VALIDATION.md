# PROMPT ANTIGRAVITY — FINAL VALIDATION PHASE 3B

Gunakan Gemini 3.6 Flash High atau Claude Sonnet 4.6 Thinking.

Tujuan:
1. Menjalankan empat concurrency test Phase 3B pada MariaDB/MySQL nyata.
2. Memastikan migration Phase 3B dapat diterapkan pada database test khusus.
3. Memverifikasi one-active-referral, referral number uniqueness, handoff idempotency, dan one-return-per-referral.
4. Memeriksa auth route stub agar bukan authentication bypass.
5. Memverifikasi private referral documents.
6. Menutup Phase 3B dengan status GO/NO-GO.
7. Jangan memulai Phase 3C.

## Aturan
- Gunakan database `poskestren_health_test`, bukan development/production.
- Jangan tampilkan `.env`, password, token, atau secret.
- Jangan memakai SQLite sebagai bukti concurrency.
- Jangan melonggarkan test agar lulus.
- Jangan menyatakan production-ready bila masih ada skipped concurrency test.
- Jangan menjalankan fitur Phase 3C.

## Preflight

Jalankan:

```bash
pwd
git branch --show-current
git status
git log --oneline -10
php artisan about
php artisan migrate:status
php artisan route:list
```

Periksa:
- driver database aktual;
- MariaDB/MySQL availability;
- route bernama `login`;
- implementasi auth route stub;
- middleware dan Policy pada seluruh route referral;
- disk private referral documents;
- migration Phase 3B applied/not applied.

## Database test

Gunakan `.env.testing` atau environment process aman:

```text
APP_ENV=testing
DB_CONNECTION=mysql
DB_DATABASE=poskestren_health_test
```

Jangan cetak credential.

Verifikasi:

```bash
APP_ENV=testing php artisan about
APP_ENV=testing php artisan migrate:status
APP_ENV=testing php artisan migrate --force
APP_ENV=testing php artisan migrate:status
```

Catat:
- database version;
- transaction isolation level;
- InnoDB;
- migration result;
- unique constraints dan foreign keys.

## Auth stub review

Pastikan route stub `login`:
- tidak melakukan login otomatis;
- tidak membuat user sintetis;
- tidak menonaktifkan middleware auth;
- tidak menerima identity/role dari payload;
- tidak menjadi bypass authorization.

Test:
- guest referral route -> redirect/401;
- login stub tidak mengautentikasi user;
- authenticated tanpa permission -> 403;
- authorized user -> allowed.

## Concurrency tests

Jalankan:

```bash
APP_ENV=testing ./vendor/bin/pest --group=concurrency --stop-on-failure
```

Empat test wajib berjalan, bukan skipped:

1. Dua request membuat referral aktif untuk visit sama:
   - tepat satu berhasil;
   - satu ditolak aman;
   - hanya satu active referral;
   - audit sukses hanya satu.

2. Concurrent referral number:
   - tidak ada nomor duplikat;
   - jangan memakai unsafe `MAX()+1`.

3. Handoff idempotency:
   - dua request dengan idempotency key sama menghasilkan satu efek.

4. Concurrent return:
   - hanya satu return record;
   - state referral konsisten.

Catat:
- jumlah worker/process;
- isolation level;
- lock target;
- elapsed time;
- deadlock/retry behavior;
- final row counts.

Jangan mengubah test concurrent menjadi sequential.

## Full regression

Setelah concurrency lulus:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
git diff --check
```

Target:
- 0 failed;
- 0 concurrency skipped;
- tidak ada mutation route closure;
- Policy enforced;
- private document tests lulus;
- unauthorized download 403;
- no public referral document URL.

## Private document re-check

Verifikasi:
- disk `referral_documents` private;
- root `storage/app/private/referrals`;
- opaque filename;
- no patient data in path;
- SHA-256 checksum;
- finalized version immutable;
- revision creates new file;
- old file unchanged;
- download via authorized controller;
- download audited;
- path traversal rejected;
- invalid MIME/oversized/executable upload rejected;
- malware scanning ditulis hook-only bila scanner belum aktif.

## Graphify

Update graph tanpa `--code-only`, lalu query:
- referral route -> controller -> authorize -> policy;
- one-active-referral lock path;
- referral number generation;
- handoff idempotency;
- return unique constraint;
- referral version -> private disk -> download controller;
- login stub bypass;
- remaining route closures;
- unauthorized admin path;
- hard delete path;
- missing tests.

## Dokumentasi

Perbarui:
- `docs/10-delivery/PHASE-3B-MARIADB-CONCURRENCY-REPORT.md`
- `docs/10-delivery/PHASE-3B-CLOSURE.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `plans/KNOWN-ISSUES.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Status:
- `PRODUCTION-READY-FOUNDATION` hanya bila 4 concurrency tests berjalan dan lulus.
- `PASSED-WITH-BLOCKERS` bila masih skipped/offline.
- `FAILED` bila invariant rusak.

## Commit

Hanya jika seluruh bukti lulus:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "test(referral): prove Phase 3B MariaDB concurrency and close hardening"
git status
```

## Output akhir

Berikan:
1. MariaDB/MySQL version.
2. Test database confirmation.
3. Migration result.
4. Auth stub security result.
5. Four concurrency test results.
6. Worker/isolation/locking evidence.
7. Full suite tests/assertions/skips.
8. Private document result.
9. Policy/controller result.
10. Graphify findings.
11. Remaining risks.
12. Final closure status.
13. Commit.
14. Working tree status.
15. GO/NO-GO for Phase 3C.

Berhenti. Jangan mulai Phase 3C.
