# PROMPT ANTIGRAVITY — PHASE 3A
## Phase 2D2 Closure Hardening and External Clinical Consultation

Anda adalah principal Laravel architect, health information system engineer, medication safety reviewer, clinical data exchange architect, application security engineer, database concurrency reviewer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini:

1. memverifikasi dan mengeraskan hasil Phase 2D2;
2. membangun konsultasi klinis eksternal profesional-ke-profesional;
3. membangun ringkasan klinis terstruktur dan versioned;
4. membangun mitra fasilitas kesehatan dan penerima konsultasi;
5. membangun secure transmission abstraction;
6. membangun external clinical advice dengan attribution;
7. membangun local clinical decision setelah respons;
8. memastikan konsultasi tidak menunda emergency referral;
9. berhenti sebelum rujukan aktual, transportasi, kembali dari rujukan, discharge final, billing, dan klaim.

Fase ini tidak boleh membuat diagnosis otomatis, rekomendasi obat AI, atau keputusan klinis otomatis.

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
13. `docs/02-workflows/REMOTE-CLINICAL-CONSULTATION.md`
14. `docs/02-workflows/HOSPITAL-REFERRAL.md`
15. `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
16. `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
17. `docs/03-requirements/TRACEABILITY-MATRIX.md`
18. `docs/04-architecture/MODULE-BOUNDARIES.md`
19. `docs/04-architecture/APPLICATION-LAYERS.md`
20. `docs/04-architecture/INTEGRATIONS.md`
21. `docs/05-data/DOMAIN-MODEL.md`
22. `docs/05-data/ENTITY-RELATIONSHIPS.md`
23. `docs/05-data/DATA-DICTIONARY.md`
24. `docs/05-data/STATE-MACHINES.md`
25. `docs/05-data/MEDICAL-RECORD-VERSIONING.md`
26. `docs/07-security/MEDICAL-DATA-PRIVACY.md`
27. `docs/07-security/AUDIT-LOG.md`
28. `docs/07-security/REMOTE-CONSULTATION-GOVERNANCE.md`
29. `docs/08-api/INTEGRATION-CONTRACTS.md`
30. `docs/09-testing/TEST-STRATEGY.md`
31. `docs/09-testing/BUSINESS-SCENARIOS.md`
32. `docs/09-testing/SECURITY-TESTS.md`
33. `docs/10-delivery/READINESS-REVIEW.md`
34. `plans/KNOWN-ISSUES.md`
35. `docs/11-decisions/ADR-007-REMOTE-CLINICAL-CONSULTATION.md`
36. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika `docs/10-delivery/PHASE-2D2-CLOSURE.md` belum tersedia, buat pada tahap closure.

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang regulasi, SOP, consent, kewenangan tenaga kesehatan, mitra resmi, kanal resmi, atau SLA respons.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data pasien nyata.
4. Jangan membuat diagnosis otomatis.
5. Jangan membuat rekomendasi obat atau perhitungan dosis otomatis.
6. Jangan membuat rujukan aktual pada fase ini.
7. Jangan membuat transportasi, pendamping, biaya, atau dokumen rujukan final.
8. Jangan membuat discharge final.
9. Jangan mengirim data ke Puskesmas/rumah sakit nyata tanpa konfigurasi dan persetujuan eksplisit.
10. Default external channel harus fake/sandbox/local transport.
11. Jangan memakai WhatsApp pribadi, email pribadi, atau public link sebagai default.
12. Jangan mengirim seluruh rekam medis.
13. Terapkan minimum necessary.
14. Emergency referral tidak boleh tertahan oleh consultation.
15. External advice tidak boleh otomatis menjadi local diagnosis atau local decision.
16. Actor, timestamp, state, recipient, dan version ditentukan server.
17. Summary final tidak boleh diedit langsung.
18. Semua transmission, download, response, dan decision wajib diaudit.
19. Gunakan transaction untuk operasi multi-tabel.
20. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 2D2 CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-2D2-CLOSURE.md`

## A.1 Medication order hardening

Verifikasi:

- draft tidak mengurangi stok;
- active order immutable;
- revision menyimpan `parent_order_id` atau chain setara;
- original order tetap tersedia;
- discontinuation non-destruktif;
- actor dan ordered_at dari server;
- dose/unit/route tervalidasi;
- tidak ada automatic dose calculation;
- tidak ada direct status mutation dari payload;
- inactive medicine ditolak;
- optimistic locking tersedia.

## A.2 Allergy acknowledgement

Verifikasi:

- acknowledgement append-only;
- patient, visit, order/administration, warning snapshot, actor, time, dan reason tersedia;
- tidak ada automatic allergy-drug conclusion tanpa mapping resmi;
- acknowledgement tidak dianggap sebagai bukti aman;
- warning tidak dapat dilewati tanpa permission/reason;
- data aktif alergi menggunakan clinical + verification status yang benar.

## A.3 Medication administration and stock

Verifikasi:

- hanya `administered` mengurangi stok;
- scheduled/held/refused/missed/cancelled tidak mengurangi stok;
- administration dan stock movement cross-reference;
- movement type khusus medication issue/reversal;
- batch eligible;
- expired/quarantined/recalled/depleted/entered-in-error ditolak;
- stock issue dan administration satu transaction;
- failed transaction rollback penuh;
- correction/entered-in-error mempertahankan original;
- reversal hanya sekali;
- idempotency database unique;
- no negative stock.

## A.4 MariaDB concurrency proof

Gunakan MariaDB/MySQL nyata dan dokumentasikan:

- dua administration concurrent terhadap sisa stok yang sama;
- hanya jumlah valid yang berhasil;
- stok tidak negatif;
- duplicate idempotency menghasilkan satu administration/issue;
- concurrent reversal hanya satu yang berhasil;
- ledger balance sama dengan current quantity;
- transaksi gagal tidak meninggalkan audit sukses.

Jika belum terbukti, perbaiki sebelum Phase 3A.

## A.5 One-time administration governance

Verifikasi apakah one-time administration:

- diterapkan;
- ditunda;
- atau tidak diizinkan.

Jika diterapkan:

- permission khusus;
- reason;
- assessment/visit context;
- tidak menjadi bypass order rutin;
- audit.

Jika governance belum final, nonaktifkan dan tandai `[PERLU DIKONFIRMASI]`.

## A.6 Authorization, audit, Graphify

Verifikasi:

- admin teknis tidak otomatis memiliki medication permission;
- order dan administration permission terpisah;
- direct URL/IDOR test;
- audit immutable;
- Graphify updated;
- exclusions aktif;
- query findings dicatat.

## A.7 Closure status

Gunakan:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika medication-stock atomicity, idempotency, no-negative-stock, authorization, atau immutability memiliki temuan Critical, berhenti.

---

# 4. TAHAP B — HEALTHCARE PARTNER FOUNDATION

Implementasikan master mitra layanan kesehatan.

## B.1 Partner facilities

Buat `healthcare_partners`:

- ULID;
- code unique;
- name;
- partner_type `puskesmas|hospital|clinic|other`;
- address nullable;
- phone nullable;
- official_email nullable;
- cooperation_reference nullable;
- is_active;
- consultation_enabled;
- referral_enabled;
- default_channel nullable;
- created_by_id;
- updated_by_id;
- lock_version;
- timestamps.

Jangan memasukkan mitra nyata tanpa data resmi stakeholder. Gunakan data sintetis pada test.

## B.2 External clinicians/contact points

Buat struktur seperti `healthcare_partner_contacts`:

- partner;
- name;
- profession/role;
- registration_identifier nullable;
- department/unit nullable;
- official_contact nullable;
- channel type;
- is_active;
- verified_at nullable;
- verified_by_id nullable;
- timestamps.

Aturan:

- contact tidak dianggap verified tanpa proses eksplisit;
- personal contact tidak otomatis dianggap official;
- inactive/unverified recipient memiliki warning/guard;
- tidak hard delete jika telah dipakai consultation.

## B.3 Authorization

Permission minimum:

- `view-healthcare-partners`;
- `manage-healthcare-partners`;
- `verify-healthcare-partner-contacts`.

Admin teknis tidak otomatis dapat melihat clinical consultation content.

---

# 5. TAHAP C — CLINICAL CONSULTATION AGGREGATE

## C.1 Consultation schema

Buat `clinical_consultations` dengan ULID:

- `medical_visit_id`;
- `clinical_assessment_id`;
- `observation_episode_id` nullable;
- `healthcare_partner_id`;
- `recipient_contact_id` nullable;
- `purpose`;
- `clinical_question`;
- `urgency`;
- `status`;
- `created_by_id`;
- `finalized_at` nullable;
- `finalized_by_id` nullable;
- `sent_at` nullable;
- `sent_by_id` nullable;
- `acknowledged_at` nullable;
- `completed_at` nullable;
- `completed_by_id` nullable;
- `cancelled_at` nullable;
- `cancelled_by_id` nullable;
- `cancellation_reason` nullable;
- `superseded_by_referral_at` nullable;
- `lock_version`;
- timestamps.

Status:

- `draft`;
- `ready`;
- `sent`;
- `acknowledged`;
- `responded`;
- `completed`;
- `cancelled`;
- `expired`;
- `superseded`;
- `superseded_by_referral`;
- `entered_in_error`.

## C.2 Preconditions

Consultation hanya dapat dibuat jika:

- visit valid dan tidak cancelled;
- assessment final tersedia;
- actor berwenang;
- partner aktif dan consultation enabled;
- tujuan dan clinical question tersedia;
- emergency/red-flag guard dievaluasi;
- consent/authority basis tersedia atau ditandai belum final sesuai policy.

## C.3 Emergency guard

Jika disposition/observation outcome menunjukkan:

- `emergency_referral_required`;
- atau red flag terstruktur yang menurut SOP mewajibkan rujukan;

maka:

- consultation tidak boleh menahan workflow rujukan;
- UI menampilkan prominent warning;
- consultation dapat dibuat paralel hanya jika permission dan reason mengizinkan;
- status consultation dapat menjadi `superseded_by_referral`;
- tidak ada auto-cancel referral;
- decision server-side;
- audit.

Jangan mengarang red-flag clinical rules. Gunakan data/state yang sudah tersedia dan tandai SOP final `[PERLU DIKONFIRMASI]`.

---

# 6. TAHAP D — VERSIONED CONSULTATION SUMMARY

## D.1 Consultation versions

Buat `clinical_consultation_versions`:

- ULID;
- `clinical_consultation_id`;
- version number;
- `summary_payload` JSON terstruktur;
- `rendered_document_path` nullable;
- checksum;
- authored_by_id;
- finalized_at;
- supersedes_version_id nullable;
- redaction/minimum-necessary note nullable;
- consent/authority reference nullable;
- timestamps.

Unique:

- consultation + version number.

## D.2 Summary content

Summary draft dapat mengambil data terpilih dari:

- minimal patient identity;
- age/date of birth jika tersedia dan diperlukan;
- patient type;
- chief complaint;
- symptom chronology;
- final assessment summary;
- relevant vital signs;
- active allergies;
- important conditions;
- relevant initial actions;
- relevant medication administrations;
- observation summary jika ada;
- clinical question;
- urgency;
- author and generated timestamp.

Aturan:

- petugas memilih data yang relevan;
- jangan mengirim seluruh timeline otomatis;
- jangan memasukkan audit log;
- jangan memasukkan role/permission;
- jangan memasukkan data orang lain;
- preview minimum necessary wajib;
- field sensitif tertentu dapat direda ction;
- summary final immutable;
- revisi membuat version baru;
- checksum;
- private storage;
- print/PDF light mode;
- file tidak public.

## D.3 Snapshot

Data summary harus menjadi snapshot versioned. Perubahan rekam lokal setelah summary final tidak mengubah version lama secara diam-diam.

## D.4 Document generation

Jika PDF dibuat:

- private storage;
- generated server-side;
- checksum;
- watermark/reference consultation;
- no public permanent URL;
- secure temporary delivery only jika channel mendukung;
- download diaudit;
- test bahwa dark theme tidak memengaruhi print/PDF.

---

# 7. TAHAP E — TRANSMISSION ABSTRACTION

## E.1 Channel contract

Buat interface:

- `ClinicalConsultationTransportContract`.

Implementasi awal:

- `FakeClinicalConsultationTransport`;
- optional `ManualSecureHandoffTransport`.

Jangan mengimplementasikan pengiriman production ke email/WhatsApp/API tanpa kontrak dan persetujuan.

## E.2 Transmission schema

Buat `clinical_consultation_transmissions`:

- consultation;
- consultation version;
- partner;
- recipient;
- channel;
- status;
- idempotency_key;
- external_reference nullable;
- attempted_at;
- sent_at nullable;
- acknowledged_at nullable;
- failed_at nullable;
- failure_code nullable;
- failure_message_sanitized nullable;
- initiated_by_id;
- correlation_id;
- timestamps.

Status:

- `queued`;
- `sending`;
- `sent`;
- `acknowledged`;
- `failed`;
- `cancelled`;
- `expired`.

## E.3 Rules

- hanya finalized version dapat dikirim;
- recipient dan channel verified/approved;
- idempotency;
- retry aman;
- timeout;
- failure tidak mengubah visit menjadi selesai;
- tidak menulis secret/payload penuh di log;
- transmission append-only;
- resend menghasilkan attempt/transmission baru atau retry record yang dapat ditelusuri;
- sent_at server-side;
- audit.

## E.4 Manual secure handoff

Jika belum ada integration resmi:

- sistem dapat menghasilkan package/reference untuk dikirim manual melalui kanal resmi;
- harus mencatat siapa menyerahkan, kepada siapa, kapan, dan channel;
- jangan membuat public link permanen;
- jangan mengklaim delivered jika hanya file dibuat;
- acknowledgement terpisah.

---

# 8. TAHAP F — EXTERNAL CLINICAL ADVICE

## F.1 Schema

Buat `external_clinical_advices`:

- ULID;
- `clinical_consultation_id`;
- `healthcare_partner_id`;
- `recipient_contact_id` nullable;
- `clinician_name`;
- `clinician_profession`;
- `clinician_identifier` nullable;
- `department` nullable;
- `responded_at`;
- `received_at` server-side;
- `channel`;
- `advice_text`;
- `limitations_text` nullable;
- `recommended_next_step` nullable;
- `verification_status`;
- `verified_at` nullable;
- `verified_by_id` nullable;
- `recorded_by_id`;
- `source_document_path` nullable;
- checksum nullable;
- `status`;
- `parent_advice_id` nullable;
- lock version;
- timestamps.

Verification status:

- `unverified`;
- `partially_verified`;
- `verified`;
- `refuted`.

Status:

- `draft`;
- `finalized`;
- `amended`;
- `entered_in_error`.

## F.2 Rules

- response harus memiliki facility/source attribution;
- missing attribution menjadi unverified;
- external advice tidak otomatis menjadi local assessment;
- external diagnosis tidak otomatis menjadi local diagnosis;
- finalized advice immutable;
- correction via amendment/entered-in-error;
- source attachment private;
- actor/time server-side;
- audit.

## F.3 Inbound integration

Default input manual oleh petugas berwenang atau fake callback.

Jangan membuat public unauthenticated callback.

Jika callback dibuat:

- authenticated/signed;
- replay protection;
- schema validation;
- idempotency;
- partner mapping;
- rate limit;
- audit.

---

# 9. TAHAP G — LOCAL CLINICAL DECISION

Buat `consultation_local_decisions` atau struktur setara:

- consultation;
- external advice nullable;
- decision type;
- rationale;
- decided_by_id;
- decided_at server-side;
- status `draft|finalized|amended|entered_in_error`;
- parent_decision_id nullable;
- lock version;
- timestamps.

Decision type:

- `continue_current_care`;
- `continue_observation`;
- `return_to_activity_recommended`;
- `rest_recommended`;
- `follow_up_required`;
- `referral_recommended`;
- `emergency_referral_required`;
- `other`.

Aturan:

- hanya petugas berwenang;
- advice dapat dipertimbangkan tetapi tidak otomatis menentukan decision;
- rationale wajib;
- final immutable;
- revision/addendum;
- emergency decision menampilkan prominent alert;
- tidak membuat referral actual pada Phase 3A;
- tidak menutup visit;
- consultation dapat completed setelah local decision final;
- audit.

---

# 10. VISIT STATE PHASE 3A

Tambahkan status bila diperlukan:

- `external_consultation_pending`;
- `external_consultation_completed`.

Transisi harus mempertimbangkan visit yang:

- `assessment_completed`;
- `under_observation`;
- `observation_completed`.

Aturan:

- consultation tidak harus mengambil alih status visit jika berjalan paralel;
- pertimbangkan sub-state/read model daripada state utama jika lebih aman;
- dokumentasikan keputusan melalui ADR;
- consultation completed tidak otomatis discharge;
- emergency referral decision tidak otomatis membuat referral;
- semua transition server-side.

---

# 11. PERMISSION DAN POLICY

Permission minimum:

- `view-clinical-consultations`;
- `create-clinical-consultations`;
- `finalize-clinical-consultation-summaries`;
- `send-clinical-consultations`;
- `cancel-clinical-consultations`;
- `record-external-clinical-advice`;
- `verify-external-clinical-advice`;
- `finalize-local-clinical-decisions`;
- `download-clinical-consultation-documents`;
- `view-clinical-consultation-transmissions`;
- `manage-healthcare-partners`;
- `verify-healthcare-partner-contacts`.

Aturan:

1. admin teknis tidak otomatis memiliki akses isi klinis;
2. partner management dan clinical access dipisahkan;
3. sender dan decision-maker dapat dipisah;
4. direct URL/IDOR Policy;
5. UI hiding bukan authorization;
6. download diaudit;
7. recipient substitution dicegah;
8. self-escalation dilarang;
9. patient-as-employee conflict policy ditandai `[PERLU DIKONFIRMASI]`.

---

# 12. UI PHASE 3A

Pertahankan tema biru muda, light/dark/system.

## Consultation list

- draft;
- ready;
- sent;
- waiting response;
- responded;
- completed;
- failed;
- superseded by referral.

## Consultation composer

- patient/visit header;
- active allergy warning;
- summary source selector;
- minimum necessary checklist;
- clinical question;
- purpose;
- urgency;
- partner;
- recipient;
- consent/authority reference;
- preview;
- finalize.

## Transmission

- selected finalized version;
- recipient;
- channel;
- send/manual handoff;
- idempotency;
- status timeline;
- retry/failure details sanitized.

## External advice

- source attribution;
- clinician;
- facility;
- response time;
- advice;
- limitations;
- next step;
- verification;
- attachment.

## Local decision

- selected advice;
- rationale;
- decision;
- emergency warning;
- finalize/addendum.

## UX requirements

- mobile-first;
- accessible;
- keyboard focus;
- loading/empty/error/forbidden;
- no color-only warning;
- version comparison;
- preview before send;
- no public document URL;
- screenshots light/dark desktop/mobile with synthetic data.

Jangan membuat referral execution, transport, billing, or discharge screen.

---

# 13. DOMAIN EVENTS DAN AUDIT

Event minimum:

- `HealthcarePartnerCreated`;
- `HealthcarePartnerContactVerified`;
- `ClinicalConsultationCreated`;
- `ClinicalConsultationSummaryFinalized`;
- `ClinicalConsultationSummaryRevised`;
- `ClinicalConsultationTransmissionQueued`;
- `ClinicalConsultationSent`;
- `ClinicalConsultationTransmissionFailed`;
- `ClinicalConsultationAcknowledged`;
- `ExternalClinicalAdviceRecorded`;
- `ExternalClinicalAdviceVerified`;
- `ExternalClinicalAdviceAmended`;
- `ConsultationLocalDecisionFinalized`;
- `ClinicalConsultationCompleted`;
- `ClinicalConsultationCancelled`;
- `ClinicalConsultationSupersededByReferral`;
- `ClinicalConsultationDocumentDownloaded`.

Audit harus:

- append-only;
- actor/time server-side;
- recipient/channel;
- version/checksum;
- reason;
- correlation ID;
- no secret;
- no full payload in technical logs;
- no success audit on rollback;
- download/transmission auditable;
- idempotent retry not duplicated.

---

# 14. TEST WAJIB

## Phase 2D2 closure

- order does not reduce stock;
- only administered reduces stock;
- atomic administration-stock transaction;
- expired/quarantined/recalled/depleted rejected;
- idempotency unique;
- no-negative-stock MariaDB concurrency;
- reversal once;
- reconciliation equals ledger;
- allergy acknowledgement append-only;
- order revision immutable;
- one-time administration governance;
- unauthorized admin 403;
- Graphify update proof.

## Partner

- unique code;
- inactive partner cannot receive new consultation;
- unverified recipient warning/guard;
- used partner/contact cannot hard delete;
- permission separation.

## Consultation

- finalized assessment required;
- purpose/question required;
- partner enabled;
- actor/time server-side;
- emergency guard;
- draft mutable;
- finalized summary immutable;
- revision creates new version;
- snapshot old version unchanged;
- checksum;
- minimum necessary;
- no audit log included;
- no unrelated patient data;
- unauthorized direct URL/IDOR 403.

## Transmission

- only finalized version;
- approved recipient/channel;
- idempotency;
- duplicate send safe;
- failure does not complete consultation;
- retry traceable;
- no public URL;
- fake/manual transport only by default;
- sanitized failure logs;
- download audited.

## Advice

- attribution required or unverified;
- finalized immutable;
- amendment preserves original;
- no automatic local assessment/diagnosis;
- attachment private;
- callback authentication if implemented.

## Local decision

- permission required;
- rationale required;
- advice does not auto-decide;
- emergency alert;
- no referral entity created;
- no discharge;
- final immutable;
- addendum.

## Regression

- identity/Gate unchanged;
- patient/visit/assessment unchanged;
- observation unchanged;
- medication/pharmacy unchanged;
- no actual referral/discharge;
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

Laporkan skipped tests dan alasannya.

---

# 15. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - Visit -> Assessment -> ClinicalConsultation;
   - Consultation -> Version -> Transmission;
   - Consultation -> ExternalAdvice -> LocalDecision;
   - emergency guard;
   - summary snapshot immutability;
   - recipient substitution path;
   - public file exposure;
   - unauthorized admin path;
   - external advice auto-diagnosis leakage;
   - referral creation leakage;
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
- `docs/10-delivery/PHASE-2D2-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md`;
- `docs/01-domain/MEDICAL-TERMINOLOGY.md`;
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`;
- `docs/02-workflows/REMOTE-CLINICAL-CONSULTATION.md`;
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`;
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/04-architecture/MODULE-BOUNDARIES.md`;
- `docs/04-architecture/INTEGRATIONS.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/05-data/STATE-MACHINES.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/07-security/REMOTE-CONSULTATION-GOVERNANCE.md`;
- `docs/08-api/INTEGRATION-CONTRACTS.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Review dan update ADR-007. Buat ADR tambahan untuk:

- consultation versioning;
- secure transmission abstraction;
- summary snapshot;
- recipient verification;
- emergency referral guard;
- local decision separation;
- visit state/sub-state strategy.

---

# 17. OUTPUT AKHIR

Berikan:

1. Phase 2D2 closure status.
2. Hardening findings and fixes.
3. Schema/migrations.
4. Healthcare partner architecture.
5. Consultation aggregate/state machine.
6. Versioned summary and minimum necessary.
7. Transmission abstraction.
8. External advice attribution/versioning.
9. Local decision separation.
10. Emergency guard.
11. Permissions and Policies.
12. Routes and UI.
13. Audit events.
14. Files created/changed.
15. Commands executed.
16. Tests and actual results.
17. Graphify results and query findings.
18. Screenshots light/dark desktop/mobile.
19. Risks and blockers.
20. Git diff summary.
21. Exact next recommended phase.

---

# 18. CHECKPOINT WAJIB

Berhenti jika:

- Phase 2D2 closure gagal;
- medication-stock transaction belum aman;
- consultation dapat menunda emergency referral;
- summary final dapat diedit langsung;
- old version berubah setelah source record berubah;
- recipient/channel dapat diganti tanpa authorization/audit;
- document dapat diakses public;
- external advice otomatis menjadi local diagnosis/decision;
- authorization/IDOR gagal;
- actor/timestamp dapat dimanipulasi;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **External Clinical Consultation**;
- jangan membuat referral actual;
- jangan membuat transport/companion workflow;
- jangan membuat return-from-referral;
- jangan membuat discharge final;
- jangan membuat billing/claim;
- tunggu persetujuan eksplisit pengguna.
