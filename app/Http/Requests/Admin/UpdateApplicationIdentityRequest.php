<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateApplicationIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage-system-settings') ?? false;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'application_name' => ['required', 'string', 'max:120'],
            'application_short_name' => ['required', 'string', 'max:50'],
            'institution_name' => ['required', 'string', 'max:160'],
            'tagline' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'footer_text' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'mimetypes:image/png,image/jpeg,image/webp', 'max:2048'],
            'logo_dark' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'mimetypes:image/png,image/jpeg,image/webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg,webp', 'mimetypes:image/png,image/jpeg,image/webp', 'max:1024'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (['logo', 'logo_dark', 'favicon'] as $field) {
                $file = $this->file($field);
                if (! $file || $validator->errors()->has($field)) {
                    continue;
                }

                $imageInfo = @getimagesize($file->getPathname());
                $detectedMime = is_array($imageInfo) ? $imageInfo['mime'] : null;
                if (! in_array($detectedMime, ['image/png', 'image/jpeg', 'image/webp'], true)) {
                    $validator->errors()->add($field, 'Isi file harus benar-benar berupa PNG, JPEG, atau WebP yang valid.');
                }
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'logo.image' => 'Logo harus berupa gambar PNG, JPEG, atau WebP yang valid.',
            'logo_dark.image' => 'Logo dark mode harus berupa gambar PNG, JPEG, atau WebP yang valid.',
            'favicon.image' => 'Favicon harus berupa gambar PNG, JPEG, atau WebP yang valid.',
            '*.mimes' => 'SVG dan format aktif lain tidak diizinkan. Gunakan PNG, JPEG, atau WebP.',
            '*.mimetypes' => 'Isi file harus benar-benar berupa PNG, JPEG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 2 MB.',
            'logo_dark.max' => 'Ukuran logo dark mode maksimal 2 MB.',
            'favicon.max' => 'Ukuran favicon maksimal 1 MB.',
        ];
    }
}
