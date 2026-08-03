<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE warehouse_slips MODIFY type VARCHAR(20) NOT NULL");
        } else {
            Schema::table('warehouse_slips', fn (Blueprint $table) => $table->string('type', 20)->change());
        }

        Schema::table('warehouse_slips', function (Blueprint $table) {
            $table->foreignId('return_of_slip_id')->nullable()->after('sales_order_id')
                ->constrained('warehouse_slips')->nullOnDelete();
        });

        // Chuyển các yêu cầu hoàn đã từng ghi trực tiếp trên phiếu xuất thành
        // phiếu nhập hàng hoàn độc lập, đồng thời giữ nguyên lịch sử phiếu xuất.
        DB::table('warehouse_slips')->where('type', 'export')->whereNotNull('return_status')
            ->orderBy('id')->get()->each(function ($export) {
                $returnId = DB::table('warehouse_slips')->insertGetId([
                    'company_id' => $export->company_id,
                    'code' => 'PHT-OLD-'.$export->id,
                    'warehouse_id' => $export->warehouse_id,
                    'sales_order_id' => $export->sales_order_id,
                    'return_of_slip_id' => $export->id,
                    'type' => 'return',
                    'note' => 'Phiếu nhập hoàn chuyển đổi từ '.$export->code,
                    'created_by' => $export->return_requested_by ?: $export->created_by,
                    'approved_by' => $export->return_approved_by,
                    'approved_at' => $export->return_approved_at,
                    'status' => $export->return_status === 'approved' ? 'approved' : 'pending',
                    'return_status' => $export->return_status,
                    'return_reason' => $export->return_reason,
                    'return_requested_by' => $export->return_requested_by,
                    'return_requested_at' => $export->return_requested_at,
                    'return_received_by' => $export->return_received_by,
                    'return_received_at' => $export->return_received_at,
                    'return_approved_by' => $export->return_approved_by,
                    'return_approved_at' => $export->return_approved_at,
                    'created_at' => $export->return_requested_at ?: $export->created_at,
                    'updated_at' => now(),
                ]);

                DB::table('warehouse_slip_items')->where('slip_id', $export->id)->get()->each(function ($item) use ($returnId) {
                    $copy = (array) $item;
                    unset($copy['id']);
                    $copy['slip_id'] = $returnId;
                    $copy['returned_quantity'] = $item->returned_quantity ?: $item->quantity;
                    DB::table('warehouse_slip_items')->insert($copy);
                });

                DB::table('warehouse_slips')->where('id', $export->id)->update([
                    'return_status' => null, 'return_reason' => null,
                    'return_requested_by' => null, 'return_requested_at' => null,
                    'return_received_by' => null, 'return_received_at' => null,
                    'return_approved_by' => null, 'return_approved_at' => null,
                ]);
                DB::table('warehouse_slip_items')->where('slip_id', $export->id)->update(['returned_quantity' => 0]);
            });
    }

    public function down(): void
    {
        Schema::table('warehouse_slips', fn (Blueprint $table) => $table->dropConstrainedForeignId('return_of_slip_id'));
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE warehouse_slips MODIFY type ENUM('import','export') NOT NULL");
        }
    }
};
