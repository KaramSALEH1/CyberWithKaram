<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'level', 'is_active', 'requires_purchase'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'requires_purchase' => 'boolean',
        ];
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order_no');
    }
}
