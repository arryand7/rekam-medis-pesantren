---
id: TEST-APPLICATION-IDENTITY-BRANDING
title: "Application Identity & Branding Test Matrix"
status: active
owner: "Tim Pengembang POSKESTREN"
last_updated: 2026-08-15
---

# Application Identity & Branding Test Matrix

| Area | Bukti otomatis | Acceptance |
|---|---|---|
| Authorization | `ApplicationIdentityAuthorizationTest` | super_admin dan explicit system permission allowed; role terbatas 403 dan menu tersembunyi |
| Persist/cache | `ApplicationIdentityManagementTest` | nilai persisten, cache invalidated, request berikutnya langsung merender nilai baru |
| Upload | management + security tests | PNG/JPEG/WebP valid; executable, fake JPEG/PHP, oversize, dan SVG ditolak |
| Cleanup | management test | aset lama dihapus setelah replacement; source default tetap ada |
| Reset/audit | management test | confirmation required, row/aset dibersihkan, default/cache/audit benar |
| Fallback | `ApplicationBrandingRenderingTest` | default, missing file, dark-logo chain, dan favicon default tidak broken |
| Global rendering | rendering test | login, header, sidebar, title, footer, favicon |
| UX/accessibility | rendering + browser smoke | section, label/hint association, file accept, focus/theme, no viewport overflow |
| Public safety | rendering + hygiene scan | default SVG generic; tidak ada runtime branding/prompt/secret yang di-track |

Target browser: `/admin/system/application-identity`, `/login`, `/dashboard`, `/referrals`, dan `/dashboards/management` pada 375×812, 768×1024, 1024×768, dan 1440×900 dengan light/dark/system. Hasil final dicatat pada closure; bukti yang tidak dapat dijalankan tidak boleh diklaim.
