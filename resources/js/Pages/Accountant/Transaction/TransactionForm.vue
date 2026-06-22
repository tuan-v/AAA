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
            <!-- AMOUNT + CURRENCY -->
            <div class="section-label">Số tiền</div>
            <div class="grid2">
                <div class="field">
                    <label class="label">
                        <i class="ti ti-currency-dong"></i>Số tiền
                    </label>
                    <div class="amount-wrap">
                        <input
                            v-model="form.amount"
                            type="number"
                            class="input"
                            placeholder="0"
                        />
                        <span class="currency-badge">
                            {{ selectedCurrencyCode }}
                        </span>
                    </div>
                </div>
                <div class="field">
                    <label class="label">
                        <i class="ti ti-world"></i>Tiền tệ
                    </label>
                    <select v-model="form.currency_id" class="input">
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

            <!-- CATEGORY -->
            <div class="section-label">Phân loại</div>
            <div class="field">
                <label class="label">
                    <i class="ti ti-tag"></i>Loại thanh toán
                </label>
                <select v-model="form.category_id" class="input">
                    <option value="">Chọn loại</option>
                    <option
                        v-for="c in categories || []"
                        :key="c.id"
                        :value="c.id"
                    >
                        {{ c.name }}
                    </option>
                </select>
            </div>
            <div class="section-label">Thời gian giao dịch</div>

            <div class="field">
                <label class="label">
                    <i class="ti ti-calendar"></i>Ngày giao dịch
                </label>

                <input
                    v-model="form.transaction_date"
                    type="date"
                    class="input"
                />
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
            <button class="btn btn-primary" @click="save">
                <i class="ti ti-check"></i>
                {{ isEdit ? "Cập nhật" : "Lưu giao dịch" }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    transaction: { type: Object, default: null },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    currencies: { type: Array, default: () => [] },
});

const emit = defineEmits(["saved", "close"]);

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

const selectedCurrencyCode = computed(() => {
    if (!form.currency_id) return "";
    const c = normalizedCurrencies.value.find((x) => x.id === form.currency_id);
    return c?.code ?? "";
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
        transaction_date: "",
        description: "",
    });
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
            transaction_date: val.transaction_date?.slice?.(0, 10) ?? "",
            description: val.description ?? "",
        });
    },
    { immediate: true },
);

async function save() {
    const payload = {
        ...form,
    };
    if (isEdit.value) {
        await axios.put(`/api/accountant/transactions/${form.id}`, form);
    } else {
        await axios.post(`/api/accountant/transactions`, form);
    }
    emit("saved");
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
</style>
