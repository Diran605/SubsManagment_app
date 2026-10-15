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
        // 1. Add new columns
        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->after('description');
            $table->json('supported_modes')->nullable()->after('price');
        });

        // 2. Migrate data
        $packages = \DB::table('packages')->get();
        foreach ($packages as $pkg) {
            $modes = [];
            $maxPrice = 0;
            if ($pkg->online_price) {
                $modes[] = 'online';
                $maxPrice = max($maxPrice, $pkg->online_price);
            }
            if ($pkg->offline_price) {
                $modes[] = 'offline';
                $maxPrice = max($maxPrice, $pkg->offline_price);
            }
            if ($pkg->hybrid_price) {
                $modes[] = 'hybrid';
                $maxPrice = max($maxPrice, $pkg->hybrid_price);
            }

            \DB::table('packages')
                ->where('id', $pkg->id)
                ->update([
                    'price' => $maxPrice,
                    'supported_modes' => json_encode($modes),
                ]);
        }

        // 3. Drop old columns
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['online_price', 'offline_price', 'hybrid_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['price', 'supported_modes']);
            
            $table->decimal('online_price', 10, 2)->nullable();
            $table->decimal('offline_price', 10, 2)->nullable();
            $table->decimal('hybrid_price', 10, 2)->nullable();
        });
    }
};
