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
            <div v-if="form.type === 'receipt' && !requiresSupplier" class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-user"></i>Khách hàng
                        <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.customer_id"
                        :options="customerOptions"
                        placeholder="Chọn khách hàng"
                        :error="errors.customer_id || ''"
                        @update:model-value="onCustomerChange"
                    />
                </div>
                <div class="field">
                    <label class="label">
                        <i class="ti ti-file-description"></i>Đơn bán
                        <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.sales_order_id"
                        :options="orderSelectOptions"
                        placeholder="Chọn đơn bán"
                        :error="errors.sales_order_id || ''"
                        @update:model-value="onSalesOrderChange"
                    />
                </div>
            </div>

            <div v-if="form.type === 'receipt' && requiresSupplier" class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-building-store"></i>Nhà cung cấp
                        <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.supplier_id"
                        :options="supplierOptions"
                        placeholder="Chọn nhà cung cấp"
                        :error="errors.supplier_id || ''"
                    />
                </div>
            </div>

            <div v-if="form.type === 'payment'" class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-building-store"></i>Nhà cung cấp
                        <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.supplier_id"
                        :options="supplierOptions"
                        placeholder="Chọn nhà cung cấp"
                        :error="errors.supplier_id || ''"
                        @update:model-value="onSupplierChange"
                    />
                </div>
                <div class="field">
                    <label class="label">
                        <i class="ti ti-file-description"></i>Đơn mua
                        <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.purchase_order_id"
                        :options="orderSelectOptions"
                        placeholder="Chọn đơn mua"
                        :error="errors.purchase_order_id || ''"
                        @update:model-value="onPurchaseOrderChange"
                    />
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
                <FormSelect
                    v-model="form.category_id"
                    :options="categoryOptions"
                    placeholder="Chọn loại thanh toán"
                    :error="errors.category_id || ''"
                />
                <p v-if="categoryMismatchMessage" class="category-error">
                    <i class="ti ti-alert-circle"></i>
                    {{ categoryMismatchMessage }}
                </p>
            </div>
            <div class="field">
                <label class="label">
                    <i class="ti ti-wallet"></i>Hình thức giao dịch
                    <span class="required">*</span>
                </label>
                <FormSelect
                    v-model="form.payment_method"
                    :options="paymentMethodOptions"
                    placeholder="Chọn hình thức giao dịch"
                    :can-clear="false"
                    :disabled="form.type === 'transfer'"
                    :error="errors.payment_method || ''"
                />
                <p v-if="form.type === 'transfer'" class="hint-text">
                    Chuyển nội bộ không làm phát sinh hoặc thanh toán công nợ.
                </p>
            </div>
            <div class="divider"></div>

            <!-- ACCOUNTS -->
            <div class="section-label">Tài khoản</div>

            <div v-if="!filteredAccounts.length" class="account-empty-state">
                <div>
                    <strong>Chưa có tài khoản phù hợp</strong>
                    <p>
                        Hãy tạo tài khoản {{ form.payment_method === "cash" ? "tiền mặt" : "ngân hàng" }} để tiếp tục giao dịch.
                    </p>
                </div>
                <button
                    v-if="canCreateAccount"
                    type="button"
                    class="btn-create-account"
                    @click="openAccountCreate(defaultAccountTarget)"
                >
                    <i class="ti ti-plus"></i>
                    Tạo tài khoản
                </button>
            </div>

            <div class="grid2">
                <!-- RECEIPT: to_account -->
                <div v-if="form.type === 'receipt'" class="field">
                    <label class="label">
                        <i class="ti ti-building-bank"></i>Tài khoản nhận <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.to_account_id"
                        :options="accountOptions"
                        placeholder="Chọn tài khoản nhận"
                        :allow-create="canCreateAccount"
                        add-new-text="Tạo tài khoản mới"
                        :error="errors.to_account_id || ''"
                        @add-new="openAccountCreate('to_account_id')"
                    />
                </div>

                <!-- PAYMENT: from_account -->
                <div v-if="form.type === 'payment'" class="field">
                    <label class="label">
                        <i class="ti ti-building-bank"></i>Tài khoản chi <span class="required">*</span>
                    </label>
                    <FormSelect
                        v-model="form.from_account_id"
                        :options="accountOptions"
                        placeholder="Chọn tài khoản chi"
                        :allow-create="canCreateAccount"
                        add-new-text="Tạo tài khoản mới"
                        :error="errors.from_account_id || ''"
                        @add-new="openAccountCreate('from_account_id')"
                    />
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
                        <FormSelect
                            v-model="form.from_account_id"
                            :options="accountOptions"
                            placeholder="Chọn tài khoản chuyển"
                            :allow-create="canCreateAccount"
                            add-new-text="Tạo tài khoản mới"
                            :error="errors.from_account_id || ''"
                            @add-new="openAccountCreate('from_account_id')"
                        />
                        <p v-if="selectedSourceAccount" class="account-balance">
                            <i class="ti ti-wallet"></i>
                            Số dư khả dụng:
                            <strong>{{ formatAccountBalance(selectedSourceAccount) }}</strong>
                        </p>
                    </div>
                    <div class="field">
                        <label class="label">
                            <i class="ti ti-building-bank"></i>Tài khoản nhận <span class="required">*</span>
                        </label>
                        <FormSelect
                            v-model="form.to_account_id"
                            :options="accountOptions"
                            placeholder="Chọn tài khoản nhận"
                            :allow-create="canCreateAccount"
                            add-new-text="Tạo tài khoản mới"
                            :error="errors.to_account_id || ''"
                            @add-new="openAccountCreate('to_account_id')"
                        />
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
                            :class="{ 'input-error': errors.amount }"
                            :aria-invalid="Boolean(errors.amount)"
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
                    :max="today()"
                    class="input"
                    :class="{ 'input-error': errors.transaction_date }"
                    :aria-invalid="Boolean(errors.transaction_date)"
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
                    :class="{ 'input-error': errors.description }"
                    :aria-invalid="Boolean(errors.description)"
                    rows="3"
                    maxlength="2000"
                    placeholder="Nhập nội dung giao dịch..."
                ></textarea>
                <p v-if="errors.description" class="field-error">{{ errors.description }}</p>
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

    <Modal v-if="showAccountModal" @close="showAccountModal = false">
        <template #body>
            <AccountForm
                :default-type="newAccountType"
                :default-currency-id="form.currency_id"
                @saved="onAccountCreated"
                @close="showAccountModal = false"
            />
        </template>
    </Modal>
</template>

<script setup>
import { reactive, computed, watch, ref } from "vue";
import axios from "axios";
import { formatMoney, getValidationMessage } from "@/config/helpers";
import { toast } from "vue3-toastify";
import FormSelect from "@/components/FormSelect.vue";
import Modal from "@/components/Modal.vue";
import AccountForm from "@/Pages/Accountant/Account/AccountForm.vue";
import { usePermission } from "@/composables/usePermission";

const props = defineProps({
    transaction: { type: Object, default: null },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
    customers: { type: Array, default: () => [] },
    suppliers: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved", "close", "account-created"]);
const { can } = usePermission();

const saving = ref(false);
const validationAttempted = ref(false);
const showAccountModal = ref(false);
const accountTargetField = ref("to_account_id");
const localAccounts = ref([]);
const canCreateAccount = computed(() => can("tai_khoan.them"));
const newAccountType = computed(() =>
    form.payment_method === "cash" ? "cash" : "bank",
);
const defaultAccountTarget = computed(() =>
    form.type === "receipt" ? "to_account_id" : "from_account_id",
);
const errors = reactive({});

function setFieldErrors(values = {}) {
    Object.keys(errors).forEach((key) => delete errors[key]);
    Object.entries(values).forEach(([key, messages]) => {
        errors[key] = Array.isArray(messages) ? messages[0] : messages;
    });
}

function validateForm(markAttempted = true) {
    if (markAttempted) validationAttempted.value = true;
    const next = {};
    if (!form.category_id) next.category_id = "Vui lòng chọn loại thanh toán.";
    if (categoryMismatchMessage.value) next.category_id = categoryMismatchMessage.value;
    if (!form.payment_method) next.payment_method = "Vui lòng chọn hình thức giao dịch.";
    if (!Number(form.amount) || Number(form.amount) <= 0) next.amount = "Số tiền phải lớn hơn 0.";
    if (!form.transaction_date) next.transaction_date = "Vui lòng chọn ngày giao dịch.";
    if (form.transaction_date && form.transaction_date > today()) {
        next.transaction_date = "Ngày giao dịch không được lớn hơn ngày hôm nay.";
    }
    if ((form.description || "").length > 2000) {
        next.description = "Nội dung không được vượt quá 2.000 ký tự.";
    }
    if (requiresCustomer.value && !form.customer_id) {
        next.customer_id = "Vui lòng chọn khách hàng.";
    }
    if (requiresSalesOrder.value && !form.sales_order_id) {
        next.sales_order_id = "Vui lòng chọn đơn bán đã duyệt.";
    }
    if (requiresSupplier.value && !form.supplier_id) {
        next.supplier_id = "Vui lòng chọn nhà cung cấp.";
    }
    if (requiresPurchaseOrder.value && !form.purchase_order_id) {
        next.purchase_order_id = "Vui lòng chọn đơn mua đã duyệt.";
    }
    if (form.type === "receipt" && !form.to_account_id) next.to_account_id = "Vui lòng chọn tài khoản nhận.";
    if (form.type === "payment" && !form.from_account_id) next.from_account_id = "Vui lòng chọn tài khoản chi.";
    if (form.type === "transfer") {
        if (!form.from_account_id) next.from_account_id = "Vui lòng chọn tài khoản chuyển.";
        if (!form.to_account_id) next.to_account_id = "Vui lòng chọn tài khoản nhận.";
        if (form.from_account_id && Number(form.from_account_id) === Number(form.to_account_id)) {
            next.to_account_id = "Tài khoản nhận phải khác tài khoản chuyển.";
        }
    }

    const sourceAccount = selectedSourceAccount.value;
    if (sourceAccount && Number(form.amount) > 0) {
        const requiredAmount = amountInSourceAccountCurrency.value;
        if (requiredAmount > Number(sourceAccount.current_balance || 0)) {
            next.amount = `Số tiền vượt quá số dư khả dụng của tài khoản (${formatAccountBalance(sourceAccount)}).`;
        }
    }

    if (
        (form.sales_order_id || form.purchase_order_id) &&
        selectedOrderRemaining.value !== null &&
        Number(form.amount) > selectedOrderRemaining.value
    ) {
        next.amount = `Số tiền không được vượt quá công nợ còn lại của đơn (${formatMoney(selectedOrderRemaining.value)} ${selectedCurrencyLabel.value}).`;
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

function businessErrorFields(message = "") {
    if (/số tiền|số dư|công nợ/i.test(message)) return { amount: message };
    if (/nhà cung cấp/i.test(message) && !/đơn mua/i.test(message)) return { supplier_id: message };
    if (/đơn mua/i.test(message)) return { purchase_order_id: message };
    if (/khách hàng/i.test(message) && !/đơn bán/i.test(message)) return { customer_id: message };
    if (/đơn bán/i.test(message)) return { sales_order_id: message };
    if (/tài khoản nguồn|tài khoản chi/i.test(message)) return { from_account_id: message };
    if (/tài khoản nhận|tài khoản đích/i.test(message)) return { to_account_id: message };
    if (/ngày giao dịch/i.test(message)) return { transaction_date: message };
    if (/loại thanh toán/i.test(message)) return { category_id: message };
    return {};
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

const customerOptions = computed(() => {
    const rows = Array.isArray(props.customers)
        ? props.customers
        : props.customers?.data || [];
    return rows.map((customer) => ({
        value: customer.id,
        label: [customer.code, customer.name].filter(Boolean).join(" - "),
    }));
});

const supplierOptions = computed(() => {
    const rows = Array.isArray(props.suppliers)
        ? props.suppliers
        : props.suppliers?.data || [];
    return rows.map((supplier) => ({
        value: supplier.id,
        label: [supplier.code, supplier.name].filter(Boolean).join(" - "),
    }));
});

const orderSelectOptions = computed(() =>
    orderOptions.value.map((order) => ({
        value: order.id,
        label: order.label,
    })),
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

const categoryOptions = computed(() =>
    filteredCategories.value.map((category) => ({
        value: category.id,
        label: category.name,
    })),
);

const paymentMethodOptions = [
    { value: "cash", label: "Tiền mặt" },
    { value: "bank_transfer", label: "Chuyển khoản" },
];

const selectedCategory = computed(() => {
    if (!form.category_id) return null;
    return (
        normalizedCategories.value.find(
            (c) => Number(c.id) === Number(form.category_id),
        ) || null
    );
});

const selectedCategoryCode = computed(() => selectedCategory.value?.code || "");
const requiresCustomer = computed(() => selectedCategoryCode.value === "THU_KH");
const requiresSalesOrder = computed(() => selectedCategoryCode.value === "THU_KH");
const requiresSupplier = computed(() =>
    ["CHI_NCC", "TAM_UNG_NCC", "HOAN_TAM_UNG_NCC"].includes(
        selectedCategoryCode.value,
    ),
);
const requiresPurchaseOrder = computed(() => selectedCategoryCode.value === "CHI_NCC");

// Thông báo lỗi khi category đã chọn không khớp loại giao dịch hiện tại
// (phòng trường hợp dữ liệu cũ khi sửa giao dịch, hoặc select chưa lọc kịp)
const categoryMismatchMessage = computed(() => {
    const cat = selectedCategory.value;
    if (!cat || !cat.type) return "";
    const expectedType = TYPE_TO_CATEGORY_TYPE[form.type];
    if (cat.type !== expectedType) {
        const catLabel = CATEGORY_TYPE_LABELS[cat.type] || cat.type;
        return `Loại thanh toán "${cat.name}" chỉ dùng cho giao dịch ${catLabel}, không phù hợp với giao dịch bạn đang tạo.`;
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

watch(
    () => props.accounts,
    (accounts) => {
        const rows = Array.isArray(accounts) ? accounts : accounts?.data || [];
        localAccounts.value = [...rows];
    },
    { immediate: true, deep: true },
);

const normalizedAccounts = computed(() => localAccounts.value);

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

const accountOptions = computed(() =>
    filteredAccounts.value.map((account) => ({
        value: account.id,
        label: accountLabel(account),
    })),
);

function openAccountCreate(targetField) {
    accountTargetField.value = targetField;
    showAccountModal.value = true;
}

async function onAccountCreated(createdAccount) {
    try {
        const response = await axios.get("/api/accountant/accounts/all");
        localAccounts.value = Array.isArray(response.data)
            ? response.data
            : response.data?.data || [];

        const accountId = createdAccount?.id;
        const newAccount = localAccounts.value.find(
            (account) => Number(account.id) === Number(accountId),
        );

        if (newAccount) {
            form[accountTargetField.value] = newAccount.id;
            form.currency_id =
                newAccount.currency_id || newAccount.currency?.id || form.currency_id;
            const currency = normalizedCurrencies.value.find(
                (item) => Number(item.id) === Number(form.currency_id),
            );
            form.exchange_rate = Number(currency?.exchange_rate || 1);
            emit("account-created", newAccount);
        }
    } catch (error) {
        toast.error("Đã tạo tài khoản nhưng chưa thể tải lại danh sách tài khoản.");
    } finally {
        showAccountModal.value = false;
    }
}

const selectedSourceAccount = computed(() => {
    if (!["payment", "transfer"].includes(form.type) || !form.from_account_id) return null;
    return (
        normalizedAccounts.value.find(
            (account) => Number(account.id) === Number(form.from_account_id),
        ) || null
    );
});

const selectedPaymentAccount = computed(() =>
    form.type === "payment" ? selectedSourceAccount.value : null,
);

const amountInSourceAccountCurrency = computed(() => {
    const account = selectedSourceAccount.value;
    const amount = Number(form.amount || 0);
    if (!account || !amount) return 0;

    const accountCurrencyId = account.currency_id || account.currency?.id;
    if (Number(accountCurrencyId) === Number(form.currency_id)) return amount;

    const accountCurrency = normalizedCurrencies.value.find(
        (currency) => Number(currency.id) === Number(accountCurrencyId),
    );
    const transactionRate = Number(form.exchange_rate || selectedCurrency.value?.exchange_rate || 1);
    const accountRate = Number(account.currency?.exchange_rate || accountCurrency?.exchange_rate || 1);
    return accountRate > 0 ? (amount * transactionRate) / accountRate : Number.POSITIVE_INFINITY;
});

function formatAccountBalance(account) {
    const currency = account.currency;
    const currencyLabel = currency?.code || currency?.symbol || "";
    return `${formatMoney(Number(account.current_balance || 0))} ${currencyLabel}`.trim();
}

watch([() => form.payment_method, () => localAccounts.value], () => {
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
    validationAttempted.value = false;
    setFieldErrors({});
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

watch(
    [
        () => form.type,
        () => form.category_id,
        () => form.payment_method,
        () => form.customer_id,
        () => form.supplier_id,
        () => form.sales_order_id,
        () => form.purchase_order_id,
        () => form.from_account_id,
        () => form.to_account_id,
        () => form.amount,
        () => form.transaction_date,
        () => form.description,
        () => selectedOrderRemaining.value,
    ],
    () => {
        if (validationAttempted.value) validateForm(false);
    },
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
                params: {
                    customer_id: form.customer_id,
                    transaction_eligible: 1,
                    per_page: 100,
                },
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
                params: {
                    supplier_id: form.supplier_id,
                    transaction_eligible: 1,
                    per_page: 100,
                },
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
        () => localAccounts.value,
    ],
    () => {
        if (form.sales_order_id || form.purchase_order_id) return;
        const accountId =
            form.type === "receipt" ? form.to_account_id : form.from_account_id;
        const account = normalizedAccounts.value.find(
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
        validationAttempted.value = true;
        const message = getValidationMessage(error, "Không thể lưu giao dịch.");
        const serverErrors = error.response?.data?.errors || {};
        setFieldErrors(
            Object.keys(serverErrors).length
                ? serverErrors
                : businessErrorFields(message),
        );
        // Bắt lỗi nghiệp vụ từ BE (vd category không khớp loại giao dịch) trả về qua message
        toast.error(message);
    } finally {
        saving.value = false;
    }
}
</script>

<style scoped>
.required { color: #dc2626; margin-left: 2px; }
.field-error { margin-top: 6px; color: #dc2626; font-size: 12px; line-height: 1.4; }
.input-error {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1) !important;
}
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

.account-empty-state {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px;
    color: #92400e;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    font-size: 13px;
}
.account-empty-state p {
    margin-top: 2px;
    color: #a16207;
    font-size: 12px;
}
.btn-create-account {
    display: inline-flex;
    flex: 0 0 auto;
    align-items: center;
    gap: 5px;
    padding: 8px 12px;
    color: #fff;
    background: #2563eb;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}
.btn-create-account:hover {
    background: #1d4ed8;
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
