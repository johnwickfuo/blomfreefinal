<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    public const STATUSES = ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    protected $fillable = ['product_id', 'user_id', 'rating', 'display_name', 'title', 'body', 'status'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public static function hasVerifiedPurchase(User $user, Product $product): bool
    {
        if (! $user->hasVerifiedEmail()) {
            return false;
        }

        return Order::query()
            ->where('customer_email', $user->email)
            ->where('payment_status', 'paid')
            ->whereHas('items', fn (Builder $query) => $query->where(function (Builder $items) use ($product) {
                $items->where(function (Builder $direct) use ($product) {
                    $direct->where('orderable_type', Product::class)->where('orderable_id', $product->getKey());
                })->orWhere(function (Builder $variants) use ($product) {
                    $variants->where('orderable_type', ProductVariant::class)
                        ->whereIn('orderable_id', ProductVariant::query()->select('id')->where('product_id', $product->getKey()));
                });
            }))
            ->exists();
    }
}
