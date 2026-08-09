# PROMPT ANTIGRAVITY — PHASE 3C1
## Phase 3B Closure Verification, Visit Discharge, Follow-up, Return-to-Activity, and Operational Handoff

Anda adalah principal Laravel architect, health information system engineer, clinical workflow architect, application security engineer, privacy engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash High** atau **Claude Sonnet 4.6 Thinking**.

Tujuan fase ini:
1. memverifikasi closure final Phase 3B;
2. membangun discharge/clinical closure kunjungan;
3. membangun rekomendasi kembali ke aktivitas, istirahat, atau tindak lanjut;
4. membangun follow-up plan terstruktur;
5. membangun operational handoff internal setelah kunjungan selesai;
6. memastikan kunjungan hanya dapat ditutup bila seluruh invariant klinis dan administratif terpenuhi;
7. mempertahankan audit, immutability, authorization, dan traceability;
8. berhenti sebelum integrasi Absensi production, notifikasi WhatsApp/email production, billing, klaim, dan analytics manajemen lanjut.

Jangan membuat diagnosis otomatis, rekomendasi klinis otomatis, atau keputusan discharge otomatis.

# 1. DOKUMEN WAJIB DIBACA

Baca terlebih dahulu:
- `AGENTS.md`
- `README.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/README.md`
- `docs/01-domain/OPERATIONAL-CONTEXT.md`
- `docs/01-domain/BUSINESS-RULES.md`
- `docs/01-domain/PATIENT-JOURNEY.md`
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`
- `docs/02-workflows/DISCHARGE-AND-RETURN.md`
- `docs/02-workflows/RETURN-FROM-REFERRAL.md`
- `docs/02-workflows/OBSERVATION-AND-CARE.md`
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/04-architecture/MODULE-BOUNDARIES.md`
- `docs/05-data/DOMAIN-MODEL.md`
- `docs/05-data/ENTITY-RELATIONSHIPS.md`
- `docs/05-data/DATA-DICTIONARY.md`
- `docs/05-data/STATE-MACHINES.md`
- `docs/05-data/MEDICAL-RECORD-VERSIONING.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/09-testing/TEST-STRATEGY.md`
- `docs/09-testing/BUSINESS-SCENARIOS.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `docs/10-delivery/PHASE-3B-CLOSURE.md`
- `docs/10-delivery/PHASE-3B-MARIADB-CONCURRENCY-REPORT.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Jika path aktual berbeda, gunakan path aktual. Jangan membuat file duplikat karena typo path. Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`.

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, password, token, secret, atau credential.
2. Jangan menggunakan data pasien nyata.
3. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau production deployment.
4. Jangan membuat keputusan discharge otomatis.
5. Jangan mengarang SOP kapan pasien boleh kembali ke kelas/asrama/aktivitas.
6. Jangan mengarang lama istirahat atau pembatasan aktivitas.
7. Jangan otomatis menutup visit karena observation/referral selesai.
8. Jangan otomatis menghentikan medication order tanpa rule manusia.
9. Jangan otomatis mengirim data ke Absensi, WhatsApp, email, atau aplikasi eksternal.
10. Jangan menganggap seluruh patient adalah santri.
11. Workflow return-to-dorm/class hanya berlaku bila patient type dan konteksnya relevan.
12. Untuk guru/staf/pengasuh, destination/return context harus generik dan configurable.
13. Actor dan official timestamp berasal dari server.
14. Final discharge record immutable.
15. Koreksi memakai addendum/versioning/entered-in-error, bukan edit langsung.
16. Semua mutation multi-tabel memakai transaction.
17. Semua closure, download, handoff, dan correction wajib diaudit.
18. Berhenti pada checkpoint wajib.

# 3. TAHAP A — PHASE 3B FINAL SANITY CHECK

Lakukan read-only verification:

```bash
pwd
git branch --show-current
git status
git log --oneline -10
php artisan migrate:status
php artisan route:list
```

Verifikasi:
- commit `e59e78f` atau commit final Phase 3B ada;
- working tree clean;
- 0 concurrency skipped pada laporan final;
- referral route tidak memakai closure;
- `ReferralPolicy` enforced;
- referral document private;
- return review tidak membuat discharge otomatis;
- visit masih terbuka setelah referral return review;
- Graphify up-to-date.

Buat `docs/10-delivery/PHASE-3B-FINAL-CLOSURE.md`.

Status hanya `PASSED` atau `FAILED`. Jika ada regresi Critical, berhenti.

# 4. TAHAP B — DISCHARGE DOMAIN MODEL

Bangun aggregate `VisitDischarge` atau nama domain yang konsisten.

## Schema

Buat `visit_discharges` dengan ULID:
- `medical_visit_id` unique;
- `discharge_type`;
- `discharge_destination`;
- `clinical_summary`;
- `final_condition`;
- `activity_recommendation`;
- `rest_recommendation` nullable;
- `restriction_notes` nullable;
- `follow_up_required`;
- `follow_up_summary` nullable;
- `follow_up_date` nullable;
- `follow_up_partner_id` nullable;
- `prepared_by_id`;
- `prepared_at`;
- `finalized_by_id` nullable;
- `finalized_at` nullable;
- `status`;
- `parent_discharge_id` nullable;
- `lock_version`;
- timestamps.

Status:
- `draft`
- `finalized`
- `amended`
- `entered_in_error`

Discharge type:
- `return_to_activity`
- `rest_required`
- `continue_poskestren_care`
- `follow_up_external`
- `referred_again`
- `transfer_of_care`
- `other`

Destination harus mendukung santri maupun non-santri. Jangan hard-code bahwa semua pasien kembali ke asrama.

# 5. TAHAP C — DISCHARGE READINESS ENGINE

Buat service/action seperti `EvaluateVisitDischargeReadinessAction`.

Engine hanya memeriksa invariant teknis/domain, bukan mengambil keputusan klinis:

1. visit valid dan tidak cancelled;
2. assessment final tersedia;
3. observation yang pernah aktif sudah completed/transferred;
4. referral tidak sedang departed/accepted/under_external_care;
5. jika returned from referral, local return review final tersedia bila diwajibkan;
6. consultation state konsisten;
7. tidak ada critical draft clinical record yang seharusnya final;
8. medication administration konsisten dengan ledger;
9. active medication orders tidak otomatis dihentikan;
10. actor berwenang.

Output:
- `ready`
- `technical_blockers`
- `warnings`

Jangan membuat clinical readiness score.

# 6. TAHAP D — FOLLOW-UP PLAN

Buat `visit_follow_up_plans` dengan field:
- discharge;
- follow_up_type;
- due_at/date nullable;
- healthcare_partner_id nullable;
- instructions;
- responsible_party_type nullable;
- responsible_party_reference nullable;
- status;
- created_by;
- completed_at nullable;
- completed_by nullable;
- cancellation_reason nullable;
- lock_version;
- timestamps.

Jenis:
- `poskestren_recheck`
- `external_facility`
- `activity_reassessment`
- `medication_review`
- `wound_review`
- `other`

Status:
- `planned`
- `completed`
- `cancelled`
- `entered_in_error`

Tidak ada auto-complete.

# 7. TAHAP E — RETURN-TO-ACTIVITY / RESTRICTION

Buat structured record atau field terpisah:
- activity_status;
- effective_start;
- effective_until nullable;
- restriction_type;
- restriction_note;
- allowed_activity_note nullable;
- issued_by;
- issued_at;
- review_date nullable;
- status/version.

Activity status:
- `full_activity`
- `limited_activity`
- `rest`
- `temporarily_not_cleared`
- `other`

Aplikasi tidak menentukan nilai ini otomatis.

# 8. TAHAP F — OPERATIONAL HANDOFF INTERNAL

Bangun internal handoff/outbox, belum external sending.

Buat `clinical_operational_handoffs`:
- visit/discharge;
- recipient_type;
- recipient_reference nullable;
- purpose;
- payload_snapshot JSON minimum;
- status;
- prepared_by;
- prepared_at;
- acknowledged_at nullable;
- acknowledged_by nullable;
- channel `internal`;
- lock_version;
- timestamps.

Recipient type:
- `dorm_supervisor`
- `homeroom_teacher`
- `guardian`
- `patient`
- `staff_supervisor`
- `other`

Payload minimum dapat berisi:
- identitas minimum;
- activity/rest status;
- effective period;
- follow-up requirement;
- restrictions;
- acknowledgement reference.

Jangan otomatis memasukkan diagnosis detail, assessment narrative, medication ledger, full allergy history, consultation advice, atau audit log.

Status:
- `draft`
- `ready`
- `acknowledged`
- `cancelled`
- `entered_in_error`

Belum ada external send.

# 9. TAHAP G — VISIT CLOSURE STATE MACHINE

Tambahkan:
- `discharge_prepared`
- `discharged`

Aturan:
1. discharge draft tidak menutup visit;
2. finalization membutuhkan permission;
3. finalization dan perubahan visit ke `discharged` satu transaction;
4. discharged visit immutable terhadap mutation klinis biasa;
5. addendum masih dapat dilakukan melalui workflow terkontrol;
6. reopening belum dibuat kecuali requirement jelas;
7. discharged visit tidak hard delete;
8. actor/time server-side;
9. audit.

# 10. PRIVATE DISCHARGE SUMMARY

Bila requirement mendukung, buat summary private versioned.

Isi minimum:
- identitas minimum;
- visit number;
- chief complaint;
- ringkasan perjalanan klinis;
- final condition;
- tindakan penting;
- medication administered bila relevan;
- referral outcome bila relevan;
- activity/rest status;
- restriction;
- follow-up.

Aturan:
- immutable/versioned;
- checksum;
- private storage;
- authorized download;
- audited;
- minimum necessary;
- no public URL.

PDF resmi boleh ditunda bila renderer belum final.

# 11. AUTHORIZATION

Permission minimum:
- `view-visit-discharges`
- `prepare-visit-discharges`
- `finalize-visit-discharges`
- `amend-visit-discharges`
- `manage-follow-up-plans`
- `manage-activity-restrictions`
- `prepare-operational-handoffs`
- `acknowledge-operational-handoffs`
- `download-discharge-summaries`

Admin teknis tidak otomatis memiliki clinical permission. Direct URL/IDOR wajib dilindungi.

# 12. UI

Pertahankan warna dasar biru muda dan light/dark/system.

Buat:
- Visit Closure Workspace;
- readiness blockers/warnings;
- discharge type/destination;
- activity/rest;
- restrictions;
- follow-up;
- operational handoff preview;
- finalization;
- discharge summary/version/download;
- follow-up queue.

Tidak ada tombol production send ke Absensi, WhatsApp, email, atau external app.

# 13. AUDIT EVENTS

Minimal:
- `VisitDischargePrepared`
- `VisitDischargeFinalized`
- `VisitDischargeAmended`
- `VisitDischargeEnteredInError`
- `VisitFollowUpPlanned`
- `VisitFollowUpCompleted`
- `VisitFollowUpCancelled`
- `ActivityRestrictionIssued`
- `ActivityRestrictionAmended`
- `OperationalHandoffPrepared`
- `OperationalHandoffAcknowledged`
- `MedicalVisitDischarged`
- `DischargeSummaryDownloaded`

Audit append-only, server-authoritative, no success on rollback.

# 14. TEST WAJIB

Phase 3B regression:
- concurrency remains passed;
- private referral docs;
- policies/routes.

Readiness:
- cancelled visit cannot discharge;
- active external referral blocks discharge;
- return review requirement;
- no auto medication discontinuation.

Discharge:
- authorized draft;
- unauthorized finalize 403;
- server actor/time;
- final immutable;
- amendment preserves original;
- one final discharge chain;
- atomic visit state transition;
- rollback leaves visit open;
- IDOR/mass assignment;
- no hard delete.

Follow-up:
- create/complete/cancel permissions;
- due/overdue;
- no auto-completion.

Operational handoff:
- minimum necessary;
- no full medical narrative by default;
- no external send;
- acknowledgement permission;
- patient-type-aware logic.

Document:
- private storage;
- no public URL;
- checksum;
- authorized audited download;
- old version immutable.

Run:

```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
git diff --check
```

Gunakan MariaDB test database untuk integration/locking tests.

# 15. GRAPHIFY

Update graph tanpa `--code-only`.

Query:
- Visit -> Discharge
- ReferralReturnReview -> Discharge
- discharge readiness blockers
- medication auto-discontinue leakage
- VisitDischarge -> FollowUp
- VisitDischarge -> OperationalHandoff
- operational payload privacy
- discharged visit mutation paths
- private discharge document
- unauthorized admin path
- hard delete path
- missing tests

Perbarui traceability/document-code mapping.

# 16. DOKUMENTASI

Buat:
- `docs/10-delivery/PHASE-3C1-CLOSURE.md`

Perbarui:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `docs/01-domain/BUSINESS-RULES.md`
- `docs/01-domain/PATIENT-JOURNEY.md`
- `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`
- `docs/02-workflows/DISCHARGE-AND-RETURN.md`
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
- `docs/03-requirements/TRACEABILITY-MATRIX.md`
- `docs/05-data/ENTITY-RELATIONSHIPS.md`
- `docs/05-data/DATA-DICTIONARY.md`
- `docs/05-data/STATE-MACHINES.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/MEDICAL-DATA-PRIVACY.md`
- `docs/07-security/AUDIT-LOG.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

# 17. GIT

Sebelum mulai:

```bash
git status
git log --oneline -5
```

Jika clean:

```bash
git tag -a phase-3b-complete -m "Phase 3B referral and return workflow complete"
```

Setelah semua lulus:

```bash
git status
git diff --check
git add -A
git diff --cached --check
git commit -m "feat(discharge): complete Phase 3C1 visit closure and follow-up"
git status
```

Target working tree clean.

# 18. OUTPUT AKHIR

Berikan:
1. Phase 3B final sanity result.
2. Schema/migrations.
3. Discharge aggregate.
4. Readiness engine.
5. Visit state machine.
6. Follow-up plan.
7. Activity/restriction model.
8. Operational handoff dan minimum-necessary policy.
9. Private discharge summary.
10. Permissions/Policies.
11. Routes/UI.
12. Audit events.
13. Test/assertion/skipped results.
14. MariaDB integration result.
15. Graphify findings.
16. Remaining risks.
17. Commit.
18. Working tree status.
19. GO/NO-GO for Phase 3C2.

# 19. CHECKPOINT WAJIB

Jangan lanjut ke Phase 3C2 jika:
- discharge dapat difinalisasi ketika referral masih aktif;
- final discharge dapat diedit langsung;
- visit tidak berubah secara atomik;
- medication dihentikan otomatis tanpa rule;
- operational handoff membocorkan full medical record;
- authorization/IDOR gagal;
- private document exposure;
- critical tests gagal.

Jika lulus:
- commit;
- working tree clean;
- berhenti;
- jangan membuat production notification;
- jangan membuat Absensi integration;
- jangan membuat billing/claim;
- tunggu persetujuan eksplisit pengguna.
