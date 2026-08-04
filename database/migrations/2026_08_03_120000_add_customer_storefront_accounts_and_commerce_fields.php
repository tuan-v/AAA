<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['company_id', 'email']);
            $table->unique(['company_id', 'customer_id']);
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_account_id')->constrained()->cascadeOnDelete();
            $table->string('label')->default('Nhà riêng');
            $table->string('recipient_name');
            $table->string('phone', 30);
            $table->text('address');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('storefront_visible')->default(true)->after('status');
            $table->decimal('promotional_price', 18, 2)->nullable()->after('sell_price');
            $table->timestamp('promotion_starts_at')->nullable()->after('promotional_price');
            $table->timestamp('promotion_ends_at')->nullable()->after('promotion_starts_at');
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('customer_account_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->string('shipping_method', 30)->default('standard')->after('payment_method');
            $table->decimal('shipping_fee', 18, 2)->default(0)->after('shipping_method');
            $table->string('tracking_code')->nullable()->after('shipping_fee');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_account_id');
            $table->dropColumn(['shipping_method', 'shipping_fee', 'tracking_code']);
        });
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn([
            'storefront_visible', 'promotional_price', 'promotion_starts_at', 'promotion_ends_at',
        ]));
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customer_accounts');
    }
};
