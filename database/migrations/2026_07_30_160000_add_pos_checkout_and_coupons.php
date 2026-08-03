<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('type', 20); // fixed, percent
            $table->decimal('value', 18, 2);
            $table->decimal('minimum_order_amount', 18, 2)->default(0);
            $table->decimal('maximum_discount', 18, 2)->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('pos_coupon_id')->nullable()->after('paid_amount')->constrained('pos_coupons')->nullOnDelete();
            $table->decimal('discount_amount', 18, 2)->default(0)->after('pos_coupon_id');
            $table->decimal('tendered_amount', 18, 2)->default(0)->after('discount_amount');
            $table->decimal('change_amount', 18, 2)->default(0)->after('tendered_amount');
            $table->string('payment_reference')->nullable()->after('discount_amount');
            $table->timestamp('completed_at')->nullable()->after('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pos_coupon_id');
            $table->dropColumn(['discount_amount', 'tendered_amount', 'change_amount', 'payment_reference', 'completed_at']);
        });
        Schema::dropIfExists('pos_coupons');
    }
};
