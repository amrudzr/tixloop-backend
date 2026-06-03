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
        Schema::create('ticket_ownership_history', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUlid('previous_owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('new_owner_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('transferred_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_ownership_history');
    }
};
