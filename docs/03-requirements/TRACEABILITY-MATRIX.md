---
id: DOC-TRACEABILITY
title: "Traceability Matrix"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# Traceability Matrix

| Requirement | Business Rule | Workflow | Implementation Target | Test Target | Status |
|---|---|---|---|---|---|
| FR-VISIT-001 | BR-002, BR-003, BR-005 | WF-ADMISSION-001 | `RegisterMedicalVisitAction` | `RegisterMedicalVisitTest` | Planned |
| FR-VISIT-002 | BR-004 | WF-ADMISSION-001 | `ActiveVisitGuard` | `DuplicateActiveVisitTest` | Planned |
| FR-VITAL-001 | BR-007, BR-008 | WF-ASSESS-001 | `RecordVitalSignsAction` | `RecordVitalSignsTest` | Planned |
| FR-ASSESS-001 | BR-009, BR-010 | WF-ASSESS-001 | `FinalizeAssessmentAction` | `FinalizeAssessmentTest` | Planned |
| FR-OBS-001 | BR-011, BR-012 | WF-OBS-001 | `StartObservationAction` | `StartObservationTest` | Planned |
| FR-MED-003 | BR-014, BR-015, BR-016 | WF-MED-001 | `AdministerMedicationAction` | `AdministerMedicationTest` | Planned |
| FR-REF-001 | BR-018, BR-019 | WF-REF-001 | `CreateReferralAction` | `CreateReferralTest` | Planned |
| FR-DIS-001 | BR-013 | WF-DISCHARGE-001 | `DischargeVisitAction` | `DischargeVisitTest` | Planned |
| FR-AUDIT-001 | BR-024 | Semua | `MedicalAuditService` | `MedicalAuditTest` | Planned |
| FR-THEME-001 | NFR-THEME-001 | UI | `ThemePreference` | `ThemePreferenceTest` | Planned |

Matrix ini harus diperbarui setiap requirement berubah atau diimplementasikan.

| FR-GATE-001 | BR-031, BR-032, BR-033 | WF-GATE-SYNC-001 | `SyncGateUsersAction` | `SyncGateUsersTest` | Planned |
| FR-PERSON-001 | BR-034, BR-035 | WF-GATE-SYNC-001 | `Person`, `User`, `Patient` | `PersonPatientSeparationTest` | Planned |
| FR-PATIENT-003 | BR-037 | WF-ADMISSION-001 | `PatientEligibilityService` | `AdminHumanPatientEligibilityTest` | Planned |
| FR-CONSULT-001 | BR-040, BR-041 | WF-CONSULT-001 | `CreateClinicalConsultationAction` | `CreateClinicalConsultationTest` | Planned |
| FR-CONSULT-005 | BR-044, BR-045 | WF-CONSULT-001 | `RecordExternalAdviceAction` | `RecordExternalAdviceTest` | Planned |
| FR-CONSULT-008 | BR-046, BR-047 | WF-CONSULT-001 | `ClinicalConsultationAudit` | `ClinicalConsultationAuditTest` | Planned |
