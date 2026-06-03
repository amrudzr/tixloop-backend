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
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('event_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('current_owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('original_buyer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ticket_code', 50)->index();
            $table->string('seat_number')->nullable();
            $table->json('ticket_metadata')->nullable();
            $table->string('ticket_proof_path')->nullable();
            $table->string('ticket_proof_type')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->string('qr_secret_key')->nullable();
            $table->string('device_binding_id')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamp('burned_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->unique(['ticket_code', 'current_owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
