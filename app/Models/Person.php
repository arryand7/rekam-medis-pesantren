<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Person extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'people';

    protected $fillable = [
        'gate_user_id',
        'name',
        'nik',
        'nis_nip',
        'user_type',
        'gender',
        'phone',
        'email',
        'source_status',
        'source_updated_at',
        'source_version',
        'checksum',
        'synced_at',
        'photo_path',
        'photo_checksum',
    ];

    protected function casts(): array
    {
        return [
            'source_updated_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'person_id');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'person_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->name ?? '';
    }

    /**
     * URL foto profil dari private storage (via signed route), atau null jika tidak ada.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        if (! Storage::disk('person_photos')->exists($this->photo_path)) {
            return null;
        }

        return route('person.photo', ['person' => $this->id]);
    }

    /**
     * Foto profil jika ada, atau fallback ke initial avatar via ui-avatars.com.
     */
    public function getPhotoOrAvatarUrlAttribute(): string
    {
        if ($this->photo_url) {
            return $this->photo_url;
        }

        $initials = collect(explode(' ', $this->name ?? ''))
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        return 'https://ui-avatars.com/api/?name='.urlencode($initials).'&size=200&background=0f766e&color=ffffff&bold=true';
    }

    /**
     * Check if person is a human eligible to become a patient.
     * Rule: All humans are eligible (santri, guru, staf, pengasuh, health workers, admins).
     * Only bots, service accounts, or pure technical accounts are not eligible.
     */
    public function isHumanPatientEligible(): bool
    {
        return ! in_array(strtolower($this->user_type), ['service_account', 'bot', 'technical_account'], true);
    }
}
