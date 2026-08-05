<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_coupons', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('is_active');
            $table->json('channels')->nullable()->after('status');
            $table->unsignedInteger('usage_limit_per_customer')->nullable()->after('usage_limit');
            $table->text('description')->nullable()->after('name');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained('pos_coupons')->cascadeOnDelete();
            $table->foreignId('sales_order_id')->unique()->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('channel', 20);
            $table->decimal('discount_amount', 18, 2);
            $table->timestamp('reversed_at')->nullable();
            $table->timestamps();
            $table->index(['coupon_id', 'customer_id', 'reversed_at']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('coupon_code_snapshot')->nullable()->after('pos_coupon_id');
            $table->string('coupon_name_snapshot')->nullable()->after('coupon_code_snapshot');
            $table->string('coupon_type_snapshot', 20)->nullable()->after('coupon_name_snapshot');
            $table->decimal('coupon_value_snapshot', 18, 2)->nullable()->after('coupon_type_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['coupon_code_snapshot', 'coupon_name_snapshot', 'coupon_type_snapshot', 'coupon_value_snapshot']);
        });
        Schema::dropIfExists('coupon_usages');
        Schema::table('pos_coupons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn(['status', 'channels', 'usage_limit_per_customer', 'description']);
        });
    }
};
