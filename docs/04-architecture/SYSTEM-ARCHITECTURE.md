---
id: DOC-SYSTEM-ARCH
title: "Arsitektur Sistem"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Arsitektur Sistem

```mermaid
flowchart LR
    U[Browser Pengguna] --> W[Nginx]
    W --> A[Laravel + Livewire]
    A --> DB[(MariaDB)]
    A --> R[(Redis)]
    A --> FS[Private File Storage]
    A --> Q[Queue Worker]
    Q --> EXT[Gate / SSS / Absensi / Notification]
```

## Gaya arsitektur

Modular monolith dengan satu deployment utama. Modul domain memiliki boundary, action, policy, event, model, dan test masing-masing.

## Trust boundary

- Browser tidak dipercaya.
- Integrasi eksternal harus diautentikasi.
- Database adalah sumber kebenaran transaksi aplikasi.
- File medis tidak disajikan sebagai URL publik.
- Queue tidak boleh kehilangan context authorization/audit.

## Availability

MVP dioptimalkan untuk jaringan internal dan akses aman dari luar bila disediakan. Mode offline belum termasuk MVP.

## External clinical consultation boundary

```mermaid
flowchart LR
    V[Visit and Assessment] --> C[Clinical Consultation Module]
    C --> D[Private Summary Document]
    C --> X[Approved Secure Channel]
    X --> H[Puskesmas or Hospital]
    H --> R[Attributed External Advice]
    R --> C
    C --> L[Local Clinical Decision]
```

Puskesmas/rumah sakit diperlakukan sebagai trust boundary eksternal. Tidak ada akses langsung ke database internal.
