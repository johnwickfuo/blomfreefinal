<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MarketplacePlacement extends Model
{
    public const SECTIONS = [
        'featured' => 'Featured Across BLOMFREE',
        'deals' => "Today's Deals (real product discounts only)",
        'spotlight' => "Editor's Picks",
    ];

    public const ITEM_TYPES = [
        Product::class => 'Fashion or Gadget',
        Vehicle::class => 'Automobile',
        Land::class => 'Real Estate',
        Animal::class => 'Kennel & Farm',
    ];

    protected $fillable = ['item_type', 'item_id', 'section', 'display_order', 'is_active', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean', 'display_order' => 'integer',
            'starts_at' => 'datetime', 'ends_at' => 'datetime',
        ];
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }
}
