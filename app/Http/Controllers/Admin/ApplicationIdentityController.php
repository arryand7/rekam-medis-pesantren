<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateApplicationIdentityRequest;
use App\Services\ApplicationIdentityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;

class ApplicationIdentityController extends Controller
{
    public function edit(ApplicationIdentityService $identityService): View
    {
        Gate::authorize('manage-system-settings');

        return view('pages.admin.application-identity.edit', [
            'identity' => $identityService->get(),
        ]);
    }

    public function update(UpdateApplicationIdentityRequest $request, ApplicationIdentityService $identityService): RedirectResponse
    {
        $validated = $request->validated();
        $identityService->update(
            Arr::except($validated, ['logo', 'logo_dark', 'favicon']),
            [
                'logo' => $request->file('logo'),
                'logo_dark' => $request->file('logo_dark'),
                'favicon' => $request->file('favicon'),
            ]
        );

        return redirect()->route('admin.system.application-identity.edit')
            ->with('success', 'Identitas aplikasi berhasil diperbarui.');
    }

    public function reset(Request $request, ApplicationIdentityService $identityService): RedirectResponse
    {
        Gate::authorize('manage-system-settings');
        $request->validate(['confirm_reset' => ['required', 'accepted']]);

        $identityService->reset();

        return redirect()->route('admin.system.application-identity.edit')
            ->with('success', 'Identitas aplikasi dikembalikan ke default.');
    }
}
