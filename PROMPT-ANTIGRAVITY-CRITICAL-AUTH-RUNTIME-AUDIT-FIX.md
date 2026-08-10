# PROMPT ANTIGRAVITY — CRITICAL AUTH RUNTIME AUDIT & FIX
## POSKESTREN Production Login Enforcement, Gate SSO Runtime Verification, Admin Dashboard Protection

Anda adalah principal Laravel security engineer, IAM/OIDC engineer, production incident responder, dan authorization auditor untuk proyek SABIRA POSKESTREN Health.

Gunakan Claude Opus 4.6 Thinking.

KONTEKS INSIDEN:
Setelah Phase 4C2 dinyatakan production live, pengguna membuka aplikasi dan mendapati aplikasi tampak langsung masuk ke Dashboard Admin tanpa melihat proses login.

Ini harus diperlakukan sebagai critical authentication runtime discrepancy sampai dibuktikan bahwa penyebabnya hanya session browser yang masih authenticated.

JANGAN menambah fitur baru.
JANGAN lanjut Phase 4D.
JANGAN menganggap test suite sebagai bukti runtime production.
Verifikasi perilaku aktual dari browser/curl tanpa cookie.

## 1. INCIDENT CLASSIFICATION

Status awal:
`PRODUCTION-AUTH-INCIDENT-UNDER-REVIEW`

Kemungkinan:
A. Browser masih memiliki session authenticated yang valid.
B. `/` public langsung render admin dashboard.
C. `/dashboard` tidak memakai `auth` middleware.
D. admin routes berada di luar auth/permission group.
E. dev/demo auto-login masih aktif.
F. fallback route/login stub membuat guest menjadi admin.
G. Gate feature flag OFF menyebabkan fallback tidak aman.
H. role/dashboard resolver default ke admin.
I. production runtime config/cache tidak sama dengan source/test configuration.

Jangan menentukan root cause tanpa bukti.

## 2. IMMEDIATE NON-DESTRUCTIVE TEST

Pertama instruksikan operator:
1. buka Incognito/Private Window;
2. jangan membawa cookie/session lama;
3. buka root production URL;
4. buka `/dashboard`;
5. buka satu admin URL;
6. catat HTTP behavior.

Expected guest behavior:
- root public boleh redirect ke `/login` atau Gate;
- `/dashboard` MUST require authentication;
- admin/clinical route MUST require authentication;
- unauthenticated user tidak boleh melihat patient/admin data.

Jika incognito redirect ke Gate/login:
- kemungkinan browser sebelumnya masih memiliki valid session;
- lanjut session/logout audit.

Jika incognito tetap melihat dashboard:
- confirmed auth bypass;
- lanjut incident containment.

## 3. CURL WITHOUT COOKIE

Dari mesin yang tidak memiliki browser session:

```bash
curl -skI https://<PRODUCTION_HOST>/
curl -skI https://<PRODUCTION_HOST>/dashboard
curl -skI https://<PRODUCTION_HOST>/admin
curl -skI https://<PRODUCTION_HOST>/patients
```

Jangan gunakan cookie.

Expected:
- protected routes => 302 login/Gate atau 401/403;
- never HTTP 200 admin content untuk guest.

## 4. ROUTE MIDDLEWARE AUDIT

Run:
```bash
php artisan route:list -v
```

Inspect:
- `/`
- `/login`
- `/auth/gate/*`
- `/dashboard`
- admin routes
- patients
- visits
- assessments
- pharmacy
- referrals
- discharge
- reports
- integration
- Gate sync

Every sensitive route wajib:
- `auth`
- entitlement middleware jika didesain
- Policy/permission yang sesuai

Buat matrix Route/Auth/Entitlement/Policy/Guest Expected/Actual.

## 5. ROUTES AUDIT

Inspect actual route definitions. Cari pola seperti:

```php
Route::get('/', fn () => view('dashboard'));
Route::view('/', 'dashboard');
Route::get('/dashboard', ...); // outside auth
```

Expected architecture:
- guest root -> login/Gate atau safe public landing;
- authenticated -> role-aware dashboard;
- sensitive routes dalam `auth` group.

Jangan copy contoh secara buta; adaptasi dengan arsitektur existing.

## 6. SEARCH FOR AUTO-LOGIN / DEVELOPMENT BYPASS

```bash
rg -n "loginUsingId|onceUsingId|Auth::login|Auth::once|setUser|actingAs|withoutMiddleware|bypass|auto.?login|demo.?login|dev.?login|super.?admin|user_id.?=.?1" app bootstrap config routes resources -g '!tests/**'
```

Audit juga:
- AppServiceProvider
- middleware
- bootstrap/app.php
- auth controllers
- Gate controllers
- dashboard controllers
- demo/dev providers

Production auto-login = Critical.

## 7. SESSION VS BYPASS

Jika incognito aman tetapi browser biasa langsung dashboard:
- verifikasi session lama;
- session lifetime;
- remember-me;
- logout;
- Gate SSO session behavior.

Test:
Authenticated -> logout -> session invalidate -> revisit `/dashboard` -> login/Gate.

Jika logout rusak/tidak ada, perbaiki.

## 8. EFFECTIVE PRODUCTION CONFIG

Verifikasi effective cached config tanpa mencetak secret:
- APP_ENV
- APP_DEBUG
- Gate SSO enabled?
- Gate driver?
- break glass?

Expected jika Gate production benar-benar aktif:
```text
APP_ENV=production
APP_DEBUG=false
GATE_SSO_ENABLED=true
GATE_CLIENT_DRIVER=http
BREAK_GLASS_ENABLED=false
```

Jika runtime ternyata masih:
```text
GATE_SSO_ENABLED=false
GATE_CLIENT_DRIVER=fake
```
maka laporan cutover tidak mencerminkan runtime aktual dan config harus dikoreksi secara aman.

Setelah perubahan config yang valid:
```bash
php artisan config:clear
php artisan config:cache
```

## 9. LOGIN ROUTE

Saat Gate SSO aktif:
Guest `/` -> `/login`/Gate.
`GET /login` tidak boleh:
- auto-authenticate;
- membuat local admin;
- membuat fake user;
- memberi role dari query.

## 10. DASHBOARD ROLE RESOLUTION

Dashboard tidak boleh default ke admin.

Expected:
- clinical staff -> clinical dashboard;
- pharmacy -> pharmacy dashboard;
- management -> aggregate dashboard;
- dorm/homeroom -> minimum-necessary operational dashboard;
- patient/santri -> patient-facing dashboard jika ada;
- technical admin -> technical dashboard, tanpa clinical detail kecuali permission.

Unknown user -> safe dashboard atau 403, bukan super-admin.

## 11. AUTHENTICATION VS AUTHORIZATION

Pastikan:
```text
Gate SSO
 -> authentication
 -> application entitlement
 -> Laravel User
 -> local roles/permissions
 -> Policies
```

Authenticated != admin.

## 12. INCIDENT CONTAINMENT

Jika guest dapat melihat admin/clinical data:
1. hentikan penggunaan normal;
2. containment via maintenance/auth middleware fix/rollback sesuai runbook;
3. preserve logs;
4. catat timestamp;
5. review access logs sejak deployment;
6. jangan klaim tidak ada exposure tanpa bukti.

## 13. REGRESSION TESTS

Tambahkan tests:
- guest `/dashboard` tidak 200 admin;
- guest admin denied;
- guest patients denied;
- guest visits denied;
- guest pharmacy denied;
- guest reports denied;
- guest integrations denied;
- guest `/` -> Gate/login atau safe landing;
- authenticated `/` -> correct role dashboard;
- `/login` tidak auto-auth;
- valid Gate callback auth;
- invalid state denied;
- entitlement denied remains guest;
- santri tidak melihat admin dashboard;
- dorm/teacher tidak mendapat clinical dashboard tanpa permission;
- technical admin tidak membaca clinical records;
- logout invalidates session;
- setelah logout `/dashboard` denied;
- production config tidak mengaktifkan fake login.

Run:
```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

No critical auth test may be skipped.

## 14. REAL RUNTIME VALIDATION AFTER FIX

Gunakan NEW incognito window.

Test:
Guest:
- `/`
- `/dashboard`
- `/patients`
- `/reports`

Expected no sensitive page.

Gate login:
- real Gate redirect;
- approved identity;
- entitlement;
- correct role dashboard.

Unauthorized role:
- direct admin URL -> 403.

Logout:
- logout;
- `/dashboard` -> login/Gate.

## 15. DOCUMENTATION

Create:
- `docs/10-delivery/PRODUCTION-AUTH-RUNTIME-INCIDENT.md`
- `docs/10-delivery/PRODUCTION-AUTH-RUNTIME-VERIFICATION.md`

Update:
- `PHASE-4C2-FINAL-STATUS.md`
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- readiness/security docs
- feature test matrix
- Graphify mapping
- known issues

Jika bypass confirmed, qualification pada klaim previous production cutover wajib diperbarui.

## 16. GRAPHIFY

```bash
graphify update .
```

No `--code-only`.

Query:
- guest -> root;
- guest -> dashboard;
- guest -> admin;
- login -> Gate;
- callback -> session;
- auth middleware coverage;
- entitlement middleware;
- dashboard resolver;
- dev auto-login path;
- fake Gate production path;
- clinical route bypass.

## 17. FINAL STATUS

Use one:
- `SESSION-ONLY-NO-BYPASS`
- `AUTH-BYPASS-FIXED`
- `AUTH-BYPASS-CONTAINED`
- `NO-GO`

Jangan kembali ke `PRODUCTION-CUTOVER-PASSED` sampai runtime incognito/curl benar-benar aman.

## 18. FINAL OUTPUT

Report:
1. Incognito result.
2. Curl-no-cookie result.
3. Root route behavior.
4. Dashboard middleware.
5. Admin/clinical middleware.
6. Auto-login findings.
7. Effective Gate flags.
8. Login route behavior.
9. Session/logout.
10. Dashboard resolver.
11. Root cause.
12. Fixes.
13. Tests.
14. Redeploy/containment.
15. Real Gate login.
16. Unauthorized role.
17. Logout runtime.
18. Access-log review.
19. Git/runtime SHA.
20. Final status.

Stop setelah authentication runtime terbukti aman.
