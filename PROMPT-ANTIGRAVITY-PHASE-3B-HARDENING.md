# PROMPT ANTIGRAVITY — PHASE 3B HARDENING
## MariaDB Concurrency, Controller/Policy Enforcement, and Private Referral Documents

Anda adalah principal Laravel architect, application security engineer, database concurrency reviewer, privacy engineer, dan health information system auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Claude Sonnet 4.6 (Thinking)** atau model reasoning setara.

Tujuan fase ini hanya:

1. membuktikan concurrency referral pada MariaDB nyata;
2. memindahkan seluruh route closure Phase 3B ke Controller/Action yang tepat;
3. menegakkan `ReferralPolicy` melalui `authorize()` atau route middleware;
4. mengimplementasikan private versioned referral documents;
5. menutup Phase 3B secara production-ready;
6. berhenti sebelum Phase 3C.

Jangan menambah discharge final, notifikasi, integrasi Absensi, billing, klaim, atau laporan manajemen baru.

---

# 1. DOKUMEN WAJIB DIBACA

Baca terlebih dahulu:

- `AGENTS.md`
- `PROMPT-ANTIGRAVITY-PHASE-3B.md`
- `PROMPT-CLAUDE-RESUME-PHASE-3B.md`
- `PROJECT-STATUS.md`
- `docs/10-delivery/PHASE-3B-RESUME-STATE.md`
- `docs/10-delivery/PHASE-3B-CLOSURE.md`
- `docs/02-workflows/HOSPITAL-REFERRAL.md`
- `docs/02-workflows/RETURN-FROM-REFERRAL.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/09-testing/SECURITY-TESTS.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jangan mengarang requirement baru.

---

# 2. PREFLIGHT

Jalankan read-only:

```bash
pwd
git branch --show-current
git status
git log --oneline -10
php artisan migrate:status
php artisan route:list
php artisan about
```

Periksa:

- driver database aktual;
- apakah MariaDB/MySQL tersedia;
- route Phase 3B yang masih closure;
- route yang belum memiliki auth/policy enforcement;
- storage disk referral document;
- file referral document yang sudah ada;
- migration applied/not applied.

Buat:

`docs/10-delivery/PHASE-3B-HARDENING-PLAN.md`

Isi:

- temuan;
- risiko;
- exact files;
- exact migration status;
- exact continuation plan;
- rollback plan.

---

# 3. MARIA DB CONCURRENCY

## 3.1 Requirement

Buktikan dengan MariaDB/MySQL nyata:

1. dua request concurrent membuat referral aktif untuk visit yang sama;
2. hanya satu referral aktif berhasil;
3. dua request concurrent membuat nomor referral;
4. nomor referral tidak duplikat;
5. dua request concurrent handoff dengan idempotency key sama;
6. hanya satu handoff efektif;
7. dua request concurrent membuat return record;
8. hanya satu return berhasil;
9. transaction gagal tidak meninggalkan audit atau state parsial.

## 3.2 Test environment

Gunakan database test khusus, bukan database development berisi data manual.

Contoh konfigurasi:

```text
DB_CONNECTION=mysql
DB_DATABASE=poskestren_health_test
```

Jangan menampilkan password.

Jangan menggunakan SQLite untuk concurrency proof.

## 3.3 Locking

Verifikasi:

- parent `medical_visits` row dikunci;
- active-referral check dan insert berada dalam transaction yang sama;
- referral number generation tidak memakai `MAX()+1` tanpa lock;
- idempotency key memiliki unique constraint database;
- return unique constraint tersedia;
- handoff duplicate submission aman.

Jika desain sekarang tidak cukup, buat corrective migration dan ADR.

## 3.4 Evidence

Dokumentasikan:

- test class;
- transaction strategy;
- number of workers/processes;
- expected result;
- actual result;
- database version;
- isolation level;
- deadlock/retry behavior.

Buat:

`docs/10-delivery/PHASE-3B-MARIADB-CONCURRENCY-REPORT.md`

---

# 4. CONTROLLER DAN POLICY ENFORCEMENT

## 4.1 Hapus route closure

Refactor seluruh route Phase 3B dari closure ke Controller.

Controller minimum sesuai kebutuhan:

- `ReferralController`
- `ReferralVersionController`
- `ReferralTransportController`
- `ReferralCompanionController`
- `ReferralHandoverController`
- `ReferralStatusController`
- `ReferralReturnController`
- `ReferralReturnReviewController`
- `ReferralDocumentController`

Jangan membuat controller gemuk. Business logic tetap berada di Action/Service.

## 4.2 Authorization

Setiap action harus memanggil:

```php
$this->authorize(...)
```

atau menggunakan middleware `can:` yang tepat.

Periksa minimal:

- index/view;
- create/store;
- approve;
- finalize document;
- download document;
- arrange transport;
- assign companion;
- depart;
- handoff;
- acknowledge;
- destination status;
- return;
- upload external document;
- local review;
- cancel;
- supersede.

UI hiding bukan authorization.

## 4.3 Form Requests

Gunakan Form Request untuk mutation.

Pastikan:

- allowlist field;
- enum validation;
- foreign key scope;
- no actor field from payload;
- no official timestamp from payload;
- no status escalation from payload;
- no mass assignment;
- XSS-safe rendering;
- route-model binding tidak membuka IDOR.

## 4.4 Security tests

Tambahkan test:

- guest -> redirect/401;
- authenticated without permission -> 403;
- direct URL -> 403;
- IDOR referral milik record lain -> 403;
- user cannot approve own referral if policy forbids;
- recipient/destination substitution rejected;
- download without permission -> 403;
- return review without permission -> 403;
- closure routes no longer exist.

---

# 5. PRIVATE VERSIONED REFERRAL DOCUMENTS

## 5.1 Storage

Gunakan private disk, misalnya:

```text
storage/app/private/referrals/
```

atau disk Laravel khusus:

```php
'referral_documents' => [
    'driver' => 'local',
    'root' => storage_path('app/private/referrals'),
    'visibility' => 'private',
],
```

Jangan gunakan:

- `public` disk;
- `storage/app/public`;
- permanent public URL;
- direct path exposure.

## 5.2 Document generation

Setiap finalized referral version dapat memiliki:

- private document path;
- checksum SHA-256;
- generated_at;
- generated_by;
- mime type;
- size;
- version reference;
- document status.

PDF/print selalu light mode.

## 5.3 Download

Gunakan authorized controller.

Aturan:

- Policy check;
- file existence check;
- path traversal protection;
- content type;
- safe filename;
- no raw storage path in response;
- download audit;
- correlation ID;
- rate limiting jika diperlukan.

## 5.4 Version immutability

- finalized version immutable;
- document lama tidak berubah;
- revision membuat version baru;
- checksum version lama tetap sama;
- source record berubah tidak mengubah snapshot lama;
- no overwrite file path;
- entered-in-error/cancelled tetap mempertahankan history.

## 5.5 External documents

Jika return-from-referral external documents sudah ada:

- private storage;
- extension allowlist;
- MIME validation;
- size limit;
- generated safe filename;
- checksum;
- no executable file;
- malware scanning hook/interface jika scanner belum tersedia;
- download authorization and audit.

Jangan mengklaim malware scan aktif jika hanya hook tersedia.

## 5.6 Tests

Tambahkan test:

- file stored on private disk;
- no public URL;
- unauthorized download 403;
- authorized download works;
- download audited;
- checksum stable;
- revision creates new file;
- old file unchanged;
- source update does not mutate old snapshot;
- path traversal rejected;
- invalid MIME rejected;
- oversized file rejected;
- executable upload rejected.

---

# 6. CLOSURE HARDENING LAIN

Verifikasi dan perbaiki:

- `ReferralPolicy` terdaftar/terdeteksi Laravel;
- all mutation actions use transaction;
- audit written only after successful transaction;
- actor and timestamps server-authoritative;
- final records immutable;
- no hard delete;
- state machine rejects invalid transitions;
- emergency referral does not wait for consultation;
- external result does not auto-create diagnosis, medication, or discharge;
- visit remains open after return review.

---

# 7. GRAPHIFY

Setelah refactor:

1. update graph tanpa `--code-only`;
2. query:
   - Referral routes -> Controllers -> Policies;
   - Referral download authorization;
   - ReferralVersion -> private storage;
   - one-active-referral lock path;
   - referral number generation;
   - handoff idempotency;
   - return unique path;
   - public document exposure;
   - route closure remaining;
   - unauthorized admin path;
   - hard delete path;
   - missing tests.

Perbarui:

- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`.

---

# 8. VERIFIKASI AKHIR

Jalankan:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
git diff --check
```

Tambahkan pengecekan bahwa route Phase 3B tidak lagi memakai closure.

Laporkan:

- jumlah tests;
- jumlah assertions;
- skipped tests;
- MariaDB version;
- concurrency result;
- route count;
- static analysis result;
- build result;
- private storage result;
- Graphify findings.

---

# 9. DOKUMENTASI

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-3B-CLOSURE.md`;
- `docs/10-delivery/PHASE-3B-HARDENING-PLAN.md`;
- `docs/10-delivery/PHASE-3B-MARIADB-CONCURRENCY-REPORT.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`;
- `docs/07-security/AUDIT-LOG.md`;
- `docs/09/testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Ubah status Phase 3B menjadi:

- `PRODUCTION-READY-FOUNDATION` hanya jika seluruh hardening lulus;
- `PASSED-WITH-BLOCKERS` jika masih ada blocker.

---

# 10. GIT

Sebelum final commit:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "fix(referral): harden Phase 3B authorization concurrency and private documents"
git status
```

Target working tree clean.

---

# 11. OUTPUT AKHIR

Berikan:

1. Preflight result.
2. MariaDB availability/version.
3. Concurrency test method/result.
4. Route closure findings.
5. Controllers/Form Requests created.
6. Policy enforcement matrix.
7. Private storage implementation.
8. Version immutability proof.
9. Download/upload security.
10. Migration changes.
11. Tests and assertions.
12. PHPStan/Pint/build results.
13. Graphify findings.
14. Remaining risks.
15. Final commit.
16. Working tree status.
17. Go/no-go recommendation for Phase 3C.

---

# 12. CHECKPOINT WAJIB

Jangan lanjut ke Phase 3C jika:

- MariaDB concurrency belum diuji;
- one-active-referral belum terbukti aman;
- referral number belum concurrency-safe;
- route closure Phase 3B masih menangani mutation;
- Policy belum ditegakkan;
- document masih public;
- download tidak diaudit;
- final version dapat berubah;
- external upload tidak tervalidasi;
- critical security test gagal.

Jika seluruh hardening lulus:

- commit;
- working tree clean;
- berhenti;
- tunggu persetujuan eksplisit untuk Phase 3C.
