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
        // 1. Add category to services
        Schema::table('services', function (Blueprint $table) {
            $table->string('category')->default('standard')->after('description');
        });

        // 2. Add pricing_model and payer_type to packages
        Schema::table('packages', function (Blueprint $table) {
            $table->string('pricing_model')->default('flat')->after('price'); // 'flat' or 'per_student'
            $table->string('payer_type')->default('school')->after('pricing_model'); // 'school' or 'student_momo'
        });

        // 3. Add student_count to subscriptions
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->integer('student_count')->nullable()->after('amount');
        });

        // 4. Add avg_student_count to budget_packages
        Schema::table('budget_packages', function (Blueprint $table) {
            $table->integer('avg_student_count')->nullable()->after('expected_licenses');
        });

        // 5. Create subscription_collections table for Mobile Money collections
        Schema::create('subscription_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('collection_date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_collections');

        Schema::table('budget_packages', function (Blueprint $table) {
            $table->dropColumn('avg_student_count');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('student_count');
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['pricing_model', 'payer_type']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
