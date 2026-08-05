---
id: DOC-DOMAIN-MODEL
title: "Model Domain"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Model Domain

## Aggregate utama

### StudentHealthProfile
Alergi, kondisi penting, obat rutin, kontak darurat.

### MedicalVisit
Root untuk satu episode pelayanan. Memiliki keluhan, status, assignment, assessment, dan outcome.

### ObservationEpisode
Pemantauan selama santri berada di ruang kesehatan.

### MedicationAdministration
Kejadian obat benar-benar diberikan.

### Referral
Proses rujukan eksternal dan tindak lanjut.

### MedicineInventory
Master obat, batch, stok, dan movement.

## Invariant

- Kunjungan final tidak menerima perubahan langsung.
- Satu kunjungan maksimal satu observasi aktif.
- Satu santri tidak memiliki kunjungan aktif ganda tanpa override.
- Administration tidak boleh menghasilkan stok negatif.
- Referral aktif harus memiliki tujuan.
- Actor dan timestamp tidak berasal dari client.

### Person
Identitas manusia authoritative dari Gate.

### User
Akun autentikasi dan role aplikasi.

### Patient
Person sebagai subjek rekam kesehatan. Tidak bergantung pada role.

### ClinicalConsultation
Ringkasan kasus, recipient, transmission, external advice, dan keputusan lokal.

## Invariant tambahan

- Semua visit merujuk `patient_id`, bukan langsung ke user login.
- Patient history bertahan saat user disabled.
- External advice tidak dapat mengubah assessment secara otomatis.
- Consultation yang dikirim memiliki immutable version.
