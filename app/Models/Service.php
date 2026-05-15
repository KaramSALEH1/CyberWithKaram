<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'price',
        'is_automated',
        'is_visible',
        'is_available',
        'payment_instructions',
        'script_code',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_automated' => 'boolean',
            'is_visible' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function agentStatuses(): HasMany
    {
        return $this->hasMany(AgentStatus::class);
    }

    public function requiresPayment(): bool
    {
        return (float) $this->price > 0;
    }

    public function setFullDescriptionAttribute($value)
    {
        $allowedTags = '<b><i><u><strong><em><ul><ol><li><p><br><span><div><a><img><h4><h3><h2><h1>';
        $this->attributes['full_description'] = strip_tags($value, $allowedTags);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
