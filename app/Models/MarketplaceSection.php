<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceSection extends Model
{
    public const DEFAULTS = [
        'deals' => ['title' => 'Today’s Deals', 'order' => 10],
        'featured' => ['title' => 'Featured Across BLOMFREE', 'order' => 20],
        'spotlight' => ['title' => 'Editor’s Picks', 'order' => 30],
        'departments' => ['title' => 'Shop by Division', 'order' => 40],
        'new' => ['title' => 'Just Added', 'order' => 50],
        'insights' => ['title' => 'Shopping Guides & Insights', 'order' => 60],
        'voices' => ['title' => 'Customer Experiences', 'order' => 70],
    ];

    protected $fillable = ['section_key', 'title', 'is_enabled', 'display_order'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'display_order' => 'integer'];
    }

    public static function configured(): array
    {
        $overrides = static::query()->get()->keyBy('section_key');
        return collect(static::DEFAULTS)->map(function (array $default, string $key) use ($overrides) {
            $record = $overrides->get($key);
            return [
                'key' => $key,
                'title' => $record?->title ?: $default['title'],
                'enabled' => $record ? $record->is_enabled : true,
                'order' => $record ? $record->display_order : $default['order'],
            ];
        })->sortBy('order')->values()->all();
    }
}
