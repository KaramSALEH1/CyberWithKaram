<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'level', 'price', 'is_active', 'requires_purchase'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'requires_purchase' => 'boolean',
        ];
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order_no');
    }

    public function lessons()
    {
        return $this->hasManyThrough(Lesson::class, Module::class);
    }
}
