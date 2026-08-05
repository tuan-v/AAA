<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_coupons', function (Blueprint $table) {
            $table->string('scope', 20)->default('public')->after('channels');
        });

        Schema::create('coupon_customer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained('pos_coupons')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('status', 20)->default('available');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
            $table->unique(['coupon_id', 'customer_id']);
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->string('status', 20)->default('redeemed')->after('discount_amount');
            $table->timestamp('redeemed_at')->nullable()->after('status');
        });
        DB::table('coupon_usages')->whereNull('reversed_at')->update(['status' => 'redeemed', 'redeemed_at' => DB::raw('created_at')]);
        DB::table('coupon_usages')->whereNotNull('reversed_at')->update(['status' => 'reversed']);
    }

    public function down(): void
    {
        Schema::table('coupon_usages', fn (Blueprint $table) => $table->dropColumn(['status', 'redeemed_at']));
        Schema::dropIfExists('coupon_customer_assignments');
        Schema::table('pos_coupons', fn (Blueprint $table) => $table->dropColumn('scope'));
    }
};
