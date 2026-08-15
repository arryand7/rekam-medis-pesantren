<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ApplicationIdentity extends Model
{
    use HasUlids;

    protected $fillable = [
        'singleton',
        'application_name',
        'application_short_name',
        'institution_name',
        'tagline',
        'description',
        'footer_text',
        'logo_path',
        'logo_dark_path',
        'favicon_path',
    ];

    protected function casts(): array
    {
        return [
            'singleton' => 'boolean',
        ];
    }
}
