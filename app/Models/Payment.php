<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'product_id',
        'product_type',
        'amount',
        'account_name_number',
        'transaction_amount',
        'transaction_id_reference',
        'notes',
        'status',
        'license_key',
        'approved_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'product_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
