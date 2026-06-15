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
        Schema::table('resale_listings', function (Blueprint $table) {
            $table->boolean('is_auto_drop')->default(false)->after('current_asking_price');
            $table->decimal('floor_price', 12, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resale_listings', function (Blueprint $table) {
            $table->dropColumn('is_auto_drop');
            $table->decimal('floor_price', 12, 2)->nullable(false)->change();
        });
    }
};
