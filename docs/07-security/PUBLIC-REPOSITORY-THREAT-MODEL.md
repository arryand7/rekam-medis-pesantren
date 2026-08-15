---
id: SEC-PUBLIC-REPOSITORY-THREAT-MODEL
title: "Public Repository Threat Model"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Public Repository Threat Model

## Tujuan dan batas

Dokumen ini memodelkan risiko saat source code SABIRA POSKESTREN Health dipublikasikan. Publikasi source tidak sama dengan izin deployment, akses sistem, atau pemrosesan data pasien. Environment staging/production, infrastruktur, dan data operasional berada di luar scope repositori publik.

## Aset yang dilindungi

- credential aplikasi, database, Gate, Attendance, mail, cloud, session, dan signing/encryption key;
- data pasien, rekam medis, identitas personal, audit trail, log, backup, dump, dan ekspor;
- detail infrastruktur privat yang memudahkan serangan;
- integritas authorization, lifecycle klinis, dan rantai audit;
- riwayat Git, karena penghapusan pada working tree tidak menghapus objek commit lama.

## Pelaku dan jalur paparan

Pelaku mencakup pembaca anonim, bot pemindai secret, dependency attacker, contributor yang keliru, dan pihak yang mengorelasikan metadata. Jalur utama adalah source/config hardcoded, `.env`, fixture/data seed, dokumentasi, screenshot, archive/database, log/build artifact, issue/PR, dependency, dan seluruh Git history.

## Kontrol

- secret hanya melalui environment/secret manager; contoh publik kosong dan endpoint memakai domain reserved `.invalid`;
- `.gitignore` menolak environment lokal, key/certificate, database/dump, backup/archive, private storage, cache, dan prompt AI transien;
- data contoh wajib sintetis (`.test`/`.invalid`) dan demo seed opt-in;
- Policy/Gate, server-side validation, transaction, audit trail, dan private storage tetap merupakan kontrol runtime;
- current-tree scan, history scan, dependency audit, static test, review manusia, dan release checklist wajib sebelum publikasi;
- incident secret: revoke/rotate dahulu, audit akses, lalu tentukan history rewrite secara terkoordinasi.

## Risiko residu

Riwayat mengandung metadata lokal pengembangan lama dan prompt historis yang sudah dihapus, tetapi audit Phase 6A0 tidak menemukan secret aktif atau data pasien nyata. Metadata tersebut tidak memberikan akses. Git author metadata, nama proyek, arsitektur, dan informasi local-development memang akan menjadi informasi publik. Keputusan lisensi dan kanal laporan keamanan masih harus ditetapkan pemilik.

## Aturan disclosure

Jangan meletakkan credential atau data medis di public issue. Ikuti `SECURITY.md`; kanal privat ditandai `[PERLU DIKONFIRMASI]` sampai pemilik menetapkannya.
