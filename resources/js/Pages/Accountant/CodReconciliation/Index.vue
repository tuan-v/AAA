<template>
    <Head title="Đối soát COD" />
    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[{ text: 'Đối soát COD', link: null }]"
        />

        <div
            class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end"
        >
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Đối soát COD</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Theo dõi tiền hãng vận chuyển đã thu và chuyển về cửa hàng.
                </p>
            </div>
            <button
                v-if="can('doi_soat_cod.them')"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-gray-50"
                @click="showPartnerForm = !showPartnerForm"
            >
                + Đơn vị vận chuyển
            </button>
        </div>

        <div class="mb-6 grid gap-4 md:grid-cols-3">
            <SummaryCard
                label="Đơn chờ đối soát"
                :value="summary.pending_orders"
            />
            <SummaryCard
                label="COD hãng đang giữ"
                :value="money(summary.pending_amount)"
            />
            <SummaryCard
                label="Tiền đã nhận"
                :value="money(summary.reconciled_amount)"
            />
        </div>

        <form
            v-if="showPartnerForm"
            class="mb-6 grid gap-3 rounded-xl border bg-white p-5 md:grid-cols-4"
            @submit.prevent="createPartner"
        >
            <input
                v-model.trim="partnerForm.code"
                required
                class="rounded-lg border-gray-300"
                placeholder="Mã, ví dụ GHN"
            />
            <input
                v-model.trim="partnerForm.name"
                required
                class="rounded-lg border-gray-300"
                placeholder="Tên đơn vị vận chuyển"
            />
            <input
                v-model.trim="partnerForm.phone"
                class="rounded-lg border-gray-300"
                placeholder="Số điện thoại"
            />
            <input
                v-model.trim="partnerForm.tracking_url_template"
                class="rounded-lg border-gray-300 md:col-span-3"
                placeholder="Link tra cứu, dùng {tracking_code} tại vị trí mã vận đơn"
            />
            <button
                :disabled="saving"
                class="rounded-lg bg-gray-900 px-4 py-2 font-semibold text-white disabled:opacity-50"
            >
                Lưu đối tác
            </button>
        </form>

        <div
            v-if="error"
            class="mb-5 rounded-lg bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"
        >
            {{ error }}
        </div>

        <section class="overflow-hidden rounded-xl border bg-white shadow-sm">
            <div
                class="flex flex-col justify-between gap-3 border-b p-5 md:flex-row md:items-center"
            >
                <div>
                    <h2 class="text-lg font-bold">COD chờ đối soát</h2>
                    <p class="text-sm text-gray-500">
                        Chỉ gồm đơn web COD đã giao thành công.
                    </p>
                </div>
                <button
                    v-if="selectedIds.length && can('doi_soat_cod.duyet')"
                    class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700"
                    @click="openReconcile"
                >
                    Đối soát {{ selectedIds.length }} đơn
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead
                        class="bg-gray-50 text-left text-xs uppercase text-gray-500"
                    >
                        <tr>
                            <th class="px-5 py-3">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    @change="toggleAll"
                                />
                            </th>
                            <th class="px-5 py-3">Đơn hàng</th>
                            <th class="px-5 py-3">Khách hàng</th>
                            <th class="px-5 py-3">Mã vận đơn</th>
                            <th class="px-5 py-3">Ngày giao</th>
                            <th class="px-5 py-3 text-right">COD</th>
                            <th class="px-5 py-3 text-right">Phí theo đơn</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr
                            v-for="row in pending"
                            :key="row.id"
                            class="hover:bg-gray-50"
                        >
                            <td class="px-5 py-4">
                                <input
                                    v-model="selectedIds"
                                    type="checkbox"
                                    :value="row.id"
                                />
                            </td>
                            <td class="px-5 py-4 font-bold">{{ row.code }}</td>
                            <td class="px-5 py-4">
                                <p>{{ row.customer }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ row.phone }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                {{ row.tracking_code || "—" }}
                            </td>
                            <td class="px-5 py-4">{{ row.collected_at }}</td>
                            <td class="px-5 py-4 text-right font-bold">
                                {{ money(row.cod_amount) }}
                            </td>
                            <td class="px-5 py-4 text-right text-gray-500">
                                {{ money(row.shipping_fee) }}
                            </td>
                        </tr>
                        <tr v-if="!pending.length">
                            <td
                                colspan="7"
                                class="px-5 py-12 text-center text-gray-400"
                            >
                                Không có đơn COD chờ đối soát.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section
            class="mt-8 overflow-hidden rounded-xl border bg-white shadow-sm"
        >
            <div class="border-b p-5">
                <h2 class="text-lg font-bold">Lịch sử đối soát</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead
                        class="bg-gray-50 text-left text-xs uppercase text-gray-500"
                    >
                        <tr>
                            <th class="px-5 py-3">Mã phiếu</th>
                            <th class="px-5 py-3">Ngày</th>
                            <th class="px-5 py-3">Đơn vị vận chuyển</th>
                            <th class="px-5 py-3">Tài khoản nhận</th>
                            <th class="px-5 py-3 text-right">COD</th>
                            <th class="px-5 py-3 text-right">Phí</th>
                            <th class="px-5 py-3 text-right">Thực nhận</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="row in history.data" :key="row.id">
                            <td class="px-5 py-4 font-bold">{{ row.code }}</td>
                            <td class="px-5 py-4">
                                {{ row.reconciliation_date }}
                            </td>
                            <td class="px-5 py-4">{{ row.partner?.name }}</td>
                            <td class="px-5 py-4">{{ row.account?.name }}</td>
                            <td class="px-5 py-4 text-right">
                                {{ money(row.cod_amount) }}
                            </td>
                            <td class="px-5 py-4 text-right text-red-600">
                                {{ money(totalFees(row)) }}
                            </td>
                            <td
                                class="px-5 py-4 text-right font-bold text-emerald-700"
                            >
                                {{ money(row.received_amount) }}
                            </td>
                        </tr>
                        <tr v-if="!history.data?.length">
                            <td
                                colspan="7"
                                class="px-5 py-10 text-center text-gray-400"
                            >
                                Chưa có phiếu đối soát.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AdminLayout>

    <Modal v-if="showReconcileForm" @close="showReconcileForm = false">
        <template #body>
            <form
                class="max-h-[calc(100vh-2.5rem)] w-full max-w-2xl space-y-4 overflow-y-auto rounded-2xl bg-white p-5 shadow-2xl sm:p-6"
                @submit.prevent="reconcile"
            >
                <div>
                    <h2 class="text-xl font-bold">Xác nhận thu tiền COD</h2>
                    <p class="text-sm text-gray-500">
                        {{ selectedIds.length }} đơn · {{ money(selectedCod) }}
                    </p>
                </div>
                <div v-if="modalError" class="rounded-lg bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ modalError }}</div>
                <label class="block text-sm font-semibold"
                    >Đơn vị vận chuyển<select
                        v-model="form.shipping_partner_id"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300"
                    >
                        <option value="">Chọn đơn vị</option>
                        <option v-for="p in partners" :key="p.id" :value="p.id">
                            {{ p.name }}
                        </option>
                    </select></label
                >
                <label class="block text-sm font-semibold"
                    >Tài khoản nhận tiền<select
                        v-model="form.account_id"
                        required
                        class="mt-1 w-full rounded-lg border-gray-300"
                    >
                        <option value="">Chọn tài khoản</option>
                        <option v-for="a in accounts" :key="a.id" :value="a.id">
                            {{ a.code }} · {{ a.name }} ({{ a.currency?.code }})
                        </option>
                    </select></label
                >
                <label class="block text-sm font-semibold"
                    >Ngày nhận tiền<input
                        v-model="form.reconciliation_date"
                        required
                        type="date"
                        class="mt-1 w-full rounded-lg border-gray-300"
                /></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-sm font-semibold">Phí vận chuyển hãng khấu trừ
                        <InputMoney v-model="form.shipping_fee" :show-text="false" class="mt-1" />
                    </label>
                    <label class="block text-sm font-semibold">Phí dịch vụ
                        <InputMoney v-model="form.service_fee" :show-text="false" class="mt-1" />
                    </label>
                    <label class="block text-sm font-semibold">Phí bảo hiểm
                        <InputMoney v-model="form.insurance_fee" :show-text="false" class="mt-1" />
                    </label>
                    <label class="block text-sm font-semibold">Điều chỉnh (+/-)
                        <InputMoney v-model="form.adjustment_amount" :show-text="false" class="mt-1" />
                    </label>
                </div>
                <label class="block text-sm font-semibold"
                    >Mã giao dịch ngân hàng<input
                        v-model.trim="form.payment_reference"
                        class="mt-1 w-full rounded-lg border-gray-300"
                /></label>
                <div class="rounded-lg bg-gray-50 p-4">
                    <div class="flex justify-between">
                        <span>Tổng COD</span><b>{{ money(selectedCod) }}</b>
                    </div>
                    <div class="mt-2 flex justify-between text-lg">
                        <span class="font-bold">Thực nhận</span
                        ><b class="text-emerald-700">{{
                            money(receivedAmount)
                        }}</b>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        class="rounded-lg border px-4 py-2"
                        @click="showReconcileForm = false"
                    >
                        Đóng</button
                    ><button
                        :disabled="saving || receivedAmount <= 0"
                        class="rounded-lg bg-blue-600 px-5 py-2 font-bold text-white disabled:opacity-50"
                    >
                        {{ saving ? "Đang xử lý..." : "Xác nhận đối soát" }}
                    </button>
                </div>
            </form>
        </template>
    </Modal>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import axios from "axios";
import { computed, defineComponent, h, onMounted, reactive, ref } from "vue";
import { toast } from "vue3-toastify";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import Modal from "@/components/Modal.vue";
import { usePermission } from "@/composables/usePermission";
import InputMoney from "@/components/InputMoney.vue";

const { can } = usePermission();
const pending = ref([]),
    history = ref({ data: [] }),
    partners = ref([]),
    accounts = ref([]),
    selectedIds = ref([]);
const summary = ref({
    pending_orders: 0,
    pending_amount: 0,
    reconciled_amount: 0,
});
const saving = ref(false),
    error = ref(""),
    modalError = ref(""),
    showPartnerForm = ref(false),
    showReconcileForm = ref(false);
const partnerForm = reactive({
    code: "",
    name: "",
    phone: "",
    tracking_url_template: "",
});
const form = reactive({
    shipping_partner_id: "",
    account_id: "",
    reconciliation_date: new Date().toISOString().slice(0, 10),
    shipping_fee: 0,
    service_fee: 0,
    insurance_fee: 0,
    adjustment_amount: 0,
    payment_reference: "",
});
const selectedCod = computed(() =>
    pending.value
        .filter((x) => selectedIds.value.includes(x.id))
        .reduce((sum, x) => sum + Number(x.cod_amount), 0),
);
const receivedAmount = computed(
    () =>
        selectedCod.value -
        Number(form.shipping_fee || 0) -
        Number(form.service_fee || 0) -
        Number(form.insurance_fee || 0) +
        Number(form.adjustment_amount || 0),
);
const allSelected = computed(
    () =>
        pending.value.length > 0 &&
        selectedIds.value.length === pending.value.length,
);
const money = (value) =>
    `${new Intl.NumberFormat("vi-VN").format(Number(value || 0))} ₫`;
const totalFees = (row) =>
    Number(row.shipping_fee) +
    Number(row.service_fee) +
    Number(row.insurance_fee) -
    Number(row.adjustment_amount);
const message = (e) =>
    Object.values(e.response?.data?.errors || {}).flat()[0] ||
    e.response?.data?.message ||
    "Có lỗi xảy ra.";
function toggleAll(e) {
    selectedIds.value = e.target.checked ? pending.value.map((x) => x.id) : [];
}
function openReconcile() {
    error.value = "";
    modalError.value = "";
    const rows = pending.value.filter((x) => selectedIds.value.includes(x.id));
    const partnerIds = [...new Set(rows.map((x) => x.partner?.id).filter(Boolean))];
    if (partnerIds.length > 1) {
        error.value = "Chỉ có thể đối soát cùng lúc các đơn thuộc một đơn vị vận chuyển.";
        return;
    }
    const currencyIds = [...new Set(rows.map((x) => x.currency?.id).filter(Boolean))];
    if (currencyIds.length > 1) {
        error.value = "Chỉ có thể đối soát cùng lúc các đơn có cùng tiền tệ.";
        return;
    }
    form.shipping_partner_id = partnerIds[0] || (partners.value.length === 1 ? partners.value[0].id : "");
    form.shipping_fee = rows.reduce((sum, row) => sum + Number(row.shipping_fee || 0), 0);
    form.service_fee = rows.reduce((sum, row) => sum + Number(row.service_fee || 0), 0);
    form.insurance_fee = rows.reduce((sum, row) => sum + Number(row.insurance_fee || 0), 0);
    form.adjustment_amount = 0;
    const matchingAccounts = accounts.value.filter((a) => Number(a.currency_id) === Number(currencyIds[0]));
    form.account_id = matchingAccounts.length === 1 ? matchingAccounts[0].id : "";
    showReconcileForm.value = true;
}
async function load() {
    const { data } = await axios.get("/api/accountant/cod-reconciliations");
    pending.value = data.pending;
    history.value = data.history;
    partners.value = data.partners;
    accounts.value = data.accounts;
    summary.value = data.summary;
}
async function createPartner() {
    saving.value = true;
    error.value = "";
    try {
        await axios.post(
            "/api/accountant/cod-reconciliations/partners",
            partnerForm,
        );
        Object.assign(partnerForm, {
            code: "",
            name: "",
            phone: "",
            tracking_url_template: "",
        });
        showPartnerForm.value = false;
        await load();
        toast.success("Đã thêm đơn vị vận chuyển.");
    } catch (e) {
        error.value = message(e);
    } finally {
        saving.value = false;
    }
}
async function reconcile() {
    saving.value = true;
    modalError.value = "";
    try {
        await axios.post("/api/accountant/cod-reconciliations", {
            ...form,
            order_ids: selectedIds.value,
        });
        selectedIds.value = [];
        showReconcileForm.value = false;
        Object.assign(form, {
            shipping_partner_id: "",
            account_id: "",
            shipping_fee: 0,
            service_fee: 0,
            insurance_fee: 0,
            adjustment_amount: 0,
            payment_reference: "",
        });
        await load();
        toast.success("Đối soát COD thành công.");
    } catch (e) {
        modalError.value = message(e);
    } finally {
        saving.value = false;
    }
}
const SummaryCard = defineComponent({
    props: ["label", "value"],
    setup: (p) => () =>
        h("div", { class: "rounded-xl border bg-white p-5 shadow-sm" }, [
            h("p", { class: "text-sm text-gray-500" }, p.label),
            h("p", { class: "mt-2 text-2xl font-bold" }, p.value),
        ]),
});
onMounted(load);
</script>
