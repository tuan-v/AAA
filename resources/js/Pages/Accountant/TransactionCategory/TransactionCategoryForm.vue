<template>
    <div class="tx-modal">
        <!-- HEADER -->
        <div class="tx-header">
            <div>
                <h2 class="tx-title">
                    {{
                        isEdit
                            ? "Cập nhật loại giao dịch"
                            : "Thêm loại giao dịch"
                    }}
                </h2>
                <p v-if="form.code" class="tx-code">
                    <i class="ti ti-hash"></i> {{ form.code }}
                </p>
            </div>

            <!-- STATUS BADGE (chỉ hiện khi sửa) -->
            <div v-if="isEdit" class="status-toggle">
                <button
                    class="tab"
                    :class="{ active: form.status === 1 }"
                    @click="form.status = 1"
                >
                    <i class="ti ti-lock-open"></i>
                    Hoạt động
                </button>
                <button
                    class="tab"
                    :class="{ active: form.status === 0 }"
                    @click="form.status = 0"
                >
                    <i class="ti ti-lock"></i>
                    Đã khóa
                </button>
            </div>
        </div>

        <!-- USED WARNING -->
        <div v-if="isUsed" class="used-banner">
            <i class="ti ti-alert-triangle"></i>
            Loại giao dịch này đã phát sinh giao dịch — chỉ có thể khóa/mở,
            không thể chỉnh sửa tên, loại thanh toán và ghi chú.
        </div>

        <!-- FORM BODY -->
        <div class="tx-body">
            <div class="section-label">Thông tin chung</div>

            <div class="field">
                <label class="label">
                    <i class="ti ti-tag"></i>Tên loại giao dịch
                    <span class="required">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    class="input"
                    :class="{ 'input-error': errors.name }"
                    :disabled="isUsed"
                    placeholder="VD: Thanh toán lương, Thu tiền bán hàng..."
                />
                <p v-if="errors.name" class="error-text">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div class="field">
                <label class="label">
                    <i class="ti ti-transfer-in"></i>Loại thanh toán
                    <span class="required">*</span>
                </label>
                <div class="type-tabs">
                    <button
                        v-for="t in typeOptions"
                        :key="t.value"
                        type="button"
                        class="tab"
                        :class="{ active: form.type === t.value }"
                        :disabled="isUsed"
                        @click="!isUsed && (form.type = t.value)"
                    >
                        <i :class="t.icon"></i>
                        {{ t.label }}
                    </button>
                </div>
                <p v-if="errors.type" class="error-text">
                    {{ errors.type[0] }}
                </p>
                <p class="hint-text">
                    Dùng để lọc loại thanh toán phù hợp khi tạo giao dịch
                    Thu/Chi/Chuyển khoản.
                </p>
            </div>

            <div class="divider"></div>

            <div class="section-label">Ghi chú</div>
            <div class="field">
                <label class="label"> <i class="ti ti-notes"></i>Mô tả </label>
                <textarea
                    v-model="form.description"
                    class="input"
                    rows="3"
                    :disabled="isUsed"
                    placeholder="Mô tả thêm (không bắt buộc)..."
                ></textarea>
            </div>
        </div>

        <!-- ACTIONS -->
        <div class="tx-footer">
            <button class="btn" @click="$emit('close')">Đóng</button>
            <button class="btn btn-primary" :disabled="saving" @click="save">
                <i class="ti ti-check"></i>
                {{
                    saving
                        ? "Đang lưu..."
                        : isEdit
                          ? "Cập nhật"
                          : "Lưu loại giao dịch"
                }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, watch, ref } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import { useActionConfirm } from "@/composables/useActionConfirm";

const { alertAction } = useActionConfirm();

const props = defineProps({
    category: { type: Object, default: null },
});

const emit = defineEmits(["saved", "close"]);

const isEdit = computed(() => !!props.category?.id);
const isUsed = computed(() => !!props.category?.is_used);

const saving = ref(false);
const errors = ref({});

// 3 loại thanh toán khớp đúng giá trị đang lưu trong DB (income/expense/transfer)
const typeOptions = [
    { value: "income", label: "Thu tiền", icon: "ti ti-arrow-down-circle" },
    { value: "expense", label: "Chi tiền", icon: "ti ti-arrow-up-circle" },
    { value: "transfer", label: "Chuyển nội bộ", icon: "ti ti-arrows-exchange" },
];

const form = reactive({
    id: null,
    code: "",
    name: "",
    description: "",
    type: "income",
    status: 1,
});

function resetForm() {
    Object.assign(form, {
        id: null,
        code: "",
        name: "",
        description: "",
        type: "income",
        status: 1,
    });
}

watch(
    () => props.category,
    (val) => {
        errors.value = {};
        if (!val?.id) {
            resetForm();
            return;
        }
        Object.assign(form, {
            id: val.id,
            code: val.code ?? "",
            name: val.name ?? "",
            description: val.description ?? "",
            type: val.type ?? "income",
            status: val.status ?? 1,
        });
    },
    { immediate: true },
);

async function save() {
    saving.value = true;
    errors.value = {};

    // Nếu đã dùng, chỉ gửi status - tránh gửi nhầm name/type/note bị backend từ chối
    const payload = isUsed.value
        ? { status: form.status }
        : {
              name: form.name,
              description: form.description,
              type: form.type,
              status: form.status,
          };

    try {
        if (isEdit.value) {
            await axios.put(
                `/api/accountant/transaction-categories/${form.id}`,
                payload,
            );
        } else {
            await axios.post(`/api/accountant/transaction-categories`, payload);
        }
        toast.success("Thêm loại giao dịch thành công");
        emit("saved");
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors ?? {};
        } else {
            await alertAction({ title: "Không thể lưu", message: e.response?.data?.message ?? "Có lỗi xảy ra, vui lòng thử lại.", confirmText: "Đã hiểu", tone: "danger" });
        }
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
    max-width: 520px;
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

/* ── Status toggle & type tabs (dùng chung style) ── */
.status-toggle,
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
    white-space: nowrap;
    font-family: inherit;
}
.tab.active {
    background: #fff;
    color: #185fa5;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}
.tab:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ── Used warning ── */
.used-banner {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 1rem 1.5rem 0;
    padding: 10px 14px;
    background: #fff7ed;
    border: 1px solid #fed7aa;
    color: #c2410c;
    font-size: 13px;
    border-radius: 8px;
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
.required {
    color: #dc2626;
}
.hint-text {
    font-size: 11.5px;
    color: #9ca3af;
    margin-top: 2px;
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
.input:disabled {
    background: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
}
.input-error {
    border-color: #dc2626;
}
.error-text {
    font-size: 12px;
    color: #dc2626;
}
textarea.input {
    resize: none;
    line-height: 1.55;
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
.btn-primary:hover:not(:disabled) {
    background: #0c447c;
    border-color: #0c447c;
}
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
