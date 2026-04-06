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
        'is_automated',
        'is_visible',
        'script_code'
    ];
}
