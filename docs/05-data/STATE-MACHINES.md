---
id: DOC-STATE-MACHINES
title: "State Machines"
status: draft
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-05
---

# State Machines

## MedicalVisit

```text
registered
  -> waiting_assessment
  -> under_assessment
  -> initial_treatment
  -> under_observation
  -> referral_prepared
  -> referred_external
  -> returned_from_referral
  -> discharge_prepared
  -> discharged
```

Transisi tambahan terkontrol: `cancelled`, `reopened`.

## ObservationEpisode

`planned -> active -> completed | transferred | cancelled`

## Referral

`draft -> prepared -> departed -> at_facility -> returned -> completed | cancelled`

## MedicationAdministration

`scheduled -> administered | held | refused | missed | cancelled`

## Assessment

`draft -> finalized -> amended`

## Aturan implementasi

- Transisi melalui Action.
- Policy diperiksa.
- Prasyarat divalidasi.
- Event dibuat setelah sukses.
- Audit menyimpan source dan destination state.
- UI tidak boleh mengirim state tujuan tanpa server memvalidasi transisi.

## ClinicalConsultation

`draft -> ready -> sent -> acknowledged -> responded -> completed`

Alternatif:
- `cancelled`
- `expired`
- `superseded`
- `superseded_by_referral`

## GateSyncRun

`created -> fetching -> validating -> preview_ready -> applying -> completed`

Failure:
- `partially_failed`
- `failed`
- `cancelled`
