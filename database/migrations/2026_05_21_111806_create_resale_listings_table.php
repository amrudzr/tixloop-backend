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
            $table->foreignUlid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('ticket_type');
            $table->string('seat_number')->nullable();
            $table->decimal('original_price', 12, 2);
            $table->decimal('current_price', 12, 2);
            $table->decimal('floor_price', 12, 2);
            $table->boolean('burn_prevention_active')->default(true);
            $table->timestamp('last_price_drop_at')->nullable();
            $table->boolean('verified_seller')->default(false);
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
