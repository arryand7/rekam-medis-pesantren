---
id: DOC-API-ENDPOINTS
title: "Rancangan Endpoint API"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Rancangan Endpoint API

## Students
- `GET /api/v1/students`
- `GET /api/v1/students/{student}`
- `GET /api/v1/students/{student}/health-profile`

## Visits
- `GET /api/v1/visits`
- `POST /api/v1/visits`
- `GET /api/v1/visits/{visit}`
- `POST /api/v1/visits/{visit}/vitals`
- `POST /api/v1/visits/{visit}/assessments`
- `POST /api/v1/visits/{visit}/transitions`
- `POST /api/v1/visits/{visit}/discharge`

## Observations
- `POST /api/v1/visits/{visit}/observations`
- `POST /api/v1/observations/{observation}/records`
- `POST /api/v1/observations/{observation}/complete`

## Medication
- `POST /api/v1/visits/{visit}/medication-administrations`
- `POST /api/v1/medication-administrations/{administration}/cancel`

## Referrals
- `POST /api/v1/visits/{visit}/referrals`
- `PATCH /api/v1/referrals/{referral}/status`
- `POST /api/v1/referrals/{referral}/return`

## Admin
- Endpoint role, permission, audit, dan sync hanya untuk use case yang disetujui.

Daftar ini adalah kontrak awal, bukan izin untuk langsung mengimplementasikan seluruh endpoint.

## Identity sync

- `POST /api/v1/admin/gate-sync/preview`
- `POST /api/v1/admin/gate-sync/apply`
- `GET /api/v1/admin/gate-sync/runs`
- `GET /api/v1/admin/gate-sync/runs/{run}`
- `POST /api/v1/admin/identity-conflicts/{conflict}/resolve`

## Clinical consultations

- `GET /api/v1/clinical-consultations`
- `POST /api/v1/visits/{visit}/clinical-consultations`
- `GET /api/v1/clinical-consultations/{consultation}`
- `POST /api/v1/clinical-consultations/{consultation}/finalize`
- `POST /api/v1/clinical-consultations/{consultation}/send`
- `POST /api/v1/clinical-consultations/{consultation}/external-advices`
- `POST /api/v1/clinical-consultations/{consultation}/complete`
