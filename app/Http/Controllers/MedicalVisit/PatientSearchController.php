<?php

namespace App\Http\Controllers\MedicalVisit;

use App\Http\Controllers\Controller;
use App\Queries\MedicalVisit\PatientSearchQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PatientSearchController extends Controller
{
    public function __invoke(Request $request, PatientSearchQuery $patientSearch): JsonResponse
    {
        Gate::authorize('create-medical-visits');

        $validator = Validator::make($request->query(), [
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Kata pencarian pasien tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        return response()->json([
            'data' => $patientSearch->search($validator->validated()['q']),
        ]);
    }
}
