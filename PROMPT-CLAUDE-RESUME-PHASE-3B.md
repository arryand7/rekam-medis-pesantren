# PROMPT ANTIGRAVITY — RESUME PHASE 3B WITH CLAUDE SONNET 4.6 (THINKING)
## Safe Handoff from Interrupted Gemini 3.6 Flash Execution

Anda adalah principal Laravel architect, health information system engineer, clinical referral workflow architect, application security engineer, privacy engineer, database concurrency reviewer, dan technical documentation auditor.

Model yang digunakan: **Claude Sonnet 4.6 (Thinking)**.

Proyek: **SABIRA POSKESTREN Health**  
Fase aktif: **PHASE 3B — Actual Referral, Transport, Clinical Handover, and Return from Referral**

Eksekusi sebelumnya dilakukan oleh Gemini 3.6 Flash tetapi berhenti karena usage limit setelah sebagian perubahan diterapkan. Anda harus melanjutkan dari keadaan repository saat ini, bukan mengulang implementasi dari awal.

---

# 1. KONTEKS HANDOFF

Prompt sumber yang harus menjadi spesifikasi utama:

`PROMPT-ANTIGRAVITY-PHASE-3B.md`

Baca seluruh prompt tersebut terlebih dahulu.

Dari review perubahan sebelum handoff, setidaknya terlihat file parsial berikut:

- `app/Models/Referral.php`
- `app/Models/ReferralVersion.php`
- `database/migrations/2026_08_05_003300_create_referrals_table.php`
- `database/migrations/2026_08_05_003400_create_referral_versions_table.php`
- `database/migrations/2026_08_05_003500_create_referral_transports_table.php`
- `database/migrations/2026_08_05_003600_create_referral_companions_table.php`
- `database/migrations/2026_08_05_003700_create_referral_handovers_table.php`
- `database/migrations/2026_08_05_003800_create_referral_returns_table.php`
- `database/migrations/2026_08_05_003900_create_referral_return_reviews_table.php`
- `docs/10-delivery/PHASE-3A-CLOSURE.md`

Daftar tersebut hanya petunjuk awal. Jangan menganggap file telah lengkap atau benar. Inspeksi working tree untuk menentukan kondisi aktual.

---

# 2. ATURAN PENGAMBILALIHAN

1. Jangan menghapus, menolak, membatalkan, atau menimpa perubahan parsial sebelum diperiksa.
2. Jangan menjalankan `git reset --hard`, `git clean -fd`, `git checkout -- .`, `git restore .`, atau `git stash drop`.
3. Jangan menekan atau mengasumsikan perubahan pada panel Review sudah aman.
4. Jangan membuat ulang model, migration, enum, service, atau tabel yang sudah ada.
5. Jangan membuat migration dengan tujuan sama tetapi nama berbeda sebelum memeriksa migration yang telah dibuat.
6. Jangan mengubah migration yang sudah pernah diterapkan pada database mana pun. Gunakan corrective migration.
7. Migration yang belum pernah diterapkan dan hanya berada pada local development boleh diperbaiki setelah statusnya dibuktikan.
8. Jangan mengarang hasil kerja Gemini sebelumnya.
9. Jangan percaya walkthrough atau komentar kode tanpa memverifikasi implementasi.
10. Jangan menampilkan `.env`, token, password, secret, atau credential.
11. Jangan menggunakan data pasien nyata.
12. Jangan menjalankan production deployment.
13. Jangan memperluas scope di luar Phase 3B.
14. Jangan membuat discharge final, notifikasi wali/asrama, integrasi Absensi, billing, atau klaim.
15. Jangan menyatakan selesai sebelum seluruh verifikasi aktual lulus.

---

# 3. TAHAP 0 — RECOVERY PREFLIGHT

Sebelum menulis kode, lakukan read-only inspection:

```bash
pwd
git branch --show-current
git status --short
git status
git diff --stat
git diff --check
git diff --name-status
git log --oneline -10
```

Periksa:

- file modified;
- file untracked;
- staged vs unstaged;
- konflik merge;
- file generated;
- accidental secret;
- `.env`;
- `vendor/`;
- `node_modules/`;
- `public/build/`;
- `storage/`;
- `graphify-out/`.

Jangan mencetak isi `.env`.

Tampilkan struktur file Phase 3B:

```bash
find app database routes resources/views tests docs -maxdepth 5 \
  \( -iname '*referral*' -o -iname '*handover*' -o -iname '*transport*' \) \
  -print
```

Periksa migration:

```bash
php artisan migrate:status
```

Periksa route dan model parsial:

```bash
php artisan route:list
```

Jangan menjalankan migration baru sebelum schema dan migration parsial selesai direview.

---

# 4. BUAT RECOVERY CHECKPOINT

Setelah memastikan tidak ada secret atau dependency terikut:

1. Pertahankan seluruh perubahan parsial.
2. Buat branch pengambilalihan jika belum berada pada branch khusus:

```bash
git switch -c resume/phase-3b-claude
```

Jika branch tersebut sudah ada, tetap gunakan branch aktif dan dokumentasikan.

3. Review file yang akan masuk checkpoint:

```bash
git diff --check
git status --short
```

4. Buat WIP checkpoint lokal hanya setelah memastikan aman:

```bash
git add -A
git diff --cached --check
git commit -m "wip(referral): checkpoint interrupted Phase 3B implementation"
```

Jangan rewrite history atau squash otomatis. Laporkan commit WIP secara terpisah pada output akhir.

Jika commit tidak aman karena code mengandung secret/generated files, perbaiki staging terlebih dahulu. Jangan menghapus source change yang valid.

---

# 5. TAHAP 1 — RECONSTRUCT ACTUAL STATE

Baca:

1. `AGENTS.md`
2. `PROMPT-ANTIGRAVITY-PHASE-3B.md`
3. `PROJECT-STATUS.md`
4. `CHANGELOG.md`
5. `docs/10-delivery/PHASE-3A-CLOSURE.md`
6. `docs/02-workflows/HOSPITAL-REFERRAL.md`
7. `docs/02-workflows/RETURN-FROM-REFERRAL.md`
8. `docs/02-workflows/EMERGENCY-HANDLING.md`
9. `docs/05-data/STATE-MACHINES.md`
10. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
11. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`
12. seluruh file parsial Phase 3B.

Kemudian buat:

`docs/10-delivery/PHASE-3B-RESUME-STATE.md`

Isi wajib:

- commit terakhir Phase 3A;
- branch aktif;
- WIP checkpoint commit;
- daftar file parsial;
- tabel/migration yang sudah dibuat;
- migration applied/not applied;
- fitur yang lengkap;
- fitur yang sebagian;
- fitur yang belum dimulai;
- compile/static-analysis errors;
- test yang tersedia;
- risiko schema;
- risiko authorization;
- risiko concurrency;
- exact continuation plan.

Gunakan status:

- `COMPLETE`;
- `PARTIAL`;
- `NOT_STARTED`;
- `BLOCKED`.

Jangan mulai coding lanjutan sebelum state reconstruction selesai.

---

# 6. REVIEW PERUBAHAN PARSIAL

Untuk setiap file parsial:

1. Bandingkan dengan requirement Phase 3B.
2. Periksa naming dan namespace.
3. Periksa casts, fillable/guarded, relation, ULID, enum, timestamp, soft behavior, dan lock version.
4. Periksa foreign key dan delete behavior.
5. Periksa unique constraint.
6. Periksa indexes.
7. Periksa state values.
8. Periksa server-authoritative actor/time.
9. Periksa mass-assignment risk.
10. Periksa hard-delete path.
11. Periksa Policy coverage.
12. Periksa audit integration.
13. Periksa transaction boundary.
14. Periksa concurrency safety.

Khusus migration yang terlihat:

## `referrals`

Wajib mencakup atau memiliki corrective plan:

- visit;
- final assessment;
- optional observation;
- optional consultation/local decision;
- partner;
- contact;
- concurrency-safe referral number;
- urgency;
- reason;
- requested service;
- full state attribution;
- cancellation/superseding;
- lock version;
- safe foreign keys.

## `referral_versions`

Wajib:

- version unique per referral;
- immutable snapshot;
- private document path;
- checksum;
- author/finalized time;
- supersedes reference;
- minimum-necessary note;
- authority/consent reference.

## Transport/companions/handover

Wajib:

- attribution;
- actual server timestamps;
- status;
- cancellation/error behavior;
- lock/version;
- no public document;
- idempotency for handoff;
- acknowledgement distinct from handoff;
- primary companion uniqueness strategy.

## Return/review

Wajib:

- one return per referral;
- external content does not automatically mutate local diagnosis or medication;
- private external documents are represented;
- local review separated and versioned;
- no automatic discharge.

Tambahkan migration/tabel yang belum ada sesuai original Phase 3B, seperti status events atau external documents, hanya setelah memastikan benar-benar belum dibuat.

---

# 7. CONTINUATION PLAN

Setelah audit parsial, lanjutkan pekerjaan yang belum selesai dari prompt sumber dengan urutan:

1. Selesaikan/correct referral aggregate.
2. Selesaikan referral number strategy.
3. Selesaikan one-active-referral guard.
4. Selesaikan versioned referral document.
5. Selesaikan transport.
6. Selesaikan companion assignment.
7. Selesaikan clinical handover.
8. Selesaikan destination status events.
9. Selesaikan return from referral.
10. Selesaikan private external documents.
11. Selesaikan local return review.
12. Selesaikan state machine integration.
13. Selesaikan Policies/permissions.
14. Selesaikan Actions/Services/domain events/audit.
15. Selesaikan controllers/routes/requests.
16. Selesaikan UI.
17. Selesaikan tests.
18. Perbarui documentation.
19. Update Graphify.
20. Jalankan final verification.

Jangan mengulang file yang sudah benar. Perbaiki atau lanjutkan dari implementasi yang ada.

---

# 8. MIGRATION SAFETY

Sebelum mengubah migration:

1. Catat hasil `php artisan migrate:status`.
2. Jika migration Phase 3B belum applied:
   - boleh diperbaiki langsung;
   - pastikan urutan timestamp tetap konsisten;
   - jangan membuat duplicate table migration.
3. Jika migration sudah applied:
   - jangan edit migration lama;
   - buat corrective migration baru;
   - dokumentasikan alasan.
4. Jangan gunakan `migrate:fresh`.
5. Jalankan migration development/test secara terkontrol.
6. Pastikan rollback aman untuk migration baru.
7. Jangan menghapus data existing.

Jika schema aktual dan migration berbeda, gunakan database inspection dan corrective migration.

---

# 9. IMPLEMENTATION REQUIREMENTS YANG TETAP WAJIB

Seluruh requirement dari `PROMPT-ANTIGRAVITY-PHASE-3B.md` tetap berlaku, termasuk:

- referral tidak wajib memiliki consultation;
- emergency referral tidak menunggu consultation;
- satu active referral per visit;
- referral number concurrency-safe;
- referral document versioned/private;
- minimum necessary;
- transport/companion;
- handoff tidak sama dengan acceptance;
- destination status terverifikasi;
- return from referral;
- private external documents;
- external result tidak otomatis mengubah local diagnosis/medication;
- local review tidak otomatis membuat discharge;
- server-authoritative actors/timestamps;
- append-only audit;
- direct URL/IDOR protection;
- no hard delete;
- no billing/discharge/attendance integration.

---

# 10. TEST DAN VERIFIKASI

Jangan hanya melanjutkan test count lama.

Buat test untuk seluruh risiko Phase 3B:

## Recovery regression

- partial files compile;
- migration ordering;
- no duplicate table/model/routes;
- no accidental removal of Phase 3A behavior.

## Referral

- finalized assessment required;
- consultation optional;
- partner referral enabled;
- one active referral;
- concurrent creation with MariaDB;
- referral number concurrency with MariaDB;
- invalid state transitions;
- actor/time server-side;
- direct URL/IDOR;
- emergency bypass.

## Version/document

- immutable final version;
- old snapshot unchanged;
- checksum;
- private storage;
- download audit;
- no public URL;
- minimum necessary.

## Transport/companion/handover

- readiness guard;
- primary companion uniqueness;
- actual departure server time;
- idempotent handoff;
- acknowledgement distinct;
- recipient substitution rejected;
- atomic state change.

## Return

- return only from valid referral state;
- only one return;
- external document private and validated;
- no automatic diagnosis update;
- no automatic medication order;
- local review immutable;
- no discharge creation.

## Audit

- rollback creates no success audit;
- correction non-destructive;
- download/handoff/status changes audited.

Run actual commands:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
```

Concurrency test wajib memakai MariaDB/MySQL nyata. Jangan mengklaim concurrency-safe dari SQLite.

Laporkan skipped test dan alasannya.

---

# 11. GRAPHIFY

Setelah code stabil:

1. update graph tanpa `--code-only`;
2. pastikan dependency/generated exclusions aktif;
3. query:
   - Assessment/Observation/Consultation -> Referral;
   - Referral -> Version -> Transport -> Handoff;
   - Referral -> Return -> LocalReview;
   - emergency referral bypass;
   - one active referral guard;
   - referral number generation;
   - private document path;
   - external diagnosis auto-update leakage;
   - medication auto-create leakage;
   - discharge creation leakage;
   - unauthorized admin path;
   - missing tests;
   - hard delete path.

Perbarui mapping dan traceability docs.

---

# 12. FINAL DOCUMENTATION

Perbarui seluruh dokumen yang diwajibkan oleh prompt sumber, ditambah:

- `docs/10-delivery/PHASE-3B-RESUME-STATE.md`;
- `docs/10-delivery/PHASE-3B-CLOSURE.md`.

`PHASE-3B-CLOSURE.md` harus membedakan:

- pekerjaan yang berasal dari Gemini sebelum interruption;
- correction yang dilakukan Claude;
- implementation baru yang diselesaikan Claude;
- unresolved follow-up.

---

# 13. FINAL GIT CHECKPOINT

Setelah seluruh verification lulus:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "feat(referral): complete Phase 3B referral and return workflow"
git status
```

Target:

- final working tree clean;
- WIP checkpoint tetap terlihat;
- final commit terpisah;
- jangan rewrite history otomatis.

---

# 14. OUTPUT AKHIR

Berikan:

1. Resume-state summary.
2. WIP checkpoint commit.
3. Phase 3A closure status.
4. Files inherited from Gemini.
5. Defects found in partial implementation.
6. Corrections made by Claude.
7. New files/features completed by Claude.
8. Schema/migrations and applied status.
9. Referral state machine.
10. Emergency referral behavior.
11. Versioned/private referral document.
12. Transport/companion/handover.
13. Destination status and return workflow.
14. Local review separation.
15. Policies/permissions/audit.
16. Tests and actual results.
17. MariaDB concurrency result.
18. Graphify findings.
19. Screenshots/artifacts.
20. Risks/blockers.
21. Final commit and working-tree status.
22. Exact recommended next phase.

---

# 15. CHECKPOINT WAJIB

Berhenti jika:

- partial migration state tidak dapat ditentukan;
- migration yang sudah applied akan diedit;
- secret ditemukan;
- duplicate table/model conflict tidak dapat diselesaikan aman;
- emergency referral tertahan consultation;
- one-active-referral guard tidak concurrency-safe;
- referral number tidak concurrency-safe;
- private document exposure ditemukan;
- return review otomatis mengubah diagnosis, medication, atau discharge;
- authorization/IDOR gagal;
- critical test gagal.

Jika seluruh requirement lulus:

- selesaikan Phase 3B;
- commit final;
- working tree clean;
- jangan melanjutkan ke Phase 3C;
- tunggu persetujuan eksplisit pengguna.
