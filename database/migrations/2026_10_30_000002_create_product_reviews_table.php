<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('display_name', 60);
            $table->string('title', 120);
            $table->text('body');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['product_id', 'user_id']);
            $table->index(['product_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
