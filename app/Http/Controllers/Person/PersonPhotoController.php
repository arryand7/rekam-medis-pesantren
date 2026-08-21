<?php

namespace App\Http\Controllers\Person;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersonPhotoController extends Controller
{
    /**
     * Serve foto profil person yang disimpan di private storage.
     * Hanya dapat diakses oleh pengguna yang terautentikasi dan berwenang.
     */
    public function __invoke(Request $request, Person $person): StreamedResponse
    {
        Gate::authorize('view-patient-profile');

        if (! $person->photo_path) {
            abort(404, 'Foto profil tidak tersedia.');
        }

        if (! Storage::disk('person_photos')->exists($person->photo_path)) {
            abort(404, 'File foto tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($person->photo_path, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];
        $mimeType = $mimeTypes[$ext] ?? 'image/jpeg';

        return Storage::disk('person_photos')->response(
            $person->photo_path,
            null,
            [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
