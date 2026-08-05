---
id: DOC-DOC-CODE-MAP
title: "Pemetaan Dokumen ke Kode"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Pemetaan Dokumen ke Kode

## Phase 0 — Foundation & Theme Engine Mapping

```text
ADR-005 (Theme Strategy) & LIGHT-DARK-THEME.md
  -> DESIGN-SYSTEM.md
  -> resources/css/app.css (Semantic Tokens)
  -> resources/js/theme-switcher.js (Anti-Flicker & Persistence)
  -> resources/views/components/theme-switcher.blade.php
  -> resources/views/layouts/app.blade.php (App Shell)
  -> app/View/Components/AppLayout.php
  -> app/Http/Controllers/HealthController.php
  -> tests/Feature/ThemePreferenceTest.php
  -> tests/Feature/DashboardTest.php
  -> tests/Feature/HealthCheckTest.php
```

## Template capability

```text
BR-002
  -> FR-VISIT-001
  -> UC-VISIT-001
  -> WF-ADMISSION-001
  -> SCR-VIS-002
  -> RegisterMedicalVisitAction
  -> MedicalVisitPolicy
  -> medical_visits
  -> MedicalVisitRegistered
  -> RegisterMedicalVisitTest
```

## Komentar kode

Jangan memenuhi kode dengan komentar ID. Gunakan ID pada:
- nama test atau dataset,
- PHPDoc class use case bila membantu,
- commit/PR,
- traceability matrix,
- event metadata bila diperlukan.

## Update wajib

Ketika class atau tabel berubah, perbarui:
- requirement,
- traceability matrix,
- data dictionary,
- test matrix,
- Graphify graph.

## Gate mapping

```text
BR-031
  -> FR-GATE-001
  -> WF-GATE-SYNC-001
  -> SyncGateUsersAction
  -> Person
  -> PatientEligibilityService
  -> GateUserSynced
  -> SyncGateUsersTest
```

## Consultation mapping

```text
BR-040
  -> FR-CONSULT-001
  -> WF-CONSULT-001
  -> CreateClinicalConsultationAction
  -> ClinicalConsultation
  -> ConsultationVersion
  -> ClinicalConsultationPolicy
  -> CreateClinicalConsultationTest
```
