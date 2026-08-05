# PROMPT ANTIGRAVITY — PHASE 2A
## Phase 1 Closure Hardening, Patient Health Profile, and Medical Visit Intake Foundation

Anda adalah principal Laravel architect, health information system engineer, application security engineer, dan technical documentation auditor untuk proyek **SABIRA POSKESTREN Health**.

Gunakan **Gemini 3.6 Flash** dengan reasoning/thinking level **High**.

Tujuan fase ini adalah:

1. memverifikasi dan mengeraskan hasil Phase 1;
2. memperbaiki pemisahan data identitas dan data kesehatan;
3. membangun profil kesehatan pasien yang terstruktur;
4. membangun registrasi kunjungan awal;
5. berhenti sebelum assessment klinis, diagnosis, tanda vital, obat, observasi, konsultasi eksternal, rujukan, atau discharge.

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
8. `docs/01-domain/PERSON-PATIENT-IDENTITY.md`
9. `docs/01-domain/BUSINESS-RULES.md`
10. `docs/01-domain/PATIENT-JOURNEY.md`
11. `docs/01-domain/VISIT-STATUS-LIFECYCLE.md`
12. `docs/02-workflows/POSKESTREN-ADMISSION.md`
13. `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`
14. `docs/03-requirements/ACCEPTANCE-CRITERIA.md`
15. `docs/03-requirements/TRACEABILITY-MATRIX.md`
16. `docs/04-architecture/MODULE-BOUNDARIES.md`
17. `docs/04-architecture/APPLICATION-LAYERS.md`
18. `docs/05-data/DOMAIN-MODEL.md`
19. `docs/05-data/IDENTITY-AND-PATIENT-MODEL.md`
20. `docs/05-data/ENTITY-RELATIONSHIPS.md`
21. `docs/05-data/DATA-DICTIONARY.md`
22. `docs/05-data/DATABASE-CONVENTIONS.md`
23. `docs/05-data/MEDICAL-RECORD-VERSIONING.md`
24. `docs/07-security/ACCESS-CONTROL-MATRIX.md`
25. `docs/07-security/MEDICAL-DATA-PRIVACY.md`
26. `docs/07-security/AUDIT-LOG.md`
27. `docs/09-testing/TEST-STRATEGY.md`
28. `docs/09-testing/SECURITY-TESTS.md`
29. `docs/10-delivery/PHASE-0-CLOSURE.md`
30. `docs/10-delivery/READINESS-REVIEW.md`
31. `plans/KNOWN-ISSUES.md`
32. `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

Tandai keputusan yang belum tersedia sebagai `[PERLU DIKONFIRMASI]`. Jangan mengarang SOP medis.

---

# 2. ATURAN KESELAMATAN

1. Jangan menampilkan `.env`, credential, token, password, atau secret.
2. Jangan menjalankan `migrate:fresh`, `db:wipe`, `DROP DATABASE`, hard delete, force push, atau deployment production.
3. Jangan menggunakan data nyata.
4. Jangan menghasilkan diagnosis, rekomendasi obat, dosis, atau keputusan klinis otomatis.
5. Jangan membuat fitur konsultasi Puskesmas/rumah sakit pada fase ini.
6. Jangan membuat apply sync Gate massal.
7. Jangan menaruh data kesehatan pada tabel `people` atau `users`.
8. Jangan menjadikan `allergies_summary` sebagai sumber kebenaran alergi.
9. Jangan membuat role `admin` otomatis dapat membaca profil kesehatan.
10. Actor dan timestamp resmi harus berasal dari server.
11. Seluruh mutation kesehatan wajib diaudit.
12. Gunakan transaction untuk operasi multi-tabel.
13. Catatan yang telah difinalisasi tidak boleh hard delete.
14. Berhenti pada checkpoint wajib.

---

# 3. TAHAP A — PHASE 1 CLOSURE AUDIT

Lakukan pemeriksaan read-only dan tulis hasil pada:

`docs/10-delivery/PHASE-1-CLOSURE.md`

## A.1 Schema audit

Periksa migration dan schema aktual untuk:

- `people`;
- `users`;
- `patients`;
- `roles`;
- `permissions`;
- pivot role/permission;
- `audit_logs`;
- Gate sync run/report/conflict tables jika tersedia.

Verifikasi:

- ULID konsisten;
- foreign key benar;
- unique constraint benar;
- tidak ada cascade delete yang dapat menghapus patient history;
- role tidak menentukan patient eligibility;
- deactivation user tidak menghapus person/patient;
- `gate_user_id` stabil dan unique bila tersedia;
- field authoritative Gate dan field lokal jelas.

## A.2 Patient table boundary

Periksa keberadaan:

- `blood_type`;
- `allergies_summary`;
- field kesehatan lain pada `patients`.

Keputusan arsitektur fase ini:

- `patients` hanya menyimpan identitas pasien lokal dan eligibility;
- `blood_type` dipindahkan ke `patient_health_profiles`;
- alergi dipindahkan ke struktur alergi terpisah;
- `allergies_summary` tidak menjadi sumber kebenaran;
- bila dipertahankan sebagai cache/display summary, harus diturunkan dari data terstruktur dan tidak diedit langsung.

Buat migration aman untuk memindahkan data sintetis/development bila diperlukan. Jangan menghapus data tanpa migrasi dan verifikasi.

## A.3 Gate dry-run hardening

Verifikasi:

- dry-run benar-benar non-mutatif;
- ada `gate_sync_runs` dan item/result yang dapat ditelusuri atau mekanisme setara;
- preview memiliki actor, timestamp, source version, dan summary;
- `source_missing` hanya disimpulkan setelah full snapshot selesai atau source menyediakan tombstone;
- approved legacy mapping tersedia atau ditandai belum tersedia;
- tidak auto-merge berdasarkan nama;
- payload sama idempotent;
- conflict resolution belum dapat menaikkan privilege;
- fake client tidak aktif pada production.

Jika kelemahan High/Critical ditemukan, perbaiki sebelum Phase 2A dilanjutkan.

## A.4 Audit immutability

Verifikasi:

- tidak ada route update/delete audit log;
- model/service tidak menyediakan mutation publik;
- Policy menolak update/delete;
- database privilege atau application guard didokumentasikan;
- operasi gagal tidak menulis success audit palsu;
- secret sanitization diuji;
- before/after tidak menyimpan password hash dan token.

## A.5 Authorization coverage

Verifikasi seluruh route Phase 1 memiliki:

- authentication;
- server-side authorization;
- direct URL test;
- pagination dan query validation;
- tidak ada mass assignment;
- tidak ada self role escalation.

## A.6 Test sufficiency

Jangan hanya menghitung jumlah test. Petakan test terhadap:

- Person/User/Patient;
- eligibility;
- deactivation;
- role/permission;
- audit;
- Gate DTO;
- dry-run;
- conflicts;
- theme preference;
- direct URL authorization.

Status closure:

- `PASSED`;
- `PASSED-WITH-FOLLOW-UP`;
- `FAILED`.

Jika ada temuan Critical, berhenti.

---

# 4. TAHAP B — PATIENT HEALTH PROFILE FOUNDATION

Implementasikan modul `HealthProfiles` sebagai boundary tersendiri.

## B.1 Patient health profile

Buat tabel/model untuk profil kesehatan dasar.

Field awal:

- ULID;
- `patient_id` unique;
- `blood_type` nullable dan tervalidasi;
- catatan darurat yang sangat terbatas;
- tanggal pembaruan;
- `updated_by`;
- version/lock bila diperlukan.

Jangan menyimpan diagnosis, assessment, visit, atau resep pada profil dasar.

## B.2 Structured allergies

Buat model terstruktur. Pilih desain yang konsisten dengan dokumentasi, misalnya:

- `allergies` sebagai master atau reference;
- `patient_allergies` sebagai relasi pasien.

Data minimum relasi:

- patient;
- allergen/substance;
- reaction nullable;
- severity nullable;
- status `suspected|confirmed|resolved|entered-in-error`;
- verification status;
- onset/recorded date nullable;
- notes terbatas;
- recorded by;
- verified by nullable;
- timestamps;
- finalization/versioning bila diperlukan.

Aturan:

- tidak menggunakan satu text field sebagai sumber kebenaran;
- alergi aktif mudah terlihat;
- koreksi menggunakan status/addendum, bukan hard delete;
- data `entered-in-error` tetap dapat diaudit;
- nilai severity tidak boleh dipakai aplikasi untuk memberikan keputusan klinis otomatis.

## B.3 Medical conditions

Buat struktur kondisi medis penting:

- master/label kondisi;
- patient relation;
- status aktif/inaktif/resolved/entered-in-error;
- onset nullable;
- notes;
- recorded by;
- verified by nullable;
- audit.

Jangan membuat diagnosis kunjungan pada fase ini. Ini hanya profil kondisi penting yang sudah diketahui.

## B.4 Emergency contacts

Buat struktur kontak darurat pasien:

- patient;
- name;
- relationship;
- phone;
- priority;
- source `gate|local`;
- active;
- consent/communication note jika relevan;
- recorded by;
- audit.

Field dari Gate tidak boleh diedit langsung bila authoritative.

## B.5 Optional routine medication declaration

Jangan membuat medication order atau administration.

Jika dokumentasi mengharuskan obat rutin, hanya buat deklarasi profil yang sangat sederhana dan tandai sebagai self/guardian-reported, bukan resep aktif. Jika requirement belum cukup, tunda dan tandai `[PERLU DIKONFIRMASI]`.

## B.6 Authorization

Permission minimum baru:

- `view-patient-health-profile`;
- `update-patient-health-profile`;
- `manage-patient-allergies`;
- `manage-patient-conditions`;
- `manage-emergency-contacts`.

Aturan:

- admin teknis tidak otomatis mendapat permission;
- pengasuh dan wali kelas tidak boleh melihat profil penuh;
- list pasien tidak boleh memuat detail kesehatan tanpa permission;
- export belum dibuat pada fase ini;
- seluruh direct URL dilindungi Policy.

---

# 5. TAHAP C — MEDICAL VISIT INTAKE FOUNDATION

Bangun hanya registrasi dan antrean awal.

## C.1 Medical visit aggregate

Buat `medical_visits` dengan field minimum:

- ULID;
- server-generated `visit_number`;
- `patient_id`;
- `status`;
- `arrived_at` dari server;
- `chief_complaint`;
- source/reporting type;
- source/reporting name nullable;
- origin location nullable;
- receiving officer;
- assigned officer nullable;
- created by;
- cancellation reason nullable;
- finalized/closed fields belum digunakan kecuali diperlukan;
- optimistic lock/version;
- timestamps.

Status fase ini hanya:

- `registered`;
- `waiting_assessment`;
- `cancelled`.

Jangan membuat `under_assessment`, diagnosis, disposition, observation, referral, atau discharge logic pada fase ini.

## C.2 Visit complaint

Putuskan secara eksplisit:

- apakah `chief_complaint` cukup berada pada visit; atau
- apakah perlu `visit_complaints`.

Untuk Phase 2A, prioritaskan desain sederhana tetapi dapat berkembang. Dokumentasikan keputusan. Jangan menduplikasi sumber kebenaran tanpa alasan.

## C.3 Active visit guard

Aturan:

- satu patient tidak boleh memiliki dua kunjungan aktif tanpa override;
- pengecekan harus aman terhadap race condition;
- gunakan transaction dan row lock atau database constraint yang cocok untuk MariaDB;
- jika menggunakan generated column/unique index atau strategi lain, dokumentasikan melalui ADR;
- override hanya oleh permission khusus dan wajib memiliki alasan;
- override diaudit.

Permission:

- `create-medical-visits`;
- `view-medical-visits`;
- `cancel-medical-visits`;
- `override-active-visit`.

## C.4 Registration workflow

Implementasikan:

1. cari patient;
2. tampilkan identitas dan warning alergi/kondisi penting bagi petugas berwenang;
3. cek active visit;
4. isi chief complaint;
5. catat sumber laporan/pengantar;
6. buat visit dengan server time;
7. ubah ke `waiting_assessment` bila workflow menetapkan otomatis;
8. tulis audit;
9. tampilkan detail dan timeline awal.

## C.5 Cancellation

Cancellation:

- bukan hard delete;
- permission khusus;
- reason wajib;
- actor dan server time;
- audit;
- cancelled visit tidak dianggap active;
- tidak dapat dibatalkan ulang;
- restore/reopen ditunda.

## C.6 Visit numbering

Nomor visit dibuat server-side dan unik.

Jangan menaruh data sensitif berlebihan dalam nomor. Dokumentasikan format, concurrency, dan reset policy. Jika belum ada keputusan, gunakan opaque sequence/ULID display yang aman dan tandai format bisnis final sebagai `[PERLU DIKONFIRMASI]`.

---

# 6. UI PHASE 2A

Pertahankan tema biru muda, light/dark/system, dan semantic tokens.

Buat atau perbarui halaman:

## Patient

- detail identitas;
- health profile;
- active allergies;
- medical conditions;
- emergency contacts;
- audit summary terbatas.

## Visit

- daftar kunjungan;
- registrasi kunjungan;
- detail intake;
- antrean `waiting_assessment`;
- cancel dialog;
- active visit warning.

## UX requirements

- mobile-first;
- loading, empty, error, forbidden state;
- searchable patient selector;
- server-side pagination;
- keyboard accessible;
- focus visible;
- warning memiliki teks dan ikon;
- jangan menampilkan semua data kesehatan pada tabel umum;
- screenshot light/dark desktop/mobile menggunakan data sintetis.

Jangan membuat assessment workspace penuh.

---

# 7. AUDIT EVENT WAJIB

Tambahkan event:

- health profile created/updated;
- allergy created/updated/finalized/entered-in-error;
- condition created/updated/resolved/entered-in-error;
- emergency contact created/updated/deactivated;
- medical visit registered;
- active visit override;
- medical visit cancelled;
- sensitive health profile viewed jika kebijakan audit mewajibkan.

Audit harus:

- append-only;
- konsisten dengan transaction;
- menyimpan actor dan correlation ID;
- menyensor secret;
- tidak menyimpan seluruh model secara buta jika ada field tidak relevan.

---

# 8. TEST WAJIB

## Phase 1 closure

- patient table tidak lagi menjadi sumber kebenaran blood type/allergy;
- dry-run Gate tetap non-mutatif;
- source_missing tidak dihitung dari page parsial;
- audit tidak dapat update/delete;
- direct URL authorization lulus.

## Health profile

- satu patient satu profile;
- blood type validation;
- allergy structured record;
- allergy entered-in-error tetap tersimpan;
- condition status transition;
- admin teknis tidak otomatis dapat melihat profil;
- authorized health user dapat melihat/mengelola;
- emergency contact source ownership.

## Visit intake

- authorized user dapat membuat visit;
- actor tidak dapat dikirim dari payload;
- arrived_at menggunakan server;
- invalid patient ditolak;
- active visit ganda ditolak;
- concurrency test active visit;
- override butuh permission dan reason;
- cancellation tidak menghapus;
- cancelled visit tidak active;
- visit number unik;
- audit ditulis setelah transaksi sukses;
- failed transaction tidak menulis success audit.

## Security

- IDOR patient profile;
- IDOR visit;
- mass assignment;
- unauthorized allergy update;
- XSS pada chief complaint/notes;
- pagination/filter allowlist;
- sensitive fields tidak bocor ke response.

## Theme and UI

- light/dark/system regression;
- responsive smoke test;
- accessible labels;
- no theme flicker regression.

Jalankan:

```bash
php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
php artisan route:list
php artisan migrate:status
```

Laporkan hasil aktual.

---

# 9. GRAPHIFY

Setelah implementasi:

1. update graph tanpa `--code-only`;
2. pastikan exclusions tetap aktif;
3. query:
   - Person -> Patient -> HealthProfile;
   - allergy source of truth;
   - admin authorization;
   - MedicalVisit active guard;
   - server timestamp;
   - audit events;
   - requirements tanpa test;
   - code path hard delete;
   - patient table medical-field leakage.

Perbarui:

- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`.

---

# 10. DOKUMENTASI WAJIB

Perbarui:

- `PROJECT-STATUS.md`;
- `CHANGELOG.md`;
- `docs/10-delivery/PHASE-1-CLOSURE.md`;
- `docs/10-delivery/READINESS-REVIEW.md`;
- `docs/01-domain/BUSINESS-RULES.md` bila ada klarifikasi;
- `docs/03-requirements/FUNCTIONAL-REQUIREMENTS.md`;
- `docs/03-requirements/ACCEPTANCE-CRITERIA.md`;
- `docs/03-requirements/TRACEABILITY-MATRIX.md`;
- `docs/05-data/ENTITY-RELATIONSHIPS.md`;
- `docs/05-data/DATA-DICTIONARY.md`;
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`;
- `docs/09-testing/FEATURE-TEST-MATRIX.md`;
- `plans/KNOWN-ISSUES.md`;
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`.

Buat ADR jika memilih strategi active visit uniqueness atau versioning yang belum didokumentasikan.

---

# 11. OUTPUT AKHIR

Berikan:

1. Phase 1 closure status.
2. Temuan dan perbaikan hardening.
3. Schema dan migration baru.
4. Pemindahan blood type/allergy dari patient.
5. Health profile architecture.
6. Medical visit intake architecture.
7. Permissions dan Policies.
8. Routes dan UI.
9. Audit events.
10. File dibuat/diubah.
11. Command dijalankan.
12. Test dan hasil aktual.
13. Graphify result dan query findings.
14. Screenshot light/dark desktop/mobile.
15. Risiko dan blocker.
16. Git diff summary.
17. Exact next recommended phase.

---

# 12. CHECKPOINT WAJIB

Berhenti jika:

- Phase 1 closure gagal;
- data kesehatan masih bercampur dengan identity model tanpa migration aman;
- active visit guard tidak aman terhadap concurrency;
- authorization/IDOR gagal;
- audit dapat dimutasi;
- migration merusak data;
- test kritis gagal.

Jika semua berhasil:

- commit dengan pesan yang sesuai;
- pastikan working tree clean;
- berhenti setelah **Health Profile + Medical Visit Intake**;
- jangan membuat tanda vital;
- jangan membuat assessment;
- jangan membuat diagnosis;
- jangan membuat tindakan medis;
- jangan membuat medication;
- jangan membuat observation;
- jangan membuat consultation;
- jangan membuat referral;
- tunggu persetujuan eksplisit pengguna.
