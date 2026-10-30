<?php
namespace App\Http\Controllers;

use App\Services\MarketplaceHomeService;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceHomeController extends Controller
{
    public function __invoke(MarketplaceHomeService $service): Response
    {
        return Inertia::render('Home', $service->data());
    }
}
