<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('purchase_order_items')
            || ! Schema::hasColumn('purchase_order_items', 'received_quantity')) {
            return;
        }

        DB::transaction(function () {
            DB::table('purchase_order_items')->update(['received_quantity' => 0]);

            $receivedRows = DB::table('warehouse_slips as slips')
                ->join('warehouse_slip_items as items', 'items.slip_id', '=', 'slips.id')
                ->where('slips.type', 'import')
                ->where('slips.status', 'approved')
                ->whereNotNull('slips.purchase_order_id')
                ->groupBy('slips.purchase_order_id', 'items.product_id')
                ->selectRaw('slips.purchase_order_id, items.product_id, SUM(items.quantity) as received_quantity')
                ->get();

            foreach ($receivedRows as $row) {
                DB::table('purchase_order_items')
                    ->where('purchase_order_id', $row->purchase_order_id)
                    ->where('product_id', $row->product_id)
                    ->update(['received_quantity' => $row->received_quantity]);
            }
        });
    }

    public function down(): void
    {
        // Không hoàn tác vì đây là dữ liệu tổng hợp từ chứng từ kho đã duyệt.
    }
};
