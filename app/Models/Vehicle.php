<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Vehicle extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const CONDITIONS = [
        'brand_new' => 'Brand New',
        'foreign_used' => 'Foreign Used (Tokunbo)',
        'nigerian_used' => 'Nigerian Used',
    ];
    public const STATUSES = ['available' => 'Available', 'reserved' => 'Reserved', 'sold' => 'Sold', 'on_order' => 'On Order'];
    public const TRANSMISSIONS = ['automatic' => 'Automatic', 'manual' => 'Manual', 'cvt' => 'CVT', 'other' => 'Other'];
    public const FUELS = ['petrol' => 'Petrol', 'diesel' => 'Diesel', 'hybrid' => 'Hybrid', 'electric' => 'Electric', 'other' => 'Other'];
    public const BODY_TYPES = ['sedan' => 'Sedan', 'suv' => 'SUV', 'hatchback' => 'Hatchback', 'coupe' => 'Coupe', 'wagon' => 'Wagon', 'pickup' => 'Pickup', 'van' => 'Van', 'bus' => 'Bus', 'other' => 'Other'];
    public const MAKES = ['Toyota', 'Lexus', 'Honda', 'Mercedes-Benz', 'BMW', 'Audi', 'Volkswagen', 'Ford', 'Hyundai', 'Kia', 'Nissan', 'Peugeot', 'Mazda', 'Land Rover', 'Range Rover', 'Volvo', 'Mitsubishi', 'Suzuki', 'Jeep', 'Chevrolet', 'Porsche', 'Other'];

    protected $fillable = [
        'title', 'slug', 'stock_reference', 'make', 'model', 'trim', 'year', 'condition',
        'body_type', 'mileage_km', 'transmission', 'fuel_type', 'engine', 'drivetrain',
        'seats', 'exterior_color', 'interior_color', 'state', 'city', 'viewing_address',
        'price', 'negotiable', 'status', 'is_published', 'is_featured', 'display_order',
        'description', 'features', 'known_issues', 'accident_history', 'service_history',
        'customs_status', 'documents', 'internal_notes', 'video_url',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer', 'mileage_km' => 'integer', 'seats' => 'integer',
            'price' => 'decimal:2', 'negotiable' => 'boolean', 'is_published' => 'boolean',
            'is_featured' => 'boolean', 'display_order' => 'integer',
            'features' => 'array', 'known_issues' => 'array', 'documents' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Vehicle $vehicle): void {
            if (blank($vehicle->slug)) {
                $vehicle->slug = static::uniqueSlug($vehicle->title);
            }
            if (blank($vehicle->stock_reference)) {
                do {
                    $reference = 'BW-'.strtoupper(Str::random(8));
                } while (static::where('stock_reference', $reference)->exists());
                $vehicle->stock_reference = $reference;
            }
        });
    }

    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'vehicle';
        $slug = $base;
        for ($suffix = 2; static::where('slug', $slug)->exists(); $suffix++) {
            $slug = $base.'-'.$suffix;
        }
        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function inspections(): MorphMany
    {
        return $this->morphMany(Inspection::class, 'inspectable');
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('responsive')->withResponsiveImages();
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->getFirstMediaUrl('cover') ?: ($this->getFirstMediaUrl('gallery') ?: null));
    }

    protected function galleryUrls(): Attribute
    {
        return Attribute::get(function (): array {
            $cover = $this->getFirstMediaUrl('cover');
            return array_values(array_filter(array_merge(
                $cover ? [$cover] : [],
                $this->getMedia('gallery')->map(fn ($media) => $media->getUrl())->all()
            )));
        });
    }

    public function card(): array
    {
        return [
            'id' => $this->id, 'title' => $this->title, 'slug' => $this->slug,
            'make' => $this->make, 'model' => $this->model, 'year' => $this->year,
            'condition' => $this->condition, 'body_type' => $this->body_type,
            'mileage_km' => $this->mileage_km, 'transmission' => $this->transmission,
            'fuel_type' => $this->fuel_type, 'state' => $this->state, 'city' => $this->city,
            'price' => (float) $this->price, 'negotiable' => $this->negotiable,
            'status' => $this->status, 'image' => $this->cover_url,
            'href' => route('automobiles.show', $this),
        ];
    }

    public function publicDetails(): array
    {
        return array_merge($this->card(), [
            'stock_reference' => $this->stock_reference, 'trim' => $this->trim,
            'engine' => $this->engine, 'drivetrain' => $this->drivetrain,
            'seats' => $this->seats, 'exterior_color' => $this->exterior_color,
            'interior_color' => $this->interior_color, 'description' => $this->description,
            'features' => $this->features ?? [], 'known_issues' => $this->known_issues ?? [],
            'accident_history' => $this->accident_history, 'service_history' => $this->service_history,
            'customs_status' => $this->customs_status, 'documents' => $this->documents ?? [],
            'gallery' => $this->gallery_urls, 'video_url' => $this->video_url,
        ]);
    }
}
