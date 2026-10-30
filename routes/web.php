<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AffiliateController;
use App\Http\Controllers\AffiliateDashboardController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AutomobileController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\LandController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SavedListingController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/saved', SavedListingController::class)->name('saved');
Route::get('/search', SearchController::class)->name('search')->middleware('throttle:40,1');

Route::get('/', function () {
    $lands = \App\Models\Land::query()->with('media')->where('status', 'available')
        ->orderByDesc('is_featured')->orderBy('order')->limit(3)->get();
    $animals = \App\Models\Animal::query()->with('media')
        ->where(fn ($query) => $query->where(fn ($pool) => $pool->where('listing_type', 'pool')->where('stock', '>', 0))
            ->orWhere(fn ($individual) => $individual->where('listing_type', 'individual')->where('availability', 'available')))
        ->orderByDesc('is_featured')->orderBy('order')->limit(3)->get();
    $products = \App\Models\Product::query()->with(['media', 'variants'])
        ->where('is_published', true)->whereIn('subsidiary', ['collections', 'gadgets'])
        ->orderByDesc('is_featured')->orderBy('order')->limit(24)->get();

    $productCards = fn ($division) => $products->where('subsidiary', $division)
        ->filter(fn ($product) => $product->is_in_stock)->take(3)->map(fn ($product) => [
            'id' => $product->id, 'title' => $product->name, 'image' => $product->cover_url,
            'meta' => $product->category, 'price' => (float) $product->display_price,
            'href' => route($division.'.show', $product),
        ])->values();

    $vehicles = \App\Models\Vehicle::published()->where('status', 'available')->where('is_featured', true)
        ->with('media')->latest()->limit(3)->get();
    $articles = \App\Models\Article::query()->where('is_published', true)->whereNotNull('published_at')
        ->where('published_at', '<=', now())->latest('published_at')->limit(3)->get();
    $testimonials = \App\Models\Testimonial::query()->where('is_approved', true)
        ->orderBy('display_order')->limit(3)->get();
    $banners = \App\Models\HomepageBanner::query()->where('is_active', true)
        ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
        ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
        ->orderBy('display_order')->limit(3)->get();

    return Inertia::render('Home', [
        'articles' => $articles->map(fn ($article) => [
            'id' => $article->id, 'title' => $article->title, 'excerpt' => $article->excerpt,
            'category' => $article->category, 'image' => $article->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) : null,
            'href' => route('articles.show', $article->slug),
        ]),
        'testimonials' => $testimonials->map(fn ($item) => [
            'id' => $item->id, 'name' => $item->customer_name, 'quote' => $item->quote,
            'division' => $item->division, 'image' => $item->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) : null,
        ]),
        'banners' => $banners->map(fn ($banner) => [
            'id' => $banner->id, 'eyebrow' => $banner->eyebrow, 'headline' => $banner->headline,
            'description' => $banner->description, 'image' => $banner->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_path) : null,
            'button_label' => $banner->button_label,
            'button_url' => preg_match('~^/(?!/)[a-zA-Z0-9/_?=&%-]*$~', $banner->button_url ?? '') ? $banner->button_url : null,
        ]),
        'featured' => [
            'lands' => $lands->map(fn ($land) => [
                'id' => $land->id, 'title' => $land->title, 'image' => $land->cover_url,
                'meta' => $land->city_or_lga.', '.$land->state,
                'price' => (float) $land->price_per_plot,
                'detail' => $land->plot_size_sqm ? $land->plot_size_sqm.' m² per plot' : null,
                'installments' => $land->installmentAvailableForPurchase(),
                'href' => route('lands.show', $land),
            ]),
            'animals' => $animals->map(fn ($animal) => [
                'id' => $animal->id, 'title' => $animal->name, 'image' => $animal->cover_url,
                'meta' => $animal->breed, 'price' => (float) $animal->price,
                'href' => route('kennel-farm.show', $animal),
            ]),
            'collections' => $productCards('collections'),
            'gadgets' => $productCards('gadgets'),
            'vehicles' => $vehicles->map(fn ($vehicle) => [
                'id' => $vehicle->id, 'title' => $vehicle->title, 'image' => $vehicle->cover_url,
                'meta' => $vehicle->year.' · '.$vehicle->make, 'price' => (float) $vehicle->price,
                'href' => route('automobiles.show', $vehicle),
            ]),
        ],
    ]);
})->name('home')->middleware('edge-cache');

Route::get('/insights', function () {
    return Inertia::render('Articles/Index', [
        'articles' => \App\Models\Article::query()->where('is_published', true)
            ->whereNotNull('published_at')->where('published_at', '<=', now())
            ->latest('published_at')->paginate(12)->through(fn ($article) => [
                'title' => $article->title, 'slug' => $article->slug, 'excerpt' => $article->excerpt,
                'category' => $article->category, 'published_at' => $article->published_at->toDateString(),
                'image' => $article->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) : null,
            ]),
    ]);
})->name('articles.index');
Route::get('/insights/{slug}', function (string $slug) {
    $article = \App\Models\Article::query()->where('slug', $slug)->where('is_published', true)
        ->whereNotNull('published_at')->where('published_at', '<=', now())->firstOrFail();
    return Inertia::render('Articles/Show', ['article' => [
        'title' => $article->title, 'excerpt' => $article->excerpt, 'body' => $article->body,
        'category' => $article->category, 'published_at' => $article->published_at->toDateString(),
        'seo_title' => $article->seo_title, 'seo_description' => $article->seo_description,
        'image' => $article->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($article->image_path) : null,
    ]]);
})->name('articles.show');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about')->middleware('edge-cache');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

/*
 * Public contact form + inquiry submission.
 */
Route::get('/contact', function () {
    return Inertia::render('Contact', [
        'subjects' => \App\Models\Inquiry::SUBJECTS,
        'whatsappNumber' => '2348103965317',
    ]);
})->name('contact.index')->middleware('edge-cache');

Route::post('/inquiries', [InquiryController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('inquiries.store');

/*
 * BLOMFREE Estates & Properties — land listings.
 */
Route::get('/automobiles', [AutomobileController::class, 'index'])->name('automobiles.index');
Route::get('/automobiles/compare', [AutomobileController::class, 'compare'])->name('automobiles.compare');
Route::get('/automobiles/saved', [AutomobileController::class, 'saved'])->name('automobiles.saved');
Route::get('/automobiles/{vehicle}', [AutomobileController::class, 'show'])->name('automobiles.show');
Route::get('/automobiles/{vehicle}/inspect', [InspectionController::class, 'createForVehicle'])->name('automobiles.inspect');

Route::get('/lands', [LandController::class, 'index'])->name('lands.index')->middleware('edge-cache');
Route::get('/lands/{land}', [LandController::class, 'show'])->name('lands.show');
Route::get('/lands/{land}/inspect', [InspectionController::class, 'createForLand'])->name('inspections.create');

/*
 * BLOMFREE Kennel & Farm — animal listings.
 */
Route::get('/kennel-farm', [AnimalController::class, 'index'])->name('kennel-farm.index')->middleware('edge-cache');
Route::get('/kennel-farm/{animal}', [AnimalController::class, 'show'])->name('kennel-farm.show');
Route::get('/kennel-farm/{animal}/inspect', [InspectionController::class, 'createForAnimal'])->name('animal-inspections.create');

/*
 * Inspection booking system — shared across subsidiaries (Phase 3).
 */
Route::post('/inspections', [InspectionController::class, 'store'])
    ->middleware('throttle:5,60')
    ->name('inspections.store');
Route::get('/inspections/{inspection}', [InspectionController::class, 'show'])->name('inspections.show');

/*
 * BLOMFREE Collections — unisex clothing & accessories catalog.
 */
Route::get('/collections', [ProductController::class, 'collectionsIndex'])->name('collections.index')->middleware('edge-cache');
Route::post('/shop/products/{product}/reviews', [ProductReviewController::class, 'store'])
    ->middleware(['auth', 'verified', 'throttle:5,1'])->name('products.reviews.store');

Route::get('/collections/{product}', [ProductController::class, 'collectionsShow'])->name('collections.show');

/*
 * BLOMFREE Gadgets & Accessories — devices catalog.
 */
Route::get('/gadgets', [ProductController::class, 'gadgetsIndex'])->name('gadgets.index')->middleware('edge-cache');
Route::get('/gadgets/{product}', [ProductController::class, 'gadgetsShow'])->name('gadgets.show');

/*
 * Session-based shopping cart (guest checkout — no user accounts).
 */
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('cart.add');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.items.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'destroy'])->name('cart.items.destroy');

/*
 * Affiliate code application — session-scoped, no auth required.
 */
Route::post('/cart/apply-affiliate', [AffiliateController::class, 'applyCode'])
    ->middleware('throttle:20,1')
    ->name('cart.apply-affiliate');
Route::delete('/cart/apply-affiliate', [AffiliateController::class, 'removeCode'])
    ->name('cart.remove-affiliate');

/*
 * Checkout & orders — guest, no auth required.
 */
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.place');
Route::get('/checkout/verify/{reference}', [CheckoutController::class, 'verify'])->name('checkout.verify');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/track', [OrderController::class, 'trackForm'])->name('orders.track');
Route::post('/track', [OrderController::class, 'trackLookup']);

/*
 * Payment gateway webhooks (CSRF-exempt — see bootstrap/app.php).
 */
Route::post('/webhooks/paystack', [WebhookController::class, 'paystack'])->name('webhooks.paystack');
Route::post('/webhooks/flutterwave', [WebhookController::class, 'flutterwave'])->name('webhooks.flutterwave');

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();

    return Inertia::render('Dashboard', [
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
        ],
        'isAdmin' => (bool) $user->is_admin,
        'isAffiliate' => (bool) $user->is_affiliate,
        'installmentCount' => $user->installmentPlans()->count(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

/*
 * BLOMFREE Affiliate Program — public marketing + signup, then auth + active
 * affiliate dashboard.
 */
Route::get('/affiliate', [AffiliateController::class, 'landing'])->name('affiliate.landing')->middleware('edge-cache');
Route::get('/affiliate/terms', [AffiliateController::class, 'terms'])->name('affiliate.terms')->middleware('edge-cache');
// GET is public so AffiliateController::signupForm can redirect logged-in
// users to the right destination (existing affiliate -> dashboard; new
// customer -> /account/affiliate/become; admin -> message). POST stays
// guest-only — existing accounts use /account/affiliate/become.
Route::get('/affiliate/signup', [AffiliateController::class, 'signupForm'])->name('affiliate.signup');
Route::middleware('guest')->group(function () {
    Route::post('/affiliate/signup', [AffiliateController::class, 'signup'])
        ->middleware('throttle:3,1440');
});

Route::middleware('auth')->group(function () {
    Route::get('/affiliate/pending', [AffiliateController::class, 'pending'])->name('affiliate.pending');
    Route::get('/affiliate/suspended', [AffiliateController::class, 'suspended'])->name('affiliate.suspended');
});

Route::middleware(['auth', 'affiliate'])->prefix('affiliate/dashboard')->name('affiliate.')->group(function () {
    Route::get('/', [AffiliateDashboardController::class, 'home'])->name('dashboard');
    Route::get('/orders', [AffiliateDashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/commissions', [AffiliateDashboardController::class, 'commissions'])->name('dashboard.commissions');
    Route::get('/withdrawals', [AffiliateDashboardController::class, 'withdrawals'])->name('dashboard.withdrawals');
    Route::get('/withdrawals/new', [AffiliateDashboardController::class, 'newWithdrawal'])->name('dashboard.withdrawals.new');
    Route::post('/withdrawals', [AffiliateDashboardController::class, 'storeWithdrawal'])
        ->middleware('throttle:5,60')
        ->name('dashboard.withdrawals.store');
    Route::get('/bank-details', [AffiliateDashboardController::class, 'bankDetails'])->name('dashboard.bank-details');
    Route::patch('/bank-details', [AffiliateDashboardController::class, 'updateBankDetails'])->name('dashboard.bank-details.update');
    Route::get('/profile', [AffiliateDashboardController::class, 'profile'])->name('dashboard.profile');
    Route::patch('/profile', [AffiliateDashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
 * BLOMFREE Installments — public marketing + initiation flow.
 */
Route::get('/installments', [InstallmentController::class, 'landing'])->name('installments.landing')->middleware('edge-cache');
Route::get('/installments/terms', [InstallmentController::class, 'terms'])->name('installments.terms')->middleware('edge-cache');
Route::middleware('auth')->group(function () {
    Route::get('/installments/initiate', [InstallmentController::class, 'show'])->name('installments.initiate');
    Route::post('/installments/initiate', [InstallmentController::class, 'store'])
        ->middleware('throttle:10,60')
        ->name('installments.store');
    Route::post('/installments/{plan}/initiate-payment', [InstallmentController::class, 'initiatePayment'])
        ->middleware('throttle:30,60')
        ->name('installments.initiate-payment');
    Route::get('/installments/{plan}/verify-payment', [InstallmentController::class, 'verifyPayment'])
        ->name('installments.verify-payment');
});

/*
 * Customer dashboard at /account — overview, installments, profile, bank.
 */
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/installments', [AccountController::class, 'installmentsIndex'])->name('installments.index');
    Route::get('/installments/{plan}', [AccountController::class, 'installmentShow'])->name('installments.show');
    Route::post('/installments/{plan}/cancel', [AccountController::class, 'cancelInstallment'])->name('installments.cancel');
    Route::get('/installments/{plan}/payments/{payment}/receipt', [AccountController::class, 'paymentReceipt'])->name('installments.receipt');
    Route::get('/installments/{plan}/statement', [AccountController::class, 'planStatement'])->name('installments.statement');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/affiliate/become', [AccountController::class, 'affiliateBecomeForm'])->name('affiliate.become');
    Route::post('/affiliate/become', [AccountController::class, 'affiliateBecomeStore'])
        ->middleware('throttle:3,60')
        ->name('affiliate.become.store');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::patch('/profile', [AccountController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    Route::delete('/account', [AccountController::class, 'deactivate'])->name('deactivate');
    Route::get('/bank', [AccountController::class, 'bank'])->name('bank');
    Route::patch('/bank', [AccountController::class, 'updateBank'])->name('bank.update');
});

require __DIR__.'/auth.php';
