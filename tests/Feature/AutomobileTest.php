<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AutomobileTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(array $attributes = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'title' => 'Toyota Camry 2018',
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2018,
            'condition' => 'foreign_used',
            'price' => 12000000,
            'description' => 'Vehicle available for inspection.',
            'status' => 'available',
            'is_published' => true,
        ], $attributes));
    }

    public function test_draft_vehicles_are_not_public_and_homepage_has_empty_vehicle_inventory(): void
    {
        $vehicle = $this->vehicle(['is_published' => false]);
        $this->get('/automobiles')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Automobiles/Index')->has('vehicles.data', 0)
        );
        $this->get('/automobiles/'.$vehicle->slug)->assertNotFound();
        $this->get('/')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Home')->has('featured.vehicles', 0)
        );
    }

    public function test_published_vehicles_can_be_found_and_filtered(): void
    {
        $toyota = $this->vehicle(['is_featured' => true]);
        $this->vehicle(['title' => 'Honda Civic 2019', 'make' => 'Honda', 'model' => 'Civic', 'year' => 2019]);
        $this->get('/automobiles?make=Toyota')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Automobiles/Index')->has('vehicles.data', 1)
        );
        $this->get('/automobiles/'.$toyota->slug)->assertOk();
        $this->get('/')->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Home')->has('featured.vehicles', 1)
        );
        $this->get('/automobiles/compare?ids='.$toyota->id)->assertOk()->assertInertia(
            fn (Assert $page) => $page->component('Automobiles/Compare')->has('vehicles', 1)
        );
    }

    public function test_unavailable_vehicle_cannot_receive_inspection_or_inquiry(): void
    {
        Mail::fake();
        $vehicle = $this->vehicle(['status' => 'sold']);
        $this->get('/automobiles/'.$vehicle->slug.'/inspect')->assertNotFound();
        $this->post('/inspections', $this->inspectionData($vehicle))->assertSessionHasErrors('inspectable_id');
        $this->post('/inquiries', $this->inquiryData($vehicle))->assertSessionHasErrors('vehicle_id');
    }

    public function test_available_vehicle_receives_inquiry_and_inspection(): void
    {
        Mail::fake();
        $vehicle = $this->vehicle();
        $this->get('/automobiles/'.$vehicle->slug.'/inspect')->assertOk();
        $this->post('/inquiries', $this->inquiryData($vehicle))->assertRedirect(route('automobiles.show', $vehicle));
        $this->assertDatabaseHas('inquiries', ['vehicle_id' => $vehicle->id, 'subject' => 'automobiles']);
        $this->post('/inspections', $this->inspectionData($vehicle))->assertRedirect();
        $this->assertDatabaseHas('inspections', ['inspectable_type' => Vehicle::class, 'inspectable_id' => $vehicle->id]);
    }

    private function inquiryData(Vehicle $vehicle): array
    {
        return [
            'name' => 'Sample Buyer', 'email' => 'buyer@example.test', 'phone' => '08012345678',
            'subject' => 'automobiles', 'vehicle_id' => $vehicle->id, 'inquiry_category' => 'general',
            'message' => 'I would like to inspect this vehicle before purchasing.',
        ];
    }

    private function inspectionData(Vehicle $vehicle): array
    {
        return [
            'inspectable_type' => 'vehicle', 'inspectable_id' => $vehicle->id,
            'customer_name' => 'Sample Buyer', 'customer_email' => 'buyer@example.test',
            'customer_phone' => '08012345678',
            'preferred_date' => now()->next(\Illuminate\Support\Carbon::MONDAY)->toDateString(),
            'preferred_time_slot' => '9am-11am', 'party_size' => 1,
        ];
    }
}
