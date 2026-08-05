# PROMPT ANTIGRAVITY — PHASE 2B
## Phase 2A Closure Hardening, Vital Signs, Clinical Assessment, Initial Actions, and Disposition Recommendation

Anda adalah principal Laravel architect, health information system engineer, application security engineer, database concurrency reviewer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini:

1. menutup dan mengeraskan hasil Phase 2A;
2. membangun pencatatan tanda vital yang terstruktur;
3. membangun assessment klinis draft/final;
4. membangun tindakan awal non-obat;
5. membangun rekomendasi disposisi;
6. menjaga versioning, audit, authorization, dan keselamatan data;
7. berhenti sebelum modul observasi penuh, medication, farmasi, konsultasi eksternal, rujukan, atau discharge final.

Jangan membangun seluruh modul medis dalam satu iterasi.

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
13. `docs/02-workflows/POSKESTREN-ADMISSION.md`
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
31. `docs/10-delivery/PHASE-1-CLOSURE.md`
32. `docs/10-delivery/READINESS-REVIEW.md`
33. `plans/KNOWN-ISSUES.md`
34. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang SOP klinis, nilai diagnosis, dosis, atau kewenangan tenaga kesehatan.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data pasien nyata.
4. Jangan membuat diagnosis otomatis, decision support otomatis, rekomendasi obat, atau perhitungan dosis.
5. Jangan membuat medication order/administration.
6. Jangan membuat observasi penuh, bed management, referral, remote consultation, atau discharge final.
7. Jangan menerima actor, timestamp resmi, status final, atau disposition final dari payload tanpa validasi server.
8. Semua perubahan medis wajib memiliki authorization dan audit.
9. Catatan final tidak boleh diedit langsung.
10. Koreksi final record harus memakai addendum/versioning.
11. Gunakan database transaction untuk operasi multi-tabel.
12. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 2A CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-2A-CLOSURE.md`

## A.1 Health profile schema hardening

Periksa schema aktual:

- `patient_health_profiles`;
- `patient_allergies`;
- `patient_medical_conditions`;
- `patient_emergency_contacts`;
- `medical_visits`.

Verifikasi dan perbaiki bila diperlukan:

### Patient health profile

- satu patient satu profile;
- `updated_by_id` memiliki FK yang benar;
- optimistic lock/version tersedia atau keputusan penundaan didokumentasikan;
- tidak ada cascade delete yang berisiko;
- `emergency_notes` memiliki batas panjang dan authorization ketat.

### Allergy

Pisahkan minimal:

- `clinical_status`: `active|inactive|resolved|entered_in_error`;
- `verification_status`: `unconfirmed|provisional|confirmed|refuted`;
- `severity`: nullable dan controlled;
- `reaction`;
- `recorded_by_id`;
- `verified_by_id`;
- `recorded_at`;
- `verified_at`;
- optional onset date;
- notes.

Jangan menggunakan satu kolom `status` untuk mencampur clinical status dan verification status.

Jika migration sebelumnya sudah memiliki data, buat data migration aman dan reversible bila memungkinkan.

### Medical condition

Periksa kebutuhan:

- clinical status;
- verification status;
- onset date nullable;
- recorded by;
- verified by nullable;
- recorded/verified timestamp;
- entered-in-error tanpa hard delete.

### Emergency contact

Periksa:

- `recorded_by_id`;
- source ownership `gate|local`;
- field authoritative Gate tidak dapat diedit lokal;
- phone normalization;
- priority constraint;
- active status;
- audit.

## A.2 Medical visit schema completeness

Verifikasi field berikut benar-benar tersedia atau dokumentasikan penundaan:

- `visit_number`;
- `patient_id`;
- `status`;
- `arrived_at`;
- `chief_complaint`;
- `reporting_type`;
- `reporting_name` nullable;
- `origin_location` nullable;
- `receiving_officer_id`;
- `assigned_officer_id` nullable;
- `created_by_id`;
- `cancellation_reason`;
- `cancelled_by_id`;
- `cancelled_at`;
- `lock_version`;
- timestamps.

Actor dan server time tidak boleh berasal dari payload.

## A.3 Active visit concurrency proof

Periksa implementasi `lockForUpdate()`.

Aturan wajib:

1. lock baris `patients` yang selalu ada;
2. lakukan active-visit check di transaction yang sama;
3. buat visit di transaction yang sama;
4. jangan hanya mengunci query active visit yang hasilnya bisa kosong;
5. lakukan integration/concurrency test menggunakan database yang mendukung row lock;
6. dokumentasikan behavior MariaDB;
7. jika memakai generated column/unique index atau lock table, buat ADR.

Jangan menyatakan race-safe hanya berdasarkan unit test biasa.

## A.4 Visit number concurrency

Format `VIS-YYYYMMDD-XXXXX` harus:

- dibuat server-side;
- unik;
- aman terhadap concurrent request;
- tidak menghitung `MAX()+1` tanpa lock;
- memiliki retry untuk unique collision;
- memiliki test concurrency;
- memiliki keputusan reset harian yang terdokumentasi.

Jika implementasi belum aman, gunakan sequence table/date counter dengan row lock atau strategi lain yang terdokumentasi.

## A.5 Authorization and audit

Verifikasi:

- admin teknis tidak otomatis melihat health profile;
- direct URL profile dan visit dilindungi Policy;
- cancellation dan override memiliki permission terpisah;
- audit ditulis setelah transaksi sukses;
- operasi gagal tidak membuat success audit;
- tidak ada update/delete route untuk finalized audit;
- notes dan chief complaint aman dari stored XSS pada output.

## A.6 Closure result

Status:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika ada Critical issue pada concurrency, authorization, atau data migration, berhenti sebelum Phase 2B.

---

# 4. TAHAP B — VITAL SIGNS FOUNDATION

Implementasikan `VitalSigns` sebagai data terstruktur dan berulang untuk satu visit.

## B.1 Schema

Buat tabel `vital_signs` dengan ULID dan field minimum:

- `medical_visit_id`;
- `recorded_at` server-side;
- `recorded_by_id`;
- `temperature_c` decimal nullable;
- `systolic_bp` small integer nullable;
- `diastolic_bp` small integer nullable;
- `pulse_bpm` small integer nullable;
- `respiratory_rate` small integer nullable;
- `spo2_percent` decimal/small integer nullable;
- `weight_kg` decimal nullable;
- `height_cm` decimal nullable;
- `pain_score` nullable jika disetujui dokumentasi;
- `consciousness_level` nullable jika disetujui dokumentasi;
- `notes` nullable;
- `status` `draft|finalized|entered_in_error`;
- `finalized_at` nullable;
- `finalized_by_id` nullable;
- `version` atau lock;
- timestamps.

Jangan menyimpan tekanan darah sebagai string gabungan.

## B.2 Validation

- unit jelas;
- precision benar;
- setidaknya satu measurement tersedia;
- outlier tidak otomatis ditolak jika masih mungkin secara klinis;
- outlier meminta acknowledgement dan alasan sesuai requirement;
- aplikasi tidak menyatakan diagnosis berdasarkan nilai;
- gunakan configurable validation range sebagai quality control, bukan clinical decision support;
- nilai negatif dan format mustahil ditolak;
- actor dan recorded_at resmi dari server;
- backdated entry hanya bila permission dan alasan tersedia, dengan actual input time tetap tercatat.

## B.3 Finalization

- draft dapat diedit oleh author/role yang diizinkan;
- finalized tidak dapat diedit langsung;
- koreksi menggunakan new record atau addendum/entered-in-error;
- record asli tetap ada;
- finalization diaudit.

---

# 5. TAHAP C — CLINICAL ASSESSMENT

Bangun assessment tanpa membuat diagnosis otomatis.

## C.1 Schema

Buat tabel `clinical_assessments` atau nama domain yang konsisten:

- ULID;
- `medical_visit_id`;
- author;
- assigned/authorizing clinician bila diperlukan;
- history/current illness text;
- relevant history text nullable;
- examination findings text;
- assessment text;
- working diagnosis text/code nullable hanya untuk role berwenang;
- status `draft|finalized|amended|entered_in_error`;
- disposition recommendation;
- finalization timestamps;
- version;
- lock;
- timestamps.

Jika diagnosis belum memiliki governance dan coding system yang final:

- jangan membuat diagnosis master yang seolah final;
- gunakan field assessment/working impression yang jelas;
- tandai terminologi/coding diagnosis `[PERLU DIKONFIRMASI]`.

## C.2 One or multiple assessments

Tentukan dan dokumentasikan:

- apakah satu visit memiliki satu assessment utama dengan revisions; atau
- beberapa assessment per episode/shift.

Pilih desain yang mendukung handover dan addendum tanpa menghapus catatan asli.

## C.3 Draft and finalization

- draft dapat autosave jika aman;
- finalization memerlukan permission;
- final record immutable;
- addendum terhubung ke record asal;
- version history dapat ditelusuri;
- optimistic locking mencegah lost update;
- finalization menghasilkan domain event dan audit.

## C.4 Role boundary

Permission minimum:

- `record-vital-signs`;
- `finalize-vital-signs`;
- `create-clinical-assessments`;
- `finalize-clinical-assessments`;
- `amend-clinical-assessments`;
- `record-working-diagnosis`;
- `record-initial-actions`;
- `recommend-visit-disposition`.

Jangan menyimpulkan bahwa semua petugas kesehatan memiliki semua permission. Mapping role final tetap mengikuti kewenangan yang disetujui.

---

# 6. TAHAP D — INITIAL ACTIONS NON-MEDICATION

Bangun pencatatan tindakan awal yang tidak mencakup pemberian obat.

## D.1 Schema

Buat `clinical_actions` atau nama yang konsisten:

- visit;
- assessment nullable/required sesuai keputusan;
- action type;
- description;
- performed_at server-side;
- performed_by;
- status `performed|cancelled|entered_in_error`;
- notes;
- cancellation/entered-in-error reason;
- timestamps.

## D.2 Scope tindakan

Contoh kategori hanya sebagai struktur, bukan SOP:

- first aid;
- wound care;
- hydration/supportive care;
- rest recommendation;
- monitoring instruction;
- non-medication procedure;
- other.

Jangan menyediakan daftar tindakan medis yang mengarahkan petugas tanpa SOP yang disetujui.

## D.3 Safety

- tidak ada medication field;
- tidak ada dosage;
- tindakan tidak boleh di-hard-delete;
- cancellation atau entered-in-error diaudit;
- UI menampilkan actor dan time.

---

# 7. TAHAP E — DISPOSITION RECOMMENDATION

Bangun rekomendasi disposisi, bukan eksekusi modul downstream.

## E.1 Types

Gunakan enum terkontrol:

- `return_to_activity`;
- `rest_at_poskestren`;
- `observation_required`;
- `external_consultation_required`;
- `referral_required`;
- `emergency_referral_required`;
- `follow_up_required`;
- `other`.

Tandai jenis final sesuai dokumentasi dan SOP.

## E.2 Rules

- recommendation dibuat oleh petugas berwenang;
- memiliki reason;
- terkait assessment final;
- server timestamp;
- dapat direvisi hanya melalui addendum/versioning;
- emergency recommendation menghasilkan prominent alert;
- tidak otomatis membuat referral/observation/consultation pada fase ini;
- downstream state ditunda ke Phase berikutnya;
- visit berubah menjadi status `assessment_completed` setelah assessment final dan recommendation tersimpan.

## E.3 Visit lifecycle Phase 2B

Tambahkan status:

- `under_assessment`;
- `assessment_completed`.

Transisi yang diizinkan:

```text
registered
  -> waiting_assessment
  -> under_assessment
  -> assessment_completed
```

Alternatif:

```text
registered|waiting_assessment|under_assessment
  -> cancelled
```

Aturan:

- `assessment_completed` membutuhkan assessment finalized;
- cancellation setelah assessment final harus ditunda atau menggunakan void workflow khusus;
- jangan membuat status observasi, referred, atau discharged pada fase ini;
- seluruh transition server-side dan diaudit.

---

# 8. UI PHASE 2B

Pertahankan tema biru muda, light/dark/system.

Buat workspace assessment untuk petugas berwenang:

## Header pasien

- identitas minimum;
- patient number;
- active allergy warning;
- kondisi penting;
- visit number;
- arrival time;
- status;
- assigned officer.

## Sections

1. Intake summary.
2. Vital signs timeline.
3. Assessment draft/final.
4. Initial actions.
5. Disposition recommendation.
6. Audit/timeline terbatas.

## UX

- mobile-first;
- autosave draft bila aman;
- unsaved changes warning;
- optimistic lock conflict message;
- finalization confirmation;
- entered-in-error/addendum flow;
- loading, empty, error, forbidden state;
- no diagnosis auto-suggestion;
- warning color disertai ikon dan teks;
- screenshots light/dark desktop/mobile dengan data sintetis.

Jangan membuat medication, observation, consultation, referral, atau discharge screen.

---

# 9. DOMAIN EVENTS DAN AUDIT

Event minimum:

- `VitalSignsRecorded`;
- `VitalSignsFinalized`;
- `VitalSignsEnteredInError`;
- `ClinicalAssessmentStarted`;
- `ClinicalAssessmentFinalized`;
- `ClinicalAssessmentAmended`;
- `ClinicalActionRecorded`;
- `ClinicalActionCancelled`;
- `DispositionRecommended`;
- `MedicalVisitEnteredAssessment`;
- `MedicalVisitAssessmentCompleted`.

Audit harus:

- append-only;
- ditulis konsisten dengan transaction;
- menyimpan actor, subject, before/after yang relevan, reason, correlation ID;
- tidak menyimpan secret;
- tidak menyalin seluruh record secara buta;
- tidak mencatat success event saat transaksi rollback.

---

# 10. TEST WAJIB

## Phase 2A closure

- allergy clinical status terpisah dari verification status;
- health fields tidak kembali ke `patients`;
- emergency-contact source ownership;
- active-visit concurrency proof;
- visit-number concurrency proof;
- direct URL authorization;
- failed operation tidak menulis success audit.

## Vital signs

- setidaknya satu measurement;
- unit/precision validation;
- impossible negative value ditolak;
- server-side actor/time;
- outlier acknowledgement;
- draft edit allowed;
- finalized immutable;
- entered-in-error preserves original;
- unauthorized access 403;
- IDOR ditolak;
- mass assignment ditolak.

## Assessment

- authorized draft creation;
- unauthorized finalization;
- finalized immutable;
- addendum preserves original;
- optimistic-lock conflict;
- working diagnosis permission;
- assessment completion requires finalized assessment;
- XSS-safe output;
- actor/time server-side.

## Actions

- non-medication action only;
- medication-like payload ditolak;
- cancellation non-destructive;
- entered-in-error audit.

## Disposition

- controlled enum;
- reason required;
- permission required;
- linked to finalized assessment;
- emergency alert;
- does not create downstream entity;
- visit transition valid;
- invalid transition returns conflict.

## Regression

- Gate dry-run remains non-mutative;
- Person/User/Patient unchanged;
- health profile authorization remains;
- theme light/dark/system;
- build and route security.

Jalankan:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
```

Untuk concurrency, gunakan integration test dengan database MariaDB/MySQL yang nyata, bukan SQLite in-memory.

Laporkan hasil aktual, termasuk skipped test dan alasannya.

---

# 11. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - MedicalVisit -> VitalSigns;
   - MedicalVisit -> ClinicalAssessment;
   - finalized assessment immutability;
   - addendum path;
   - server-authoritative actor/time;
   - disposition recommendation;
   - emergency recommendation;
   - medication leakage;
   - unauthorized admin path;
   - requirement tanpa test;
   - hard delete path.

Perbarui:

- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`.

---

# 12. DOKUMENTASI WAJIB

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-2A-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md`;
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`;
- `docs/02-workflows/INITIAL-ASSESSMENT.md`;
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

Buat ADR bila memilih strategi:

- assessment revision;
- active-visit uniqueness;
- visit-number sequence;
- vital-sign outlier handling;
- diagnosis coding;
- optimistic locking.

---

# 13. OUTPUT AKHIR

Berikan:

1. Phase 2A closure status.
2. Hardening findings dan perbaikan.
3. Schema/migration baru.
4. Vital-sign architecture.
5. Assessment/versioning architecture.
6. Initial-action architecture.
7. Disposition recommendation dan visit state machine.
8. Permissions dan Policies.
9. Routes dan UI.
10. Audit events.
11. File dibuat/diubah.
12. Command dijalankan.
13. Test dan hasil aktual.
14. Concurrency test method dan result.
15. Graphify result dan query findings.
16. Screenshot light/dark desktop/mobile.
17. Risiko dan blocker.
18. Git diff summary.
19. Exact next recommended phase.

---

# 14. CHECKPOINT WAJIB

Berhenti jika:

- Phase 2A closure gagal;
- allergy statuses masih tercampur;
- active-visit atau visit-number concurrency tidak terbukti aman;
- finalized clinical record dapat diedit langsung;
- authorization/IDOR gagal;
- actor/timestamp dapat dimanipulasi client;
- migration merusak data;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **Vital Signs + Clinical Assessment + Initial Actions + Disposition Recommendation**;
- jangan membuat medication;
- jangan membuat pharmacy;
- jangan membuat observation episode;
- jangan membuat remote consultation;
- jangan membuat referral;
- jangan membuat discharge final;
- tunggu persetujuan eksplisit pengguna.
