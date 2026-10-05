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

        $sanitized = strip_tags((string) $value, $allowedTags);

        // `strip_tags()` keeps attributes on allowed tags, so drop every
        // attribute as well. `full_description` is rendered as HTML in the
        // public service page, which would otherwise allow event-handler
        // injection (e.g. `<p onclick="...">`) or `javascript:` URLs.
        $sanitized = preg_replace('/<([a-z0-9]+)\s[^>]*>/i', '<$1>', $sanitized);

        // Allow only http/https targets on surviving links and images.
        $sanitized = preg_replace(
            '/\s(?:href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*\1/i',
            '',
            $sanitized
        );

        $this->attributes['full_description'] = $sanitized;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Approximate USD equivalent of a SYP price.
     *
     * The marketplace is priced in Syrian Pounds; USD is shown alongside it so
     * international customers can compare. Uses the nominal rate configured in
     * `config/cyberlogia.php` (`syp_per_usd`), which defaults to 10,000.
     */
    public function usdPrice(): float
    {
        $rate = (float) config('cyberlogia.syp_per_usd', 10000);

        if ($rate <= 0) {
            return 0.0;
        }

        return round((float) $this->price / $rate, 2);
    }

    /**
     * "80,000 SYP · $8" style price label for the storefront.
     */
    public function priceLabel(): string
    {
        if (! $this->requiresPayment()) {
            return 'Custom Quote';
        }

        return number_format((float) $this->price, 0).' SYP'
            .' · $'.number_format($this->usdPrice(), 0).'/mo';
    }
}
