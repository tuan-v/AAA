<template>
    <div class="tx-modal">
        <!-- HEADER -->
        <div class="tx-header">
            <div>
                <h2 class="tx-title">
                    {{ isEdit ? "Cập nhật giao dịch" : "Thêm giao dịch" }}
                </h2>
                <p v-if="form.code" class="tx-code">
                    <i class="ti ti-hash"></i> {{ form.code }}
                </p>
            </div>

            <!-- TYPE TABS -->
            <div class="type-tabs">
                <button
                    v-for="t in typeOptions"
                    :key="t.value"
                    class="tab"
                    :class="{ active: form.type === t.value }"
                    @click="form.type = t.value"
                >
                    <i :class="t.icon"></i>
                    {{ t.label }}
                </button>
            </div>
        </div>

        <!-- FORM BODY -->
        <div class="tx-body">
            <!-- RELATED ORDER -->
            <div class="section-label">Đơn hàng liên quan</div>
            <div v-if="form.type === 'receipt'" class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-user"></i>Khách hàng
                    </label>
                    <select
                        v-model="form.customer_id"
                        class="input"
                        @change="onCustomerChange"
                    >
                        <option value="">Chọn khách hàng</option>
                        <option
                            v-for="c in customers || []"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.code }} - {{ c.name }}
                        </option>
                    </select>
                </div>
                <div class="field">
                    <label class="label">
                        <i class="ti ti-file-description"></i>Đơn bán
                    </label>
                    <select
                        v-model="form.sales_order_id"
                        class="input"
                        @change="onSalesOrderChange"
                    >
                        <option value="">Chọn đơn bán</option>
                        <option
                            v-for="o in orderOptions"
                            :key="o.id"
                            :value="o.id"
                        >
                            {{ o.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="form.type === 'payment'" class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-building-store"></i>Nhà cung cấp
                    </label>
                    <select
                        v-model="form.supplier_id"
                        class="input"
                        @change="onSupplierChange"
                    >
                        <option value="">Chọn nhà cung cấp</option>
                        <option
                            v-for="s in suppliers || []"
                            :key="s.id"
                            :value="s.id"
                        >
                            {{ s.code }} - {{ s.name }}
                        </option>
                    </select>
                </div>
                <div class="field">
                    <label class="label">
                        <i class="ti ti-file-description"></i>Đơn mua
                    </label>
                    <select
                        v-model="form.purchase_order_id"
                        class="input"
                        @change="onPurchaseOrderChange"
                    >
                        <option value="">Chọn đơn mua</option>
                        <option
                            v-for="o in orderOptions"
                            :key="o.id"
                            :value="o.id"
                        >
                            {{ o.label }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="currencyHintMessage" class="currency-hint">
                <i class="ti ti-info-circle"></i>
                {{ currencyHintMessage }}
            </div>

            <div v-if="currencyMismatchMessage" class="currency-warning">
                <i class="ti ti-alert-triangle"></i>
                {{ currencyMismatchMessage }}
            </div>

            <div class="divider"></div>

            <!-- CATEGORY -->
            <div class="section-label">Phân loại</div>
            <div class="field">
                <label class="label">
                    <i class="ti ti-tag"></i>Loại thanh toán
                </label>
                <select v-model="form.category_id" class="input">
                    <option value="">Chọn loại</option>
                    <option
                        v-for="c in filteredCategories"
                        :key="c.id"
                        :value="c.id"
                    >
                        {{ c.name }}
                    </option>
                </select>
                <p v-if="categoryMismatchMessage" class="category-error">
                    <i class="ti ti-alert-circle"></i>
                    {{ categoryMismatchMessage }}
                </p>
            </div>
            <div class="divider"></div>

            <!-- ACCOUNTS -->
            <div class="section-label">Tài khoản</div>

            <div class="grid2">
                <!-- RECEIPT: to_account -->
                <div v-if="form.type === 'receipt'" class="field">
                    <label class="label">
                        <i class="ti ti-building-bank"></i>Tài khoản nhận
                    </label>
                    <select v-model="form.to_account_id" class="input">
                        <option value="">Chọn tài khoản</option>
                        <option
                            v-for="a in accounts || []"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ a.name }}
                        </option>
                    </select>
                </div>

                <!-- PAYMENT: from_account -->
                <div v-if="form.type === 'payment'" class="field">
                    <label class="label">
                        <i class="ti ti-building-bank"></i>Tài khoản chi
                    </label>
                    <select v-model="form.from_account_id" class="input">
                        <option value="">Chọn tài khoản</option>
                        <option
                            v-for="a in accounts || []"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ a.name }}
                        </option>
                    </select>
                </div>

                <!-- TRANSFER: both -->
                <template v-if="form.type === 'transfer'">
                    <div class="field">
                        <label class="label">
                            <i class="ti ti-building-bank"></i>Từ tài khoản
                        </label>
                        <select v-model="form.from_account_id" class="input">
                            <option value="">Chọn tài khoản</option>
                            <option
                                v-for="a in accounts || []"
                                :key="a.id"
                                :value="a.id"
                            >
                                {{ a.name }}
                            </option>
                        </select>
                    </div>
                    <div class="field">
                        <label class="label">
                            <i class="ti ti-building-bank"></i>Đến tài khoản
                        </label>
                        <select v-model="form.to_account_id" class="input">
                            <option value="">Chọn tài khoản</option>
                            <option
                                v-for="a in accounts || []"
                                :key="a.id"
                                :value="a.id"
                            >
                                {{ a.name }}
                            </option>
                        </select>
                    </div>
                </template>
            </div>

            <div class="divider"></div>

            <!-- AMOUNT + CURRENCY -->
            <div class="section-label">Số tiền</div>
            <div class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-currency-dong"></i>Số tiền
                    </label>
                    <div class="amount-wrap">
                        <input
                            :value="formatMoney(form.amount)"
                            @input="handleAmountInput"
                            type="text"
                            class="input"
                            placeholder="0"
                        />
                        <span class="currency-badge">
                            {{ selectedCurrencyLabel }}
                        </span>
                    </div>
                </div>
                <div class="field">
                    <label class="label">
                        <i class="ti ti-world"></i>Tiền tệ
                    </label>
                    <select
                        v-model="form.currency_id"
                        class="input"
                        @change="onCurrencyChange"
                    >
                        <option value="">Chọn tiền tệ</option>
                        <option
                            v-for="c in normalizedCurrencies"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.code }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="divider"></div>

            <!-- DESCRIPTION -->
            <div class="section-label">Ghi chú</div>
            <div class="field">
                <label class="label">
                    <i class="ti ti-notes"></i>Nội dung
                </label>
                <textarea
                    v-model="form.description"
                    class="input"
                    rows="3"
                    placeholder="Nhập nội dung giao dịch..."
                ></textarea>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="tx-footer">
            <button class="btn" @click="$emit('close')">Đóng</button>
            <button class="btn btn-primary" @click="save" :disabled="saving">
                <i class="ti ti-check"></i>
                {{
                    saving
                        ? "Đang lưu..."
                        : isEdit
                          ? "Cập nhật"
                          : "Lưu giao dịch"
                }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, watch, ref } from "vue";
import axios from "axios";
import { formatMoney } from "@/config/helpers";
import { toast } from "vue3-toastify";

const props = defineProps({
    transaction: { type: Object, default: null },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    suppliers: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved", "close"]);

const saving = ref(false);

function handleAmountInput(e) {
    const raw = e.target.value.replace(/[^\d]/g, "");
    form.amount = raw ? Number(raw) : 0;
}

const isEdit = computed(() => !!props.transaction?.id);

const typeOptions = [
    { value: "receipt", label: "Thu tiền", icon: "ti ti-arrow-down-circle" },
    { value: "payment", label: "Chi tiền", icon: "ti ti-arrow-up-circle" },
    { value: "transfer", label: "Chuyển khoản", icon: "ti ti-arrows-exchange" },
];

const normalizedCurrencies = computed(() =>
    Array.isArray(props.currencies)
        ? props.currencies
        : props.currencies?.data || [],
);

const selectedCurrency = computed(() => {
    if (!form.currency_id) return null;
    return (
        normalizedCurrencies.value.find(
            (x) => Number(x.id) === Number(form.currency_id),
        ) || null
    );
});

const selectedCurrencyLabel = computed(() => {
    if (!selectedCurrency.value) return "";
    const symbol =
        selectedCurrency.value.symbol || selectedCurrency.value.code || "";
    return `${selectedCurrency.value.code} ${symbol}`.trim();
});

const selectedOrderCurrency = ref(null);
const orderOptions = ref([]);

const currencyHintMessage = computed(() => {
    if (!selectedOrderCurrency.value) return "";
    return;
    `Đơn hàng đang dùng ${selectedOrderCurrency.value.code} (${selectedOrderCurrency.value.symbol || selectedOrderCurrency.value.code || ""}). Giao dịch sẽ tự động khớp theo đơn hàng.`;
});

const currencyMismatchMessage = computed(() => {
    if (!selectedOrderCurrency.value || !form.currency_id) return "";
    if (Number(form.currency_id) !== Number(selectedOrderCurrency.value.id)) {
        return;
        `Đơn hàng này đang dùng ${selectedOrderCurrency.value.code} (${selectedOrderCurrency.value.symbol || selectedOrderCurrency.value.code || ""}). Vui lòng chọn cùng tiền tệ với đơn hàng hoặc tạo giao dịch quy đổi.`;
    }
    return "";
});

// ==================== CATEGORY TYPE VALIDATION ====================

// Ánh xạ transaction.type (receipt/payment/transfer) sang category.type
// (income/expense/transfer) — 2 bảng dùng tên khác nhau nên cần map qua lại.
const TYPE_TO_CATEGORY_TYPE = {
    receipt: "income",
    payment: "expense",
    transfer: "transfer",
};

const CATEGORY_TYPE_LABELS = {
    income: "Thu tiền",
    expense: "Chi tiền",
    transfer: "Chuyển khoản",
};

// Chuẩn hoá danh sách category (phòng trường hợp API trả về dạng {data: [...]})
const normalizedCategories = computed(() =>
    Array.isArray(props.categories)
        ? props.categories
        : props.categories?.data || [],
);

// Chỉ hiển thị category phù hợp với loại giao dịch đang chọn.
// Category có type rỗng/NULL vẫn hiển thị (coi như dùng chung).
const filteredCategories = computed(() => {
    const expectedType = TYPE_TO_CATEGORY_TYPE[form.type];
    return normalizedCategories.value.filter(
        (c) => !c.type || c.type === expectedType,
    );
});

const selectedCategory = computed(() => {
    if (!form.category_id) return null;
    return (
        normalizedCategories.value.find(
            (c) => Number(c.id) === Number(form.category_id),
        ) || null
    );
});

// Thông báo lỗi khi category đã chọn không khớp loại giao dịch hiện tại
// (phòng trường hợp dữ liệu cũ khi sửa giao dịch, hoặc select chưa lọc kịp)
const categoryMismatchMessage = computed(() => {
    const cat = selectedCategory.value;
    if (!cat || !cat.type) return "";
    const expectedType = TYPE_TO_CATEGORY_TYPE[form.type];
    if (cat.type !== expectedType) {
        const catLabel = CATEGORY_TYPE_LABELS[cat.type] || cat.type;
        return toast.error(
            `Loại thanh toán "${cat.name}" chỉ dùng cho giao dịch ${catLabel}, không phù hợp với giao dịch bạn đang tạo.`,
        );
    }
    return "";
});
const form = reactive({
    id: null,
    code: "",
    type: "receipt",
    amount: 0,
    currency_id: "",
    category_id: "",
    from_account_id: "",
    to_account_id: "",
    customer_id: "",
    supplier_id: "",
    sales_order_id: "",
    purchase_order_id: "",
    exchange_rate: 1,
    transaction_date: "",
    description: "",
});

function resetForm() {
    Object.assign(form, {
        id: null,
        code: "",
        type: "receipt",
        amount: 0,
        currency_id: "",
        category_id: "",
        from_account_id: "",
        to_account_id: "",
        customer_id: "",
        supplier_id: "",
        sales_order_id: "",
        purchase_order_id: "",
        exchange_rate: 1,
        transaction_date: "",
        description: "",
    });
    orderOptions.value = [];
    selectedOrderCurrency.value = null;
}

watch(
    () => props.transaction,
    (val) => {
        if (!val?.id) {
            resetForm();
            return;
        }
        Object.assign(form, {
            id: val.id,
            code: val.code ?? "",
            type: val.type ?? "receipt",
            amount: val.amount ?? 0,
            currency_id: val.currency_id ?? "",
            category_id: val.category_id ?? "",
            from_account_id: val.from_account_id ?? "",
            to_account_id: val.to_account_id ?? "",
            customer_id: val.customer_id ?? "",
            supplier_id: val.supplier_id ?? "",
            sales_order_id: val.sales_order_id ?? "",
            purchase_order_id: val.purchase_order_id ?? "",
            exchange_rate: val.exchange_rate ?? 1,
            transaction_date: val.transaction_date?.slice?.(0, 10) ?? "",
            description: val.description ?? "",
        });

        if (form.type === "receipt" && form.customer_id) {
            loadOrderOptions();
        }

        if (form.type === "payment" && form.supplier_id) {
            loadOrderOptions();
        }
    },
    { immediate: true },
);

// Khi đổi loại giao dịch (receipt/payment/transfer): reset các field liên quan
// gồm cả category_id, vì category cũ có thể không còn phù hợp với loại mới.
watch(
    () => form.type,
    () => {
        form.sales_order_id = "";
        form.purchase_order_id = "";
        form.customer_id = "";
        form.supplier_id = "";
        form.category_id = "";
        orderOptions.value = [];
        selectedOrderCurrency.value = null;
    },
);

watch(
    () => form.customer_id,
    (value) => {
        if (!value) {
            orderOptions.value = [];
            return;
        }
        if (form.type === "receipt") {
            loadOrderOptions();
        }
    },
);

watch(
    () => form.supplier_id,
    (value) => {
        if (!value) {
            orderOptions.value = [];
            return;
        }
        if (form.type === "payment") {
            loadOrderOptions();
        }
    },
);

async function loadOrderOptions() {
    try {
        if (form.type === "receipt") {
            if (!form.customer_id) {
                orderOptions.value = [];
                return;
            }

            const res = await axios.get("/api/sale/orders", {
                params: { customer_id: form.customer_id },
            });

            orderOptions.value = (res.data.data || []).map((order) => ({
                id: order.id,
                label: `${order.code} - ${formatMoney(order.total_amount ?? 0)}`,
                customer_id: order.customer?.id ?? form.customer_id,
                currency: order.currency || null,
                currency_id: order.currency?.id ?? order.currency_id ?? null,
                exchange_rate: order.currency?.exchange_rate ?? 1,
            }));
            return;
        }

        if (form.type === "payment") {
            if (!form.supplier_id) {
                orderOptions.value = [];
                return;
            }

            const res = await axios.get("/api/purchase/orders", {
                params: { supplier_id: form.supplier_id },
            });

            orderOptions.value = (res.data.data || []).map((order) => ({
                id: order.id,
                label: `${order.code} - ${formatMoney(order.total_amount ?? 0)}`,
                supplier_id: order.supplier?.id ?? form.supplier_id,
                currency: order.currency || null,
                currency_id: order.currency?.id ?? order.currency_id ?? null,
                exchange_rate: order.currency?.exchange_rate ?? 1,
            }));
            return;
        }

        orderOptions.value = [];
    } catch (error) {
        console.error("Không tải được danh sách đơn hàng liên quan", error);
        orderOptions.value = [];
    }
}

function onCustomerChange() {
    form.sales_order_id = "";
    selectedOrderCurrency.value = null;
    loadOrderOptions();
}

function onSupplierChange() {
    form.purchase_order_id = "";
    selectedOrderCurrency.value = null;
    loadOrderOptions();
}

function onCurrencyChange() {
    const currency = selectedCurrency.value;
    if (currency?.exchange_rate) {
        form.exchange_rate = Number(currency.exchange_rate) || 1;
    }
}

function onSalesOrderChange() {
    const selected = orderOptions.value.find(
        (order) => Number(order.id) === Number(form.sales_order_id),
    );
    if (selected?.customer_id) {
        form.customer_id = selected.customer_id;
    }
    if (selected?.currency_id) {
        form.currency_id = selected.currency_id;
        form.exchange_rate = Number(selected.exchange_rate) || 1;
        selectedOrderCurrency.value = selected.currency || null;
    }
}

function onPurchaseOrderChange() {
    const selected = orderOptions.value.find(
        (order) => Number(order.id) === Number(form.purchase_order_id),
    );
    if (selected?.supplier_id) {
        form.supplier_id = selected.supplier_id;
    }
    if (selected?.currency_id) {
        form.currency_id = selected.currency_id;
        form.exchange_rate = Number(selected.exchange_rate) || 1;
        selectedOrderCurrency.value = selected.currency || null;
    }
}

async function save() {
    // Chặn lưu ngay tại FE nếu category không khớp loại giao dịch
    if (categoryMismatchMessage.value) {
        window.alert(categoryMismatchMessage.value);
        return;
    }

    if (currencyMismatchMessage.value) {
        window.alert(currencyMismatchMessage.value);
        return;
    }

    saving.value = true;
    try {
        const payload = {
            ...form,
        };
        if (isEdit.value) {
            await axios.put(`/api/accountant/transactions/${form.id}`, payload);
        } else {
            await axios.post(`/api/accountant/transactions`, payload);
        }
        emit("saved");
    } catch (error) {
        console.error(error);
        // Bắt lỗi nghiệp vụ từ BE (vd category không khớp loại giao dịch) trả về qua message
        const msg =
            error.response?.data?.message ||
            "Có lỗi xảy ra khi lưu giao dịch, vui lòng kiểm tra lại.";
        window.alert(msg);
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
/* ── Container ── */
.tx-modal {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12);
    width: 100%;
    max-width: 820px;
    overflow: hidden;
    z-index: 50;
}

/* ── Header ── */
.tx-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
}
.tx-title {
    font-size: 18px;
    font-weight: 600;
    color: #111;
}
.tx-code {
    font-size: 12px;
    color: #185fa5;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 3px;
}

/* ── Type tabs ── */
.type-tabs {
    display: flex;
    gap: 4px;
    padding: 4px;
    background: #f5f5f5;
    border-radius: 10px;
}
.tab {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 7px;
    border: none;
    background: transparent;
    font-size: 13px;
    font-weight: 500;
    color: #666;
    cursor: pointer;
    transition: all 0.15s;
}
.tab.active {
    background: #fff;
    color: #185fa5;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

/* ── Body ── */
.tx-body {
    padding: 1.25rem 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.section-label {
    font-size: 11px;
    font-weight: 600;
    color: #999;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.divider {
    border: none;
    border-top: 1px solid #f0f0f0;
    margin: 2px 0;
}
.grid2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.label {
    font-size: 12px;
    font-weight: 500;
    color: #555;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Inputs ── */
.input {
    width: 100%;
    padding: 8px 12px;
    font-size: 14px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    color: #111;
    outline: none;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
    font-family: inherit;
}
.input:focus {
    border-color: #185fa5;
    box-shadow: 0 0 0 3px rgba(24, 95, 165, 0.1);
}
textarea.input {
    resize: none;
    line-height: 1.55;
}

/* ── Amount ── */
.amount-wrap {
    position: relative;
}
.amount-wrap .input {
    padding-right: 52px;
}
.currency-badge {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    font-weight: 600;
    color: #888;
    pointer-events: none;
}

/* ── Category error ── */
.category-error {
    font-size: 12px;
    color: #c0392b;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

/* ── Currency hint / warning ── */
.currency-hint {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #185fa5;
    background: #eef5fc;
    border: 1px solid #d3e6f7;
    border-radius: 8px;
    padding: 8px 12px;
}
.currency-warning {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #c0392b;
    background: #fdecea;
    border: 1px solid #f5c6cb;
    border-radius: 8px;
    padding: 8px 12px;
}

/* ── Footer ── */
.tx-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f0f0f0;
}
.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    background: #fff;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.15s;
}
.btn:hover {
    background: #f7f7f7;
}
.btn-primary {
    background: #185fa5;
    color: #fff;
    border-color: #185fa5;
}
.btn-primary:hover {
    background: #0c447c;
    border-color: #0c447c;
}
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
