---
id: DOC-VISIT-LIFECYCLE
title: "Lifecycle Status Kunjungan"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-01
---

# Lifecycle Status Kunjungan

## Status yang diusulkan

| Status | Makna |
|---|---|
| `registered` | Kunjungan telah dibuat. |
| `waiting_assessment` | Menunggu assessment. |
| `under_assessment` | Sedang diperiksa. |
| `initial_treatment` | Tindakan awal sedang/selesai diberikan. |
| `under_observation` | Santri berada dalam observasi. |
| `referral_prepared` | Persiapan rujukan dilakukan. |
| `referred_external` | Santri telah berangkat/berada di fasilitas rujukan. |
| `returned_from_referral` | Santri telah kembali dan perlu tindak lanjut. |
| `discharge_prepared` | Instruksi akhir sedang disiapkan. |
| `discharged` | Kunjungan selesai. |
| `cancelled` | Kunjungan dibatalkan dengan alasan. |

## Prinsip transisi

- Hanya transisi yang didefinisikan yang diperbolehkan.
- Setiap transisi menyimpan actor, waktu, alasan, dan source state.
- Status final tidak diedit langsung.
- Reopen memerlukan permission dan alasan.
- Side effect dijalankan melalui event dan queue bila tidak kritis.

## [PERLU DIKONFIRMASI]

- Apakah `reported` dan `en_route_to_poskestren` perlu menjadi status aplikasi.
- Apakah santri dapat langsung pulang setelah registrasi tanpa assessment.
- Apakah rujukan darurat boleh melewati status persiapan.
