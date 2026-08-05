# PROMPT ANTIGRAVITY — PHASE 3B
## Phase 3A Closure Hardening, Actual Referral, Transport, Clinical Handover, and Return from Referral

Anda adalah principal Laravel architect, health information system engineer, clinical referral workflow architect, application security engineer, privacy engineer, database concurrency reviewer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini:

1. memverifikasi dan mengeraskan hasil Phase 3A;
2. membangun rujukan aktual dari POSKESTREN ke fasilitas kesehatan;
3. membangun dokumen rujukan yang versioned dan private;
4. membangun transportasi dan pendamping rujukan;
5. membangun pencatatan keberangkatan, serah terima, penerimaan fasilitas, dan status eksternal;
6. membangun alur kembali dari rujukan;
7. membangun ringkasan hasil eksternal dan keputusan tindak lanjut lokal;
8. menjaga emergency referral tidak bergantung pada konsultasi;
9. berhenti sebelum discharge final, notifikasi wali/kelas/asrama, integrasi Absensi, billing, klaim, dan laporan manajemen final.

Jangan membuat diagnosis otomatis, rekomendasi klinis otomatis, atau keputusan rujukan otomatis.

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
12. `docs/02-workflows/REMOTE-CLINICAL-CONSULTATION.md`
13. `docs/02-workflows/HOSPITAL-REFERRAL.md`
14. `docs/02-workflows/RETURN-FROM-REFERRAL.md`
15. `docs/02-workflows/EMERGENCY-HANDLING.md`
16. `docs/02-workflows/DISCHARGE-AND-RETURN.md`
17. `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
18. `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
19. `docs/03-requirements/TRACEABILITY-MATRIX.md`
20. `docs/04-architecture/MODULE-BOUNDARIES.md`
21. `docs/04-architecture/APPLICATION-LAYERS.md`
22. `docs/04-architecture/INTEGRATIONS.md`
23. `docs/05-data/DOMAIN-MODEL.md`
24. `docs/05-data/ENTITY-RELATIONSHIPS.md`
25. `docs/05-data/DATA-DICTIONARY.md`
26. `docs/05-data/STATE-MACHINES.md`
27. `docs/05-data/MEDICAL-RECORD-VERSIONING.md`
28. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
29. `docs/07-security/MEDICAL-DATA-PRIVACY.md`
30. `docs/07-security/AUDIT-LOG.md`
31. `docs/07-security/REMOTE-CONSULTATION-GOVERNANCE.md`
32. `docs/08-api/INTEGRATION-CONTRACTS.md`
33. `docs/09-testing/TEST-STRATEGY.md`
34. `docs/09-testing/BUSINESS-SCENARIOS.md`
35. `docs/09-testing/SECURITY-TESTS.md`
36. `docs/10-delivery/READINESS-REVIEW.md`
37. `plans/KNOWN-ISSUES.md`
38. `docs/11-decisions/ADR-007-REMOTE-CLINICAL-CONSULTATION.md`
39. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika `docs/10-delivery/PHASE-3A-CLOSURE.md` belum tersedia, buat pada tahap closure.

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang SOP rujukan, daftar red flag, mitra resmi, otorisasi wali, tarif, ambulans, atau kewenangan tenaga kesehatan.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data pasien nyata.
4. Jangan membuat keputusan rujukan otomatis.
5. Jangan mengarang kriteria klinis rujukan.
6. Jangan menunda emergency referral karena consultation belum selesai.
7. Jangan mensyaratkan external advice sebelum emergency referral.
8. Jangan membuat discharge final.
9. Jangan membuat billing, klaim, BPJS, invoice, atau pembayaran.
10. Jangan mengirim data nyata ke mitra eksternal.
11. Jangan membuat public document URL.
12. Jangan mengirim seluruh rekam medis.
13. Terapkan minimum necessary.
14. Jangan menerima actor, official timestamp, referral state, destination, atau acceptance status langsung dari payload tanpa authorization dan validasi server.
15. Dokumen final tidak boleh diedit langsung.
16. Koreksi memakai versioning, addendum, cancellation, superseding, atau entered-in-error.
17. Semua transition dan operasi multi-tabel memakai transaction.
18. Semua export, download, handoff, dan perubahan state wajib diaudit.
19. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 3A CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-3A-CLOSURE.md`

## A.1 Healthcare partner hardening

Verifikasi:

- `referral_enabled` tersedia dan terpisah dari `consultation_enabled`;
- partner active;
- contact active;
- contact verification status;
- `verified_by_id`;
- partner/contact yang pernah digunakan tidak dapat hard delete;
- official contact tidak dapat diganti tanpa audit;
- admin teknis tidak otomatis dapat membaca isi konsultasi.

Jika `referral_enabled` belum ada, buat migration aman.

## A.2 Consultation aggregate completeness

Verifikasi field/status aktual:

- draft;
- ready;
- sent;
- acknowledged;
- responded;
- completed;
- cancelled;
- expired;
- superseded;
- superseded_by_referral;
- entered_in_error.

Verifikasi attribution:

- created/finalized/sent/completed/cancelled by;
- corresponding server timestamps;
- cancellation reason;
- lock version;
- clinical assessment final;
- partner/recipient valid;
- emergency guard.

## A.3 Versioned summary proof

Buktikan:

- summary final immutable;
- revision menghasilkan version baru;
- version lama tidak berubah ketika source record berubah;
- checksum diverifikasi;
- unique consultation + version;
- minimum-necessary selection;
- summary tidak memasukkan audit log, role, secret, atau data pasien lain;
- document private;
- file tidak berada pada public disk;
- download diaudit;
- PDF/print selalu light.

## A.4 Transmission proof

Verifikasi:

- default hanya fake/manual secure handoff;
- production transport nonaktif tanpa konfigurasi eksplisit;
- only finalized version can send;
- recipient dan channel tervalidasi;
- database idempotency;
- retry traceable;
- failure tidak menyelesaikan consultation;
- failure logs tersanitasi;
- file created tidak dianggap delivered;
- no public permanent URL;
- recipient substitution ditolak.

## A.5 External advice and local decision

Verifikasi:

- external advice memiliki facility/clinician attribution;
- missing attribution menjadi unverified;
- finalized advice immutable;
- amendment mempertahankan original;
- advice tidak otomatis mengubah assessment/diagnosis;
- local decision terpisah;
- local decision membutuhkan permission dan rationale;
- local decision final immutable;
- referral recommendation belum membuat referral entity;
- emergency decision tidak tertahan consultation.

## A.6 Authorization, audit, Graphify, tests

Verifikasi:

- direct URL/IDOR;
- partner management permission terpisah dari consultation access;
- download/transmission audit;
- rollback tidak menulis success audit;
- Graphify updated;
- exclusions aktif;
- query results dicatat;
- test Phase 3A memadai terhadap requirement, bukan hanya jumlah.

## A.7 Closure status

Gunakan:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika private storage, version immutability, recipient authorization, emergency guard, atau advice-decision separation memiliki temuan Critical, berhenti.

---

# 4. TAHAP B — REFERRAL DOMAIN BOUNDARY

Implementasikan modul `Referrals` terpisah tetapi terhubung ke:

- medical visit;
- finalized assessment;
- observation outcome nullable;
- consultation/local decision nullable;
- healthcare partner;
- referral document;
- transport;
- companion;
- handover;
- external status;
- return from referral.

Rujukan tidak harus memiliki consultation sebelumnya.

---

# 5. TAHAP C — REFERRAL AGGREGATE

## C.1 Schema

Buat `referrals` dengan ULID dan field minimum:

- `medical_visit_id`;
- `clinical_assessment_id`;
- `observation_episode_id` nullable;
- `clinical_consultation_id` nullable;
- `consultation_local_decision_id` nullable;
- `healthcare_partner_id`;
- `recipient_contact_id` nullable;
- `referral_number` unique server-generated;
- `urgency`;
- `reason`;
- `clinical_summary`;
- `requested_service_or_department` nullable;
- `status`;
- `initiated_by_id`;
- `initiated_at`;
- `approved_by_id` nullable;
- `approved_at` nullable;
- `ready_at` nullable;
- `departed_at` nullable;
- `arrived_at_destination` nullable;
- `accepted_at_destination` nullable;
- `returned_at` nullable;
- `completed_at` nullable;
- `cancelled_at` nullable;
- `cancelled_by_id` nullable;
- `cancellation_reason` nullable;
- `supersedes_referral_id` nullable;
- `lock_version`;
- timestamps.

## C.2 Status

Gunakan state terkontrol:

- `draft`;
- `prepared`;
- `approved`;
- `ready_to_depart`;
- `departed`;
- `arrived`;
- `accepted`;
- `under_external_care`;
- `return_planned`;
- `returned`;
- `completed`;
- `cancelled`;
- `declined_by_destination`;
- `superseded`;
- `entered_in_error`.

Jangan mengarang status acceptance jika fasilitas belum mengonfirmasi. Gunakan status aktual yang dapat dibuktikan.

## C.3 Urgency

Enum:

- `routine`;
- `urgent`;
- `emergency`.

Urgency dipilih petugas berwenang. Sistem tidak menentukan secara otomatis.

## C.4 Preconditions

Rujukan dapat dibuat jika:

- visit valid dan tidak cancelled;
- assessment final;
- partner active dan referral enabled;
- reason;
- urgency;
- actor berwenang.

Untuk emergency:

- approval tambahan tidak boleh menjadi blocker jika SOP mengizinkan emergency override;
- reason dan emergency authority dicatat;
- consultation tidak wajib;
- dokumen minimum dapat difinalisasi paralel tanpa menunda keberangkatan;
- audit.

## C.5 One active referral guard

Aturan:

- satu visit maksimal satu referral aktif kecuali superseding workflow;
- lock parent medical visit yang selalu ada;
- check dan create dalam transaction sama;
- MariaDB concurrency test;
- cancelled/completed/entered-in-error tidak dianggap active;
- superseding memerlukan permission dan reason.

## C.6 Referral number

Nomor referral:

- server-generated;
- unique;
- concurrency-safe;
- tidak memakai `MAX()+1` tanpa lock;
- dapat menggunakan sequence/date counter atau opaque ID;
- tidak memuat data sensitif;
- ADR dan test concurrency.

---

# 6. TAHAP D — VERSIONED REFERRAL DOCUMENT

## D.1 Referral versions

Buat `referral_versions`:

- referral;
- version number;
- summary payload JSON;
- document path nullable;
- checksum;
- authored by;
- finalized at;
- supersedes version nullable;
- minimum-necessary note;
- consent/authority reference nullable;
- timestamps.

## D.2 Content

Ringkasan referral dapat memilih:

- identitas minimum;
- referral number;
- facility destination;
- urgency;
- reason;
- chief complaint;
- chronology;
- final assessment;
- relevant vital signs;
- allergies;
- important conditions;
- relevant clinical actions;
- relevant medications administered;
- observation summary;
- consultation/advice summary jika relevan;
- requested service/department;
- current condition before departure;
- author/date.

Aturan:

- snapshot versioned;
- final immutable;
- revision creates new version;
- old version unchanged;
- private storage;
- checksum;
- print light;
- download audited;
- minimum necessary;
- no audit log or secret;
- no public URL.

## D.3 Emergency document behavior

Emergency departure tidak boleh tertahan hanya karena PDF belum selesai.

Sistem harus mendukung:

- minimum handoff summary;
- document finalization paralel sesuai SOP;
- timestamp aktual;
- audit emergency override.

Jangan mengarang SOP final.

---

# 7. TAHAP E — TRANSPORT AND COMPANION

## E.1 Referral transport

Buat `referral_transports`:

- referral;
- transport type;
- vehicle identifier nullable;
- driver name nullable;
- driver contact nullable;
- arranged by;
- arranged at;
- departure planned nullable;
- departure actual nullable;
- arrival actual nullable;
- status;
- notes;
- lock version;
- timestamps.

Transport type configurable:

- school vehicle;
- ambulance partner;
- external ambulance;
- private vehicle;
- other.

Gunakan synthetic data. Jangan membuat armada nyata tanpa stakeholder data.

Status:

- `planned`;
- `ready`;
- `departed`;
- `arrived`;
- `cancelled`;
- `entered_in_error`.

## E.2 Referral companions

Buat `referral_companions`:

- referral;
- person/user reference nullable;
- name snapshot;
- role/relationship;
- phone nullable;
- is_primary;
- assigned_by;
- assigned_at;
- acknowledgement nullable;
- status;
- timestamps.

Aturan:

- minimal satu pendamping jika kebijakan mewajibkan;
- primary companion unique per active referral;
- contact data minimum necessary;
- assignment dan change audited;
- bukan clinical authorization otomatis.

## E.3 Departure readiness

Referral `ready_to_depart` hanya jika requirement yang disetujui terpenuhi:

- destination;
- urgency/reason;
- current finalized referral version atau emergency minimum handoff;
- transport;
- companion jika required;
- responsible actor;
- required authority/consent reference.

Jangan mengarang field wajib yang belum disetujui; dokumentasikan.

---

# 8. TAHAP F — CLINICAL HANDOVER AND DESTINATION STATUS

## F.1 Referral handoff

Buat `referral_handovers`:

- referral;
- referral version;
- from user;
- destination partner;
- recipient contact nullable;
- channel/method;
- handed_over_at;
- acknowledged_at nullable;
- acknowledgement source;
- notes;
- status;
- idempotency key;
- correlation ID;
- timestamps.

Status:

- `prepared`;
- `handed_over`;
- `acknowledged`;
- `failed`;
- `cancelled`;
- `entered_in_error`.

## F.2 Rules

- only finalized/minimum emergency version;
- recipient substitution guarded;
- handoff does not equal acceptance;
- acknowledgement separate;
- idempotency;
- audit;
- no public document link;
- no secret in logs.

## F.3 Destination updates

Buat timeline/status event seperti `referral_status_events`:

- referral;
- event type;
- occurred_at;
- received_at;
- source;
- facility/contact attribution;
- notes;
- recorded by;
- external reference nullable;
- verification status;
- idempotency key;
- timestamps.

Event examples:

- arrived;
- accepted;
- declined;
- under_external_care;
- return_planned;
- returned.

Manual entry default. Public unauthenticated callback dilarang.

---

# 9. TAHAP G — RETURN FROM REFERRAL

## G.1 Return record

Buat `referral_returns`:

- referral unique;
- returned_at;
- recorded_by;
- return transport nullable;
- accompanied_by nullable;
- external outcome summary;
- external diagnosis text nullable;
- external procedures text nullable;
- external medication instructions text nullable;
- restrictions text nullable;
- follow_up_date nullable;
- follow_up_facility nullable;
- documents_received;
- source verification status;
- local review status;
- reviewed_by_id nullable;
- reviewed_at nullable;
- lock version;
- timestamps.

## G.2 External documents

Buat `referral_external_documents`:

- referral;
- return record nullable;
- document type;
- private path;
- checksum;
- issued by facility;
- issued at nullable;
- received at;
- recorded by;
- verification status;
- status;
- parent document nullable;
- timestamps.

Aturan:

- private;
- no public URL;
- malware/file-type/size validation;
- download audited;
- original retained;
- correction/versioning;
- no automatic parsing into local diagnosis/medication.

## G.3 Local review

External result tidak otomatis menjadi local assessment.

Buat `referral_return_reviews` atau struktur setara:

- referral return;
- local reviewer;
- review summary;
- follow-up recommendation;
- medication reconciliation note nullable;
- activity/rest recommendation;
- status;
- finalized at;
- parent review nullable;
- lock version;
- timestamps.

Decision types:

- `continue_poskestren_care`;
- `continue_observation`;
- `follow_up_external`;
- `rest_recommended`;
- `return_to_activity_recommended`;
- `new_referral_recommended`;
- `emergency_referral_required`;
- `other`.

Aturan:

- permission;
- rationale;
- final immutable;
- addendum;
- does not create discharge final;
- does not automatically create medication order;
- medication instruction from external requires local reconciliation in future/approved flow;
- audit.

---

# 10. VISIT AND CONSULTATION STATE INTEGRATION

## H.1 Visit states

Tambahkan bila diperlukan:

- `referral_prepared`;
- `referred_external`;
- `returned_from_referral`;
- `referral_review_completed`.

Transisi:

```text
assessment_completed|observation_completed|external_consultation_completed
    -> referral_prepared
    -> referred_external
    -> returned_from_referral
    -> referral_review_completed
```

Emergency dapat berjalan dari status klinis yang sah dengan override sesuai SOP.

Jangan menutup visit otomatis setelah referral review.

## H.2 Consultation integration

Saat referral actual dimulai:

- consultation pending dapat ditandai `superseded_by_referral`;
- consultation tidak harus dibatalkan;
- advice yang datang kemudian tetap dicatat dan diberi konteks;
- referral tidak menunggu consultation;
- audit.

## H.3 Observation and medication

- observation dapat ditransfer/completed sesuai workflow;
- medication schedule pending tidak boleh diam-diam dianggap administered;
- medication orders tidak otomatis discontinued;
- handover referral menyertakan medication administered/pending hanya jika relevan;
- no automatic stock change.

---

# 11. PERMISSION DAN POLICY

Permission minimum:

- `view-referrals`;
- `create-referrals`;
- `approve-referrals`;
- `prepare-referral-documents`;
- `finalize-referral-documents`;
- `download-referral-documents`;
- `arrange-referral-transport`;
- `assign-referral-companions`;
- `record-referral-departure`;
- `record-referral-arrival`;
- `record-referral-handover`;
- `record-destination-status`;
- `cancel-referrals`;
- `supersede-referrals`;
- `record-return-from-referral`;
- `upload-referral-external-documents`;
- `review-return-from-referral`;
- `view-referral-audit`.

Aturan:

1. admin teknis tidak otomatis memiliki clinical referral access;
2. create/approve/departure/return review dapat dipisah;
3. emergency override permission terpisah;
4. direct URL/IDOR Policy;
5. recipient/destination substitution dicegah;
6. document download audited;
7. companion contact minimum necessary;
8. self-escalation dilarang;
9. conflict-of-interest ditandai `[PERLU DIKONFIRMASI]`.

---

# 12. UI PHASE 3B

Pertahankan tema biru muda, light/dark/system.

## Referral queue

- draft/prepared;
- ready to depart;
- departed;
- arrived/accepted;
- under external care;
- return planned;
- returned;
- review pending;
- completed/cancelled/declined.

## Referral composer

- patient/visit header;
- allergy warning;
- current condition;
- reason/urgency;
- destination partner/contact;
- source assessment/observation/consultation;
- requested service;
- minimum necessary;
- preview/version finalize.

## Logistics

- transport;
- companion;
- readiness checklist;
- departure confirmation;
- actual server timestamp;
- emergency override reason.

## Handoff/status

- handoff document;
- recipient;
- acknowledgement;
- destination status timeline;
- decline reason;
- return plan.

## Return workspace

- external outcome;
- documents;
- external instructions;
- verification;
- local review;
- follow-up recommendation.

## UX requirements

- mobile-first;
- accessible;
- keyboard focus;
- loading/empty/error/forbidden;
- prominent emergency state;
- no color-only warning;
- optimistic lock conflict;
- version comparison;
- no public document URL;
- screenshots light/dark desktop/mobile using synthetic data.

Jangan membuat discharge final, billing, notification integration, or attendance integration.

---

# 13. DOMAIN EVENTS DAN AUDIT

Event minimum:

- `ReferralCreated`;
- `ReferralApproved`;
- `ReferralDocumentFinalized`;
- `ReferralDocumentRevised`;
- `ReferralTransportArranged`;
- `ReferralCompanionAssigned`;
- `ReferralReadyToDepart`;
- `ReferralDeparted`;
- `ReferralHandoffRecorded`;
- `ReferralHandoffAcknowledged`;
- `ReferralArrived`;
- `ReferralAccepted`;
- `ReferralDeclined`;
- `ReferralEnteredExternalCare`;
- `ReferralReturnPlanned`;
- `PatientReturnedFromReferral`;
- `ReferralExternalDocumentReceived`;
- `ReferralReturnReviewed`;
- `ReferralCompleted`;
- `ReferralCancelled`;
- `ReferralSuperseded`;
- `ClinicalConsultationSupersededByReferral`;
- `ReferralDocumentDownloaded`.

Audit harus:

- append-only;
- actor/time server-side;
- destination/recipient;
- reason;
- version/checksum;
- correlation ID;
- no secret;
- no full payload in technical logs;
- no success on rollback;
- download/handoff auditable;
- idempotent event handling.

---

# 14. TEST WAJIB

## Phase 3A closure

- partner referral-enabled separation;
- contact verification;
- summary immutable;
- old snapshot unchanged;
- checksum;
- private document;
- transmission idempotency;
- recipient substitution blocked;
- external advice attribution;
- local decision separation;
- emergency guard;
- unauthorized admin 403;
- Graphify proof.

## Referral creation

- finalized assessment required;
- partner active/referral enabled;
- consultation not required;
- reason/urgency required;
- actor/time server-side;
- one active referral guard;
- MariaDB concurrent creation;
- referral number concurrency;
- no auto-clinical decision;
- direct URL/IDOR 403.

## Emergency referral

- consultation pending does not block;
- approval workflow does not block when emergency override authorized;
- minimum handoff supported;
- override reason/audit;
- no invented red-flag automation.

## Referral document

- final immutable;
- revision creates version;
- old snapshot unchanged;
- checksum;
- private path;
- download audit;
- no unrelated patient data;
- no public URL.

## Transport/companion

- readiness conditions;
- unique primary companion;
- departure server timestamp;
- unauthorized changes 403;
- non-destructive cancellation;
- optimistic lock.

## Handoff/status

- only valid version;
- idempotent handoff;
- acknowledgement distinct from handoff;
- recipient substitution rejected;
- arrival/acceptance attribution;
- manual status verification;
- callback authenticated if implemented.

## Return

- only departed/accepted referral can return;
- return recorded once;
- external documents private;
- file validation;
- external diagnosis/instruction does not auto-update local records;
- local review permission;
- final review immutable;
- no medication order auto-created;
- no discharge created;
- new referral recommendation does not auto-create referral.

## State machine

- valid transitions;
- invalid transitions conflict;
- consultation superseded behavior;
- observation integration;
- medication pending does not reduce stock/change status;
- visit remains open after review.

## Regression

- identity/Gate unchanged;
- health profile unchanged;
- assessment/observation unchanged;
- pharmacy/medication unchanged;
- consultation history unchanged;
- no discharge/billing/attendance integration;
- theme light/dark/system;
- route security.

Run:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
```

Concurrency tests wajib memakai MariaDB/MySQL nyata. Laporkan skipped tests dan alasannya.

---

# 15. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - Assessment/Observation/Consultation -> Referral;
   - Referral -> Version -> Transport -> Handoff;
   - Referral -> Return -> LocalReview;
   - emergency referral bypass;
   - one active referral guard;
   - referral number concurrency;
   - document public exposure;
   - external diagnosis auto-update leakage;
   - medication auto-create leakage;
   - discharge creation leakage;
   - unauthorized admin path;
   - requirements without tests;
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
- `docs/10-delivery/PHASE-3A-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md`;
- `docs/01-domain/PATIENT-JOURNEY.md`;
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`;
- `docs/01-domain/MEDICAL-TERMINOLOGY.md`;
- `docs/02-workflows/HOSPITAL-REFERRAL.md`;
- `docs/02-workflows/RETURN-FROM-REFERRAL.md`;
- `docs/02-workflows/EMERGENCY-HANDLING.md`;
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`;
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/04-architecture/MODULE-BOUNDARIES.md`;
- `docs/04-architecture/INTEGRATIONS.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`;
- `docs/07-security/AUDIT-LOG.md`;
- `docs/08-api/INTEGRATION-CONTRACTS.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Buat ADR untuk:

- referral state machine;
- emergency override;
- referral number generation;
- one-active-referral constraint;
- versioned referral document;
- transport/companion model;
- external status verification;
- return review separation.

---

# 17. OUTPUT AKHIR

Berikan:

1. Phase 3A closure status.
2. Hardening findings and fixes.
3. Schema/migrations.
4. Referral aggregate/state machine.
5. Emergency referral behavior.
6. Versioned referral document.
7. Transport and companion architecture.
8. Clinical handoff and destination status.
9. Return-from-referral architecture.
10. Local return review separation.
11. Permissions and Policies.
12. Routes and UI.
13. Audit events.
14. Files created/changed.
15. Commands executed.
16. Tests and actual results.
17. MariaDB concurrency method/result.
18. Graphify results and findings.
19. Screenshots light/dark desktop/mobile.
20. Risks and blockers.
21. Git diff summary.
22. Exact next recommended phase.

---

# 18. CHECKPOINT WAJIB

Berhenti jika:

- Phase 3A closure gagal;
- consultation summary/document tidak private;
- emergency referral dapat tertahan consultation;
- one active referral guard tidak concurrency-safe;
- referral number tidak concurrency-safe;
- document final dapat diedit langsung;
- destination/recipient dapat diganti tanpa authorization/audit;
- external result otomatis mengubah local diagnosis/medication;
- return review otomatis membuat discharge;
- authorization/IDOR gagal;
- actor/timestamp dapat dimanipulasi;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **Actual Referral + Transport + Handoff + Return from Referral + Local Review**;
- jangan membuat discharge final;
- jangan membuat guardian/class/dorm notifications;
- jangan membuat attendance integration;
- jangan membuat billing/claims;
- tunggu persetujuan eksplisit pengguna.
