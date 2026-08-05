<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('recipient_name', 150)->nullable()->after('customer_account_id');
            $table->string('recipient_phone', 30)->nullable()->after('recipient_name');
            $table->string('recipient_email', 150)->nullable()->after('recipient_phone');
        });

        DB::table('sales_orders')->orderBy('id')->chunkById(200, function ($orders) {
            foreach ($orders as $order) {
                $customer = DB::table('customers')->where('id', $order->customer_id)->first();
                if ($customer) {
                    DB::table('sales_orders')->where('id', $order->id)->update([
                        'recipient_name' => $customer->name,
                        'recipient_phone' => $customer->phone,
                        'recipient_email' => $customer->email,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['recipient_name', 'recipient_phone', 'recipient_email']);
        });
    }
};
