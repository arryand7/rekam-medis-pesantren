<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();

        return $actor && ($actor->hasPermission('manage-users') || $actor->isSuperAdmin());
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            // Identitas lokal — hanya boleh diedit jika bukan user Gate
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', "unique:users,email,{$userId}"],

            // Password opsional — diisi hanya jika ingin mengubah
            'password' => ['nullable', Password::min(8)->letters()->numbers(), 'confirmed'],

            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Nama pengguna wajib diisi.',
            'email.required'     => 'Email pengguna wajib diisi.',
            'email.unique'       => 'Email ini sudah digunakan oleh pengguna lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
