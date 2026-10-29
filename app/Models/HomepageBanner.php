<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageBanner extends Model
{
    protected $fillable = ['eyebrow', 'headline', 'description', 'image_path', 'button_label', 'button_url', 'is_active', 'display_order', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'display_order' => 'integer', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }
}
