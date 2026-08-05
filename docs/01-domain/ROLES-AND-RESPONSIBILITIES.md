---
id: DOC-ROLES
title: "Peran dan Tanggung Jawab"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Peran dan Tanggung Jawab

## ROLE-SUPER-ADMIN
Mengelola konfigurasi teknis, integrasi, role, dan permission. Tidak otomatis berhak membaca seluruh isi medis.

## ROLE-POSKESTREN-ADMIN
Mengelola administrasi kunjungan, master non-klinis, jadwal, dan laporan yang diizinkan.

## ROLE-HEAD-OF-POSKESTREN
Mengawasi operasional, menyetujui SOP, melihat laporan, dan memiliki akses klinis sesuai penugasan.

## ROLE-HEALTH-PROFESSIONAL
Melakukan assessment, tindakan, pemberian obat, observasi, dan rujukan sesuai kewenangan.

## ROLE-HEALTH-ASSISTANT
Membantu registrasi, pengukuran, monitoring, dan tugas yang secara eksplisit diizinkan.

## ROLE-PHARMACY
Mengelola master obat, batch, stok, expiry, dan distribusi sesuai SOP.

## ROLE-DORM-SUPERVISOR
Melihat status operasional seperti berada di POSKESTREN, perlu istirahat, jadwal kontrol, atau pembatasan aktivitas. Tidak otomatis melihat diagnosis.

## ROLE-HOMEROOM-TEACHER
Melihat status izin dan dampak kehadiran yang relevan. Tidak melihat rekam medis lengkap.

## ROLE-MANAGEMENT
Melihat dashboard agregat, tren, risiko, dan kasus tertentu bila ada kewenangan.

## ROLE-STUDENT
Fase lanjutan: melihat jadwal kontrol, instruksi yang dibagikan, dan riwayat terbatas.

## ROLE-GUARDIAN
Fase lanjutan: melihat informasi anak yang telah ditetapkan dapat dibagikan.

## Catatan

Role aplikasi bukan pengganti validasi kewenangan klinis. Permission seperti `administer-medication` dan `approve-referral` harus diberikan secara eksplisit.

## ROLE-EXTERNAL-CLINICIAN
Tenaga kesehatan dari Puskesmas/rumah sakit yang memberikan pertimbangan klinis melalui kanal resmi. Tidak menjadi pengguna internal kecuali integrasi dan perjanjian mengaturnya.

## Persona pasien vs role akses

Role akses tidak menentukan apakah seseorang dapat menjadi pasien. Seorang guru, staf, petugas kesehatan, kepala POSKESTREN, atau admin manusia tetap dapat memiliki patient profile.

Hanya akun non-manusia dan administratif murni yang dikecualikan.
