---
id: DOC-PROD-AUTH-EXPOSURE-REVIEW
title: "Production Authentication Historical Exposure & Log Review"
status: REVIEWED
owner: "Ryand Arifriantoni"
last_updated: 2026-08-10
---

# Production Authentication Historical Exposure & Log Review

## 1. Lingkup & Metodologi Evaluasi Log

Evaluasi dilakukan terhadap seluruh access log dan audit log aplikasi dari saat cutover produksi dinyatakan aktif hingga penerapan hotfix `58e6205`.

### Titik Audit:
- Akses request ke `GET /` dan `GET /dashboard`
- Akses request ke `GET /patients`, `GET /patients/{id}`
- Akses request ke `GET /visits`, `GET /observations`, `GET /pharmacy/*`, `GET /reports`
- Status response code (HTTP 200 vs 302/403/401)
- Pola request mencurigakan (*crawlers, external IP anomalies, brute-force, bulk harvesting*)

---

## 2. Temuan Audit Log & Klasifikasi Paparan

1. **Akses Root (`/`)**:
   - Selama jendela waktu sebelum hotfix, request ke rute root `/` menghasilkan HTTP 200 yang merender template dasar visual (*dashboard shell*) tanpa menampilkan data rekam medis pasien riil secara otomatis.
2. **Akses Rute Data Pasien (`/patients`)**:
   - Akses data pasien pada basis data produksi riil yang berisi record pasien telah diperiksa. Tidak ditemukan anomali transfer payload data dalam volume besar (*bulk extraction/scraping*).
3. **Privasi Identitas Pengguna**:
   - Tidak ada kebocoran kredensial, token OIDC, atau password hash pada log aplikasi.
4. **Klasifikasi Resmi Paparan**:
   - **`SENSITIVE-DATA-EXPOSURE-NOT-FOUND`** (dengan catatan `UI-SHELL-ONLY-EXPOSURE` pada initial landing root route sebelum diproteksi middleware).

---

## 3. Rekomendasi Lanjutan & Tindakan Pencegahan

1. Pertahankan automated security regression test (`AuthenticationRuntimeAuditAndProtectionTest.php`) di setiap pipeline integrasi.
2. Wajibkan validasi `curl -skI` tanpa cookie sebagai checklist verifikasi pra-rilis wajib pada setiap cutover berikutnya.
3. Seluruh rute aplikasi baru wajib didaftarkan di dalam blok `Route::middleware('auth')` secara default.
