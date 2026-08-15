<?php

require_once dirname(__DIR__, 2).'/Support/BrandingTestHelpers.php';
require_once dirname(__DIR__, 2).'/Support/SsoConfigurationTestHelpers.php';

test('only super admin can access SSO configuration routes and menu', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');
    $systemAdmin = createBrandingUserWithRole('delegated_system_admin', true);
    $clinicalUser = createBrandingUserWithRole('petugas_kesehatan');

    $this->actingAs($superAdmin)
        ->get(route('admin.system.sso-configuration.edit'))
        ->assertOk()
        ->assertSee('Pengaturan Gate SSO');

    foreach ([$systemAdmin, $clinicalUser] as $unauthorizedUser) {
        $this->actingAs($unauthorizedUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Pengaturan Gate SSO');

        $this->get(route('admin.system.sso-configuration.edit'))->assertForbidden();
        $this->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload())->assertForbidden();
        $this->post(route('admin.system.sso-configuration.reset'), ['confirm_reset' => '1'])->assertForbidden();
    }
});

test('guest cannot access SSO configuration routes', function () {
    $this->get(route('admin.system.sso-configuration.edit'))->assertRedirect(route('login'));
    $this->put(route('admin.system.sso-configuration.update'), ssoConfigurationPayload())->assertRedirect(route('login'));
});
