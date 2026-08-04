<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->foreignId('province_id')->nullable()->after('phone')->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->after('province_id')->constrained()->nullOnDelete();
            $table->string('province_name')->nullable()->after('ward_id');
            $table->string('ward_name')->nullable()->after('province_name');
            $table->string('address_detail', 500)->nullable()->after('ward_name');
        });
    }

    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ward_id');
            $table->dropConstrainedForeignId('province_id');
            $table->dropColumn(['province_name', 'ward_name', 'address_detail']);
        });
    }
};
