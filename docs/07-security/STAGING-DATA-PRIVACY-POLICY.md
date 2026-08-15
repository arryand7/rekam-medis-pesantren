---
id: DOC-STAGING-DATA-PRIVACY-POLICY
title: "Staging Data Privacy Policy"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Staging Data Privacy Policy

1. Gunakan data sintetis sebagai default. Data pasien nyata hanya boleh masuk staging dengan approval tertulis, tujuan terbatas, minimisasi, masking dan retention yang ditetapkan.
2. Jangan menyalin database production secara langsung. Backup staging harus terenkripsi dan aksesnya diaudit.
3. Role tetap least privilege; akun bersama dilarang. Deaktivasi akun tidak menghapus person/patient/history.
4. Dokumen medis wajib private storage; tidak boleh ada public URL langsung atau attachment ke kanal tak disetujui.
5. Integrasi hanya mengirim minimum necessary. Diagnosis, assessment, obat dan identitas yang tidak diperlukan tidak boleh masuk Attendance/log biasa.
6. Screenshot, test evidence dan incident report wajib disensor dan tidak di-commit bila memuat data sensitif.
7. Data staging, log dan backup harus mempunyai owner, expiry/retention dan prosedur secure deletion yang disetujui.

Durasi retensi dan pihak approver tetap **[PERLU DIKONFIRMASI]** oleh pengelola data.
