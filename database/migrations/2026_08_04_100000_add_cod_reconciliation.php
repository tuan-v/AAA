<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->foreignId('shipping_partner_id')->nullable()->after('shipping_method')->constrained()->nullOnDelete();
            $table->string('payment_status', 30)->default('unpaid')->after('payment_method');
            $table->string('cod_status', 30)->nullable()->after('payment_status')->index();
            $table->decimal('cod_amount', 18, 2)->default(0)->after('cod_status');
            $table->timestamp('cod_collected_at')->nullable()->after('cod_amount');
            $table->timestamp('cod_reconciled_at')->nullable()->after('cod_collected_at');
        });
        DB::table('sales_orders')->where('sales_channel', 'pos')->where('status', 'completed')->update(['payment_status' => 'paid']);
        DB::table('sales_orders')->where('sales_channel', 'storefront')->where('payment_method', 'cod')->update(['cod_status' => 'pending']);

        Schema::create('cod_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipping_partner_id')->constrained()->restrictOnDelete();
            $table->string('code');
            $table->date('reconciliation_date');
            $table->decimal('cod_amount', 18, 2);
            $table->decimal('shipping_fee', 18, 2)->default(0);
            $table->decimal('service_fee', 18, 2)->default(0);
            $table->decimal('insurance_fee', 18, 2)->default(0);
            $table->decimal('adjustment_amount', 18, 2)->default(0);
            $table->decimal('received_amount', 18, 2);
            $table->foreignId('account_id')->constrained()->restrictOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_reference')->nullable();
            $table->string('status', 20)->default('approved');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('cod_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cod_reconciliation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->restrictOnDelete();
            $table->string('tracking_code')->nullable();
            $table->decimal('cod_amount', 18, 2);
            $table->timestamps();
            $table->unique('sales_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cod_reconciliation_items');
        Schema::dropIfExists('cod_reconciliations');
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_partner_id');
            $table->dropColumn(['payment_status', 'cod_status', 'cod_amount', 'cod_collected_at', 'cod_reconciled_at']);
        });
        Schema::dropIfExists('shipping_partners');
    }
};
