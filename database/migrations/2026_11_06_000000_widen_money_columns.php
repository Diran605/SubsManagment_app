<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * decimal(10, 2) tops out just under 100 million FCFA, which a large
 * per-student contract can exceed. Widen the money columns to match the
 * budget tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('amount', 14, 2)->change();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->change();
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('total_expected_amount', 14, 2)->default(0)->change();
        });

        Schema::table('budget_packages', function (Blueprint $table) {
            $table->decimal('expected_amount', 14, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->default(0)->change();
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->decimal('total_expected_amount', 12, 2)->default(0)->change();
        });

        Schema::table('budget_packages', function (Blueprint $table) {
            $table->decimal('expected_amount', 12, 2)->default(0)->change();
        });
    }
};
