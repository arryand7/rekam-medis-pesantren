# PROMPT ANTIGRAVITY — PHASE 2D1
## Phase 2C Closure Hardening and Pharmacy Inventory Foundation

Anda adalah principal Laravel architect, health information system engineer, pharmacy inventory systems engineer, application security engineer, database concurrency reviewer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini:

1. memverifikasi dan mengeraskan hasil Phase 2C;
2. membangun master obat;
3. membangun batch, tanggal kedaluwarsa, penerimaan stok, dan ledger mutasi;
4. membangun stok minimum dan peringatan kedaluwarsa;
5. membuktikan integritas stok terhadap concurrent request;
6. mempertahankan authorization, audit, traceability, dan keamanan;
7. berhenti sebelum medication order, resep, pemberian obat kepada pasien, konsultasi eksternal, rujukan, dan discharge final.

Fase ini hanya membangun **fondasi farmasi dan inventaris**, bukan pelayanan pemberian obat kepada pasien.

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
10. `docs/02-workflows/OBSERVATION-AND-CARE.md`
11. `docs/02-workflows/MEDICATION-ADMINISTRATION.md`
12. `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
13. `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
14. `docs/03-requirements/TRACEABILITY-MATRIX.md`
15. `docs/04-architecture/MODULE-BOUNDARIES.md`
16. `docs/04-architecture/APPLICATION-LAYERS.md`
17. `docs/05-data/DOMAIN-MODEL.md`
18. `docs/05-data/ENTITY-RELATIONSHIPS.md`
19. `docs/05-data/DATA-DICTIONARY.md`
20. `docs/05-data/DATABASE-CONVENTIONS.md`
21. `docs/05-data/STATE-MACHINES.md`
22. `docs/05-data/MEDICAL-RECORD-VERSIONING.md`
23. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
24. `docs/07-security/MEDICAL-DATA-PRIVACY.md`
25. `docs/07-security/AUDIT-LOG.md`
26. `docs/09-testing/TEST-STRATEGY.md`
27. `docs/09-testing/BUSINESS-SCENARIOS.md`
28. `docs/09-testing/SECURITY-TESTS.md`
29. `docs/10-delivery/PHASE-2B-CLOSURE.md`
30. `docs/10-delivery/READINESS-REVIEW.md`
31. `plans/KNOWN-ISSUES.md`
32. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika `docs/10-delivery/PHASE-2C-CLOSURE.md` belum tersedia, buat pada tahap closure.

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang formularium, dosis, kewenangan pemberian obat, atau SOP farmasi.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data pasien atau obat nyata sebagai fixture.
4. Jangan membuat diagnosis otomatis atau clinical decision support otomatis.
5. Jangan membuat medication order, prescription, atau medication administration.
6. Jangan menghubungkan pengeluaran stok ke pasien pada fase ini.
7. Jangan menghitung dosis.
8. Jangan membuat rekomendasi obat otomatis.
9. Jangan mengizinkan stok negatif.
10. Jangan mengedit saldo stok secara langsung tanpa ledger.
11. Jangan menghapus batch atau stock movement untuk memperbaiki kesalahan.
12. Semua mutasi stok wajib transaction-safe, authorized, dan audited.
13. Actor dan timestamp resmi berasal dari server.
14. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 2C CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-2C-CLOSURE.md`

## A.1 Observation episode hardening

Verifikasi schema dan implementasi:

- `lock_version` tersedia atau keputusan penundaan terdokumentasi;
- `cancelled_at`, `cancelled_by_id`, dan cancellation reason tersedia;
- final episode tidak dapat diedit langsung;
- completion memerlukan outcome dan reason;
- `observation_completed` tidak menutup visit;
- satu visit maksimal satu active observation;
- tidak ada cascade delete berbahaya.

## A.2 Active observation concurrency

Buktikan bahwa:

1. parent `medical_visits` row yang selalu ada dikunci;
2. active-observation check dan insert berada pada transaction yang sama;
3. query kosong tidak menjadi celah race;
4. integration concurrency test memakai MariaDB/MySQL nyata;
5. hanya satu active observation yang berhasil dibuat;
6. transaksi gagal tidak menulis success audit.

Jika belum aman, perbaiki sebelum Phase 2D1.

## A.3 Monitoring versioning

Verifikasi `observation_records` memiliki atau mendukung:

- status `draft|finalized|entered_in_error`;
- `finalized_at`;
- `finalized_by_id`;
- `parent_record_id` atau addendum strategy;
- `lock_version`;
- actor dan server timestamp;
- finalized record immutable;
- linked vital sign berasal dari visit/patient yang sama;
- monitoring due/overdue memakai timezone `Asia/Jakarta`;
- overdue tidak menghasilkan kesimpulan klinis otomatis.

## A.4 Handover hardening

Verifikasi `observation_handovers` memiliki atau mendukung:

- `draft|submitted|acknowledged|cancelled|entered_in_error`;
- `risks_or_warnings`;
- `next_monitoring_due_at`;
- `submitted_at`;
- `acknowledged_at`;
- `acknowledged_by_id`;
- cancellation reason;
- `lock_version`;
- immutable after acknowledgement;
- atomically transfers `responsible_officer_id`;
- concurrent acknowledgement test;
- unassigned user tidak dapat acknowledge.

## A.5 Audit, authorization, Graphify

Verifikasi:

- direct URL Policy;
- admin teknis tidak otomatis memiliki clinical permission;
- event observasi tercatat;
- failure/rollback tidak menghasilkan success audit;
- Graphify telah diperbarui;
- dependency/generated exclusions masih aktif;
- query results dicatat.

## A.6 Closure status

Gunakan:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika concurrency, immutability, authorization, atau migration memiliki temuan Critical, berhenti.

---

# 4. TAHAP B — PHARMACY DOMAIN BOUNDARY

Implementasikan modul `Pharmacy` sebagai boundary tersendiri.

Pisahkan:

- master medicine;
- medicine batch;
- stock location;
- stock movement ledger;
- stock balance/read model;
- stock receipt;
- stock adjustment;
- expiry and low-stock monitoring.

Jangan menghubungkan modul ini ke patient, visit, assessment, observation, atau medication administration pada Phase 2D1.

---

# 5. TAHAP C — MEDICINE MASTER

## C.1 Medicine schema

Buat `medicines` dengan ULID dan field minimum:

- `code` unique;
- `generic_name`;
- `brand_name` nullable;
- `dosage_form`;
- `strength_text` nullable;
- `base_unit`;
- `category` nullable;
- `description` nullable;
- `minimum_stock` decimal/integer sesuai unit;
- `is_active`;
- `requires_batch_tracking`;
- `requires_expiry_tracking`;
- `created_by_id`;
- `updated_by_id`;
- timestamps;
- optional lock version.

Contoh dosage form hanya menjadi master data, bukan rekomendasi klinis:

- tablet;
- capsule;
- syrup;
- suspension;
- cream;
- ointment;
- drops;
- inhalation;
- injection;
- other.

Jangan mengarang formularium final. Tandai daftar kategori/form sebagai configurable dan `[PERLU DIKONFIRMASI]`.

## C.2 Units

Tentukan pendekatan unit secara eksplisit:

- satuan stok dasar;
- satuan penerimaan;
- conversion factor jika satu kemasan berisi beberapa unit;
- decimal precision;
- larangan conversion factor nol/negatif;
- rounding policy.

Jika conversion complexity belum disetujui, gunakan satu base unit per medicine pada MVP dan dokumentasikan batasannya.

## C.3 Lifecycle

Medicine dapat:

- active;
- inactive.

Inactive medicine:

- tidak dapat digunakan pada transaksi baru;
- tetap tampil pada riwayat;
- tidak hard delete jika pernah memiliki batch/movement.

## C.4 Authorization

Permission minimum:

- `view-medicines`;
- `create-medicines`;
- `update-medicines`;
- `deactivate-medicines`.

Admin teknis tidak otomatis menjadi petugas farmasi.

---

# 6. TAHAP D — STOCK LOCATIONS

Buat master `stock_locations` jika POSKESTREN membutuhkan lebih dari satu lokasi.

Field minimum:

- ULID;
- code unique;
- name;
- description nullable;
- is_active;
- created_by;
- timestamps.

Contoh:

- pharmacy room;
- emergency cabinet;
- observation cabinet.

Jangan membuat lokasi nyata tanpa data stakeholder. Gunakan data sintetis pada test.

Aturan:

- location inactive tidak menerima transaksi baru;
- movement lama tetap tersedia;
- tidak hard delete jika sudah dipakai.

Jika MVP hanya memiliki satu lokasi, tetap gunakan default location yang dapat dikonfigurasi atau dokumentasikan keputusan single-location.

---

# 7. TAHAP E — MEDICINE BATCHES

Buat `medicine_batches`:

- ULID;
- `medicine_id`;
- `stock_location_id`;
- `batch_number`;
- `expiry_date` nullable sesuai tracking;
- `received_at`;
- `supplier_name` nullable;
- `purchase_reference` nullable;
- `unit_cost` nullable jika scope mengizinkan;
- `initial_quantity`;
- `current_quantity` atau read-model balance bila dipilih;
- `status` `active|depleted|expired|quarantined|recalled|entered_in_error`;
- `created_by_id`;
- `lock_version`;
- timestamps.

## E.1 Unique policy

Tentukan unique key yang tepat, misalnya:

- medicine + location + batch number;
- tambahkan expiry date jika dibutuhkan.

Dokumentasikan keputusan.

## E.2 Expiry

- expired ditentukan server berdasarkan date;
- jangan mengeluarkan batch expired;
- hampir kedaluwarsa memakai configurable threshold;
- expiry tracking wajib jika medicine mengharuskannya;
- timezone/date boundary terdokumentasi;
- sistem tidak menghapus batch expired;
- quarantine/recalled tidak boleh tersedia untuk issue.

## E.3 Batch errors

Kesalahan input:

- jangan hard delete batch yang sudah memiliki movement;
- gunakan `entered_in_error` atau correction movement;
- alasan dan actor wajib;
- audit.

---

# 8. TAHAP F — APPEND-ONLY STOCK LEDGER

Buat `stock_movements` sebagai sumber kebenaran mutasi.

Field minimum:

- ULID;
- `medicine_id`;
- `medicine_batch_id`;
- `stock_location_id`;
- `movement_type`;
- `quantity`;
- `unit`;
- `occurred_at` server-side;
- `recorded_by_id`;
- `reason`;
- `reference_type` nullable;
- `reference_id` nullable;
- `idempotency_key` nullable/unique sesuai use case;
- `reverses_movement_id` nullable;
- `correlation_id`;
- timestamps.

Movement type fase ini:

- `receipt`;
- `adjustment_in`;
- `adjustment_out`;
- `transfer_in`;
- `transfer_out`;
- `reversal`;
- `opening_balance` hanya jika migrasi awal memerlukannya.

Jangan membuat:

- `medication_issue`;
- `patient_administration`;
- `prescription`;
- movement patient-related.

## F.1 Ledger rules

1. quantity selalu positif; arah ditentukan movement type;
2. movement immutable;
3. correction menggunakan reversal/new movement;
4. reversal mereferensikan movement asal;
5. movement tidak dapat direverse dua kali;
6. reason wajib untuk adjustment/reversal;
7. actor/time server-side;
8. source transaction idempotent;
9. no negative stock;
10. movement dan balance update atomik;
11. audit after commit only.

## F.2 Balance strategy

Pilih salah satu dan dokumentasikan melalui ADR:

### Ledger calculated balance

Balance dihitung dari sum movement.

Kelebihan: sumber kebenaran tunggal.  
Risiko: query lebih berat.

### Materialized/current balance

`medicine_batches.current_quantity` menjadi read model yang diperbarui atomik bersama ledger.

Kelebihan: cepat.  
Risiko: perlu reconciliation.

Jika memakai materialized balance:

- ledger tetap sumber kebenaran;
- current quantity tidak diedit langsung;
- sediakan reconciliation command/report;
- test mismatch detection;
- update memakai row lock.

---

# 9. TAHAP G — STOCK OPERATIONS

Implementasikan Action/Service terpisah:

- `ReceiveMedicineStockAction`;
- `AdjustMedicineStockAction`;
- `ReverseStockMovementAction`;
- `TransferMedicineStockAction` hanya jika multi-location disetujui;
- `ReconcileStockBalanceAction` atau report.

## G.1 Receipt

Receipt wajib:

- medicine active;
- location active;
- batch valid;
- quantity positive;
- expiry/batch sesuai tracking;
- actor/time server;
- idempotency;
- transaction;
- ledger + balance;
- audit.

## G.2 Adjustment

Adjustment wajib:

- permission khusus;
- reason;
- quantity;
- target batch;
- no negative result;
- audit;
- immutable movement.

## G.3 Transfer

Jika multi-location:

- transfer out dan transfer in dalam transaction yang sama;
- reference/correlation sama;
- source tidak negatif;
- destination batch policy jelas;
- rollback seluruhnya jika salah satu gagal.

Jika belum dibutuhkan, tunda dan tandai `[PERLU DIKONFIRMASI]`.

## G.4 Reversal

- permission khusus;
- reason;
- movement asal valid;
- belum pernah direverse;
- reversal tidak membuat stok negatif;
- movement asal tetap ada;
- audit.

---

# 10. STOCK ALERTS DAN REPORTS

Buat read-only operational views:

- current stock per medicine/location/batch;
- low stock;
- out of stock;
- near expiry;
- expired;
- quarantined/recalled;
- movement history;
- reconciliation status.

Aturan:

- low stock berdasarkan base unit dan configurable threshold;
- near expiry threshold configurable;
- expired/quarantined/recalled tidak dianggap available;
- report tidak mengubah stock;
- export ditunda atau dilindungi authorization dan audit;
- tidak ada patient data pada pharmacy report.

---

# 11. PERMISSION DAN POLICY

Permission minimum:

- `view-pharmacy-inventory`;
- `manage-medicine-master`;
- `receive-medicine-stock`;
- `adjust-medicine-stock`;
- `reverse-stock-movements`;
- `transfer-medicine-stock`;
- `view-stock-movements`;
- `view-stock-reconciliation`;
- `manage-stock-locations`.

Aturan:

1. admin teknis tidak otomatis memiliki pharmacy permissions;
2. role mapping mengikuti keputusan stakeholder;
3. direct URL Policy wajib;
4. UI hiding bukan authorization;
5. self-escalation dilarang;
6. reason wajib untuk high-risk operation;
7. sensitive cost data memiliki permission terpisah jika disimpan.

---

# 12. UI PHASE 2D1

Pertahankan tema biru muda, light/dark/system.

Buat:

## Medicine master

- list;
- search/filter;
- create/edit;
- deactivate;
- unit/tracking configuration.

## Stock dashboard

- stock summary;
- low stock;
- near expiry;
- expired/quarantined;
- per location.

## Batch detail

- batch information;
- current available;
- movement timeline;
- expiry status;
- adjustment/reversal actions sesuai permission.

## Stock receipt

- select medicine/location;
- batch;
- expiry;
- quantity;
- supplier/reference optional;
- idempotent submission;
- confirmation.

## Adjustment/reversal

- explicit reason;
- high-risk confirmation;
- before/after preview;
- no direct balance editing.

## UX requirements

- mobile-friendly;
- server-side pagination;
- accessible form/table/dialog;
- loading, empty, error, forbidden state;
- optimistic-lock conflict;
- duplicate submission protection;
- text+icon for expiry/stock status;
- screenshots light/dark desktop/mobile dengan data sintetis.

Jangan membuat patient medication screen.

---

# 13. DOMAIN EVENTS DAN AUDIT

Event minimum:

- `MedicineCreated`;
- `MedicineUpdated`;
- `MedicineDeactivated`;
- `StockLocationCreated`;
- `MedicineBatchCreated`;
- `MedicineBatchStatusChanged`;
- `MedicineStockReceived`;
- `MedicineStockAdjusted`;
- `StockMovementReversed`;
- `MedicineStockTransferred` jika diterapkan;
- `StockBalanceReconciled`;
- `StockBalanceMismatchDetected`.

Audit harus:

- append-only;
- actor/time server-side;
- before/after terpilih;
- reason;
- correlation ID;
- tidak menyimpan secret;
- tidak menulis success jika rollback;
- tidak mengandung data pasien.

---

# 14. TEST WAJIB

## Phase 2C closure

- active observation concurrency dengan MariaDB;
- monitoring final immutability;
- monitoring addendum chain;
- linked vital visit/patient consistency;
- due/overdue timezone;
- handover acknowledgement concurrency;
- responsibility transfer atomic;
- unassigned acknowledgement 403;
- observation completion rules;
- audit rollback behavior;
- Graphify update proof.

## Medicine master

- unique medicine code;
- inactive medicine tidak menerima transaksi baru;
- base unit validation;
- tracking configuration;
- unauthorized access 403;
- no hard delete with history.

## Batch

- unique batch policy;
- required expiry when configured;
- expired batch unavailable;
- quarantined/recalled unavailable;
- entered-in-error retains history;
- optimistic lock.

## Ledger

- positive quantity only;
- direction by movement type;
- movement immutable;
- no direct balance edit;
- no negative stock;
- idempotency;
- duplicate submission safe;
- reversal only once;
- failed transaction writes no movement/audit;
- concurrent adjustment uses MariaDB row lock;
- concurrent receipt safe;
- balance equals ledger.

## Transfer jika diterapkan

- atomic out/in;
- rollback both sides on failure;
- source no negative;
- correlation consistent.

## Authorization

- admin teknis 403 tanpa pharmacy permission;
- adjustment/reversal require specific permission;
- IDOR batch/location/movement rejected;
- mass assignment rejected;
- cost hidden without permission if applicable.

## Regression

- identity and Gate dry-run unchanged;
- patient health profile unchanged;
- visit/assessment unchanged;
- observation unchanged;
- no medication order/admin tables;
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

Concurrency test wajib menggunakan MariaDB/MySQL nyata. Laporkan skipped test dan alasannya.

---

# 15. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - Medicine -> Batch -> StockMovement;
   - stock source of truth;
   - direct current_quantity mutation paths;
   - negative stock prevention;
   - reversal path;
   - idempotency;
   - admin unauthorized path;
   - patient/visit coupling leakage;
   - medication order leakage;
   - requirements tanpa test;
   - hard delete path.

Perbarui:

- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`.

Laporkan node/edge hanya jika command benar-benar menghasilkannya.

---

# 16. DOKUMENTASI WAJIB

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-2C-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md`;
- `docs/02-workflows/MEDICATION-ADMINISTRATION.md` hanya bagian pharmacy foundation;
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`;
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/04-architecture/MODULE-BOUNDARIES.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/DATABASE-CONVENTIONS.md`;
- `docs/05-data/STATE-MACHINES.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Buat ADR untuk:

- stock ledger source of truth;
- balance materialization/reconciliation;
- unit conversion;
- batch uniqueness;
- stock transfer;
- expiry/availability rules.

---

# 17. OUTPUT AKHIR

Berikan:

1. Phase 2C closure status.
2. Hardening findings dan perbaikan.
3. Schema/migration baru.
4. Medicine master architecture.
5. Batch dan expiry architecture.
6. Stock ledger dan balance strategy.
7. Receipt, adjustment, reversal, transfer behavior.
8. Concurrency dan idempotency strategy.
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

- Phase 2C closure gagal;
- active observation atau handover concurrency belum aman;
- stock dapat menjadi negatif;
- ledger dapat diedit/dihapus;
- balance dapat diubah tanpa movement;
- reversal tidak aman;
- idempotency gagal;
- authorization/IDOR gagal;
- migration merusak data;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **Medicine Master + Batch + Stock Ledger + Inventory Operations**;
- jangan membuat medication order;
- jangan membuat prescription;
- jangan membuat medication administration;
- jangan mengurangi stok karena pasien;
- jangan membuat external consultation;
- jangan membuat referral;
- jangan membuat discharge final;
- tunggu persetujuan eksplisit pengguna.
