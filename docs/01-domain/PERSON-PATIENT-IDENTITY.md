---
id: DOC-PERSON-PATIENT-IDENTITY
title: "Model Person, User, Role, dan Patient"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Model Person, User, Role, dan Patient

## Tujuan

Mencegah pencampuran identitas manusia, akun login, permission, dan rekam medis.

## Entitas konseptual

### Person

Mewakili manusia. Detail authoritative berasal dari Gate, seperti:

- `gate_user_id`;
- NIS/NIP/identifier;
- nama;
- email;
- nomor telepon bila disediakan;
- tipe pengguna;
- status aktif;
- atribut organisasi;
- foto.

### User

Mewakili akun login lokal atau proyeksi akun Gate. User dapat:

- terhubung ke satu `person`;
- memiliki role dan permission aplikasi;
- dinonaktifkan tanpa menghapus person;
- tidak memiliki person bila merupakan service account.

### Patient

Mewakili person sebagai subjek pelayanan kesehatan. Patient menyimpan identifier medis lokal dan terhubung ke seluruh riwayat kesehatan.

## Aturan kelayakan

1. Semua `person` manusia dari Gate eligible menjadi patient.
2. Patient profile dapat dibuat saat sinkronisasi atau secara lazy saat pertama kali diperlukan.
3. Akun teknis/service/bot tidak eligible.
4. Akun administratif murni yang tidak merepresentasikan manusia tidak eligible.
5. Pengguna manusia yang memiliki role `admin` tetap eligible.
6. Perubahan role tidak mengubah atau menghapus patient profile.
7. Deaktivasi user tidak menghapus patient atau rekam medis.
8. Penggabungan person duplikat memerlukan workflow khusus dan audit.

## Source of truth

- Gate: identitas, tipe pengguna, status akun, atribut organisasi yang disepakati.
- POSKESTREN Health: patient number, health profile, clinical record, consent/communication record.
- Role/permission aplikasi: dikelola sesuai kontrak Gate dan kebutuhan klinis lokal.

## Risiko yang dicegah

- Guru atau staf tidak dapat dicatat sebagai pasien.
- Riwayat hilang saat akun dinonaktifkan.
- Admin manusia dikeluarkan dari patient eligibility.
- Perubahan nama membuat pasien baru.
- NIS/NIP digunakan sebagai primary key yang berubah.
