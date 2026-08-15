<?php

namespace App\Services;

use App\Models\ApplicationIdentity;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ApplicationIdentityService
{
    /** @return array<string, mixed> */
    public function get(): array
    {
        return Cache::rememberForever($this->cacheKey(), fn (): array => $this->load());
    }

    /**
     * @param  array<string, mixed>  $values
     * @param  array<string, UploadedFile|null>  $uploads
     */
    public function update(array $values, array $uploads = []): ApplicationIdentity
    {
        $current = Schema::hasTable('application_identities')
            ? ApplicationIdentity::query()->where('singleton', true)->first()
            : null;
        $before = $this->safeSnapshot($current);
        $oldPaths = $current ? $this->assetPaths($current) : [];
        $newPaths = [];

        foreach (['logo' => 'logo_path', 'logo_dark' => 'logo_dark_path', 'favicon' => 'favicon_path'] as $uploadKey => $column) {
            $file = $uploads[$uploadKey] ?? null;
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $this->storeUpload($file);
            $values[$column] = $path;
            $newPaths[] = $path;
        }

        try {
            $identity = DB::transaction(function () use ($current, $values, $before): ApplicationIdentity {
                $identity = $current ?? new ApplicationIdentity(['singleton' => true]);
                $identity->fill($values);
                $identity->singleton = true;
                $identity->save();

                $after = $this->safeSnapshot($identity);
                $changedFields = array_keys(array_diff_assoc($after, $before));

                AuditLogService::log(
                    'APPLICATION_IDENTITY_UPDATED',
                    ApplicationIdentity::class,
                    $identity->id,
                    ['values' => $before],
                    ['values' => $after, 'changed_fields' => $changedFields],
                    'Identitas aplikasi diperbarui melalui pengaturan sistem.'
                );

                $logoFields = array_values(array_intersect($changedFields, ['logo_path', 'logo_dark_path']));
                if ($logoFields !== []) {
                    AuditLogService::log(
                        'APPLICATION_LOGO_UPDATED',
                        ApplicationIdentity::class,
                        $identity->id,
                        ['paths' => array_intersect_key($before, array_flip($logoFields))],
                        ['paths' => array_intersect_key($after, array_flip($logoFields)), 'changed_fields' => $logoFields],
                        'Aset logo aplikasi diperbarui.'
                    );
                }

                if (in_array('favicon_path', $changedFields, true)) {
                    AuditLogService::log(
                        'APPLICATION_FAVICON_UPDATED',
                        ApplicationIdentity::class,
                        $identity->id,
                        ['favicon_path' => $before['favicon_path']],
                        ['favicon_path' => $after['favicon_path']],
                        'Favicon aplikasi diperbarui.'
                    );
                }

                return $identity;
            });
        } catch (Throwable $exception) {
            $this->deletePaths($newPaths);

            throw $exception;
        }

        $this->forget();
        $this->deletePaths(array_values(array_diff($oldPaths, $this->assetPaths($identity))));

        return $identity;
    }

    public function reset(): void
    {
        $identity = Schema::hasTable('application_identities')
            ? ApplicationIdentity::query()->where('singleton', true)->first()
            : null;
        $before = $this->safeSnapshot($identity);
        $oldPaths = $identity ? $this->assetPaths($identity) : [];

        DB::transaction(function () use ($identity, $before): void {
            $subjectId = $identity?->id;
            $identity?->delete();

            AuditLogService::log(
                'APPLICATION_IDENTITY_RESET',
                ApplicationIdentity::class,
                $subjectId,
                ['values' => $before],
                ['values' => $this->safeSnapshot(null), 'changed_fields' => array_keys($before)],
                'Identitas aplikasi dikembalikan ke default source-controlled.'
            );
        });

        $this->forget();
        $this->deletePaths($oldPaths);
    }

    public function forget(): void
    {
        Cache::forget($this->cacheKey());
    }

    /** @return array<string, mixed> */
    private function load(): array
    {
        $identity = Schema::hasTable('application_identities')
            ? ApplicationIdentity::query()->where('singleton', true)->first()
            : null;
        $defaults = config('branding.defaults');
        $values = array_merge($defaults, $identity?->only(array_keys($defaults)) ?? []);
        $version = $identity?->updated_at?->getTimestamp() ?? config('app.version', 'default');

        return array_merge($values, [
            'logo_path' => $identity?->logo_path,
            'logo_dark_path' => $identity?->logo_dark_path,
            'favicon_path' => $identity?->favicon_path,
            'logo_url' => $this->assetUrl($identity?->logo_path, config('branding.default_assets.logo'), $version),
            'logo_dark_url' => $this->assetUrl($identity?->logo_dark_path ?: $identity?->logo_path, config('branding.default_assets.logo_dark'), $version),
            'favicon_url' => $this->assetUrl($identity?->favicon_path, config('branding.default_assets.favicon'), $version),
            'mark_url' => asset(config('branding.default_assets.mark')).'?v='.rawurlencode((string) $version),
            'updated_at' => $identity?->updated_at,
            'is_customized' => $identity !== null,
        ]);
    }

    private function assetUrl(?string $customPath, string $defaultPath, int|string $version): string
    {
        if ($customPath && Storage::disk(config('branding.disk'))->exists($customPath)) {
            return Storage::disk(config('branding.disk'))->url($customPath).'?v='.rawurlencode((string) $version);
        }

        return asset($defaultPath).'?v='.rawurlencode((string) $version);
    }

    private function storeUpload(UploadedFile $file): string
    {
        $extension = match ($file->getMimeType()) {
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'image/webp' => 'webp',
            default => throw new RuntimeException('Format gambar branding tidak didukung.'),
        };
        $filename = Str::uuid().'.'.$extension;
        $path = $file->storeAs(config('branding.upload_directory'), $filename, config('branding.disk'));

        if (! is_string($path)) {
            throw new RuntimeException('Aset branding gagal disimpan.');
        }

        return $path;
    }

    /** @return array<string, mixed> */
    private function safeSnapshot(?ApplicationIdentity $identity): array
    {
        $defaults = config('branding.defaults');

        return array_merge($defaults, $identity?->only([
            ...array_keys($defaults),
            'logo_path',
            'logo_dark_path',
            'favicon_path',
        ]) ?? [
            'logo_path' => null,
            'logo_dark_path' => null,
            'favicon_path' => null,
        ]);
    }

    /** @return array<int, string> */
    private function assetPaths(ApplicationIdentity $identity): array
    {
        return array_values(array_filter([
            $identity->logo_path,
            $identity->logo_dark_path,
            $identity->favicon_path,
        ], fn (?string $path): bool => is_string($path) && str_starts_with($path, config('branding.upload_directory').'/')));
    }

    /** @param array<int, string> $paths */
    private function deletePaths(array $paths): void
    {
        if ($paths !== []) {
            Storage::disk(config('branding.disk'))->delete(array_values(array_unique($paths)));
        }
    }

    private function cacheKey(): string
    {
        return config('branding.cache_key');
    }
}
