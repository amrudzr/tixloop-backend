<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resale_listings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('seller_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('original_price', 12, 2);
            $table->decimal('current_asking_price', 12, 2);
            $table->decimal('floor_price', 12, 2);
            $table->decimal('hard_cap_price', 12, 2);
            $table->string('verification_status')->default('pending');
            $table->string('listing_status')->default('aktif');
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resale_listings');
    }
};
