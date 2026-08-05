# PROMPT ANTIGRAVITY — PHASE 2C
## Phase 2B Closure Hardening, POSKESTREN Observation, Periodic Monitoring, and Shift Handover

Anda adalah principal Laravel architect, health information system engineer, application security engineer, database concurrency reviewer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini:

1. memverifikasi dan mengeraskan hasil Phase 2B;
2. membangun episode observasi santri/pasien di POSKESTREN;
3. membangun monitoring berkala;
4. membangun handover antarpetugas/shift;
5. membangun outcome dan rekomendasi setelah observasi;
6. mempertahankan authorization, immutability, audit, dan concurrency safety;
7. berhenti sebelum farmasi, pemberian obat, konsultasi eksternal, rujukan aktual, dan discharge final.

Jangan menggabungkan modul observasi dengan farmasi atau medication.

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
9. `docs/01-domain/PATIENT-JOURNEY.md`
10. `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`
11. `docs/01-domain/MEDICAL-TERMINOLOGY.md`
12. `docs/02-workflows/INITIAL-ASSESSMENT.md`
13. `docs/02-workflows/OBSERVATION-AND-CARE.md`
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
31. `docs/10-delivery/PHASE-2A-CLOSURE.md`
32. `docs/10-delivery/READINESS-REVIEW.md`
33. `plans/KNOWN-ISSUES.md`
34. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika `docs/10-delivery/PHASE-2B-CLOSURE.md` belum tersedia, buat pada tahap closure.

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang SOP, interval monitoring klinis, diagnosis, dosis, kewenangan tenaga kesehatan, atau kriteria rujukan.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data pasien nyata.
4. Jangan membuat diagnosis otomatis atau clinical decision support otomatis.
5. Jangan membuat medication order, medication administration, master obat, batch, atau stok.
6. Jangan membuat konsultasi Puskesmas/rumah sakit.
7. Jangan membuat referral aktual atau discharge final.
8. Jangan menyebut observasi POSKESTREN sebagai rawat inap rumah sakit.
9. Jangan menerima actor, timestamp resmi, state, atau outcome final dari payload tanpa validasi server.
10. Semua mutation observasi wajib memiliki authorization dan audit.
11. Catatan final tidak boleh diedit langsung.
12. Koreksi memakai addendum, versioning, cancellation, atau `entered_in_error`.
13. Gunakan transaction untuk operasi multi-tabel.
14. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 2B CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-2B-CLOSURE.md`

## A.1 Verifikasi concurrency Phase 2A

Walkthrough sebelumnya belum cukup sebagai bukti concurrency.

Verifikasi dan dokumentasikan hasil aktual:

- active visit guard mengunci baris `patients` yang selalu ada;
- check dan insert visit berada pada transaction yang sama;
- nomor `VIS-YYYYMMDD-XXXXX` aman terhadap request bersamaan;
- tidak memakai `MAX()+1` tanpa lock;
- concurrency test memakai MariaDB/MySQL nyata, bukan SQLite in-memory;
- retry unique collision tersedia jika strategi memerlukannya;
- test membuktikan hanya satu visit aktif dan nomor tidak duplikat.

Jika tidak terbukti aman, perbaiki sebelum melanjutkan.

## A.2 Allergy hardening

Verifikasi:

- `clinical_status` terpisah dari `verification_status`;
- `recorded_at` dan `verified_at` tersedia;
- `recorded_by_id` dan `verified_by_id` benar;
- nilai lama bermigrasi aman;
- `entered_in_error` tidak menghapus record;
- alergi aktif yang tampil hanya memakai kombinasi status yang benar.

## A.3 Vital signs hardening

Verifikasi:

- tekanan darah tidak disimpan sebagai string;
- actor dan `recorded_at` berasal dari server;
- outlier acknowledgement memiliki field/alasan yang dapat diaudit;
- finalized record immutable;
- koreksi mempertahankan record asal;
- tidak ada diagnosis otomatis dari nilai vital;
- range quality-control dapat dikonfigurasi atau didokumentasikan.

## A.4 Clinical assessment/versioning

Verifikasi:

- draft dan finalized berbeda jelas;
- finalized assessment tidak dapat diedit langsung;
- `parent_assessment_id` benar-benar menghasilkan chain addendum/version;
- assessment lama tetap tersedia;
- optimistic locking mencegah lost update;
- working diagnosis hanya dapat diisi permission yang sesuai;
- disposition memiliki alasan, actor, dan server timestamp;
- `assessment_completed` hanya tercapai setelah assessment final.

## A.5 Initial action guard

Verifikasi:

- tidak ada field obat atau dosis;
- payload medication-like ditolak;
- cancellation/entered-in-error tidak hard delete;
- action type tidak dianggap SOP atau rekomendasi otomatis;
- action final memiliki attribution dan audit.

## A.6 Audit and Graphify

Verifikasi:

- event Phase 2B tercatat;
- transaksi gagal tidak menghasilkan success audit;
- route update/delete final record ditolak;
- Graphify telah diperbarui;
- exclusions dependency/generated files masih berlaku;
- query dan hasil aktual dicatat.

## A.7 Closure status

Gunakan:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika concurrency, immutability, authorization, atau data migration memiliki temuan Critical, berhenti.

---

# 4. TAHAP B — OBSERVATION DOMAIN MODEL

Implementasikan modul `Observations` sebagai boundary tersendiri.

## B.1 Observation episode

Buat `observation_episodes` dengan ULID dan field minimum:

- `medical_visit_id`;
- `reason`;
- `status`;
- `started_at`;
- `started_by_id`;
- `responsible_officer_id`;
- `location_label` nullable;
- `bed_label` nullable;
- `monitoring_interval_minutes` nullable;
- `next_monitoring_due_at` nullable;
- `ended_at` nullable;
- `ended_by_id` nullable;
- `outcome` nullable;
- `outcome_reason` nullable;
- `lock_version`;
- timestamps.

Status:

- `planned`;
- `active`;
- `completed`;
- `transferred`;
- `cancelled`;
- `entered_in_error`.

Aturan:

1. satu visit maksimal satu observation episode aktif;
2. observation hanya dapat dimulai dari visit `assessment_completed`;
3. assessment final harus memiliki rekomendasi `observation_required` atau override berizin dengan alasan;
4. `started_at`, actor, dan state ditentukan server;
5. cancellation bukan hard delete;
6. completion memerlukan outcome;
7. status final immutable melalui edit biasa;
8. tidak ada medication field.

## B.2 Lokasi dan bed

Pada fase ini cukup gunakan label lokasi/bed yang terkendali atau master sederhana bila memang diperlukan.

Jangan membangun bed-capacity management penuh kecuali requirement sudah disetujui.

Dokumentasikan bahwa:

- POSKESTREN observation bukan rawat inap rumah sakit;
- lokasi membantu keberadaan pasien dan handover;
- data lokasi tidak boleh digunakan sebagai substitute monitoring.

## B.3 Active observation concurrency

Implementasikan guard yang aman terhadap request bersamaan:

- lock `medical_visits` atau parent row yang selalu ada;
- check active observation dan create di transaction yang sama;
- integration concurrency test dengan MariaDB;
- database constraint tambahan bila memungkinkan;
- override tidak tersedia kecuali ada kebutuhan domain yang disetujui.

---

# 5. TAHAP C — PERIODIC OBSERVATION MONITORING

Buat `observation_records` atau nama domain yang konsisten.

## C.1 Schema

Field minimum:

- ULID;
- `observation_episode_id`;
- `recorded_at` server-side;
- `recorded_by_id`;
- `condition_summary`;
- `symptom_changes` nullable;
- `general_condition` nullable;
- `vital_sign_id` nullable;
- `fluid_intake_note` nullable;
- `food_intake_note` nullable;
- `elimination_note` nullable;
- `activity_or_rest_note` nullable;
- `follow_up_note` nullable;
- `status` `draft|finalized|entered_in_error`;
- `finalized_at` nullable;
- `finalized_by_id` nullable;
- `parent_record_id` nullable untuk addendum;
- `lock_version`;
- timestamps.

Jangan mencatat medication pada tabel ini.

## C.2 Vital signs reuse

Jika monitoring memerlukan tanda vital:

- gunakan `vital_signs` yang sudah ada;
- kaitkan dengan observation episode/record melalui relasi yang jelas;
- jangan menggandakan kolom vital pada observation record;
- validasi visit/patient harus sama;
- vital sign final tetap immutable.

## C.3 Monitoring due

`monitoring_interval_minutes` dan `next_monitoring_due_at` hanya alat operasional.

Aturan:

- interval ditentukan petugas berwenang/SOP, bukan AI;
- sistem boleh menandai `due` atau `overdue`;
- sistem tidak menyatakan pasien memburuk hanya karena overdue;
- perubahan interval diaudit;
- overdue tidak otomatis membuat rujukan;
- timezone `Asia/Jakarta`.

## C.4 Finalization/versioning

- draft dapat diedit secara terkontrol;
- finalized immutable;
- koreksi memakai addendum atau `entered_in_error`;
- original record tetap ada;
- actor dan input time aktual selalu tercatat;
- backdated clinical time hanya dengan permission dan alasan bila didukung.

---

# 6. TAHAP D — SHIFT HANDOVER

Buat struktur handover yang eksplisit.

## D.1 Observation handover

Buat `observation_handovers`:

- ULID;
- observation episode;
- `from_user_id`;
- `to_user_id` nullable sampai diterima;
- `prepared_at`;
- `summary`;
- `current_condition`;
- `pending_tasks`;
- `risks_or_warnings`;
- `next_monitoring_due_at` nullable;
- `status` `draft|submitted|acknowledged|cancelled|entered_in_error`;
- `submitted_at`;
- `acknowledged_at`;
- `acknowledged_by_id`;
- version/lock;
- timestamps.

## D.2 Rules

- hanya observation aktif yang dapat di-handover;
- handover disusun oleh petugas aktif;
- acknowledgement dilakukan oleh penerima;
- satu petugas tidak boleh acknowledge sebagai penerima jika tidak ditugaskan, kecuali override berizin;
- handover tidak menggantikan rekam monitoring;
- pending tasks bukan medication order;
- final handover immutable;
- pembatalan memiliki alasan dan audit;
- informasi minimum necessary.

## D.3 Responsibility transfer

Setelah handover acknowledged:

- `responsible_officer_id` dapat berubah secara atomik;
- perubahan tanggung jawab diaudit;
- failed acknowledgement tidak mengubah responsible officer;
- race condition acknowledgement diuji.

---

# 7. TAHAP E — OBSERVATION OUTCOME AND VISIT STATE

## E.1 Outcome recommendation

Gunakan enum terkontrol:

- `continue_observation`;
- `return_to_activity_recommended`;
- `rest_recommended`;
- `external_consultation_recommended`;
- `referral_recommended`;
- `emergency_referral_recommended`;
- `follow_up_recommended`;
- `other`.

Ini adalah outcome/rekomendasi observasi, bukan eksekusi downstream.

## E.2 Completion rules

Observation dapat `completed` jika:

- memiliki minimal satu finalized observation record atau alasan khusus berizin;
- outcome tersedia;
- outcome reason tersedia;
- actor dan server time tercatat;
- tidak ada draft handover penting yang belum diselesaikan, sesuai rule yang didokumentasikan;
- transaction dan audit sukses.

## E.3 Visit lifecycle Phase 2C

Tambahkan status:

- `under_observation`;
- `observation_completed`.

Transisi:

```text
assessment_completed
  -> under_observation
  -> observation_completed
```

Alternatif terkontrol:

```text
planned observation
  -> cancelled
```

Aturan:

- jangan membuat status referred/discharged;
- `observation_completed` tidak otomatis menutup visit;
- downstream action diputuskan pada fase berikutnya;
- emergency outcome menampilkan prominent alert tetapi tidak otomatis membuat referral;
- seluruh transition server-side dan diaudit.

---

# 8. PERMISSION DAN POLICY

Permission minimum:

- `start-observations`;
- `view-observations`;
- `record-observation-monitoring`;
- `finalize-observation-monitoring`;
- `amend-observation-monitoring`;
- `prepare-observation-handover`;
- `acknowledge-observation-handover`;
- `complete-observations`;
- `cancel-observations`;
- `view-observation-audit`.

Aturan:

1. admin teknis tidak otomatis memiliki permission klinis;
2. pengasuh hanya boleh melihat status operasional jika requirement mengizinkan;
3. wali kelas tidak melihat detail monitoring;
4. direct URL dilindungi Policy;
5. UI hiding bukan authorization;
6. self-escalation dilarang;
7. role mapping tidak boleh dikarang jika kewenangan belum disetujui.

---

# 9. UI PHASE 2C

Pertahankan tema biru muda, light/dark/system.

Buat:

## Observation queue

- active observations;
- due/overdue monitoring;
- responsible officer;
- lokasi;
- elapsed time;
- warning alergi/kondisi penting;
- tanpa diagnosis sensitif pada overview umum.

## Observation workspace

- patient/visit header;
- assessment summary final;
- active allergy banner;
- observation reason;
- monitoring timeline;
- add monitoring record;
- linked vital signs;
- monitoring due indicator;
- handover;
- responsible officer;
- outcome/completion.

## Handover UI

- draft;
- submit;
- acknowledgement;
- responsibility change;
- pending tasks;
- warnings.

## UX requirements

- mobile-first;
- keyboard accessible;
- focus visible;
- loading, empty, error, forbidden state;
- optimistic lock conflict;
- unsaved draft warning;
- finalization confirmation;
- text+icon for status;
- screenshots light/dark desktop/mobile dengan data sintetis.

Jangan membuat halaman obat, farmasi, konsultasi, rujukan, atau discharge.

---

# 10. DOMAIN EVENTS DAN AUDIT

Event minimum:

- `ObservationPlanned`;
- `ObservationStarted`;
- `ObservationMonitoringRecorded`;
- `ObservationMonitoringFinalized`;
- `ObservationMonitoringAmended`;
- `ObservationMonitoringEnteredInError`;
- `ObservationHandoverPrepared`;
- `ObservationHandoverSubmitted`;
- `ObservationHandoverAcknowledged`;
- `ObservationResponsibilityTransferred`;
- `ObservationCompleted`;
- `ObservationCancelled`;
- `ObservationOutcomeRecommended`;
- `MedicalVisitEnteredObservation`;
- `MedicalVisitObservationCompleted`.

Audit harus:

- append-only;
- konsisten dengan transaction;
- actor dan server time;
- reason pada override/cancel/error;
- correlation ID;
- before/after terpilih;
- tidak mencatat success saat rollback;
- tidak menyimpan secret;
- tidak menyalin seluruh medical record tanpa kebutuhan.

---

# 11. TEST WAJIB

## Phase 2B closure

- active-visit concurrency dengan MariaDB;
- visit-number concurrency dengan MariaDB;
- allergy status migration;
- vital final immutability;
- assessment final immutability;
- addendum chain;
- working-diagnosis permission;
- outlier acknowledgement;
- non-medication action guard;
- audit rollback behavior;
- Graphify update proof.

## Observation episode

- hanya visit assessment_completed yang dapat mulai;
- recommendation observation atau override permission;
- satu active observation per visit;
- concurrency start observation;
- actor/time server-side;
- cancellation non-destructive;
- completed observation immutable;
- admin teknis 403;
- IDOR 403;
- mass assignment ditolak.

## Monitoring

- active observation required;
- at least condition summary;
- finalized immutable;
- addendum preserves original;
- vital sign patient/visit consistency;
- due/overdue calculation;
- no automatic clinical conclusion;
- backdate permission/reason jika didukung;
- XSS-safe output.

## Handover

- active observation required;
- submit and acknowledge authorization;
- unassigned user cannot acknowledge;
- acknowledgement atomically changes responsibility;
- concurrent acknowledgement safe;
- final handover immutable;
- cancellation non-destructive;
- pending task does not create medication order.

## Outcome/state machine

- completion requires outcome and reason;
- observation_completed does not close visit;
- emergency recommendation does not auto-create referral;
- invalid transition returns conflict;
- audit after commit only.

## Regression

- Gate dry-run non-mutative;
- identity boundaries unchanged;
- health profile authorization;
- assessment workflow unchanged;
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

Concurrency test wajib menggunakan MariaDB/MySQL nyata. Laporkan skipped test dan alasan.

---

# 12. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - Assessment -> ObservationEpisode;
   - ObservationEpisode -> ObservationRecord;
   - ObservationRecord -> VitalSigns;
   - Handover -> ResponsibilityTransfer;
   - observation final immutability;
   - server-authoritative actor/time;
   - overdue monitoring behavior;
   - medication leakage;
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

Laporkan node/edge aktual hanya jika command benar-benar menghasilkan nilai tersebut.

---

# 13. DOKUMENTASI WAJIB

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-2B-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md`;
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`;
- `docs/02-workflows/OBSERVATION-AND-CARE.md`;
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`;
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Buat ADR jika memilih strategi:

- active observation uniqueness;
- monitoring schedule;
- observation versioning;
- handover acknowledgement;
- responsibility transfer;
- observation completion rules.

---

# 14. OUTPUT AKHIR

Berikan:

1. Phase 2B closure status.
2. Hardening findings dan perbaikan.
3. Schema/migration baru.
4. Observation episode architecture.
5. Monitoring architecture.
6. Handover dan responsibility-transfer architecture.
7. Observation outcome dan visit state machine.
8. Permissions dan Policies.
9. Routes dan UI.
10. Audit events.
11. File dibuat/diubah.
12. Command dijalankan.
13. Test dan hasil aktual.
14. Concurrency test method dan hasil.
15. Graphify results dan query findings.
16. Screenshot light/dark desktop/mobile.
17. Risiko dan blocker.
18. Git diff summary.
19. Exact next recommended phase.

---

# 15. CHECKPOINT WAJIB

Berhenti jika:

- Phase 2B closure gagal;
- active visit atau visit number belum terbukti concurrency-safe;
- active observation guard tidak concurrency-safe;
- finalized monitoring/handover dapat diedit langsung;
- responsibility transfer tidak atomik;
- authorization/IDOR gagal;
- actor/timestamp dapat dimanipulasi client;
- migration merusak data;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **Observation Episode + Monitoring + Handover + Outcome Recommendation**;
- jangan membuat medication;
- jangan membuat pharmacy;
- jangan membuat external consultation;
- jangan membuat referral;
- jangan membuat discharge final;
- tunggu persetujuan eksplisit pengguna.
