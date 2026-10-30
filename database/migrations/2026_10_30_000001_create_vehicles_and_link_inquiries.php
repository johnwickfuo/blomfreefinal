<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('stock_reference')->unique();
            $table->string('make');
            $table->string('model');
            $table->string('trim')->nullable();
            $table->unsignedSmallInteger('year');
            $table->string('condition');
            $table->string('body_type')->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->string('transmission')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('engine')->nullable();
            $table->string('drivetrain')->nullable();
            $table->unsignedSmallInteger('seats')->nullable();
            $table->string('exterior_color')->nullable();
            $table->string('interior_color')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('viewing_address')->nullable();
            $table->decimal('price', 16, 2);
            $table->boolean('negotiable')->default(false);
            $table->string('status')->default('available');
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->longText('description');
            $table->json('features')->nullable();
            $table->json('known_issues')->nullable();
            $table->string('accident_history')->nullable();
            $table->string('service_history')->nullable();
            $table->string('customs_status')->nullable();
            $table->json('documents')->nullable();
            $table->text('internal_notes')->nullable();
            $table->string('video_url')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'status', 'is_featured']);
            $table->index(['make', 'model', 'year']);
            $table->index(['price', 'mileage_km']);
            $table->index(['condition', 'state']);
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('inquiry_category')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn('inquiry_category');
        });
        Schema::dropIfExists('vehicles');
    }
};
