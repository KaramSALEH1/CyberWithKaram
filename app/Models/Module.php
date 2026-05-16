<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['course_id', 'title', 'order_no', 'price', 'requires_purchase'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'requires_purchase' => 'boolean',
        ];
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order_no');
    }
}
