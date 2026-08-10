---
id: DOC-PROD-MONITORING-BASELINE
title: "Production Monitoring Baseline, Service Level Indicators, and Incident Thresholds"
status: ACTIVE
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Monitoring Baseline, Service Level Indicators, and Incident Thresholds

## 1. Indikator Kinerja Layanan (*Service Level Indicators - SLI*)

| Komponen / Metrik | Nilai Normal (*Baseline*) | Ambang Peringatan (*Warning*) | Ambang Kritis (*Critical Alert*) |
|---|---|---|---|
| **HTTP 5xx Error Rate** | 0.00% | > 0.5% dalam 5 menit | > 1.0% dalam 2 menit |
| **HTTP Response Time (p95)** | < 100ms | > 300ms | > 1000ms |
| **Gate SSO Login Failures** | 0 gagal / jam | > 5 gagal / 15 menit | > 15 gagal / 15 menit |
| **Queue Pending Jobs** | 0 jobs | > 25 jobs tertunda | > 100 jobs atau worker mati |
| **Outbox Dead-Letter Count** | 0 events | > 0 events | > 5 events |
| **Outbox Max Pending Age** | < 10 detik | > 60 detik | > 300 detik |
| **Database Connections** | < 10 koneksi | > 50 koneksi | > 80 koneksi |
| **Database Lock Wait Timeout** | 0 per hari | > 1 per jam | > 5 per jam |
| **Disk Storage Bebas** | > 40 GB | < 10 GB | < 5 GB |
| **Usia Backup Terakhir** | < 24 jam | > 26 jam | > 30 jam |

---

## 2. Saluran & Eskalasi Notifikasi Monitoring

1. **Peringatan Tingkat Warning**: Dicatat pada log aplikasi dan dikirimkan via kanal monitoring internal untuk diinspeksi dalam 2 jam kerja.
2. **Peringatan Tingkat Kritis**: Memerlukan respons seketika (< 15 menit) oleh On-Call SRE / System Administrator dan pelaporan ke Tim Pengembang.
