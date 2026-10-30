<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInquiryRequest;
use App\Mail\InquiryReceivedAdmin;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        // Honeypot — checked after the field-level validation passes. Bots
        // that fill every input get a silent "success" so they don't iterate.
        if (filled($request->input('website'))) {
            return redirect()
                ->route('contact.index')
                ->with('success', "Thanks — we've received your message and will be in touch soon.");
        }

        $data = $request->validated();
        if (($data['subject'] ?? '') === 'automobiles' && ! empty($data['vehicle_id'])) {
            $vehicle = Vehicle::published()->where('status', 'available')->findOrFail($data['vehicle_id']);
            $data['related_url'] = route('automobiles.show', $vehicle);
        }
        $inquiry = Inquiry::create($data);

        $adminEmail = Setting::get('admin_notification_email', 'admin@blomfree.com');
        Mail::to($adminEmail)->send(new InquiryReceivedAdmin($inquiry));

        return redirect($inquiry->vehicle_id ? route('automobiles.show', $inquiry->vehicle) : route('contact.index'))
            ->with('success', "Thanks {$inquiry->name} — we've received your message and will reply shortly.");
    }
}
