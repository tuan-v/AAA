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

            <div v-if="selectedOrderRemaining !== null" class="currency-hint">
                <i class="ti ti-cash"></i>
                Số tiền còn lại của đơn:
                <strong
                    >{{ formatMoney(selectedOrderRemaining) }}
                    {{ selectedCurrencyLabel }}</strong
                >
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
                    <i class="ti ti-tag"></i>Loại thanh toán <span class="required">*</span>
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
                <p v-if="errors.category_id" class="field-error">{{ errors.category_id }}</p>
                <p v-if="categoryMismatchMessage" class="category-error">
                    <i class="ti ti-alert-circle"></i>
                    {{ categoryMismatchMessage }}
                </p>
            </div>
            <div class="field">
                <label class="label">
                    <i class="ti ti-wallet"></i>Hình thức giao dịch
                </label>
                <select
                    v-model="form.payment_method"
                    class="input"
                    :disabled="form.type === 'transfer'"
                >
                    <option value="cash">Tiền mặt</option>
                    <option value="bank_transfer">Chuyển khoản</option>
                </select>
                <p v-if="form.type === 'transfer'" class="hint-text">
                    Chuyển nội bộ không làm phát sinh hoặc thanh toán công nợ.
                </p>
            </div>
            <div class="divider"></div>

            <!-- ACCOUNTS -->
            <div class="section-label">Tài khoản</div>

            <div class="grid2">
                <!-- RECEIPT: to_account -->
                <div v-if="form.type === 'receipt'" class="field">
                    <label class="label">
                        <i class="ti ti-building-bank"></i>Tài khoản nhận <span class="required">*</span>
                    </label>
                    <select v-model="form.to_account_id" class="input">
                        <option value="">Chọn tài khoản</option>
                        <option
                            v-for="a in filteredAccounts"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ accountLabel(a) }}
                        </option>
                    </select>
                    <p v-if="errors.to_account_id" class="field-error">{{ errors.to_account_id }}</p>
                </div>

                <!-- PAYMENT: from_account -->
                <div v-if="form.type === 'payment'" class="field">
                    <label class="label">
                        <i class="ti ti-building-bank"></i>Tài khoản chi <span class="required">*</span>
                    </label>
                    <select v-model="form.from_account_id" class="input">
                        <option value="">Chọn tài khoản</option>
                        <option
                            v-for="a in filteredAccounts"
                            :key="a.id"
                            :value="a.id"
                        >
                            {{ accountLabel(a) }}
                        </option>
                    </select>
                    <p v-if="errors.from_account_id" class="field-error">{{ errors.from_account_id }}</p>
                    <p v-if="selectedPaymentAccount" class="account-balance">
                        <i class="ti ti-wallet"></i>
                        Số dư khả dụng:
                        <strong>{{
                            formatAccountBalance(selectedPaymentAccount)
                        }}</strong>
                    </p>
                </div>

                <!-- TRANSFER: both -->
                <template v-if="form.type === 'transfer'">
                    <div class="field">
                        <label class="label">
                            <i class="ti ti-building-bank"></i>Tài khoản chuyển <span class="required">*</span>
                        </label>
                        <select v-model="form.from_account_id" class="input">
                            <option value="">Chọn tài khoản</option>
                            <option
                                v-for="a in filteredAccounts"
                                :key="a.id"
                                :value="a.id"
                            >
                                {{ accountLabel(a) }}
                            </option>
                        </select>
                        <p v-if="errors.from_account_id" class="field-error">{{ errors.from_account_id }}</p>
                    </div>
                    <div class="field">
                        <label class="label">
                            <i class="ti ti-building-bank"></i>Tài khoản nhận <span class="required">*</span>
                        </label>
                        <select v-model="form.to_account_id" class="input">
                            <option value="">Chọn tài khoản</option>
                            <option
                                v-for="a in filteredAccounts"
                                :key="a.id"
                                :value="a.id"
                            >
                                {{ accountLabel(a) }}
                            </option>
                        </select>
                        <p v-if="errors.to_account_id" class="field-error">{{ errors.to_account_id }}</p>
                    </div>
                </template>
            </div>
            <div class="divider"></div>

            <!-- AMOUNT + CURRENCY -->
            <div class="section-label">Số tiền</div>
            <div class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-currency-dong"></i>Số tiền <span class="required">*</span>
                    </label>
                    <div class="amount-wrap">
                        <input
                            :value="formatMoney(form.amount)"
                            @input="handleAmountInput"
                            @keydown="handleAmountKeydown"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            class="input"
                            placeholder="0"
                        />
                        <span class="currency-badge">
                            {{ amountCurrencyUnit }}
                        </span>
                    </div>
                    <p v-if="errors.amount" class="field-error">{{ errors.amount }}</p>
                </div>
            </div>
            <div class="divider"></div>

            <div class="section-label">Thời gian</div>
            <div class="field">
                <label class="label"
                    ><i class="ti ti-calendar"></i>Ngày giao dịch <span class="required">*</span></label
                >
                <input
                    v-model="form.transaction_date"
                    type="date"
                    class="input"
                />
                <p v-if="errors.transaction_date" class="field-error">{{ errors.transaction_date }}</p>
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
import { formatMoney, getValidationMessage } from "@/config/helpers";
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
const errors = reactive({});

function setFieldErrors(values = {}) {
    Object.keys(errors).forEach((key) => delete errors[key]);
    Object.entries(values).forEach(([key, messages]) => {
        errors[key] = Array.isArray(messages) ? messages[0] : messages;
    });
}

function validateForm() {
    const next = {};
    if (!form.category_id) next.category_id = "Vui lòng chọn loại thanh toán.";
    if (!Number(form.amount) || Number(form.amount) <= 0) next.amount = "Số tiền phải lớn hơn 0.";
    if (!form.transaction_date) next.transaction_date = "Vui lòng chọn ngày giao dịch.";
    if (form.type === "receipt" && !form.to_account_id) next.to_account_id = "Vui lòng chọn tài khoản nhận.";
    if (form.type === "payment" && !form.from_account_id) next.from_account_id = "Vui lòng chọn tài khoản chi.";
    if (form.type === "transfer") {
        if (!form.from_account_id) next.from_account_id = "Vui lòng chọn tài khoản chuyển.";
        if (!form.to_account_id) next.to_account_id = "Vui lòng chọn tài khoản nhận.";
        if (form.from_account_id && Number(form.from_account_id) === Number(form.to_account_id)) {
            next.to_account_id = "Tài khoản nhận phải khác tài khoản chuyển.";
        }
    }
    setFieldErrors(next);
    return Object.keys(next).length === 0;
}

function handleAmountInput(e) {
    const raw = e.target.value.replace(/[^\d]/g, "");
    form.amount = raw ? Number(raw) : 0;
}

function handleAmountKeydown(event) {
    if (event.ctrlKey || event.metaKey) return;

    const allowedKeys = [
        "Backspace",
        "Delete",
        "Tab",
        "Enter",
        "ArrowLeft",
        "ArrowRight",
        "Home",
        "End",
    ];

    if (!/^\d$/.test(event.key) && !allowedKeys.includes(event.key)) {
        event.preventDefault();
    }
}

const isEdit = computed(() => !!props.transaction?.id);

const typeOptions = [
    { value: "receipt", label: "Thu tiền", icon: "ti ti-arrow-down-circle" },
    { value: "payment", label: "Chi tiền", icon: "ti ti-arrow-up-circle" },
    {
        value: "transfer",
        label: "Chuyển nội bộ",
        icon: "ti ti-arrows-exchange",
    },
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

const amountCurrencyUnit = computed(() => {
    if (!selectedCurrency.value) return "";
    return selectedCurrency.value.symbol || selectedCurrency.value.code || "";
});

const selectedOrderCurrency = ref(null);
const selectedOrderRemaining = ref(null);
const orderOptions = ref([]);

const currencyHintMessage = computed(() => {
    if (!selectedOrderCurrency.value) return "";
    return `Tiền tệ tự động theo đơn hàng: ${selectedOrderCurrency.value.code}.`;
});

const currencyMismatchMessage = computed(() => {
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
function today() {
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60000;
    return new Date(now.getTime() - offset).toISOString().slice(0, 10);
}

const form = reactive({
    id: null,
    code: "",
    type: "receipt",
    payment_method: "cash",
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
    transaction_date: today(),
    description: "",
});

const normalizedAccounts = computed(() =>
    Array.isArray(props.accounts) ? props.accounts : props.accounts?.data || [],
);

const filteredAccounts = computed(() => {
    if (form.payment_method === "cash") {
        return normalizedAccounts.value.filter(
            (account) => account.type === "cash" && !account.bank_id,
        );
    }

    return normalizedAccounts.value.filter((account) => account.bank_id);
});

function accountLabel(account) {
    const bankName = account.bank?.name;
    const accountNo = account.bank_account_no;
    return [account.name, bankName, accountNo]
        .filter(Boolean)
        .join(" - ");
}

const selectedPaymentAccount = computed(() => {
    if (form.type !== "payment" || !form.from_account_id) return null;
    return (
        normalizedAccounts.value.find(
            (account) => Number(account.id) === Number(form.from_account_id),
        ) || null
    );
});

function formatAccountBalance(account) {
    const currency = account.currency;
    const currencyLabel = currency?.code || currency?.symbol || "";
    return `${formatMoney(Number(account.current_balance || 0))} ${currencyLabel}`.trim();
}

watch([() => form.payment_method, () => props.accounts], () => {
    const allowedIds = new Set(
        filteredAccounts.value.map((account) => Number(account.id)),
    );

    if (form.from_account_id && !allowedIds.has(Number(form.from_account_id))) {
        form.from_account_id = "";
    }

    if (form.to_account_id && !allowedIds.has(Number(form.to_account_id))) {
        form.to_account_id = "";
    }
});

function resetForm() {
    Object.assign(form, {
        id: null,
        code: "",
        type: "receipt",
        payment_method: "cash",
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
        transaction_date: today(),
        description: "",
    });

    orderOptions.value = [];
    selectedOrderCurrency.value = null;
    selectedOrderRemaining.value = null;
}

watch(
    () => props.transaction,
    async (val) => {
        if (!val?.id) {
            resetForm();
            return;
        }
        Object.assign(form, {
            id: val.id,
            code: val.code ?? "",
            type: val.type ?? "receipt",
            payment_method: val.payment_method ?? "cash",
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
            await loadOrderOptions();
            if (form.sales_order_id) await onSalesOrderChange();
        }

        if (form.type === "payment" && form.supplier_id) {
            await loadOrderOptions();
            if (form.purchase_order_id) await onPurchaseOrderChange();
        }
    },
    { immediate: true },
);

// Khi đổi loại giao dịch (receipt/payment/transfer): reset các field liên quan
// gồm cả category_id, vì category cũ có thể không còn phù hợp với loại mới.
watch(
    () => form.type,
    () => {
        if (form.type === "transfer") form.payment_method = "bank_transfer";
        form.sales_order_id = "";
        form.purchase_order_id = "";
        form.customer_id = "";
        form.supplier_id = "";
        form.category_id = "";
        orderOptions.value = [];
        selectedOrderCurrency.value = null;
        selectedOrderRemaining.value = null;
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
                currency: order.order_currency || order.currency || null,
                currency_id:
                    order.currency_id ??
                    order.order_currency?.id ??
                    order.currency?.id ??
                    null,
                exchange_rate:
                    order.exchange_rate ??
                    order.order_currency?.exchange_rate ??
                    1,
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
                currency: order.order_currency || order.currency || null,
                currency_id:
                    order.currency_id ??
                    order.order_currency?.id ??
                    order.currency?.id ??
                    null,
                exchange_rate:
                    order.exchange_rate ??
                    order.order_currency?.exchange_rate ??
                    1,
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
    selectedOrderRemaining.value = null;
    loadOrderOptions();
}

function onSupplierChange() {
    form.purchase_order_id = "";
    selectedOrderCurrency.value = null;
    selectedOrderRemaining.value = null;
    loadOrderOptions();
}

function onCurrencyChange() {
    const currency = selectedCurrency.value;
    if (currency?.exchange_rate) {
        form.exchange_rate = Number(currency.exchange_rate) || 1;
    }
}

async function onSalesOrderChange() {
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
        await loadOrderOutstanding("sale", selected.id);
    }
}

async function onPurchaseOrderChange() {
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
        await loadOrderOutstanding("purchase", selected.id);
    }
}

async function loadOrderOutstanding(type, orderId) {
    selectedOrderRemaining.value = null;
    if (!orderId) return;
    try {
        const { data } = await axios.get(
            "/api/accountant/transactions/order-outstanding",
            { params: { type, order_id: orderId } },
        );
        selectedOrderRemaining.value = Number(data.remaining_amount || 0);
    } catch (error) {
        toast.error(
            getValidationMessage(
                error,
                "Không thể tải số tiền còn lại của đơn hàng.",
            ),
        );
    }
}

watch(
    [
        () => form.type,
        () => form.from_account_id,
        () => form.to_account_id,
        () => props.accounts,
    ],
    () => {
        if (form.sales_order_id || form.purchase_order_id) return;
        const accountId =
            form.type === "receipt" ? form.to_account_id : form.from_account_id;
        const account = (props.accounts || []).find(
            (item) => Number(item.id) === Number(accountId),
        );
        if (!account) return;
        form.currency_id = account.currency_id || account.currency?.id || "";
        const currency = normalizedCurrencies.value.find(
            (item) => Number(item.id) === Number(form.currency_id),
        );
        form.exchange_rate = Number(currency?.exchange_rate || 1);
    },
    { immediate: true },
);

async function save() {
    if (!validateForm()) {
        toast.error("Vui lòng kiểm tra các trường được đánh dấu đỏ.");
        return;
    }
    // Chặn lưu ngay tại FE nếu category không khớp loại giao dịch
    if (categoryMismatchMessage.value) {
        toast.error(categoryMismatchMessage.value);
        return;
    }

    if (currencyMismatchMessage.value) {
        toast.error(currencyMismatchMessage.value);
        return;
    }

    if (
        (form.sales_order_id || form.purchase_order_id) &&
        selectedOrderRemaining.value !== null &&
        Number(form.amount) > selectedOrderRemaining.value
    ) {
        toast.error(
            `Số tiền thanh toán không được vượt quá ${formatMoney(selectedOrderRemaining.value)} ${selectedCurrencyLabel.value}.`,
        );
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
        toast.success(
            isEdit.value
                ? "Cập nhật giao dịch thành công"
                : "Tạo giao dịch thành công",
        );
        emit("saved");
    } catch (error) {
        console.error(error);
        setFieldErrors(error.response?.data?.errors || {});
        // Bắt lỗi nghiệp vụ từ BE (vd category không khớp loại giao dịch) trả về qua message
        toast.error(getValidationMessage(error, "Không thể lưu giao dịch."));
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
.required { color: #dc2626; margin-left: 2px; }
.field-error { margin-top: 6px; color: #dc2626; font-size: 12px; line-height: 1.4; }
/* ── Container ── */
.tx-modal {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0, 0, 0, 0.12);
    width: 100%;
    max-width: 820px;
    max-height: calc(100dvh - 3rem);
    overflow: hidden;
    z-index: 50;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

/* ── Header ── */
.tx-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    flex: 0 0 auto;
    background: #fff;
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
    flex-wrap: wrap;
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
    min-height: 0;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable;
}
.tx-body::-webkit-scrollbar {
    width: 7px;
}
.tx-body::-webkit-scrollbar-track {
    background: transparent;
}
.tx-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 999px;
}
.tx-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
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
    min-width: 0;
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
    min-width: 0;
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

.account-balance {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 6px 0 0;
    padding: 8px 10px;
    color: #166534;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    font-size: 12.5px;
}

/* ── Footer ── */
.tx-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f0f0f0;
    flex: 0 0 auto;
    background: #fff;
    padding-bottom: max(1rem, env(safe-area-inset-bottom));
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

@media (max-width: 640px) {
    .tx-modal {
        max-height: calc(100dvh - 1rem);
        border-radius: 12px;
    }

    .tx-header {
        flex-direction: column;
        align-items: stretch;
        padding: 1rem;
    }

    .type-tabs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        width: 100%;
    }

    .tab {
        justify-content: center;
        padding: 8px 6px;
        white-space: nowrap;
    }

    .tx-body {
        padding: 1rem;
    }

    .grid2 {
        grid-template-columns: minmax(0, 1fr);
    }

    .tx-footer {
        padding: 0.75rem 1rem max(0.75rem, env(safe-area-inset-bottom));
    }

    .btn {
        flex: 1;
        justify-content: center;
        padding-inline: 12px;
    }
}

@media (max-height: 600px) {
    .tx-modal {
        max-height: calc(100dvh - 1rem);
    }

    .tx-header,
    .tx-footer {
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
}
</style>
