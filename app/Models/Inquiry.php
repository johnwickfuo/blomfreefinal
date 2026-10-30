<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    public const SUBJECTS = [
        'general' => 'General enquiry',
        'lands' => 'Real Estate',
        'kennel_farm' => 'Kennel & Farm',
        'collections' => 'Collections',
        'gadgets' => 'Gadgets & Accessories',
        'automobiles' => 'BLOMWHEELS Automobile',
        'partnership' => 'Partnership / Wholesale',
        'media' => 'Press & Media',
    ];

    public const STATUSES = ['new', 'in_progress', 'responded', 'closed'];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'related_url',
        'status',
        'responded_at',
        'admin_notes',
        'vehicle_id',
        'inquiry_category',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function vehicle(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->subject;
    }
}
