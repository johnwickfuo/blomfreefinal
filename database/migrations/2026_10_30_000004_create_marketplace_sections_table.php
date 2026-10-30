<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketplace_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key', 40)->unique();
            $table->string('title', 120);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('display_order')->default(50);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_sections');
    }
};
