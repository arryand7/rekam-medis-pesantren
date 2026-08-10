# PROMPT ANTIGRAVITY — PRODUCTION AUTH HOTFIX ROLLOUT & VERIFICATION

Gunakan Claude Opus 4.6 Thinking.

Konteks:
Authentication bypass pada root/dashboard telah diperbaiki di source/test runtime dengan commit:
`7be058b` — `fix(security): enforce authentication middleware on all sensitive routes and add role-aware dashboard resolver`

Local verification:
- guest protected routes -> 302 `/login`
- 197 tests passed
- 791 assertions
- Pint/PHPStan/Vite PASS
- working tree clean

Namun bukti curl/incognito sebelumnya berasal dari localhost. Production belum dianggap aman sampai hotfix benar-benar deployed dan diuji pada domain production aktual.

Tujuan:
1. audit `Gate::before()` yang baru ditambahkan;
2. deploy hotfix `7be058b` secara atomic ke production;
3. verify runtime SHA;
4. verify guest protection dari production hostname tanpa cookies;
5. verify real Gate SSO production browser flow;
6. verify role isolation;
7. verify logout/session;
8. review historical exposure;
9. close incident only if production proof passes.

Jangan menambah fitur baru.

## 1. Audit `Gate::before()`

Inspect `app/Providers/AppServiceProvider.php`.

Pastikan `Gate::before()`:
- hanya allow berdasarkan exact local permission;
- return `null` bila harus defer ke Policy;
- tidak membuat technical admin menjadi global clinical superuser;
- `manage-users` tidak imply `view-medical-record`;
- unknown ability tidak default allow;
- tidak recursive.

Unsafe example:
```php
Gate::before(fn ($user, $ability) => $user->isAdmin() ? true : null);
```

Tambahkan regression tests jika perlu.

## 2. Route authorization matrix

Run:
```bash
php artisan route:list -v
```

Audit:
- `/`
- `/dashboard`
- `/patients`
- `/visits`
- `/assessments`
- `/pharmacy/*`
- `/referrals/*`
- `/discharges/*`
- `/reports/*`
- `/integration/*`
- `/people`
- `/users`
- Gate sync/admin routes.

Catat auth middleware, entitlement, Policy/permission, guest expected, unauthorized authenticated expected.

## 3. Pre-deploy quality gate

```bash
git status
git rev-parse HEAD
git log --oneline -8
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
git diff --check
```

Required:
- 0 failures;
- 0 skipped critical auth tests;
- working tree clean.

## 4. Production pre-hotfix read-only check

Pada server production catat:
- active release path;
- runtime SHA;
- `/health`;
- `/health/ready`;
- PHP-FPM;
- queue worker;
- scheduler;
- effective auth feature flags tanpa secret.

Gunakan deployment mechanism yang aktual, misalnya:
```bash
readlink -f /var/www/poskestren/current
cd /var/www/poskestren/current
git rev-parse HEAD
```

Jangan asumsi Git lokal == runtime production.

## 5. Fresh backup

Sebelum deploy:
- DB logical backup;
- current release reference;
- private storage/config reference sesuai incident runbook;
- checksum/non-zero validation.

Jangan tampilkan secret.

## 6. Atomic deploy

Deploy release yang mengandung `7be058b` menggunakan atomic release mechanism existing.

Build:
```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jangan overwrite shared private storage.
Jangan jalankan migration kecuali memang ada pending migration relevan.

Switch release atomically, reload PHP-FPM, restart queue workers safely.

## 7. Immediate health

Expected:
```text
GET /health       -> 200
GET /health/ready -> 200
```

Jika gagal, rollback release segera.

## 8. Production curl without cookies

Dari client tanpa production cookie:

```bash
curl -skI https://<PRODUCTION_HOST>/
curl -skI https://<PRODUCTION_HOST>/dashboard
curl -skI https://<PRODUCTION_HOST>/patients
curl -skI https://<PRODUCTION_HOST>/visits
curl -skI https://<PRODUCTION_HOST>/pharmacy/inventory
curl -skI https://<PRODUCTION_HOST>/reports
curl -skI https://<PRODUCTION_HOST>/people
curl -skI https://<PRODUCTION_HOST>/users
curl -skI https://<PRODUCTION_HOST>/login
```

Expected:
- protected routes -> 302 login/Gate atau 401/403;
- `/login` safe public entry;
- guest tidak pernah menerima sensitive page HTTP 200.

Harus production hostname, bukan localhost.

## 9. Production incognito test

Gunakan NEW private/incognito browser window pada domain production:
- `/`
- `/dashboard`
- `/patients`

Expected:
- no admin shell;
- no patient list;
- login/Gate redirect.

## 10. Effective Gate production configuration

Verify cached runtime config without secrets.

Expected jika SSO production aktif:
```text
APP_ENV=production
APP_DEBUG=false
GATE_SSO_ENABLED=true
GATE_CLIENT_DRIVER=http
BREAK_GLASS_ENABLED=false
```

Jika runtime berbeda dari report, dokumentasikan discrepancy.

## 11. Real Gate production login

Gunakan approved canary user.

Must prove browser flow:
```text
POSKESTREN production /login
 -> real Gate production
 -> callback production
 -> entitlement
 -> local session
 -> correct role dashboard
```

Automated fake callback test saja tidak cukup.

Jangan log token.

## 12. Role-aware production tests

Jika akun representatif tersedia:
- clinical user -> clinical dashboard;
- technical admin without clinical permission -> technical/admin dashboard, clinical denied;
- dorm/operational -> operational dashboard, clinical denied;
- ordinary user -> minimal safe dashboard/403, never admin fallback.

Jangan merusak role user real hanya untuk test.

## 13. Logout production

Real canary:
1. login;
2. dashboard;
3. logout;
4. revisit `/dashboard`;
5. expected redirect login/Gate.

## 14. Historical exposure review

Review production access logs dari waktu cutover sampai hotfix.

Periksa:
- unauthenticated GET `/`;
- `/dashboard`;
- `/patients`;
- `/visits`;
- `/reports`;
- status response;
- suspicious request patterns.

Jangan expose IP/user identity pada general docs.

Classify:
- `UI-SHELL-ONLY-EXPOSURE`
- `SENSITIVE-DATA-EXPOSURE-NOT-FOUND`
- `SENSITIVE-DATA-EXPOSURE-CONFIRMED`
- `INSUFFICIENT-LOG-EVIDENCE`

Jangan klaim no breach tanpa bukti.

## 15. Post-deploy tests

Test DB:
```bash
APP_ENV=testing php artisan test
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=512M
npm run build
```

## 16. Graphify

```bash
graphify update .
```

No `--code-only`.

Query:
- guest -> root -> login;
- guest -> sensitive routes;
- Gate login -> session;
- `Gate::before` -> exact local permissions;
- technical admin -> clinical Policy;
- dashboard resolver;
- auth middleware gaps;
- dev/fake login path.

## 17. Documentation

Create:
- `docs/10-delivery/PRODUCTION-AUTH-HOTFIX-ROLLOUT.md`
- `docs/10-delivery/PRODUCTION-AUTH-HOTFIX-VERIFICATION.md`
- `docs/10-delivery/PRODUCTION-AUTH-EXPOSURE-REVIEW.md`

Update:
- `PROJECT-STATUS.md`
- `CHANGELOG.md`
- `docs/10-delivery/PHASE-4C2-FINAL-STATUS.md`
- `docs/10-delivery/READINESS-REVIEW.md`
- `docs/07-security/ACCESS-CONTROL-MATRIX.md`
- `docs/07-security/GATE-SSO-SECURITY.md`
- `docs/09-testing/FEATURE-TEST-MATRIX.md`
- `plans/KNOWN-ISSUES.md`
- `docs/12-graphify/DOCUMENT-CODE-MAPPING.md`

## 18. Final status

Use exactly one:
- `AUTH-HOTFIX-PRODUCTION-VERIFIED`
- `AUTH-HOTFIX-ROLLED-BACK`
- `AUTH-HOTFIX-PARTIAL`
- `AUTH-INCIDENT-OPEN`

## 19. Final output

Report:
1. `Gate::before()` audit.
2. Route auth matrix summary.
3. Pre-deploy tests.
4. Production runtime SHA before.
5. Backup result.
6. Hotfix release SHA.
7. Atomic switch result.
8. Runtime SHA after.
9. Health/ready.
10. Production curl-no-cookie.
11. Production incognito.
12. Effective Gate flags.
13. Real Gate production login.
14. Role-aware dashboard.
15. Logout.
16. Historical exposure review.
17. Full tests.
18. Graphify findings.
19. Final feature flags.
20. Remaining risks.
21. Git/runtime status.
22. Final classification.

Stop after production auth hotfix is independently verified.
