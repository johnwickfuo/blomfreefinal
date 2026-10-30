<?php

namespace Tests\Feature;

use App\Filament\Resources\MarketplacePlacementResource;
use App\Filament\Resources\MarketplaceSectionResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_users_cannot_manage_marketplace_content(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->get(MarketplacePlacementResource::getUrl('index'))->assertForbidden();
    }

    public function test_admin_can_open_homepage_merchandising_resources(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(MarketplacePlacementResource::getUrl('index'))->assertOk();
        $this->get(MarketplaceSectionResource::getUrl('index'))->assertOk();
    }
}
