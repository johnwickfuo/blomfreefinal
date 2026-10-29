<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['customer_name', 'quote', 'image_path', 'division', 'is_approved', 'display_order'];

    protected function casts(): array
    {
        return ['is_approved' => 'boolean', 'display_order' => 'integer'];
    }
}
