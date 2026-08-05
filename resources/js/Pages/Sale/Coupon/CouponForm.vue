<template>
    <div class="coupon-modal">
        <div class="coupon-header">
            <div>
                <h2 class="text-xl font-bold text-gray-900">{{ isEdit ? "Cập nhật phiếu giảm giá" : "Thêm phiếu giảm giá" }}</h2>
                <p class="mt-1 text-sm text-gray-500">Thiết lập điều kiện và phạm vi áp dụng ưu đãi</p>
            </div>
            <span v-if="isEdit" :class="statusBadgeClass" class="rounded-full px-3 py-1.5 text-xs font-semibold">{{ statusLabel }}</span>
        </div>

        <div v-if="isUsed" class="used-banner">
            <i class="ti ti-alert-triangle"></i>
            Phiếu đã phát sinh đơn hàng. Loại giảm và giá trị giảm được khóa để bảo toàn lịch sử tài chính.
        </div>

        <div class="coupon-body">
            <section>
                <div class="section-label">Thông tin chung</div>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <FormInput v-model="form.code" label="Mã phiếu" placeholder="Để trống để tự sinh PGG0001" :error="errors.code" class="uppercase" />
                    <FormInput v-model="form.name" label="Tên phiếu" placeholder="VD: Ưu đãi mùa hè" :error="errors.name" required />
                    <div class="field md:col-span-2"><label>Mô tả</label><textarea v-model="form.description" class="input resize-none" rows="2" placeholder="Mô tả nội dung chương trình..."></textarea></div>
                </div>
            </section>

            <div class="divider"></div>
            <section>
                <div class="section-label">Giá trị ưu đãi</div>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <FormSelect v-model="form.type" :options="typeOptions" label="Loại giảm" placeholder="Chọn loại giảm" :searchable="false" :can-clear="false" :disabled="isUsed" :error="errors.type" required />
                    <FormSelect v-model="form.status" :options="statusOptions" label="Trạng thái" placeholder="Chọn trạng thái" :searchable="false" :can-clear="false" :error="errors.status" required />
                </div>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                    <FormInput v-if="form.type === 'percent'" v-model="form.value" type="number" label="Giá trị giảm" unit="%" :disabled="isUsed" :error="errors.value" required />
                    <InputMoney v-else v-model="form.value" label="Giá trị giảm" :show-text="false" :disabled="isUsed" :error="errors.value" required />
                    <InputMoney v-model="form.minimum_order_amount" label="Giá trị đơn tối thiểu" :show-text="false" :error="errors.minimum_order_amount" />
                    <InputMoney v-model="form.maximum_discount" label="Giảm tối đa" :show-text="false" :disabled="form.type === 'fixed'" :error="errors.maximum_discount" placeholder="Không giới hạn" />
                </div>
            </section>

            <div class="divider"></div>
            <section>
                <div class="section-label">Thời gian và giới hạn sử dụng</div>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <InputDate v-model="form.starts_at" label="Bắt đầu" placeholder="Chọn ngày giờ bắt đầu" :error="errors.starts_at?.[0]" :config="dateTimeConfig" />
                    <InputDate v-model="form.ends_at" label="Kết thúc" placeholder="Chọn ngày giờ kết thúc" :error="errors.ends_at?.[0]" :config="dateTimeConfig" />
                    <FormInput v-model="form.usage_limit" type="number" label="Tổng lượt sử dụng" placeholder="Không giới hạn" :allow-decimal="false" :error="errors.usage_limit" />
                    <FormInput v-model="form.usage_limit_per_customer" type="number" label="Lượt sử dụng mỗi khách" placeholder="Không giới hạn" :allow-decimal="false" :error="errors.usage_limit_per_customer" />
                </div>
            </section>

            <div class="divider"></div>
            <section>
                <div class="section-label">Phạm vi khách hàng</div>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <FormSelect v-model="form.scope" :options="scopeOptions" label="Loại phiếu" :searchable="false" :can-clear="false" :error="errors.scope" required />
                    <FormSelect v-if="form.scope === 'personal'" v-model="form.customer_ids" :options="customerOptions" label="Khách hàng được cấp" mode="multiple" :close-on-select="false" searchable placeholder="Tìm và chọn khách hàng" :loading="loadingCustomers" :error="errors.customer_ids" required />
                </div>
                <p v-if="form.scope === 'personal'" class="mt-2 text-xs text-gray-500">Phiếu chỉ xuất hiện và được áp dụng cho các khách hàng đã chọn.</p>
            </section>

            <div class="divider"></div>
            <section>
                <div class="section-label">Kênh áp dụng</div>
                <div class="mt-3"><FormSelect v-model="form.channels" :options="channelOptions" label="Kênh áp dụng" mode="multiple" :close-on-select="false" placeholder="Chọn một hoặc nhiều kênh" :searchable="false" :can-clear="false" :error="errors.channels" required helper-text="Có thể áp dụng đồng thời cho POS, website và đơn bán quản trị." /></div>
            </section>
        </div>

        <div class="coupon-footer">
            <button type="button" class="btn" @click="$emit('close')">Đóng</button>
            <button type="button" class="btn btn-primary" :disabled="saving" @click="save"><i class="ti ti-check"></i>{{ saving ? "Đang lưu..." : isEdit ? "Cập nhật" : "Lưu phiếu giảm giá" }}</button>
        </div>
    </div>
</template>

<script setup>
import { computed, h, reactive, ref, watch } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import InputMoney from "@/components/InputMoney.vue";
import InputDate from "@/components/InputDate.vue";
import FormSelect from "@/components/FormSelect.vue";
import FormInput from "@/components/ui/FormInput.vue";
import { useActionConfirm } from "@/composables/useActionConfirm";

const ErrorText = (props) => props.errors?.length ? h("p", { class: "mt-1 text-xs text-red-600" }, props.errors[0]) : null;
const props = defineProps({ coupon: { type: Object, default: null } });
const emit = defineEmits(["saved", "close"]);
const { alertAction } = useActionConfirm();
const saving = ref(false); const errors = ref({});
const loadingCustomers = ref(false); const customerOptions = ref([]);
const isEdit = computed(() => Boolean(props.coupon?.id));
const isUsed = computed(() => Number(props.coupon?.actual_used_count ?? props.coupon?.used_count ?? 0) > 0);
const statusOptions = [{value:"draft",label:"Bản nháp"},{value:"active",label:"Đang chạy"},{value:"paused",label:"Tạm dừng"}];
const typeOptions = [{value:"percent",label:"Giảm theo phần trăm"},{value:"fixed",label:"Giảm số tiền cố định"}];
const channelOptions = [{value:"pos",label:"POS - Bán hàng tại quầy"},{value:"web",label:"Website - Khách đặt trực tuyến"},{value:"admin",label:"Đơn quản trị - Nhân viên tạo đơn"}];
const scopeOptions = [{value:"public",label:"Công khai - Mọi khách đủ điều kiện"},{value:"personal",label:"Cá nhân - Chỉ khách được cấp"}];
const dateTimeConfig = { enableTime: true, time_24hr: true, dateFormat: "Y-m-d H:i", altInput: true, altFormat: "d/m/Y H:i" };
const statusLabel = computed(() => statusOptions.find((item) => item.value === form.status)?.label || "");
const statusBadgeClass = computed(() => form.status === "active" ? "bg-green-50 text-green-700" : form.status === "paused" ? "bg-amber-50 text-amber-700" : "bg-gray-100 text-gray-600");
const defaults = () => ({ id:null, code:"", name:"", description:"", type:"percent", value:10, minimum_order_amount:0, maximum_discount:null, starts_at:"", ends_at:"", status:"active", scope:"public", customer_ids:[], channels:["pos","web","admin"], usage_limit:null, usage_limit_per_customer:null });
const form = reactive(defaults());
watch(() => props.coupon, (value) => { Object.assign(form, defaults(), value || {}); form.channels = [...(value?.channels || ["pos","web","admin"])]; form.customer_ids = (value?.assigned_customers || []).map((item) => item.id); form.starts_at = value?.starts_at?.slice(0,16) || ""; form.ends_at = value?.ends_at?.slice(0,16) || ""; errors.value = {}; }, { immediate:true });
async function loadCustomers() { loadingCustomers.value = true; try { const { data } = await axios.get("/api/sale/customers/all"); const rows = data.data || data || []; customerOptions.value = rows.map((item) => ({ value:item.id, label:`${item.code} - ${item.name}${item.phone ? ` - ${item.phone}` : ""}` })); } finally { loadingCustomers.value = false; } }
loadCustomers();
async function save() {
    saving.value = true; errors.value = {};
    const payload = { ...form, code: form.code.toUpperCase(), starts_at: form.starts_at || null, ends_at: form.ends_at || null, maximum_discount: form.type === "fixed" ? null : (form.maximum_discount || null), usage_limit: form.usage_limit || null, usage_limit_per_customer: form.usage_limit_per_customer || null };
    try { if (isEdit.value) await axios.put(`/api/sale/coupons/${form.id}`, payload); else await axios.post("/api/sale/coupons", payload); toast.success(isEdit.value ? "Cập nhật phiếu giảm giá thành công" : "Tạo phiếu giảm giá thành công"); emit("saved"); }
    catch (error) { if (error.response?.status === 422 && error.response?.data?.errors) errors.value = error.response.data.errors; else await alertAction({ title:"Không thể lưu", message:error.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại.", confirmText:"Đã hiểu", tone:"danger" }); }
    finally { saving.value = false; }
}
</script>

<style scoped>
.coupon-modal{width:min(880px,calc(100vw - 2rem));max-height:90vh;overflow:hidden;border-radius:16px;background:#fff;box-shadow:0 8px 40px rgba(0,0,0,.12);display:flex;flex-direction:column}.coupon-header{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.25rem 1.5rem;border-bottom:1px solid #f0f0f0}.coupon-body{padding:1.25rem 1.5rem;overflow-y:auto}.coupon-footer{display:flex;justify-content:flex-end;gap:8px;padding:1rem 1.5rem;border-top:1px solid #f0f0f0}.section-label{font-size:11px;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:.06em}.divider{border-top:1px solid #f0f0f0;margin:1.25rem 0}.field label{display:block;margin-bottom:5px;font-size:12px;font-weight:500;color:#555}.field label b{color:#dc2626}.input{width:100%;border:1px solid #e0e0e0;border-radius:8px;padding:8px 12px;font-size:14px;color:#111;outline:none}.input:focus{border-color:#185fa5;box-shadow:0 0 0 3px rgba(24,95,165,.1)}.input:disabled{background:#f9fafb;color:#9ca3af}.input.invalid{border-color:#dc2626}.status-tabs,.type-tabs{display:flex;gap:4px;padding:4px;background:#f5f5f5;border-radius:10px}.tab{padding:7px 13px;border-radius:7px;font-size:13px;font-weight:500;color:#666;white-space:nowrap}.tab.active{background:#fff;color:#185fa5;box-shadow:0 1px 4px rgba(0,0,0,.1)}.tab:disabled{opacity:.5;cursor:not-allowed}.used-banner{display:flex;align-items:center;gap:8px;margin:1rem 1.5rem 0;padding:10px 14px;border:1px solid #fed7aa;border-radius:8px;background:#fff7ed;color:#c2410c;font-size:13px}.channel-card{display:flex;align-items:flex-start;gap:10px;padding:12px;border:1px solid #e5e7eb;border-radius:10px;cursor:pointer;transition:.15s}.channel-card.selected{border-color:#93c5fd;background:#eff6ff}.channel-card strong,.channel-card small{display:block}.channel-card strong{font-size:13px;color:#374151}.channel-card small{margin-top:2px;font-size:11px;color:#9ca3af}.suffix{position:absolute;right:12px;top:8px;color:#6b7280}.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 20px;border:1px solid #e0e0e0;border-radius:8px;background:#fff;font-size:14px;font-weight:500}.btn:hover{background:#f7f7f7}.btn-primary{border-color:#185fa5;background:#185fa5;color:#fff}.btn-primary:hover{background:#0c447c}.btn:disabled{opacity:.6;cursor:not-allowed}@media(max-width:640px){.coupon-header{flex-direction:column}.status-tabs{width:100%;overflow-x:auto}.coupon-modal{max-height:95vh}}
</style>
