---
id: DOC-CONTRIBUTING
title: "Panduan Kontribusi"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Panduan Kontribusi

## Branch

- `main`: production-ready.
- `develop`: integrasi fitur bila digunakan.
- `feature/<nama-singkat>`: fitur.
- `fix/<nama-singkat>`: perbaikan.
- `docs/<nama-singkat>`: dokumentasi.

## Commit

Gunakan format:

```text
type(scope): deskripsi singkat
```

Contoh:

```text
feat(visits): add medical visit registration
test(referrals): cover unauthorized referral creation
docs(domain): clarify observation discharge rules
```

## Pull request

Pull request harus mencantumkan:

- Requirement ID.
- Business Rule ID.
- Ringkasan implementasi.
- Dampak schema.
- Dampak keamanan.
- Test dan hasilnya.
- Screenshot light/dark untuk perubahan UI.
- Dokumentasi yang diperbarui.
- Risiko deployment dan rollback.

## Coding quality

- Jalankan formatter.
- Jalankan static analysis bila dikonfigurasi.
- Jalankan seluruh test terkait.
- Jangan memasukkan credential.
- Jangan memasukkan data pasien nyata sebagai fixture.
- Gunakan data sintetis pada seeder dan test.

## Dokumentasi

Perubahan perilaku sistem tanpa pembaruan dokumentasi dianggap belum selesai.
