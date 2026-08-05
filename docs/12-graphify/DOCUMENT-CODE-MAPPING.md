---
id: DOC-DOC-CODE-MAP
title: "Pemetaan Dokumen ke Kode"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Pemetaan Dokumen ke Kode

## Phase 2D2 — Medication Orders, Medication Administration & Atomic Stock Issue Mapping

```text
MEDICATION-ADMINISTRATION.md & SAFETY-ACKNOWLEDGEMENTS.md & STATE-MACHINES.md
  -> database/migrations/2026_08_05_002300_create_medication_orders_table.php
  -> database/migrations/2026_08_05_002400_create_medication_safety_acknowledgements_table.php
  -> database/migrations/2026_08_05_002500_create_medication_administrations_table.php
  -> app/Models/MedicationOrder.php
  -> app/Models/MedicationSafetyAcknowledgement.php
  -> app/Models/MedicationAdministration.php
  -> app/Models/MedicalVisit.php
  -> app/Services/MedicationService.php
  -> app/Policies/MedicationOrderPolicy.php
  -> app/Policies/MedicationAdministrationPolicy.php
  -> resources/views/pages/visits/medications.blade.php
  -> tests/Feature/Medication/MedicationOrderTest.php
  -> tests/Feature/Medication/MedicationAdministrationTest.php
  -> tests/Feature/Medication/MedicationStatusTest.php
  -> tests/Feature/Medication/MedicationReversalTest.php
```

## Phase 2D1 — Pharmacy Inventory Foundation & Stock Ledger Mapping

```text
MEDICATION-ADMINISTRATION.md & FUNCTIONAL-REQUIREMENTS.md & DATA-DICTIONARY.md
  -> database/migrations/2026_08_05_001900_create_medicines_table.php
  -> database/migrations/2026_08_05_002000_create_stock_locations_table.php
  -> database/migrations/2026_08_05_002100_create_medicine_batches_table.php
  -> database/migrations/2026_08_05_002200_create_stock_movements_table.php
  -> app/Models/Medicine.php
  -> app/Models/StockLocation.php
  -> app/Models/MedicineBatch.php
  -> app/Models/StockMovement.php
  -> app/Services/PharmacyService.php
  -> app/Policies/MedicinePolicy.php
  -> app/Policies/MedicineBatchPolicy.php
  -> app/Policies/StockMovementPolicy.php
  -> app/Policies/StockLocationPolicy.php
  -> resources/views/pages/pharmacy/medicines/index.blade.php
  -> resources/views/pages/pharmacy/inventory/index.blade.php
  -> resources/views/pages/pharmacy/receipt/create.blade.php
  -> resources/views/pages/pharmacy/adjustments/create.blade.php
  -> tests/Feature/Pharmacy/MedicineMasterTest.php
  -> tests/Feature/Pharmacy/StockReceiptTest.php
  -> tests/Feature/Pharmacy/StockAdjustmentTest.php
  -> tests/Feature/Pharmacy/StockReversalTest.php
```

## Phase 2C — POSKESTREN Observation, Periodic Monitoring & Shift Handover Mapping

```text
OBSERVATION-AND-CARE.md & VISIT-STATUS-LIFECYCLE.md & STATE-MACHINES.md
  -> database/migrations/2026_08_05_001600_create_observation_episodes_table.php
  -> database/migrations/2026_08_05_001700_create_observation_records_table.php
  -> database/migrations/2026_08_05_001800_create_observation_handovers_table.php
  -> app/Models/ObservationEpisode.php
  -> app/Models/ObservationRecord.php
  -> app/Models/ObservationHandover.php
  -> app/Models/MedicalVisit.php
  -> app/Services/ObservationService.php
  -> app/Policies/ObservationEpisodePolicy.php
  -> app/Policies/ObservationRecordPolicy.php
  -> app/Policies/ObservationHandoverPolicy.php
  -> resources/views/pages/observations/index.blade.php
  -> resources/views/pages/observations/show.blade.php
  -> tests/Feature/Observation/ObservationEpisodeTest.php
  -> tests/Feature/Observation/ObservationMonitoringTest.php
  -> tests/Feature/Observation/ObservationHandoverTest.php
  -> tests/Feature/Observation/ObservationOutcomeTest.php
```

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
