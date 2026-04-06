<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category',
        'description',
        'full_description',
        'icon',
        'logo_url',
        'is_automated',
        'is_visible',
        'script_code'
    ];

    protected function casts(): array
    {
        return [
            'is_automated' => 'boolean',
            'is_visible' => 'boolean',
        ];
    }
}
