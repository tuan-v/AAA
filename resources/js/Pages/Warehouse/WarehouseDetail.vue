<template>
    <div class="wh-modal">
        <!-- HEADER -->
        <div class="wh-modal__header">
            <div>
                <h2 class="wh-modal__title">
                    <i class="ti ti-building-warehouse"></i>
                    {{ warehouse.name }}
                    <span class="wh-modal__code">#{{ warehouse.code }}</span>
                </h2>
                <p class="wh-modal__address" v-if="!loading">
                    <i class="ti ti-map-pin"></i>
                    {{ warehouse.address_detail }}, {{ warehouse.ward?.name }},
                    {{ warehouse.province?.name }}
                </p>
            </div>
            <button class="wh-modal__close" @click="$emit('close')">
                <i class="ti ti-x">X</i>
            </button>
        </div>

        <div v-if="loading" class="wh-modal__loading">
            <i class="ti ti-loader-2"></i> Đang tải dữ liệu kho...
        </div>

        <template v-else>
            <!-- STATS -->
            <div class="stat-grid">
                <div class="stat-card">
                    <i class="ti ti-package stat-card__icon"></i>
                    <div>
                        <p class="stat-card__label">Tổng số sản phẩm</p>
                        <p class="stat-card__value">
                            {{ summary.total_products }}
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ti ti-stack-2 stat-card__icon"></i>
                    <div>
                        <p class="stat-card__label">Tổng số lượng tồn</p>
                        <p class="stat-card__value">
                            {{ formatNumber(summary.total_quantity) }}
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="ti ti-currency-dong stat-card__icon"></i>
                    <div>
                        <p class="stat-card__label">Tổng giá trị tồn kho</p>
                        <p class="stat-card__value">
                            {{
                                formatMoneyLocal(
                                    summary.inventory_value,
                                    summary.currency_symbol,
                                )
                            }}
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <i
                        class="ti ti-arrow-down-circle stat-card__icon stat-card__icon--in"
                    ></i>
                    <div>
                        <p class="stat-card__label">Phiếu nhập</p>
                        <p class="stat-card__value">
                            {{ summary.import_slips }}
                        </p>
                    </div>
                </div>
                <div class="stat-card">
                    <i
                        class="ti ti-arrow-up-circle stat-card__icon stat-card__icon--out"
                    ></i>
                    <div>
                        <p class="stat-card__label">Phiếu xuất</p>
                        <p class="stat-card__value">
                            {{ summary.export_slips }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- TABS -->
            <div class="tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    class="tab-btn"
                    :class="{ 'tab-btn--active': activeTab === tab.key }"
                    @click="activeTab = tab.key"
                >
                    <i class="ti" :class="tab.icon"></i>
                    {{ tab.label }}
                </button>
            </div>

            <!-- TAB CONTENT (scroll riêng, header/tab luôn cố định) -->
            <div class="tab-content">
                <!-- TAB 1: TỔNG QUAN -->
                <div v-if="activeTab === 'overview'" class="tab-pane">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Mã kho</label>
                            <p>{{ warehouse.code }}</p>
                        </div>
                        <div class="info-item">
                            <label>Tên kho</label>
                            <p>{{ warehouse.name }}</p>
                        </div>
                        <div class="info-item info-item--full">
                            <label>Địa chỉ</label>
                            <p>
                                {{ warehouse.address_detail }},
                                {{ warehouse.ward?.name }},
                                {{ warehouse.province?.name }}
                            </p>
                        </div>
                        <div class="info-item">
                            <label>Trạng thái</label>
                            <p>
                                {{
                                    warehouse.status === "active"
                                        ? "Đang hoạt động"
                                        : "Ngừng hoạt động"
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: TỒN KHO SẢN PHẨM -->
                <div v-if="activeTab === 'stocks'" class="tab-pane">
                    <div class="table-toolbar">
                        <input
                            v-model="stockSearch"
                            type="text"
                            placeholder="Tìm sản phẩm theo tên/mã..."
                            class="input"
                        />
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Mã</th>
                                <th>Danh mục</th>
                                <th>Đơn vị</th>
                                <th class="text-right">Số lượng tồn</th>
                                <th class="text-right">Đơn giá nhập</th>
                                <th class="text-right">Giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="stock in filteredStocks" :key="stock.id">
                                <td>
                                    {{
                                        stock.product?.name ??
                                        "(Sản phẩm đã xóa)"
                                    }}
                                </td>
                                <td>{{ stock.product?.sku }}</td>
                                <td>
                                    {{ stock.product?.category?.name ?? "-" }}
                                </td>
                                <td>{{ stock.product?.unit?.name ?? "-" }}</td>
                                <td class="text-right">
                                    {{ formatNumber(stock.quantity) }}
                                </td>
                                <td class="text-right">
                                    {{
                                        formatMoneyLocal(
                                            stock.product?.purchase_price ?? 0,
                                            summary.currency_symbol,
                                        )
                                    }}
                                </td>
                                <td class="text-right font-bold">
                                    {{
                                        formatMoneyLocal(
                                            stockValue(stock),
                                            summary.currency_symbol,
                                        )
                                    }}
                                </td>
                            </tr>
                            <tr v-if="filteredStocks.length === 0">
                                <td colspan="7" class="text-center text-muted">
                                    Không có sản phẩm tồn kho
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TAB 3: PHIẾU NHẬP / XUẤT -->
                <div v-if="activeTab === 'slips'" class="tab-pane">
                    <div class="table-toolbar">
                        <select
                            v-model="slipTypeFilter"
                            class="input input--select"
                        >
                            <option value="">Tất cả loại phiếu</option>
                            <option value="import">Phiếu nhập</option>
                            <option value="export">Phiếu xuất</option>
                        </select>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Loại</th>
                                <th>Trạng thái</th>
                                <th>Số SP</th>
                                <th>Người tạo</th>
                                <th>Người duyệt</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="slip in filteredSlips" :key="slip.id">
                                <td>{{ slip.code }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="
                                            slip.type === 'import'
                                                ? 'badge--in'
                                                : 'badge--out'
                                        "
                                    >
                                        {{
                                            slip.type === "import"
                                                ? "Nhập kho"
                                                : "Xuất kho"
                                        }}
                                    </span>
                                </td>
                                <td>{{ slipStatusText(slip.status) }}</td>
                                <td>{{ slip.items?.length ?? 0 }}</td>
                                <td>
                                    {{
                                        slip.created_by?.name ??
                                        slip.createdBy?.name ??
                                        "-"
                                    }}
                                </td>
                                <td>
                                    {{
                                        slip.approved_by?.name ??
                                        slip.approvedBy?.name ??
                                        "-"
                                    }}
                                </td>
                                <td>{{ formatDate(slip.created_at) }}</td>
                            </tr>
                            <tr v-if="filteredSlips.length === 0">
                                <td colspan="7" class="text-center text-muted">
                                    Không có phiếu nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- TAB 4: BIẾN ĐỘNG TỒN KHO -->
                <div v-if="activeTab === 'movement'" class="tab-pane">
                    <p class="text-muted" style="margin-bottom: 16px">
                        Tổng số lượng nhập/xuất theo tháng (chỉ tính phiếu đã
                        duyệt)
                    </p>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tháng</th>
                                <th class="text-right">SL nhập</th>
                                <th class="text-right">SL xuất</th>
                                <th>Biểu đồ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in monthlyMovement" :key="row.month">
                                <td>{{ row.month }}</td>
                                <td class="text-right" style="color: #2f9e44">
                                    {{ formatNumber(row.importQty) }}
                                </td>
                                <td class="text-right" style="color: #e03131">
                                    {{ formatNumber(row.exportQty) }}
                                </td>
                                <td>
                                    <div class="bar-chart">
                                        <div
                                            class="bar-chart__bar bar-chart__bar--in"
                                            :style="{
                                                width:
                                                    barWidth(row.importQty) +
                                                    '%',
                                            }"
                                        ></div>
                                        <div
                                            class="bar-chart__bar bar-chart__bar--out"
                                            :style="{
                                                width:
                                                    barWidth(row.exportQty) +
                                                    '%',
                                            }"
                                        ></div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="monthlyMovement.length === 0">
                                <td colspan="4" class="text-center text-muted">
                                    Chưa có dữ liệu biến động
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import axios from "axios";

const props = defineProps({
    warehouseId: {
        type: [Number, String],
        required: true,
    },
});

defineEmits(["close"]);

const loading = ref(true);
const warehouse = ref({});
const summary = ref({});
const activeTab = ref("overview");
const stockSearch = ref("");
const slipTypeFilter = ref("");

const tabs = [
    { key: "overview", label: "Tổng quan", icon: "ti-layout-dashboard" },
    { key: "stocks", label: "Tồn kho sản phẩm", icon: "ti-package" },
    { key: "slips", label: "Phiếu nhập/xuất", icon: "ti-file-invoice" },
    { key: "movement", label: "Biến động tồn kho", icon: "ti-chart-bar" },
];

const fetchDetail = async (id) => {
    loading.value = true;
    try {
        const res = await axios.get(`/api/warehouses/${id}/detail`);
        warehouse.value = res.data?.data?.warehouse ?? {};
        summary.value = res.data?.data?.summary ?? {};
        activeTab.value = "overview";
    } catch (e) {
        console.error("Không thể tải chi tiết kho:", e);
    } finally {
        loading.value = false;
    }
};

// Load ngay khi modal mount, và load lại nếu warehouseId đổi (mở modal cho kho khác mà không unmount)
watch(
    () => props.warehouseId,
    (id) => {
        if (id) fetchDetail(id);
    },
    { immediate: true },
);

const filteredStocks = computed(() => {
    const stocks = warehouse.value.stocks ?? [];
    if (!stockSearch.value) return stocks;
    const q = stockSearch.value.toLowerCase();
    return stocks.filter(
        (s) =>
            s.product?.name?.toLowerCase().includes(q) ||
            s.product?.code?.toLowerCase().includes(q),
    );
});

const stockValue = (stock) =>
    stock.quantity * (stock.product?.purchase_price ?? 0);

const filteredSlips = computed(() => {
    const slips = warehouse.value.slips ?? [];
    if (!slipTypeFilter.value) return slips;
    return slips.filter((s) => s.type === slipTypeFilter.value);
});

const slipStatusText = (status) => {
    const map = {
        pending: "Chờ duyệt",
        approved: "Đã duyệt",
        rejected: "Đã từ chối",
    };
    return map[status] ?? status;
};

const monthlyMovement = computed(() => {
    const slips = (warehouse.value.slips ?? []).filter(
        (s) => s.status === "approved",
    );
    const grouped = {};

    slips.forEach((slip) => {
        const date = new Date(slip.approved_at ?? slip.created_at);
        const month = `${date.getMonth() + 1}/${date.getFullYear()}`;
        const qty = (slip.items ?? []).reduce(
            (sum, i) => sum + (i.quantity ?? 0),
            0,
        );

        if (!grouped[month]) {
            grouped[month] = {
                month,
                importQty: 0,
                exportQty: 0,
                sortKey: date.getFullYear() * 12 + date.getMonth(),
            };
        }
        if (slip.type === "import") grouped[month].importQty += qty;
        else grouped[month].exportQty += qty;
    });

    return Object.values(grouped).sort((a, b) => a.sortKey - b.sortKey);
});

const maxMovementQty = computed(() =>
    Math.max(
        1,
        ...monthlyMovement.value.map((r) => Math.max(r.importQty, r.exportQty)),
    ),
);

const barWidth = (qty) => Math.round((qty / maxMovementQty.value) * 100);

const formatNumber = (n) => new Intl.NumberFormat("vi-VN").format(n ?? 0);
const formatMoneyLocal = (amount, symbol = "₫") =>
    `${new Intl.NumberFormat("vi-VN").format(Math.round(amount ?? 0))} ${symbol}`;
const formatDate = (date) => {
    if (!date) return "-";
    return new Intl.DateTimeFormat("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(new Date(date));
};
</script>

<style scoped>
/* FIX CHÍNH: .wh-modal trước đây không có background/padding/box-shadow
   -> trong suốt, nội dung "xuyên thấu" đè lên trang phía sau thay vì
   hiển thị như 1 khối modal card độc lập. */
.wh-modal {
    position: relative;
    z-index: 1;
    background: #ffffff;
    width: 95vw;
    max-width: 1280px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    padding: 28px 32px;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    /* Modal.vue wrapper đã canh giữa bằng flex items-center justify-center,
       nên .wh-modal chỉ cần margin để không dính sát viền màn hình trên mobile */
    margin: 24px;
}

.wh-modal__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding-bottom: 16px;
    border-bottom: 1px solid #eee;
    margin-bottom: 16px;
}

.wh-modal__title {
    font-size: 20px;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}

.wh-modal__code {
    font-size: 13px;
    font-weight: 500;
    color: #868e96;
}

.wh-modal__address {
    color: #495057;
    margin-top: 6px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.wh-modal__close {
    background: none;
    border: none;
    font-size: 22px;
    color: #868e96;
    cursor: pointer;
    line-height: 1;
    padding: 4px;
}
.wh-modal__close:hover {
    color: #e03131;
}

.wh-modal__loading {
    padding: 60px 0;
    text-align: center;
    color: #868e96;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}

.stat-card {
    background: #f8f9fa;
    border: 1px solid #eee;
    border-radius: 10px;
    padding: 12px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.stat-card__icon {
    font-size: 22px;
    color: #4263eb;
}
.stat-card__icon--in {
    color: #2f9e44;
}
.stat-card__icon--out {
    color: #e8590c;
}
.stat-card__label {
    font-size: 11px;
    color: #868e96;
    margin: 0;
}
.stat-card__value {
    font-size: 15px;
    font-weight: 700;
    color: #212529;
    margin: 0;
}

.tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid #eee;
    margin-bottom: 16px;
    flex-shrink: 0;
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: #868e96;
    cursor: pointer;
    font-size: 13px;
    margin-bottom: -2px;
}
.tab-btn:hover {
    color: #4263eb;
}
.tab-btn--active {
    color: #4263eb;
    border-bottom-color: #4263eb;
    font-weight: 600;
}

.tab-content {
    overflow-y: auto;
    flex: 1;
    min-height: 200px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 14px;
}
.info-item--full {
    grid-column: span 2;
}
.info-item label {
    font-size: 12px;
    color: #868e96;
    display: block;
    margin-bottom: 4px;
}
.info-item p {
    font-weight: 500;
    color: #212529;
}

.table-toolbar {
    margin-bottom: 12px;
    display: flex;
    gap: 12px;
}
.input {
    padding: 7px 10px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    font-size: 13px;
    min-width: 220px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}
.data-table th,
.data-table td {
    padding: 8px 10px;
    border-bottom: 1px solid #f1f3f5;
    font-size: 13px;
    text-align: left;
}
.data-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #495057;
    position: sticky;
    top: 0;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}
.badge--in {
    background: #e7f5ff;
    color: #1971c2;
}
.badge--out {
    background: #fff4e6;
    color: #e8590c;
}

.text-right {
    text-align: right;
}
.text-center {
    text-align: center;
}
.text-muted {
    color: #adb5bd;
}
.font-bold {
    font-weight: 700;
}

.bar-chart {
    display: flex;
    flex-direction: column;
    gap: 3px;
    width: 100%;
    max-width: 180px;
}
.bar-chart__bar {
    height: 6px;
    border-radius: 3px;
    min-width: 2px;
}
.bar-chart__bar--in {
    background: #2f9e44;
}
.bar-chart__bar--out {
    background: #e8590c;
}
</style>
