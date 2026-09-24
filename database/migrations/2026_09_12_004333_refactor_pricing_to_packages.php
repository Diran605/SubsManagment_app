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
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('reference_price');
            $table->decimal('online_price', 10, 2)->nullable()->after('description');
            $table->decimal('offline_price', 10, 2)->nullable()->after('online_price');
            $table->decimal('hybrid_price', 10, 2)->nullable()->after('offline_price');
        });

        Schema::dropIfExists('service_pricings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('service_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('mode');
            $table->decimal('base_price', 10, 2);
            $table->timestamps();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['online_price', 'offline_price', 'hybrid_price']);
            $table->decimal('reference_price', 10, 2)->nullable();
        });
    }
};
