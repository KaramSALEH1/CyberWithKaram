<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['module_id', 'title', 'slug', 'content', 'video_url', 'video_path', 'video_type', 'order_no', 'price', 'is_free', 'requires_purchase'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'requires_purchase' => 'boolean',
            'is_free' => 'boolean',
        ];
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
