<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketplace_placements', function (Blueprint $table) {
            $table->id();
            $table->morphs('item');
            $table->string('section', 40);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->unique(['item_type', 'item_id', 'section'], 'marketplace_item_section_unique');
            $table->index(['section', 'is_active', 'display_order'], 'marketplace_section_active_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_placements');
    }
};
