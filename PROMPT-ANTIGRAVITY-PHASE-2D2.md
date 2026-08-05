# PROMPT ANTIGRAVITY — PHASE 2D2
## Phase 2D1 Closure Hardening, Medication Orders, Medication Administration, and Atomic Stock Issue

Anda adalah principal Laravel architect, health information system engineer, medication safety systems engineer, pharmacy inventory engineer, application security engineer, database concurrency reviewer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini:

1. memverifikasi dan mengeraskan fondasi farmasi Phase 2D1;
2. membangun instruksi/order obat yang terstruktur;
3. membangun pencatatan obat yang benar-benar diberikan kepada pasien;
4. mengintegrasikan pemberian obat dengan batch dan ledger stok secara atomik;
5. membangun pemeriksaan alergi, status pemberian, pembatalan, dan koreksi non-destruktif;
6. mempertahankan authorization, audit, idempotency, immutability, dan concurrency safety;
7. berhenti sebelum konsultasi eksternal, rujukan aktual, discharge final, billing, dan keputusan klinis otomatis.

Fase ini tidak boleh menghasilkan rekomendasi obat, diagnosis, atau perhitungan dosis otomatis.

---

# 1. DOKUMEN WAJIB DIBACA

Sebelum mengubah kode, baca:

1. `AGENTS.md`
2. `README.md`
3. `PROJECT-STATUS.md`
4. `CHANGELOG.md`
5. `docs/README.md`
6. `docs/00-project/MVP-SCOPE.md`
7. `docs/01-domain/OPERATIONAL-CONTEXT.md`
8. `docs/01-domain/BUSINESS-RULES.md`
9. `docs/01-domain/MEDICAL-TERMINOLOGY.md`
10. `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`
11. `docs/02-workflows/INITIAL-ASSESSMENT.md`
12. `docs/02-workflows/OBSERVATION-AND-CARE.md`
13. `docs/02-workflows/MEDICATION-ADMINISTRATION.md`
14. `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
15. `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
16. `docs/03-requirements/TRACEABILITY-MATRIX.md`
17. `docs/04-architecture/MODULE-BOUNDARIES.md`
18. `docs/04-architecture/APPLICATION-LAYERS.md`
19. `docs/05-data/DOMAIN-MODEL.md`
20. `docs/05-data/ENTITY-RELATIONSHIPS.md`
21. `docs/05-data/DATA-DICTIONARY.md`
22. `docs/05-data/DATABASE-CONVENTIONS.md`
23. `docs/05-data/STATE-MACHINES.md`
24. `docs/05-data/MEDICAL-RECORD-VERSIONING.md`
25. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
26. `docs/07-security/MEDICAL-DATA-PRIVACY.md`
27. `docs/07-security/AUDIT-LOG.md`
28. `docs/09-testing/TEST-STRATEGY.md`
29. `docs/09-testing/BUSINESS-SCENARIOS.md`
30. `docs/09-testing/SECURITY-TESTS.md`
31. `docs/10-delivery/PHASE-2C-CLOSURE.md`
32. `docs/10-delivery/READINESS-REVIEW.md`
33. `plans/KNOWN-ISSUES.md`
34. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika `docs/10-delivery/PHASE-2D1-CLOSURE.md` belum tersedia, buat pada tahap closure.

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang formularium, kewenangan peresepan, dosis, jadwal pemberian, aturan substitusi, atau SOP farmasi.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data pasien atau obat nyata.
4. Jangan membuat diagnosis otomatis.
5. Jangan merekomendasikan obat.
6. Jangan menghitung dosis berdasarkan umur, berat, diagnosis, atau parameter pasien.
7. Jangan mengubah dosis yang dimasukkan petugas.
8. Jangan membuat resep eksternal atau e-prescribing.
9. Jangan mengizinkan pemberian obat tanpa patient, visit, actor, waktu server, dan authorization.
10. Jangan mengurangi stok saat order dibuat.
11. Stok hanya berkurang ketika administration berstatus `administered`.
12. Jangan mengurangi stok untuk status `scheduled`, `held`, `refused`, `missed`, atau `cancelled`.
13. Jangan menggunakan batch expired, quarantined, recalled, depleted, atau entered-in-error.
14. Jangan mengizinkan stok negatif.
15. Jangan mengedit ledger movement atau administration final.
16. Koreksi menggunakan cancellation, reversal, addendum, atau entered-in-error.
17. Semua operasi multi-tabel memakai transaction.
18. Actor, official time, state, dan stock movement ditentukan server.
19. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 2D1 CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-2D1-CLOSURE.md`

## A.1 Medicine master completeness

Verifikasi dan perbaiki bila diperlukan:

- `code` unique;
- generic name;
- brand name nullable;
- dosage form;
- strength text;
- base unit;
- minimum stock;
- active status;
- `requires_batch_tracking`;
- `requires_expiry_tracking`;
- created/updated attribution;
- lock/version bila diperlukan;
- inactive medicine tidak menerima transaksi baru;
- medicine yang memiliki history tidak dapat hard delete.

Jika tracking flags belum tersedia, buat migration aman.

## A.2 Batch completeness

Verifikasi:

- medicine;
- location;
- batch number;
- expiry date;
- received date;
- supplier/reference nullable;
- initial quantity;
- current quantity/read model;
- status;
- created by;
- lock version;
- unique policy;
- expired/quarantined/recalled/entered-in-error tidak available;
- batch dengan movement tidak dapat hard delete.

## A.3 Ledger source of truth

Verifikasi:

- `stock_movements` append-only;
- tidak ada update/delete route;
- quantity selalu positif;
- direction ditentukan movement type;
- actor dan occurred_at dari server;
- reason wajib untuk adjustment/reversal;
- idempotency key memiliki database unique constraint sesuai scope;
- correlation ID tersedia;
- reversal reference tersedia;
- movement tidak dapat direverse dua kali;
- failed transaction tidak membuat movement/audit sukses.

## A.4 Balance materialization and reconciliation

Karena `medicine_batches.current_quantity` digunakan, dokumentasikan bahwa:

- ledger adalah source of truth;
- `current_quantity` adalah materialized balance/read model;
- current quantity tidak dapat diedit langsung;
- setiap movement dan balance update berada dalam transaction yang sama;
- row batch dikunci;
- tersedia reconciliation command/service/report;
- mismatch dapat dideteksi;
- repair tidak menghapus ledger;
- repair menggunakan controlled correction movement atau prosedur terdokumentasi.

Buat ADR jika belum tersedia.

## A.5 Concurrency and idempotency proof

Gunakan MariaDB/MySQL nyata untuk membuktikan:

- concurrent receipt tidak menduplikasi idempotency key;
- concurrent adjustment-out tidak menghasilkan stok negatif;
- concurrent reversal hanya berhasil sekali;
- current balance sama dengan ledger setelah concurrency;
- unique collision ditangani;
- transaksi gagal tidak meninggalkan balance parsial;
- tidak memakai SQLite in-memory sebagai bukti row locking.

## A.6 Expiry and availability

Verifikasi:

- expiry dihitung berdasarkan tanggal server;
- expired batch tidak dapat digunakan;
- near-expiry threshold configurable;
- depleted status sinkron dengan balance;
- quarantined/recalled tidak available;
- `available_quantity` tidak menghitung batch terlarang;
- tidak ada automatic clinical recommendation.

## A.7 Authorization, audit, Graphify

Verifikasi:

- admin teknis 403 tanpa pharmacy permission;
- adjustment dan reversal memiliki permission terpisah;
- direct URL dan IDOR test;
- audit tidak dapat dimutasi;
- Graphify telah diperbarui;
- exclusions tetap aktif;
- query results dicatat.

## A.8 Closure result

Gunakan:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika no-negative-stock, reversal, ledger immutability, idempotency, authorization, atau reconciliation memiliki temuan Critical, berhenti.

---

# 4. TAHAP B — MEDICATION DOMAIN BOUNDARY

Implementasikan modul `Medication` terpisah dari `Pharmacy`.

Pisahkan:

- medication order/instruction;
- medication administration;
- allergy acknowledgement;
- batch allocation/issue;
- administration status;
- correction/cancellation;
- stock movement reference.

Aturan dependency:

- Medication membaca medicine/batch availability melalui contract/service;
- Medication tidak mengedit `current_quantity` langsung;
- Pharmacy membuat stock movement melalui Action resmi;
- administration dan stock issue berada dalam transaction terkoordinasi;
- Pharmacy tetap tidak mengakses data klinis lebih dari yang diperlukan.

---

# 5. TAHAP C — MEDICATION ORDER / INSTRUCTION

## C.1 Scope

Medication order adalah instruksi internal POSKESTREN, bukan resep elektronik eksternal.

Order tidak otomatis menentukan obat atau dosis.

## C.2 Schema

Buat `medication_orders` dengan ULID dan field minimum:

- `medical_visit_id`;
- `clinical_assessment_id` nullable/required sesuai keputusan;
- `medicine_id`;
- `dose_value`;
- `dose_unit`;
- `route`;
- `frequency_text` atau structured frequency bila requirement cukup;
- `instructions` nullable;
- `start_at` nullable;
- `end_at` nullable;
- `quantity_per_administration`;
- `ordered_by_id`;
- `ordered_at` server-side;
- `status`;
- `reason_or_indication` nullable;
- `discontinued_at` nullable;
- `discontinued_by_id` nullable;
- `discontinuation_reason` nullable;
- `parent_order_id` nullable untuk revision;
- `lock_version`;
- timestamps.

Status:

- `draft`;
- `active`;
- `completed`;
- `discontinued`;
- `cancelled`;
- `entered_in_error`.

## C.3 Validation

- patient/visit valid;
- visit tidak cancelled;
- medicine active;
- dose positive;
- unit sesuai daftar terkontrol;
- route terkontrol;
- actor/time server-side;
- tidak menerima `ordered_by_id` dari payload;
- tidak menerima status final bebas;
- tidak menghitung dosis;
- tidak memaksa working diagnosis;
- reason/indication mengikuti SOP dan permission;
- allergy check dilakukan sebelum activation;
- order draft tidak mengurangi stok.

## C.4 Authorization

Permission minimum:

- `create-medication-orders`;
- `activate-medication-orders`;
- `revise-medication-orders`;
- `discontinue-medication-orders`;
- `view-medication-orders`.

Jangan memberikan permission berdasarkan nama role tanpa keputusan stakeholder.

## C.5 Immutability and revision

- draft dapat diedit terkontrol;
- active order tidak diedit langsung;
- perubahan active order membuat revision/new order;
- original tetap ada;
- discontinuation non-destruktif;
- entered-in-error diaudit;
- optimistic locking mencegah lost update.

---

# 6. TAHAP D — ALLERGY AND SAFETY CHECK

## D.1 Allergy review

Sebelum order diaktifkan atau administration dilakukan:

- tampilkan alergi aktif/confirmed/provisional sesuai kebijakan;
- cocokkan allergen dengan medicine menggunakan mapping yang eksplisit jika tersedia;
- jangan mengarang mapping obat-alergi berbasis nama bebas;
- jika mapping formal belum tersedia, tampilkan seluruh active allergy sebagai warning untuk review manusia;
- petugas harus mengakui warning jika melanjutkan;
- acknowledgement memiliki actor, time, reason;
- acknowledgement bukan bukti obat aman;
- aplikasi tidak membuat keputusan klinis otomatis.

## D.2 Schema acknowledgement

Buat `medication_safety_acknowledgements` atau struktur setara:

- patient;
- visit;
- medication order/administration;
- warning type;
- allergy reference nullable;
- warning snapshot;
- acknowledged by;
- acknowledged at;
- reason;
- correlation ID.

Record append-only.

## D.3 Additional safety

Jangan membuat interaction checker otomatis kecuali sumber data dan governance telah disetujui. Tandai `[PERLU DIKONFIRMASI]`.

---

# 7. TAHAP E — MEDICATION ADMINISTRATION

## E.1 Schema

Buat `medication_administrations`:

- ULID;
- `medical_visit_id`;
- `medication_order_id` nullable bila one-time administration diizinkan;
- `medicine_id`;
- `medicine_batch_id` nullable sampai administered;
- `scheduled_at` nullable;
- `status`;
- `dose_value`;
- `dose_unit`;
- `route`;
- `administered_at` nullable;
- `administered_by_id` nullable;
- `recorded_at` server-side;
- `recorded_by_id`;
- `reason` nullable;
- `notes` nullable;
- `stock_movement_id` nullable;
- `idempotency_key`;
- `parent_administration_id` nullable;
- `lock_version`;
- timestamps.

Status:

- `scheduled`;
- `administered`;
- `held`;
- `refused`;
- `missed`;
- `cancelled`;
- `entered_in_error`.

## E.2 Rules

### Scheduled

- belum mengurangi stok;
- dapat berasal dari active order;
- actor/time server;
- tidak boleh menggunakan batch final.

### Administered

Wajib:

- patient/visit valid;
- order aktif atau one-time permission;
- medicine cocok dengan order;
- dose dan route cocok atau override khusus dengan reason;
- allergy warning telah direview;
- batch eligible;
- batch belum expired;
- quantity cukup;
- actor berwenang;
- `administered_at` server-side kecuali backdate berizin;
- stock issue dibuat atomik;
- administration dan stock movement saling mereferensikan;
- idempotent;
- audit after commit only.

### Held

- tidak mengurangi stok;
- reason wajib;
- tidak dianggap administered.

### Refused

- tidak mengurangi stok;
- reason/catatan wajib;
- actor/time tercatat;
- jangan menyalahkan pasien pada label UI.

### Missed

- tidak mengurangi stok;
- reason wajib;
- status tidak otomatis ditentukan tanpa rule yang disetujui.

### Cancelled

- hanya untuk record yang belum administered;
- reason wajib;
- non-destruktif.

### Entered in error

Untuk administration yang telah dicatat salah:

- record asli dipertahankan;
- stock movement direverse melalui Pharmacy Action;
- correction/reversal atomik;
- reason dan permission khusus;
- tidak mengedit movement asal;
- tidak melakukan reversal dua kali.

## E.3 One-time administration

Jika pemberian tanpa order diperlukan:

- permission khusus `administer-one-time-medication`;
- reason wajib;
- assessment/visit context wajib;
- tidak digunakan sebagai bypass rutin;
- audit dan report terpisah.

Jika governance belum disetujui, tunda dan tandai `[PERLU DIKONFIRMASI]`.

---

# 8. TAHAP F — ATOMIC STOCK ISSUE

## F.1 Movement type

Tambahkan movement type yang jelas, misalnya:

- `medication_administration_issue`;
- `medication_administration_reversal`.

Jangan menggunakan `adjustment_out` untuk pemberian normal kepada pasien.

## F.2 Transaction boundary

Dalam satu transaction:

1. lock administration/order bila diperlukan;
2. lock medicine batch;
3. validasi availability dan expiry;
4. validasi idempotency;
5. buat stock movement issue;
6. update materialized balance;
7. tandai administration `administered`;
8. simpan cross-reference;
9. buat domain events/audit yang konsisten.

Jika salah satu langkah gagal, seluruh transaksi rollback.

## F.3 Batch selection

Default:

- petugas memilih batch eligible;
- sistem boleh mengurutkan FEFO sebagai bantuan;
- sistem tidak diam-diam mengganti batch tanpa konfirmasi;
- expired/quarantined/recalled/depleted/entered-in-error dikecualikan;
- pilihan batch diaudit.

Jika automatic FEFO akan diterapkan, buat ADR dan tetap simpan batch aktual.

## F.4 Quantity

Tentukan quantity issue secara eksplisit berdasarkan base unit.

- conversion factor harus tervalidasi;
- rounding policy terdokumentasi;
- quantity issue positive;
- no negative result;
- administration dose dan inventory quantity tidak dianggap selalu identik tanpa conversion policy.

Jika unit conversion belum matang, batasi MVP pada order/admin unit yang kompatibel dengan base unit dan tandai batasan.

## F.5 Reversal

Entered-in-error administration:

- hanya permission khusus;
- lock administration dan batch;
- pastikan belum pernah direverse;
- buat reversal movement;
- update balance;
- tandai administration entered-in-error;
- referensikan correction record bila ada;
- audit;
- transaction atomic.

---

# 9. TAHAP G — OBSERVATION INTEGRATION

Medication dapat terkait visit yang sedang:

- under assessment;
- assessment completed;
- under observation;
- observation completed sesuai rule.

Untuk observation workspace:

- tampilkan active medication orders;
- jadwal administration;
- administration history;
- allergy warning;
- batch hanya terlihat bagi role yang perlu;
- handover boleh menampilkan pending medication task sebagai informasi, tetapi bukan membuat order baru;
- pending task harus merujuk order/admin record, bukan text bebas jika tersedia.

Jangan membuat observasi otomatis berdasarkan obat.

---

# 10. VISIT STATE

Medication tidak otomatis mengubah visit menjadi discharged, referred, atau completed.

Jangan menambahkan state downstream pada fase ini.

Administration tetap menjadi bagian timeline visit.

---

# 11. PERMISSION DAN POLICY

Permission minimum:

- `view-medication-orders`;
- `create-medication-orders`;
- `activate-medication-orders`;
- `revise-medication-orders`;
- `discontinue-medication-orders`;
- `view-medication-administrations`;
- `schedule-medication-administrations`;
- `administer-medications`;
- `administer-one-time-medication`;
- `hold-medications`;
- `record-medication-refusal`;
- `record-missed-medication`;
- `correct-medication-administrations`;
- `view-medication-stock-reference`.

Aturan:

1. admin teknis tidak otomatis memiliki clinical/pharmacy permission;
2. ordering dan administering dapat dipisah;
3. petugas tidak dapat menaikkan permission sendiri;
4. direct URL Policy;
5. IDOR protection;
6. UI hiding bukan authorization;
7. batch/cost detail hanya bagi role yang memerlukan;
8. self-medication record oleh petugas yang juga patient mengikuti conflict-of-interest policy `[PERLU DIKONFIRMASI]`.

---

# 12. UI PHASE 2D2

Pertahankan tema biru muda, light/dark/system.

## Medication order UI

- patient/visit header;
- active allergy banner;
- medicine selector;
- dose/unit/route;
- instruction;
- draft/activate;
- revision/discontinue;
- permission-aware.

## Medication administration queue

- scheduled administrations;
- due/overdue indicator;
- patient;
- medicine;
- dose/route;
- order;
- observation location;
- status;
- allergy warning.

Due/overdue hanya status operasional, bukan kesimpulan klinis.

## Administration confirmation

- verify patient;
- verify medicine;
- verify dose;
- verify route;
- choose eligible batch;
- show available quantity and expiry;
- allergy acknowledgement;
- explicit final confirmation;
- duplicate submission protection.

## Administration history

- administered;
- held;
- refused;
- missed;
- cancelled;
- entered-in-error;
- actor/time;
- linked stock movement bagi role berwenang.

## UX requirements

- mobile-first;
- accessible;
- keyboard focus;
- loading/empty/error/forbidden;
- optimistic lock conflicts;
- text+icon status;
- no color-only warnings;
- no dose recommendation;
- screenshots light/dark desktop/mobile dengan data sintetis.

---

# 13. DOMAIN EVENTS DAN AUDIT

Event minimum:

- `MedicationOrderCreated`;
- `MedicationOrderActivated`;
- `MedicationOrderRevised`;
- `MedicationOrderDiscontinued`;
- `MedicationSafetyWarningAcknowledged`;
- `MedicationAdministrationScheduled`;
- `MedicationAdministered`;
- `MedicationHeld`;
- `MedicationRefused`;
- `MedicationMissed`;
- `MedicationAdministrationCancelled`;
- `MedicationAdministrationEnteredInError`;
- `MedicationStockIssued`;
- `MedicationStockIssueReversed`.

Audit harus:

- append-only;
- actor/time server-side;
- reason pada override/correction;
- correlation ID;
- before/after terpilih;
- tidak menulis success saat rollback;
- tidak menyimpan secret;
- tidak membuat duplikasi karena retry/idempotency.

---

# 14. TEST WAJIB

## Phase 2D1 closure

- tracking flags;
- batch uniqueness;
- expired/quarantined/recalled unavailable;
- ledger immutable;
- idempotency database unique;
- reversal only once;
- no-negative-stock concurrency dengan MariaDB;
- receipt concurrency;
- materialized balance equals ledger;
- reconciliation mismatch detection;
- failed transaction no partial balance/audit;
- admin technical 403;
- Graphify update proof.

## Medication order

- authorized create draft;
- unauthorized activation 403;
- actor/time server-side;
- positive dose;
- controlled unit/route;
- active order immutable;
- revision preserves original;
- discontinuation non-destructive;
- inactive medicine rejected;
- allergy warning displayed/reviewed;
- no stock movement on draft/active order;
- no automatic dose.

## Administration

- scheduled does not reduce stock;
- held/refused/missed/cancelled do not reduce stock;
- administered reduces exact stock atomically;
- batch must match medicine;
- expired/quarantined/recalled/depleted batch rejected;
- insufficient stock rejected;
- actor/time server-side;
- idempotent duplicate submission;
- administration and movement cross-reference;
- failed administration no stock movement/audit success;
- unauthorized direct URL/IDOR 403;
- mass assignment rejected;
- XSS-safe notes.

## Concurrency

Using MariaDB/MySQL nyata:

- two concurrent administrations against same remaining quantity;
- only valid quantity succeeds;
- no negative stock;
- duplicate idempotency key creates one administration/issue;
- concurrent entered-in-error reversal succeeds once;
- balance equals ledger after concurrency.

## Correction

- entered-in-error preserves original administration;
- stock reversal is append-only;
- cannot reverse twice;
- correction requires permission/reason;
- rollback safe.

## Regression

- identity/Gate unchanged;
- health profile unchanged;
- visits/assessment unchanged;
- observation/handover unchanged;
- pharmacy reconciliation unchanged;
- no external consultation/referral/discharge;
- theme light/dark/system;
- route security.

Jalankan:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
```

Concurrency test wajib memakai MariaDB/MySQL nyata. Laporkan skipped test dan alasannya.

---

# 15. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - Assessment -> MedicationOrder;
   - MedicationOrder -> MedicationAdministration;
   - MedicationAdministration -> StockMovement;
   - allergy acknowledgement path;
   - stock issue transaction;
   - idempotency path;
   - entered-in-error reversal;
   - direct current_quantity mutation;
   - dose recommendation leakage;
   - unauthorized admin path;
   - requirements tanpa test;
   - hard delete path.

Perbarui:

- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`.

Laporkan node/edge hanya jika command benar-benar menghasilkan nilai.

---

# 16. DOKUMENTASI WAJIB

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-2D1-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md`;
- `docs/01-domain/MEDICAL-TERMINOLOGY.md`;
- `docs/02-workflows/MEDICATION-ADMINISTRATION.md`;
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`;
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/04-architecture/MODULE-BOUNDARIES.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/07-security/AUDIT-LOG.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Buat ADR untuk:

- medication order revision;
- one-time administration;
- inventory unit conversion;
- batch selection/FEFO;
- allergy warning acknowledgement;
- administration-stock transaction;
- entered-in-error reversal.

---

# 17. OUTPUT AKHIR

Berikan:

1. Phase 2D1 closure status.
2. Hardening findings dan perbaikan.
3. Schema/migration baru.
4. Medication order architecture.
5. Allergy/safety acknowledgement.
6. Medication administration state machine.
7. Atomic stock issue and reversal.
8. Batch selection and unit strategy.
9. Permissions dan Policies.
10. Routes dan UI.
11. Audit events.
12. File dibuat/diubah.
13. Command dijalankan.
14. Test dan hasil aktual.
15. MariaDB concurrency test method/result.
16. Graphify results dan query findings.
17. Screenshot light/dark desktop/mobile.
18. Risiko dan blocker.
19. Git diff summary.
20. Exact next recommended phase.

---

# 18. CHECKPOINT WAJIB

Berhenti jika:

- Phase 2D1 closure gagal;
- ledger/reconciliation belum aman;
- order mengurangi stok;
- status selain administered mengurangi stok;
- administration-stock issue tidak atomik;
- expired/quarantined/recalled batch dapat dipakai;
- stok dapat negatif;
- idempotency gagal;
- finalized order/administration dapat diedit langsung;
- correction menghapus history;
- authorization/IDOR gagal;
- actor/timestamp dapat dimanipulasi;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **Medication Orders + Medication Administration + Atomic Stock Issue**;
- jangan membuat external consultation;
- jangan membuat referral;
- jangan membuat discharge final;
- jangan membuat billing;
- jangan membuat AI diagnosis/recommendation;
- tunggu persetujuan eksplisit pengguna.
