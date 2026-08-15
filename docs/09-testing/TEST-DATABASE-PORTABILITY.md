---
id: DOC-TEST-PORTABILITY
title: "Test Database Portability Specification"
status: active
owner: "QA Lead POSKESTREN"
last_updated: 2026-08-13
---

# Test Database Portability Specification

Dokumen ini mendokumentasikan standar konfigurasi database pengujian otomatis pada SABIRA POSKESTREN Health untuk memastikan portabilitas antara lingkungan lokal (macOS / XAMPP / Homebrew) dan lingkungan Continuous Integration (Linux / Docker / GitHub Actions).

```text
ENVIRONMENT=LOCAL-DEVELOPMENT
TEST_DB_NAME=poskestren_health_test
DEFAULT_CONNECTION=mysql (Standard TCP 127.0.0.1:3306)
```

---

## 1. Prinsip Portabilitas Konfigurasi

1. **Clean `phpunit.xml` Defaults**:
   - `phpunit.xml` tidak boleh menyimpan absolute path direktori developer atau socket lokal spesifik.
   - Nilai default dalam `phpunit.xml` menggunakan parameter standar portabel:
     ```xml
     <env name="APP_ENV" value="testing"/>
     <env name="DB_CONNECTION" value="mysql"/>
     <env name="DB_HOST" value="127.0.0.1"/>
     <env name="DB_PORT" value="3306"/>
     <env name="DB_DATABASE" value="poskestren_health_test"/>
     <env name="DB_USERNAME" value="root"/>
     <env name="DB_PASSWORD" value=""/>
     ```
2. **Dynamic Socket / Port Injection**:
   - Pengembang dengan konfigurasi soket UNIX non-standar (misalnya XAMPP di macOS) dapat menginjeksikan soket atau port melalui variabel lingkungan saat mengeksekusi test tanpa mengubah repository:
     ```bash
     DB_SOCKET=<LOCAL_MARIADB_SOCKET> APP_ENV=testing php artisan test
     ```
3. **CI / CD Environment Compatibility**:
   - Pada runner Linux / GitHub Actions / Docker, service container MySQL/MariaDB berjalan pada `127.0.0.1:3306` dan dapat langsung menjalankan `php artisan test` tanpa konfigurasi tambahan.

---

## 2. Verifikasi Eksekusi

Uji eksekusi pengujian dengan injeksi runtime variabel lingkungan:
```bash
DB_SOCKET=<LOCAL_MARIADB_SOCKET> APP_ENV=testing php artisan test tests/Feature/Ui/Phase5BClinicalWorkflowContinuityTest.php
```
Status: **PASSED (7/7 tests passed)**.
