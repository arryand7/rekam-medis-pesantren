<?php

use Illuminate\Http\UploadedFile;

require_once dirname(__DIR__, 2).'/Support/BrandingTestHelpers.php';

test('super admin and explicitly authorized system admin can access branding settings', function () {
    $superAdmin = createBrandingUserWithRole('super_admin');
    $systemAdmin = createBrandingUserWithRole('system_admin', true);

    $this->actingAs($superAdmin)->get(route('admin.system.application-identity.edit'))->assertOk();
    $this->actingAs($systemAdmin)->get(route('admin.system.application-identity.edit'))->assertOk();
});

test('unauthorized roles cannot see or directly access branding settings', function (string $roleName) {
    $user = createBrandingUserWithRole($roleName);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Identitas Aplikasi');
    $this->get(route('admin.system.application-identity.edit'))->assertForbidden();
    $this->put(route('admin.system.application-identity.update'), [])->assertForbidden();
    $this->post(route('admin.system.application-identity.reset'), ['confirm_reset' => '1'])->assertForbidden();
})->with(['admin_limited', 'petugas_kesehatan', 'farmasi', 'pengasuh_asrama', 'manajemen']);

test('executable fake image oversized image and unsafe svg uploads are rejected', function (string $field, UploadedFile $file) {
    $user = createBrandingUserWithRole('system_admin', true);

    $this->actingAs($user)->put(route('admin.system.application-identity.update'), [
        ...brandingPayload(),
        $field => $file,
    ])->assertSessionHasErrors($field);
})->with([
    'PHP executable' => ['logo', UploadedFile::fake()->create('shell.php', 10, 'application/x-php')],
    'PHP disguised as JPEG' => ['logo', UploadedFile::fake()->createWithContent('shell.jpg', '<?php echo "unsafe";')],
    'oversized raster' => ['logo', UploadedFile::fake()->image('huge.png', 100, 100)->size(2049)],
    'unsafe SVG' => ['favicon', UploadedFile::fake()->createWithContent('active.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')],
]);
