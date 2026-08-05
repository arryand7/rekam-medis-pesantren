---
id: DOC-DOC-CODE-MAP
title: "Pemetaan Dokumen ke Kode"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Pemetaan Dokumen ke Kode

## Phase 2B — Vital Signs, Clinical Assessment & Disposition Recommendation Mapping

```text
ADR-006 & INITIAL-ASSESSMENT.md & VISIT-STATUS-LIFECYCLE.md & STATE-MACHINES.md
  -> database/migrations/2026_08_05_001200_refactor_patient_allergies_split_status_fields.php
  -> database/migrations/2026_08_05_001300_create_vital_signs_table.php
  -> database/migrations/2026_08_05_001400_create_clinical_assessments_table.php
  -> database/migrations/2026_08_05_001500_create_clinical_actions_table.php
  -> app/Models/VitalSign.php
  -> app/Models/ClinicalAssessment.php
  -> app/Models/ClinicalAction.php
  -> app/Services/VitalSignService.php
  -> app/Services/ClinicalAssessmentService.php
  -> app/Policies/VitalSignPolicy.php
  -> app/Policies/ClinicalAssessmentPolicy.php
  -> app/Policies/ClinicalActionPolicy.php
  -> resources/views/pages/visits/assessment.blade.php
  -> tests/Feature/VitalSign/VitalSignTest.php
  -> tests/Feature/ClinicalAssessment/ClinicalAssessmentTest.php
  -> tests/Feature/ClinicalAction/ClinicalActionTest.php
```

## Phase 2A — Patient Health Profile & Medical Visit Intake Mapping

```text
ADR-006 & PERSON-PATIENT-IDENTITY.md & HEALTH-PROFILES & POSKESTREN-ADMISSION.md
  -> database/migrations/2026_08_05_000600_create_patient_health_profiles_table.php
  -> database/migrations/2026_08_05_000700_create_patient_allergies_table.php
  -> database/migrations/2026_08_05_000800_create_patient_medical_conditions_table.php
  -> database/migrations/2026_08_05_000900_create_patient_emergency_contacts_table.php
  -> database/migrations/2026_08_05_001100_create_medical_visits_table.php
  -> app/Models/PatientHealthProfile.php
  -> app/Models/PatientAllergy.php
  -> app/Models/PatientMedicalCondition.php
  -> app/Models/PatientEmergencyContact.php
  -> app/Models/MedicalVisit.php
  -> app/Services/MedicalVisitService.php
  -> app/Policies/PatientHealthProfilePolicy.php
  -> app/Policies/PatientAllergyPolicy.php
  -> app/Policies/MedicalVisitPolicy.php
  -> resources/views/pages/patients/show.blade.php
  -> resources/views/pages/visits/index.blade.php
  -> resources/views/pages/visits/create.blade.php
  -> resources/views/pages/visits/show.blade.php
  -> tests/Feature/HealthProfile/PatientHealthProfileTest.php
  -> tests/Feature/MedicalVisit/MedicalVisitIntakeTest.php
```

## Phase 1 — Identity, Access Control, Gate Contract & Dry-Run Sync Mapping

```text
ADR-006 (Person Patient Separation) & PERSON-PATIENT-IDENTITY.md
  -> IDENTITY-AND-PATIENT-MODEL.md
  -> GATE-USER-SYNC-CONTRACT.md
  -> ACCESS-CONTROL-MATRIX.md & AUDIT-LOG.md
  -> database/migrations/0000_01_01_000000_create_people_table.php
  -> database/migrations/0001_01_01_000000_create_users_table.php
  -> database/migrations/2026_08_05_000300_create_patients_table.php
  -> database/migrations/2026_08_05_000400_create_roles_and_permissions_tables.php
  -> database/migrations/2026_08_05_000500_create_audit_logs_table.php
  -> app/Models/Person.php
  -> app/Models/User.php
  -> app/Models/Patient.php
  -> app/Models/Role.php
  -> app/Models/Permission.php
  -> app/Models/AuditLog.php
  -> app/Policies/UserPolicy.php
  -> app/Policies/PersonPolicy.php
  -> app/Policies/PatientPolicy.php
  -> app/Policies/GateSyncPolicy.php
  -> app/Policies/AuditLogPolicy.php
  -> app/Services/AuditLogService.php
  -> app/Contracts/GateClientContract.php
  -> app/DTOs/GateUserDTO.php
  -> app/Services/Gate/FakeGateClientService.php
  -> app/Services/Gate/GateSyncDryRunService.php
  -> tests/Feature/Identity/PersonUserPatientTest.php
  -> tests/Feature/Authorization/PolicyAccessTest.php
  -> tests/Feature/GateSync/DryRunSyncTest.php
  -> tests/Feature/Audit/AuditLogTest.php
```

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
