<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSsoConfigurationRequest;
use App\Services\SsoConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SsoConfigurationController extends Controller
{
    public function edit(SsoConfigurationService $configurationService): View
    {
        Gate::authorize('manage-sso-settings');

        return view('pages.admin.sso-configuration.edit', [
            'settings' => $configurationService->forForm(),
            'suggestedCallback' => route('auth.gate.callback'),
        ]);
    }

    public function update(UpdateSsoConfigurationRequest $request, SsoConfigurationService $configurationService): RedirectResponse
    {
        $configurationService->update($request->validated());

        return redirect()->route('admin.system.sso-configuration.edit')
            ->with('success', 'Pengaturan Gate SSO berhasil diperbarui.');
    }

    public function reset(Request $request, SsoConfigurationService $configurationService): RedirectResponse
    {
        Gate::authorize('manage-sso-settings');
        $request->validate(['confirm_reset' => ['required', 'accepted']]);

        $configurationService->reset();

        return redirect()->route('admin.system.sso-configuration.edit')
            ->with('success', 'Pengaturan Gate SSO dikembalikan ke default aman dan nonaktif.');
    }
}
