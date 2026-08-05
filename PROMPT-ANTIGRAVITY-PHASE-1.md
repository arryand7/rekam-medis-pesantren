# PROMPT ANTIGRAVITY — PHASE 1
## Phase 0 Closure, Identity, Access Control, Gate Contract, Audit Foundation, and Dry-Run Sync

Anda adalah principal Laravel architect, identity and access management engineer, application security engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Jalankan pekerjaan secara bertahap. Jangan melompat ke modul klinis, kunjungan medis, obat, observasi, konsultasi eksternal, atau rujukan.

---

# 1. WAJIB DIBACA

Sebelum mengubah kode:

1. `AGENTS.md`
2. `README.md`
3. `PROJECT-STATUS.md`
4. `CHANGELOG.md`
5. `docs/README.md`
6. `docs/01-domain/PERSON-PATIENT-IDENTITY.md`
7. `docs/01-domain/BUSINESS-RULES.md`
8. `docs/02-workflows/GATE-USER-SYNC.md`
9. `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
10. `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
11. `docs/03-requirements/TRACEABILITY-MATRIX.md`
12. `docs/04-architecture/MODULE-BOUNDARIES.md`
13. `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
14. `docs/05-data/DATA-DICTIONARY.md`
15. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
16. `docs/07-security/GATE-SYNC-SECURITY.md`
17. `docs/07-security/AUDIT-LOG.md`
18. `docs/08-api/GATE-USER-SYNC-CONTRACT.md`
19. `docs/09-testing/TEST-STRATEGY.md`
20. `docs/10-delivery/READINESS-REVIEW.md`
21. `plans/KNOWN-ISSUES.md`
22. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jangan mengarang keputusan yang belum ada. Gunakan penanda `[PERLU DIKONFIRMASI]`.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan isi `.env`, token, secret, password, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan memakai data pengguna nyata pada seeder, test, screenshot, atau fixture.
4. Jangan membuat modul rekam medis pada Phase 1.
5. Jangan membuat diagnosis, assessment, obat, observasi, konsultasi klinis, atau rujukan.
6. Jangan menggunakan nama, email, NIS, atau NIP sebagai primary key internal.
7. Jangan melakukan auto-merge berdasarkan nama.
8. Deaktivasi akun tidak boleh menghapus `Person`, `Patient`, atau riwayat.
9. Role `admin` tidak boleh menentukan apakah seseorang dapat menjadi pasien.
10. Semua perubahan identitas, role, permission, dan sync wajib diaudit.
11. Seluruh authorization harus server-side melalui Policy/Gate/middleware.
12. Gunakan database transaction untuk operasi multi-tabel.
13. Berhenti pada checkpoint yang ditentukan.

---

# 3. TAHAP A — PHASE 0 CLOSURE VERIFICATION

Lakukan pemeriksaan read-only terlebih dahulu.

## A.1 Authentication

Verifikasi:

- route login;
- route logout;
- route dashboard;
- middleware `auth`;
- session regeneration;
- CSRF;
- unauthorized dashboard access;
- user migration;
- password reset hanya jika memang dipilih pada foundation.

Catat route dan middleware aktual. Jangan mengklaim tersedia jika belum ada.

## A.2 Database

Jalankan:

```bash
php artisan about
php artisan migrate:status
```

Catat:

- database driver;
- database server yang benar-benar digunakan;
- migration yang sudah diterapkan;
- environment development/test;
- jangan tampilkan credential.

## A.3 Theme verification

Verifikasi melalui browser:

- desktop light;
- desktop dark;
- desktop system;
- mobile light;
- mobile dark;
- no theme flicker;
- keyboard focus;
- print mode light.

Simpan artifact/screenshot sintetis, tanpa data nyata.

Catat bahwa account-level theme preference boleh ditunda sampai model `User` Phase 1 tersedia.

## A.4 Graphify hygiene

Periksa apakah knowledge graph ikut mengindeks:

- `vendor/`;
- `node_modules/`;
- `storage/`;
- `bootstrap/cache/`;
- `public/build/`;
- `graphify-out/`;
- `.git/`.

Jika iya, tambahkan exclusion sesuai mekanisme Graphify yang tersedia, lalu rebuild/update graph.

Graph harus fokus pada:

- source code;
- Markdown;
- migration;
- config;
- routes;
- tests.

Jangan mengarang jumlah node baru. Laporkan hasil aktual.

## A.5 Health endpoint

Pastikan endpoint publik `/health` hanya mengembalikan data minimum, misalnya:

```json
{
  "status": "ok"
}
```

Jangan tampilkan versi PHP, environment, hostname, database detail, stack trace, atau secret.

## A.6 Git baseline

Periksa:

```bash
git status
git log --oneline -5
```

Jika Phase 0 belum dikomit:

1. tampilkan diff summary;
2. pastikan tidak ada secret/generated dependency yang ikut;
3. commit dengan pesan:

```text
chore(foundation): complete phase 0 Laravel foundation
```

Target akhir: `working tree clean`.

## A.7 Laporan closure

Buat atau perbarui:

`docs/10-delivery/PHASE-0-CLOSURE.md`

Berikan status:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika ada masalah Critical, berhenti dan jangan lanjut ke Phase 1.

---

# 4. TAHAP B — PHASE 1A: PERSON, USER, PATIENT FOUNDATION

Implementasikan pemisahan domain:

```text
Person
├── User
│   └── Role / Permission
└── Patient
```

## B.1 Person

`Person` merepresentasikan manusia dan proyeksi identitas Gate.

Field minimum yang direncanakan:

- internal ULID;
- `gate_user_id` nullable sampai sinkronisasi;
- primary identifier;
- name;
- email nullable;
- phone nullable;
- `user_type`;
- source status;
- source updated timestamp;
- source version/checksum;
- synced timestamp.

Gunakan nama field final yang konsisten dengan data dictionary.

## B.2 User

`User` merepresentasikan akun login.

Aturan:

- dapat terhubung ke satu `Person`;
- akun teknis dapat tidak memiliki `Person`;
- deaktivasi login tidak menghapus `Person`;
- role/permission tidak disimpan sebagai satu string bebas;
- theme preference setelah login disimpan pada user preference atau struktur yang sesuai;
- local development auth harus jelas terisolasi dari Gate production auth.

## B.3 Patient

`Patient` merepresentasikan subjek rekam kesehatan.

Aturan:

- satu `Person` maksimal satu `Patient`;
- patient number dibuat server-side;
- seluruh person manusia eligible;
- service account, bot, dan administrative-only account tidak eligible;
- pengguna manusia dengan role admin tetap eligible;
- perubahan role atau tipe pengguna tidak memecah patient history;
- tidak ada tabel rekam medis yang dibuat pada Phase 1.

## B.4 Database safety

- Gunakan ULID.
- Tambahkan foreign key dan unique constraint.
- Hindari cascade delete untuk `Person`, `User`, dan `Patient`.
- Gunakan enum/string terkontrol untuk status.
- Buat factories dengan data sintetis.
- Buat migration rollback yang aman.

---

# 5. TAHAP C — PHASE 1B: ROLE, PERMISSION, DAN AUTHORIZATION

Implementasikan role dan permission secara eksplisit.

Permission minimum:

- `manage-users`;
- `manage-roles`;
- `manage-permissions`;
- `view-people`;
- `view-patients`;
- `manage-gate-sync`;
- `view-gate-sync`;
- `resolve-identity-conflicts`;
- `view-audit-log`;
- `manage-system-settings`.

Jangan membuat permission klinis aktif sebelum modul klinis dibuat. Permission klinis dapat dicatat sebagai planned documentation, bukan dipakai secara palsu.

## Authorization rules

1. Role admin tidak otomatis memiliki akses data medis.
2. Super admin teknis dapat mengelola sistem tetapi tidak otomatis membuka medical record.
3. Semua endpoint admin dilindungi server-side.
4. Direct URL access tanpa permission harus menghasilkan 403.
5. UI hiding bukan pengganti Policy.
6. Perubahan role dan permission wajib diaudit.
7. Self-assignment privilege harus dicegah.
8. Akun tidak boleh menaikkan role dirinya sendiri.

Buat Policy, middleware, atau Gate yang sesuai, dengan test.

---

# 6. TAHAP D — PHASE 1C: AUDIT FOUNDATION

Buat fondasi audit append-only untuk event administratif dan identitas.

Event minimum:

- user created;
- user updated;
- user activated/deactivated;
- person created/updated;
- patient created;
- role assigned/revoked;
- permission assigned/revoked;
- Gate sync preview;
- Gate sync apply;
- identity conflict created/resolved;
- theme preference changed jika kebijakan audit mengharuskannya.

Field audit minimum:

- actor;
- action;
- subject type;
- subject ID;
- before;
- after;
- reason;
- IP;
- user-agent;
- correlation ID;
- timestamp.

Aturan:

- audit tidak dapat diedit atau dihapus dari UI;
- jangan menyimpan secret;
- gunakan sanitization;
- audit harus konsisten dengan transaksi;
- test bahwa operasi gagal tidak menghasilkan audit sukses palsu.

---

# 7. TAHAP E — PHASE 1D: GATE CLIENT CONTRACT

Jangan langsung melakukan sync production.

Bangun:

1. `GateClientContract`;
2. DTO payload pengguna Gate;
3. fake Gate client untuk test;
4. schema validation;
5. pagination contract;
6. timeout;
7. retry policy;
8. source version/timestamp;
9. error mapping;
10. authentication configuration melalui environment;
11. health/status check internal yang tidak membocorkan secret.

Gunakan `gate_user_id` sebagai stable external identifier.

Field authoritative Gate:

- Gate ID;
- username/NIS/NIP sesuai kontrak;
- name;
- email/phone bila disepakati;
- user type;
- active status;
- organizational attributes;
- source updated timestamp/version.

Field authoritative POSKESTREN:

- patient number;
- patient eligibility review;
- theme preference lokal jika tidak disediakan Gate;
- seluruh data kesehatan di fase berikutnya.

Jika endpoint Gate belum final, implementasikan contract, fake client, configuration placeholder, dan test. Jangan mengarang URL atau payload production.

---

# 8. TAHAP F — PHASE 1E: DRY-RUN GATE SYNC

Implementasikan dry-run terlebih dahulu.

## F.1 Hasil klasifikasi

Setiap record diklasifikasikan menjadi:

- `new`;
- `matched`;
- `changed`;
- `deactivated`;
- `source_missing`;
- `conflict`;
- `unsupported_type`;
- `duplicate_identifier`;
- `invalid_payload`;
- `unchanged`.

## F.2 Matching order

1. `gate_user_id`;
2. approved legacy mapping;
3. NIS/NIP/email sebagai kandidat manual review;
4. jangan auto-merge berdasarkan nama.

## F.3 Dry-run requirements

- tidak mengubah `Person`, `User`, `Patient`, role, atau permission;
- menyimpan atau menghasilkan preview yang dapat diaudit;
- mendukung pagination;
- mendukung idempotency;
- menyediakan summary count;
- menyediakan per-item reason;
- menyembunyikan field sensitif dari user tanpa permission;
- dapat diekspor hanya jika berwenang dan diaudit.

## F.4 Apply sync

Jangan implementasikan apply massal pada Phase 1 kecuali seluruh dry-run test, conflict handling, audit, dan Gate contract sudah lulus dan dokumentasi secara eksplisit menyatakan aman.

Default checkpoint Phase 1 berhenti pada dry-run.

---

# 9. UI PHASE 1

Pertahankan identitas visual biru muda Phase 0.

Buat halaman:

- People directory;
- Patient eligibility status;
- Users;
- Roles;
- Permissions;
- Gate sync preview;
- Gate sync run detail;
- Identity conflicts;
- Audit log.

Gunakan:

- light/dark/system;
- responsive desktop/mobile;
- accessible table;
- server-side pagination;
- empty/loading/error/forbidden states;
- semantic status badge;
- tidak menampilkan data sensitif secara berlebihan.

Jangan membuat layar rekam medis.

---

# 10. TEST WAJIB

Buat dan jalankan test minimal:

## Identity

- person dapat memiliki user;
- person dapat memiliki patient;
- satu person tidak dapat memiliki dua patient;
- admin manusia tetap eligible sebagai patient;
- service account tidak eligible;
- user deactivation tidak menghapus person/patient;
- role change tidak mengubah patient ID.

## Authorization

- unauthorized direct URL menghasilkan 403;
- user tidak dapat menaikkan role sendiri;
- admin teknis tidak otomatis memiliki akses klinis;
- hanya permission yang benar dapat menjalankan dry-run sync;
- audit log tidak dapat dimodifikasi melalui UI.

## Gate contract

- valid payload diterima;
- invalid schema ditolak;
- pagination bekerja;
- retry/timeout dipetakan;
- duplicate external ID menjadi conflict;
- payload yang sama idempotent.

## Dry-run

- tidak mengubah tabel identity;
- klasifikasi new/matched/changed/conflict benar;
- tidak auto-merge berdasarkan nama;
- source_missing tidak melakukan delete;
- report dapat dibaca hanya pengguna berwenang.

## Theme

- preference akun tersimpan setelah login;
- fallback ke local/system sebelum login;
- tidak ada regresi light/dark/system.

Jalankan:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
npm run build
php artisan route:list
php artisan migrate:status
```

Laporkan hasil aktual, bukan asumsi.

---

# 11. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions masih berlaku;
3. query:
   - Gate -> Person -> User -> Patient;
   - admin human patient eligibility;
   - user deactivation;
   - dry-run sync;
   - audit event;
   - Policy untuk admin pages;
   - requirements tanpa test.

Perbarui:

- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`.

---

# 12. DOKUMENTASI WAJIB DIPERBARUI

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-0-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Buat ADR baru jika memilih package atau pendekatan role/permission yang belum didokumentasikan.

---

# 13. OUTPUT AKHIR

Berikan laporan:

1. Phase 0 closure result.
2. Executive summary Phase 1.
3. Architecture implemented.
4. Database tables dan constraints.
5. Routes/pages.
6. Policies dan permissions.
7. Gate client contract status.
8. Dry-run sync behavior.
9. Audit behavior.
10. File dibuat/diubah.
11. Command dijalankan.
12. Test dan hasil aktual.
13. Graphify results.
14. Screenshot light/dark desktop/mobile.
15. Risiko dan blocker.
16. Git diff summary.
17. Exact next recommended phase.

---

# 14. CHECKPOINT WAJIB

Berhenti jika:

- Phase 0 closure gagal;
- stable Gate ID tidak dapat ditentukan;
- model Person/User/Patient bertentangan dengan dokumentasi;
- dry-run mengubah data;
- authorization gagal;
- audit tidak konsisten;
- test penting gagal.

Jika semua berhasil:

- commit Phase 1 dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti pada **Gate dry-run sync**;
- jangan membuat apply sync massal;
- jangan membuat medical visit atau modul klinis;
- tunggu persetujuan eksplisit pengguna.
