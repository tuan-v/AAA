/**
 * Build editable data for a new sales order from a historical order.
 * System identity, workflow, fulfilment and debt fields are intentionally
 * excluded so the result is always submitted as a brand-new pending order.
 */
export function cloneSalesOrderForCreate(order) {
    if (!order) return null;

    return {
        source_order_id: order.id ?? null,
        customer_id: order.customer_id ?? order.customer?.id ?? null,
        currency_id: order.currency_id ?? order.currency?.id ?? null,
        province_id: order.province_id ?? order.province?.id ?? null,
        ward_id: order.ward_id ?? order.ward?.id ?? null,
        address_detail: order.address_detail ?? order.shipping_address ?? "",
        expected_delivery_date: "",
        note: order.note ?? "",
        items: (order.items ?? []).map((item) => ({
            product_id: item.product_id ?? item.product?.id ?? null,
            quantity: Number(item.quantity ?? 1),
            unit_price: Number(item.unit_price ?? item.price ?? 0),
            vat_percent: Number(item.vat_percent ?? 0),
            amount: Number(item.amount ?? item.total_amount ?? 0),
        })),
    };
}
