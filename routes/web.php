<?php

use App\Http\Controllers\HealthController;
use App\Models\AuditLog;
use App\Models\MedicalVisit;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Person;
use App\Models\Role;
use App\Models\User;
use App\Services\Gate\GateSyncDryRunService;
use App\Services\MedicalVisitService;
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

// Phase 2A Medical Visit Intake Routes

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

        return redirect()->route('visits.show', $visit->id)->with('success', 'Kunjungan medis berhasil didaftarkan.');
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }
})->name('visits.store');

Route::get('/visits/{id}', function (string $id) {
    $visit = MedicalVisit::with(['patient.person', 'receivingOfficer', 'assignedOfficer'])->findOrFail($id);

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
