<?php

use App\Http\Controllers\HealthController;
use App\Models\AuditLog;
use App\Models\MedicalVisit;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\ObservationEpisode;
use App\Models\ObservationHandover;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\StockLocation;
use App\Models\User;
use App\Services\ClinicalAssessmentService;
use App\Services\Gate\GateSyncDryRunService;
use App\Services\MedicalVisitService;
use App\Services\ObservationService;
use App\Services\PharmacyService;
use App\Services\VitalSignService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/health', HealthController::class)->name('health');

// Phase 1 Management Shell Routes

Route::get('/people', function () {
    $people = Person::latest()->paginate(15);

    return view('pages.people.index', compact('people'));
})->name('people.index');

Route::get('/patients', function () {
    $patients = Patient::with('person')->latest()->paginate(15);

    return view('pages.patients.index', compact('patients'));
})->name('patients.index');

Route::get('/patients/{id}', function (string $id) {
    $patient = Patient::with(['person', 'healthProfile', 'allergies', 'activeAllergies', 'medicalConditions', 'emergencyContacts'])->findOrFail($id);

    return view('pages.patients.show', compact('patient'));
})->name('patients.show');

Route::get('/users', function () {
    $users = User::with(['person', 'roles'])->latest()->paginate(15);

    return view('pages.users.index', compact('users'));
})->name('users.index');

Route::get('/roles', function () {
    $roles = Role::with('permissions')->get();
    $permissions = Permission::all();

    return view('pages.roles.index', compact('roles', 'permissions'));
})->name('roles.index');

Route::get('/gate-sync/preview', function (Request $request, GateSyncDryRunService $dryRunService) {
    $report = null;
    if ($request->has('run')) {
        $report = $dryRunService->execute();
    }

    return view('pages.gate-sync.preview', compact('report'));
})->name('gate-sync.preview');

Route::get('/gate-sync/conflicts', function () {
    return view('pages.gate-sync.conflicts');
})->name('gate-sync.conflicts');

Route::get('/audit-logs', function () {
    $auditLogs = AuditLog::latest()->paginate(20);

    return view('pages.audit-logs.index', compact('auditLogs'));
})->name('audit-logs.index');

// Phase 2A & 2B Medical Visit & Assessment Routes

Route::get('/visits', function () {
    $visits = MedicalVisit::with(['patient.person', 'receivingOfficer'])->latest()->paginate(15);

    return view('pages.visits.index', compact('visits'));
})->name('visits.index');

Route::get('/visits/create', function () {
    $patients = Patient::with('person')->where('is_eligible', true)->get();

    return view('pages.visits.create', compact('patients'));
})->name('visits.create');

Route::post('/visits', function (Request $request, MedicalVisitService $visitService) {
    $validated = $request->validate([
        'patient_id' => 'required|exists:patients,id',
        'chief_complaint' => 'required|string|min:3|max:1000',
        'reporting_type' => 'nullable|string',
        'reporting_name' => 'nullable|string|max:255',
        'origin_location' => 'nullable|string|max:255',
        'override_active' => 'nullable|boolean',
        'override_reason' => 'nullable|string|max:500',
    ]);

    try {
        $visit = $visitService->registerVisit($validated);

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Kunjungan medis berhasil didaftarkan. Silakan lakukan pengkajian.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.store');

Route::get('/visits/{id}', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'receivingOfficer', 'assignedOfficer', 'vitalSigns', 'latestAssessment', 'actions'])->findOrFail($id);

    return view('pages.visits.show', compact('visit'));
})->name('visits.show');

Route::post('/visits/{id}/cancel', function (string $id, Request $request, MedicalVisitService $visitService) {
    $request->validate([
        'cancellation_reason' => 'required|string|min:3|max:500',
    ]);

    $visit = MedicalVisit::findOrFail($id);

    try {
        $visitService->cancelVisit($visit, $request->input('cancellation_reason'));

        return redirect()->route('visits.show', $visit->id)->with('success', 'Kunjungan medis telah dibatalkan.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('visits.cancel');

// Phase 2B Clinical Assessment Workspace Routes

Route::get('/visits/{id}/assessment', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'patient.activeAllergies', 'vitalSigns', 'latestAssessment', 'actions'])->findOrFail($id);

    return view('pages.visits.assessment', compact('visit'));
})->name('visits.assessment');

Route::post('/visits/{id}/vital-signs', function (string $id, Request $request, VitalSignService $vitalService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'temperature_c' => 'nullable|numeric|between:30,45',
        'systolic_bp' => 'nullable|integer|between:40,250',
        'diastolic_bp' => 'nullable|integer|between:30,150',
        'pulse_bpm' => 'nullable|integer|between:30,220',
        'respiratory_rate' => 'nullable|integer|between:5,60',
        'spo2_percent' => 'nullable|integer|between:50,100',
        'weight_kg' => 'nullable|numeric|between:5,300',
        'height_cm' => 'nullable|numeric|between:50,250',
        'pain_score' => 'nullable|integer|between:0,10',
        'consciousness_level' => 'nullable|string',
        'notes' => 'nullable|string',
        'finalize' => 'nullable|boolean',
    ]);

    try {
        $vitalService->record($visit, $validated);

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Data tanda vital berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.vital-signs.store');

Route::post('/visits/{id}/assessment', function (string $id, Request $request, ClinicalAssessmentService $assessmentService, ObservationService $obsService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'history_current_illness' => 'required|string|min:3',
        'relevant_history' => 'nullable|string',
        'examination_findings' => 'required|string|min:3',
        'assessment_summary' => 'required|string|min:3',
        'working_diagnosis' => 'nullable|string',
        'disposition_recommendation' => 'required|string',
        'finalize' => 'nullable|boolean',
    ]);

    try {
        $assessment = $assessmentService->saveDraft($visit, $validated);

        if (! empty($request->input('finalize')) && $request->input('finalize') == '1') {
            $assessmentService->finalizeAssessment($assessment);

            // Auto start observation episode if disposition recommendation is observation_required or rest_at_poskestren
            if (in_array($validated['disposition_recommendation'], ['observation_required', 'rest_at_poskestren'])) {
                $episode = $obsService->startEpisode($visit, [
                    'reason' => 'Rekomendasi observasi dari pengkajian medis: '.$validated['assessment_summary'],
                    'location_label' => 'Ruang Observasi Poskestren',
                ]);

                return redirect()->route('observations.show', $episode->id)->with('success', 'Pengkajian medis selesai & episode observasi Poskestren telah dimulai.');
            }

            return redirect()->route('visits.show', $visit->id)->with('success', 'Pengkajian klinis medis telah difinalisasi.');
        }

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Draft pengkajian klinis medis disimpan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.assessment.store');

Route::post('/visits/{id}/actions', function (string $id, Request $request, ClinicalAssessmentService $assessmentService) {
    $visit = MedicalVisit::findOrFail($id);

    $validated = $request->validate([
        'action_type' => 'required|string',
        'description' => 'required|string|min:3',
        'notes' => 'nullable|string',
    ]);

    try {
        $assessmentService->recordAction($visit, $validated);

        return redirect()->route('visits.assessment', $visit->id)->with('success', 'Tindakan awal non-obat berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.actions.store');

// Phase 2C Observation Workspace Routes

Route::get('/observations', function () {
    $episodes = ObservationEpisode::with(['medicalVisit.patient.person', 'responsibleOfficer'])->latest()->paginate(15);

    return view('pages.observations.index', compact('episodes'));
})->name('observations.index');

Route::get('/observations/{id}', function (string $id) {
    $episode = ObservationEpisode::with(['medicalVisit.patient.person', 'medicalVisit.patient.activeAllergies', 'responsibleOfficer', 'records.recordedBy', 'handovers.fromUser', 'handovers.toUser'])->findOrFail($id);
    $medicalUsers = User::where('is_active', true)->get();

    return view('pages.observations.show', compact('episode', 'medicalUsers'));
})->name('observations.show');

Route::post('/observations/{id}/monitoring', function (string $id, Request $request, ObservationService $obsService) {
    $episode = ObservationEpisode::findOrFail($id);

    $validated = $request->validate([
        'condition_summary' => 'required|string|min:3',
        'symptom_changes' => 'nullable|string',
        'general_condition' => 'nullable|string',
    ]);

    try {
        $obsService->recordMonitoring($episode, $validated);

        return redirect()->route('observations.show', $episode->id)->with('success', 'Lembar pemantauan berkala berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('observations.monitoring.store');

Route::post('/observations/{id}/handover', function (string $id, Request $request, ObservationService $obsService) {
    $episode = ObservationEpisode::findOrFail($id);

    $validated = $request->validate([
        'to_user_id' => 'nullable|exists:users,id',
        'summary' => 'required|string|min:3',
        'current_condition' => 'required|string|min:3',
        'pending_tasks' => 'nullable|string',
    ]);

    try {
        $obsService->submitHandover($episode, $validated);

        return redirect()->route('observations.show', $episode->id)->with('success', 'Handover shift jaga berhasil diajukan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('observations.handover.store');

Route::post('/handovers/{id}/acknowledge', function (string $id, ObservationService $obsService) {
    $handover = ObservationHandover::findOrFail($id);

    try {
        $obsService->acknowledgeHandover($handover);

        return redirect()->route('observations.show', $handover->observation_episode_id)->with('success', 'Handover shift disetujui & penanggung jawab observasi berhasil dialihkan.');
    } catch (Exception $e) {
        return redirect()->back()->with('error', $e->getMessage());
    }
})->name('observations.handover.acknowledge');

Route::post('/observations/{id}/complete', function (string $id, Request $request, ObservationService $obsService) {
    $episode = ObservationEpisode::findOrFail($id);

    $validated = $request->validate([
        'outcome' => 'required|string',
        'outcome_reason' => 'required|string|min:3',
    ]);

    try {
        $obsService->completeEpisode($episode, $validated['outcome'], $validated['outcome_reason']);

        return redirect()->route('observations.show', $episode->id)->with('success', 'Episode observasi Poskestren telah diselesaikan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('observations.complete');

// Phase 2D1 Pharmacy Foundation Routes

Route::get('/pharmacy/medicines', function () {
    $medicines = Medicine::latest()->paginate(15);

    return view('pages.pharmacy.medicines.index', compact('medicines'));
})->name('pharmacy.medicines.index');

Route::post('/pharmacy/medicines', function (Request $request, PharmacyService $pharmacyService) {
    $validated = $request->validate([
        'code' => 'required|string|unique:medicines,code',
        'generic_name' => 'required|string|min:2',
        'brand_name' => 'nullable|string',
        'dosage_form' => 'required|string',
        'strength_text' => 'nullable|string',
        'base_unit' => 'required|string',
        'minimum_stock' => 'required|integer|min:1',
    ]);

    try {
        $pharmacyService->createMedicine($validated);

        return redirect()->route('pharmacy.medicines.index')->with('success', 'Master obat baru berhasil didaftarkan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('pharmacy.medicines.store');

Route::get('/pharmacy/inventory', function () {
    $batches = MedicineBatch::with(['medicine', 'location'])->where('status', '!=', 'entered_in_error')->latest()->paginate(15);

    return view('pages.pharmacy.inventory.index', compact('batches'));
})->name('pharmacy.inventory.index');

Route::get('/pharmacy/receipt/create', function () {
    $medicines = Medicine::where('is_active', true)->get();
    $locations = StockLocation::where('is_active', true)->get();

    return view('pages.pharmacy.receipt.create', compact('medicines', 'locations'));
})->name('pharmacy.receipt.create');

Route::post('/pharmacy/receipt', function (Request $request, PharmacyService $pharmacyService) {
    $validated = $request->validate([
        'medicine_id' => 'required|exists:medicines,id',
        'stock_location_id' => 'required|exists:stock_locations,id',
        'batch_number' => 'required|string|min:2',
        'expiry_date' => 'nullable|date',
        'quantity' => 'required|integer|min:1',
        'supplier_name' => 'nullable|string',
        'reason' => 'nullable|string',
    ]);

    try {
        $pharmacyService->receiveStock($validated);

        return redirect()->route('pharmacy.inventory.index')->with('success', 'Penerimaan stok obat baru berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('pharmacy.receipt.store');

Route::get('/pharmacy/adjustments/create', function () {
    $batches = MedicineBatch::with(['medicine', 'location'])->where('status', 'active')->get();

    return view('pages.pharmacy.adjustments.create', compact('batches'));
})->name('pharmacy.adjustments.create');

Route::post('/pharmacy/adjustments', function (Request $request, PharmacyService $pharmacyService) {
    $validated = $request->validate([
        'medicine_batch_id' => 'required|exists:medicine_batches,id',
        'movement_type' => 'required|in:adjustment_in,adjustment_out',
        'quantity' => 'required|integer|min:1',
        'reason' => 'required|string|min:3',
    ]);

    try {
        $pharmacyService->adjustStock($validated);

        return redirect()->route('pharmacy.inventory.index')->with('success', 'Penyesuaian stok opname berhasil dicatat.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('pharmacy.adjustments.store');
