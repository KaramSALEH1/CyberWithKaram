<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['module_id', 'title', 'video_url', 'content', 'order_no', 'requires_purchase'];

    protected function casts(): array
    {
        return [
            'requires_purchase' => 'boolean',
        ];
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
