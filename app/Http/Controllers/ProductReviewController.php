<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_published && in_array($product->subsidiary, ['collections', 'gadgets'], true), 404);
        abort_unless(ProductReview::hasVerifiedPurchase($request->user(), $product), 403, 'A verified purchase is required to review this product.');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'display_name' => ['required', 'string', 'min:2', 'max:60'],
            'title' => ['required', 'string', 'min:3', 'max:120'],
            'body' => ['required', 'string', 'min:15', 'max:2000'],
        ]);
        ProductReview::query()->updateOrCreate(
            ['product_id' => $product->getKey(), 'user_id' => $request->user()->getKey()],
            array_merge($data, ['status' => 'pending']),
        );

        return back()->with('success', 'Thank you. Your review has been submitted for approval.');
    }
}
