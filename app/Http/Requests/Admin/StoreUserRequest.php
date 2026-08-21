<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();

        return $actor && ($actor->hasPermission('manage-users') || $actor->isSuperAdmin());
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', Password::min(8)->letters()->numbers(), 'confirmed'],
            'is_active'   => ['boolean'],

            // Person baru (pilihan A)
            'person_mode'   => ['required', 'in:new,existing'],
            'user_type'     => ['required_if:person_mode,new', 'nullable', 'string', 'in:santri,guru,pengasuh,staff,admin,wali'],
            'nis_nip'       => ['nullable', 'string', 'max:100'],

            // Person yang ada (pilihan B)
            'person_id'     => ['required_if:person_mode,existing', 'nullable', 'exists:people,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'Nama pengguna wajib diisi.',
            'email.required'       => 'Email pengguna wajib diisi.',
            'email.unique'         => 'Email ini sudah digunakan oleh pengguna lain.',
            'password.required'    => 'Kata sandi wajib diisi.',
            'password.confirmed'   => 'Konfirmasi kata sandi tidak cocok.',
            'person_mode.required' => 'Pilih mode data person.',
            'person_id.required_if' => 'Pilih person yang sudah ada dari daftar.',
            'user_type.required_if' => 'Tipe pengguna wajib dipilih untuk person baru.',
        ];
    }
}
