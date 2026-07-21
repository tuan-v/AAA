<template>
    <Head title="Dashboard - Tổng quan doanh nghiệp"></Head>
    <AdminLayout>
        <PageBreadcrumb
            title="Dashboard"
            :items="[{ text: 'Dashboard', link: null }]"
        />

        <!-- ================= ROW 1: KPI TÀI CHÍNH TỔNG QUAN ================= -->
        <div
            class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-6 xl:grid-cols-4 2xl:gap-7.5"
        >
            <div
                v-for="metric in financeMetrics"
                :key="metric.title"
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h4
                            class="text-title-md font-bold text-gray-800 dark:text-white/90"
                        >
                            {{ metric.value }}
                        </h4>
                        <span
                            class="text-sm font-medium text-gray-500 dark:text-gray-400"
                            >{{ metric.title }}</span
                        >
                        <div
                            class="mt-1.5 flex items-center gap-1 text-xs font-medium"
                            :class="
                                metric.trendUp
                                    ? 'text-success-600'
                                    : 'text-error-600'
                            "
                        >
                            <i
                                class="ti"
                                :class="
                                    metric.trendUp
                                        ? 'ti-trending-up'
                                        : 'ti-trending-down'
                                "
                            ></i>
                            {{ metric.trendLabel }}
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-center w-12 h-12 rounded-full shrink-0"
                        :class="metric.bgColor"
                    >
                        <i
                            class="ti text-white text-xl"
                            :class="metric.icon"
                        ></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ROW 2: SỐ LIỆU VẬN HÀNH THEO MODULE ================= -->
        <div
            class="mt-4 grid grid-cols-2 gap-4 md:mt-6 md:grid-cols-3 md:gap-6 xl:grid-cols-6 2xl:mt-7.5 2xl:gap-7.5"
        >
            <div
                v-for="stat in operationStats"
                :key="stat.title"
                class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center w-9 h-9 rounded-lg shrink-0"
                        :class="stat.bgColor"
                    >
                        <i
                            class="ti text-lg"
                            :class="[stat.icon, stat.textColor]"
                        ></i>
                    </div>
                    <div class="min-w-0">
                        <h4
                            class="text-lg font-bold text-gray-800 dark:text-white/90 leading-tight"
                        >
                            {{ stat.value }}
                        </h4>
                        <span
                            class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate block"
                            >{{ stat.title }}</span
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ROW 3: DOANH THU vs CHI PHÍ MUA HÀNG | DÒNG TIỀN ================= -->
        <div
            class="mt-4 grid grid-cols-1 gap-4 md:mt-6 md:gap-6 xl:grid-cols-2 2xl:mt-7.5 2xl:gap-7.5"
        >
            <!-- Doanh thu vs Chi phí mua hàng -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-800 dark:text-white/90"
                        >
                            Doanh thu &amp; Chi phí mua hàng
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            6 tháng gần nhất
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-brand-500 inline-block"
                            ></i>
                            Doanh thu (Bán hàng)
                        </span>
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-warning-500 inline-block"
                            ></i>
                            Chi phí (Mua hàng)
                        </span>
                    </div>
                </div>

                <div class="flex items-end gap-4 h-52 px-2">
                    <div
                        v-for="row in monthlyFinance"
                        :key="row.month"
                        class="flex-1 flex flex-col items-center gap-2"
                    >
                        <div
                            class="w-full flex items-end justify-center gap-1.5 h-40"
                        >
                            <div
                                class="w-3 rounded-t bg-brand-500 transition-all"
                                :style="{
                                    height: barHeight(row.revenue, financeMax),
                                }"
                                :title="`Doanh thu: ${formatMoney(row.revenue)}`"
                            ></div>
                            <div
                                class="w-3 rounded-t bg-warning-500 transition-all"
                                :style="{
                                    height: barHeight(row.purchase, financeMax),
                                }"
                                :title="`Chi phí mua hàng: ${formatMoney(row.purchase)}`"
                            ></div>
                        </div>
                        <span class="text-xs text-gray-400">{{
                            row.month
                        }}</span>
                    </div>
                </div>
            </div>

            <!-- Dòng tiền -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-800 dark:text-white/90"
                        >
                            Dòng tiền (Thu / Chi)
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            6 tháng gần nhất
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-success-500 inline-block"
                            ></i>
                            Tiền vào
                        </span>
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-error-500 inline-block"
                            ></i>
                            Tiền ra
                        </span>
                    </div>
                </div>

                <svg
                    viewBox="0 0 600 200"
                    class="w-full h-52"
                    preserveAspectRatio="none"
                >
                    <line
                        v-for="n in 4"
                        :key="'grid-' + n"
                        x1="0"
                        :y1="n * 40"
                        x2="600"
                        :y2="n * 40"
                        stroke="currentColor"
                        class="text-gray-100 dark:text-gray-800"
                        stroke-width="1"
                    />
                    <polyline
                        :points="cashInPoints"
                        fill="none"
                        stroke="#16a34a"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <polyline
                        :points="cashOutPoints"
                        fill="none"
                        stroke="#dc2626"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <div
                    class="flex justify-between px-1 text-xs text-gray-400 mt-1"
                >
                    <span v-for="row in cashFlow" :key="row.month">{{
                        row.month
                    }}</span>
                </div>
            </div>
        </div>

        <!-- ================= ROW 4: BIẾN ĐỘNG CÔNG NỢ | NHẬP-XUẤT KHO ================= -->
        <div
            class="mt-4 grid grid-cols-1 gap-4 md:mt-6 md:gap-6 xl:grid-cols-2 2xl:mt-7.5 2xl:gap-7.5"
        >
            <!-- Công nợ KH vs NCC -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-800 dark:text-white/90"
                        >
                            Biến động công nợ
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Phải thu (KH) &amp; Phải trả (NCC)
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"
                            ></i>
                            Phải thu (KH)
                        </span>
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"
                            ></i>
                            Phải trả (NCC)
                        </span>
                    </div>
                </div>

                <svg
                    viewBox="0 0 600 200"
                    class="w-full h-52"
                    preserveAspectRatio="none"
                >
                    <line
                        v-for="n in 4"
                        :key="'grid2-' + n"
                        x1="0"
                        :y1="n * 40"
                        x2="600"
                        :y2="n * 40"
                        stroke="currentColor"
                        class="text-gray-100 dark:text-gray-800"
                        stroke-width="1"
                    />
                    <polyline
                        :points="receivablePoints"
                        fill="none"
                        stroke="#3b82f6"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <polyline
                        :points="payablePoints"
                        fill="none"
                        stroke="#f97316"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <div
                    class="flex justify-between px-1 text-xs text-gray-400 mt-1"
                >
                    <span v-for="row in debtTrend" :key="row.month">{{
                        row.month
                    }}</span>
                </div>
            </div>

            <!-- Nhập xuất kho -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-800 dark:text-white/90"
                        >
                            Nhập - Xuất kho
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Số phiếu theo tháng
                        </p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-teal-500 inline-block"
                            ></i>
                            Phiếu nhập
                        </span>
                        <span
                            class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                        >
                            <i
                                class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"
                            ></i>
                            Phiếu xuất
                        </span>
                    </div>
                </div>

                <div class="flex items-end gap-4 h-52 px-2">
                    <div
                        v-for="row in warehouseFlow"
                        :key="row.month"
                        class="flex-1 flex flex-col items-center gap-2"
                    >
                        <div
                            class="w-full flex items-end justify-center gap-1.5 h-40"
                        >
                            <div
                                class="w-3 rounded-t bg-teal-500 transition-all"
                                :style="{
                                    height: barHeight(row.import, warehouseMax),
                                }"
                                :title="`Phiếu nhập: ${row.import}`"
                            ></div>
                            <div
                                class="w-3 rounded-t bg-rose-500 transition-all"
                                :style="{
                                    height: barHeight(row.export, warehouseMax),
                                }"
                                :title="`Phiếu xuất: ${row.export}`"
                            ></div>
                        </div>
                        <span class="text-xs text-gray-400">{{
                            row.month
                        }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ROW 5: TRẠNG THÁI ĐƠN HÀNG | TOP KH | TOP NCC ================= -->
        <div
            class="mt-4 grid grid-cols-1 gap-4 md:mt-6 md:gap-6 xl:grid-cols-3 2xl:mt-7.5 2xl:gap-7.5"
        >
            <!-- Donut trạng thái đơn hàng -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h3
                    class="text-base font-semibold text-gray-800 dark:text-white/90 mb-5"
                >
                    Trạng thái đơn hàng (PO + SO)
                </h3>

                <div class="flex flex-col items-center">
                    <div
                        class="w-40 h-40 rounded-full flex items-center justify-center relative"
                        :style="{ background: orderStatusGradient }"
                    >
                        <div
                            class="w-24 h-24 rounded-full bg-white dark:bg-gray-900 flex flex-col items-center justify-center"
                        >
                            <span
                                class="text-xl font-bold text-gray-800 dark:text-white/90"
                                >{{ orderStatusTotal }}</span
                            >
                            <span class="text-[11px] text-gray-400"
                                >Tổng đơn</span
                            >
                        </div>
                    </div>

                    <div class="w-full mt-5 space-y-2">
                        <div
                            v-for="s in orderStatus"
                            :key="s.label"
                            class="flex items-center justify-between text-sm"
                        >
                            <span
                                class="flex items-center gap-2 text-gray-600 dark:text-gray-300"
                            >
                                <i
                                    class="w-2.5 h-2.5 rounded-full inline-block"
                                    :style="{ backgroundColor: s.color }"
                                ></i>
                                {{ s.label }}
                            </span>
                            <span
                                class="font-semibold text-gray-800 dark:text-white/90"
                                >{{ s.value }}</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top khách hàng -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h3
                    class="text-base font-semibold text-gray-800 dark:text-white/90 mb-5"
                >
                    Top 5 khách hàng (theo doanh số)
                </h3>

                <div class="space-y-4">
                    <div v-for="(c, i) in topCustomers" :key="c.name">
                        <div
                            class="flex items-center justify-between text-sm mb-1"
                        >
                            <span
                                class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-200"
                            >
                                <span
                                    class="w-5 h-5 rounded-full bg-brand-50 text-brand-600 text-[11px] font-bold flex items-center justify-center dark:bg-brand-500/10"
                                    >{{ i + 1 }}</span
                                >
                                {{ c.name }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">{{
                                formatMoney(c.value)
                            }}</span>
                        </div>
                        <div
                            class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-800"
                        >
                            <div
                                class="h-2 rounded-full bg-brand-500"
                                :style="{
                                    width: barHeight(c.value, topCustomersMax),
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top nhà cung cấp -->
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <h3
                    class="text-base font-semibold text-gray-800 dark:text-white/90 mb-5"
                >
                    Top 5 nhà cung cấp (theo giá trị nhập)
                </h3>

                <div class="space-y-4">
                    <div v-for="(s, i) in topSuppliers" :key="s.name">
                        <div
                            class="flex items-center justify-between text-sm mb-1"
                        >
                            <span
                                class="flex items-center gap-2 font-medium text-gray-700 dark:text-gray-200"
                            >
                                <span
                                    class="w-5 h-5 rounded-full bg-warning-50 text-warning-600 text-[11px] font-bold flex items-center justify-center dark:bg-warning-500/10"
                                    >{{ i + 1 }}</span
                                >
                                {{ s.name }}
                            </span>
                            <span class="text-gray-500 dark:text-gray-400">{{
                                formatMoney(s.value)
                            }}</span>
                        </div>
                        <div
                            class="w-full h-2 rounded-full bg-gray-100 dark:bg-gray-800"
                        >
                            <div
                                class="h-2 rounded-full bg-warning-500"
                                :style="{
                                    width: barHeight(s.value, topSuppliersMax),
                                }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= ROW 6: ĐƠN BÁN GẦN ĐÂY | ĐƠN MUA GẦN ĐÂY ================= -->
        <div
            class="mt-4 grid grid-cols-1 gap-4 md:mt-6 md:gap-6 xl:grid-cols-2 2xl:mt-7.5 2xl:gap-7.5"
        >
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="text-base font-semibold text-gray-800 dark:text-white/90"
                    >
                        Đơn bán hàng gần đây
                    </h3>
                    <Link
                        href="/sale/orders"
                        class="text-sm text-brand-600 hover:underline"
                        >Xem tất cả</Link
                    >
                </div>
                <DataTable
                    :columns="salesOrderColumns"
                    :data="recentSalesOrders"
                    :showIndex="false"
                    emptyMessage="Chưa có đơn bán hàng"
                />
            </div>

            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="text-base font-semibold text-gray-800 dark:text-white/90"
                    >
                        Đơn mua hàng gần đây
                    </h3>
                    <Link
                        href="/purchase/orders"
                        class="text-sm text-brand-600 hover:underline"
                        >Xem tất cả</Link
                    >
                </div>
                <DataTable
                    :columns="purchaseOrderColumns"
                    :data="recentPurchaseOrders"
                    :showIndex="false"
                    emptyMessage="Chưa có đơn mua hàng"
                />
            </div>
        </div>

        <!-- ================= ROW 7: GIAO DỊCH GẦN ĐÂY | CẢNH BÁO TỒN KHO THẤP ================= -->
        <div
            class="mt-4 grid grid-cols-1 gap-4 md:mt-6 md:gap-6 xl:grid-cols-2 2xl:mt-7.5 2xl:gap-7.5 mb-6"
        >
            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="text-base font-semibold text-gray-800 dark:text-white/90"
                    >
                        Giao dịch gần đây
                    </h3>
                    <Link
                        href="/accounting/transactions"
                        class="text-sm text-brand-600 hover:underline"
                        >Xem tất cả</Link
                    >
                </div>
                <DataTable
                    :columns="transactionColumns"
                    :data="recentTransactions"
                    :showIndex="false"
                    emptyMessage="Chưa có giao dịch"
                />
            </div>

            <div
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3
                        class="text-base font-semibold text-gray-800 dark:text-white/90"
                    >
                        Cảnh báo tồn kho thấp
                    </h3>
                    <Link
                        href="/warehouse"
                        class="text-sm text-brand-600 hover:underline"
                        >Xem kho</Link
                    >
                </div>

                <div
                    v-if="lowStockProducts.length === 0"
                    class="text-sm text-gray-400 py-6 text-center"
                >
                    Không có sản phẩm nào sắp hết hàng
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="p in lowStockProducts"
                        :key="p.name"
                        class="flex items-center justify-between rounded-lg border border-gray-100 dark:border-gray-800 px-3 py-2.5"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-9 h-9 rounded-lg bg-error-50 text-error-600 flex items-center justify-center shrink-0 dark:bg-error-500/10"
                            >
                                <i class="ti ti-alert-triangle text-lg"></i>
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate"
                                >
                                    {{ p.name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    Kho: {{ p.warehouse }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold text-error-600">
                                {{ formatQuantity(p.quantity) }} {{ p.unit }}
                            </p>
                            <p class="text-xs text-gray-400">
                                Tối thiểu: {{ p.minQuantity }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { Head, Link } from "@inertiajs/vue3";
import AdminLayout from "@/Layouts/AdminLayout.vue";
import PageBreadcrumb from "@/components/common/PageBreadcrumb.vue";
import DataTable from "@/components/DataTable.vue";
import { computed, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { formatMoney, formatQuantity } from "@/config/helpers";

// ==========================================================================
// Dữ liệu dashboard được nạp từ API thật: GET /api/dashboard/overview
// (xem DashboardController@overview + DashboardService + DashboardRepository)
// Mảng mock bên dưới CHỈ dùng làm giá trị khởi tạo / fallback khi API lỗi,
// để giao diện luôn có gì đó để hiển thị ngay cả khi mất mạng hoặc lỗi 500.
// ==========================================================================

const loading = ref(true);
const loadError = ref(false);

// ---------------- STATE CHÍNH (được gán lại sau khi fetch API) ----------------
const state = reactive({
    financeMetrics: [
        {
            title: "Doanh thu tháng này",
            value: formatMoney(0),
            icon: "ti-report-money",
            bgColor: "bg-brand-500",
            trendUp: true,
            trendLabel: "—",
        },
        {
            title: "Chi phí mua hàng tháng này",
            value: formatMoney(0),
            icon: "ti-shopping-cart",
            bgColor: "bg-warning-500",
            trendUp: false,
            trendLabel: "—",
        },
        {
            title: "Công nợ phải thu (KH)",
            value: formatMoney(0),
            icon: "ti-cash-banknote",
            bgColor: "bg-blue-500",
            trendUp: true,
            trendLabel: "—",
        },
        {
            title: "Công nợ phải trả (NCC)",
            value: formatMoney(0),
            icon: "ti-wallet",
            bgColor: "bg-orange-500",
            trendUp: false,
            trendLabel: "—",
        },
    ],
    operationStats: [
        {
            title: "Nhân sự",
            value: "0",
            icon: "ti-users",
            bgColor: "bg-blue-50 dark:bg-blue-500/10",
            textColor: "text-blue-600",
        },
        {
            title: "Khách hàng",
            value: "0",
            icon: "ti-user-heart",
            bgColor: "bg-brand-50 dark:bg-brand-500/10",
            textColor: "text-brand-600",
        },
        {
            title: "Nhà cung cấp",
            value: "0",
            icon: "ti-truck-delivery",
            bgColor: "bg-warning-50 dark:bg-warning-500/10",
            textColor: "text-warning-600",
        },
     
        {
            title: "Kho hàng",
            value: "0",
            icon: "ti-building-warehouse",
            bgColor: "bg-purple-50 dark:bg-purple-500/10",
            textColor: "text-purple-600",
        },
        {
            title: "Đơn hàng tháng này",
            value: "0",
            icon: "ti-file-invoice",
            bgColor: "bg-rose-50 dark:bg-rose-500/10",
            textColor: "text-rose-600",
        },
    ],
    monthlyFinance: [],
    cashFlow: [],
    debtTrend: [],
    warehouseFlow: [],
    orderStatus: [
        { label: "Chờ xử lý", value: 0, color: "#f59e0b" },
        { label: "Đã duyệt", value: 0, color: "#22c55e" },
        { label: "Đã hủy", value: 0, color: "#ef4444" },
    ],
    topCustomers: [],
    topSuppliers: [],
    recentSalesOrders: [],
    recentPurchaseOrders: [],
    recentTransactions: [],
    lowStockProducts: [],
});

const financeMetrics = computed(() => state.financeMetrics);
const operationStats = computed(() => state.operationStats);
const monthlyFinance = computed(() => state.monthlyFinance);
const cashFlow = computed(() => state.cashFlow);
const debtTrend = computed(() => state.debtTrend);
const warehouseFlow = computed(() => state.warehouseFlow);
const orderStatus = computed(() => state.orderStatus);
const topCustomers = computed(() => state.topCustomers);
const topSuppliers = computed(() => state.topSuppliers);
const lowStockProducts = computed(() => state.lowStockProducts);

// ---------------- CỘT BẢNG (cố định, không phụ thuộc API) ----------------
const salesOrderColumns = [
    { label: "Mã đơn", key: "code" },
    { label: "Khách hàng", key: "customer" },
    { label: "Ngày tạo", key: "date" },
    { label: "Tổng tiền", key: "total", align: "text-right" },
    { label: "Trạng thái", key: "status" },
];
const purchaseOrderColumns = [
    { label: "Mã đơn", key: "code" },
    { label: "Nhà cung cấp", key: "supplier" },
    { label: "Ngày tạo", key: "date" },
    { label: "Tổng tiền", key: "total", align: "text-right" },
    { label: "Trạng thái", key: "status" },
];
const transactionColumns = [
    { label: "Mã GD", key: "code" },
    { label: "Loại", key: "type" },
    { label: "Đối tượng", key: "target" },
    { label: "Số tiền", key: "amount", align: "text-right" },
    { label: "Ngày", key: "date" },
];

// DataTable cần field đã format tiền tệ + nhãn trạng thái tiếng Việt
const statusLabel = (status) =>
    ({
        pending: "Chờ xử lý",
        approved: "Đã duyệt",
        partial: "Nhập/xuất một phần",
        completed: "Hoàn tất",
        cancelled: "Đã hủy",
    })[status] ?? status;

const recentSalesOrders = computed(() =>
    state.recentSalesOrders.map((o) => ({
        ...o,
        total: formatMoney(o.total),
        status: statusLabel(o.status),
    })),
);
const recentPurchaseOrders = computed(() =>
    state.recentPurchaseOrders.map((o) => ({
        ...o,
        total: formatMoney(o.total),
        status: statusLabel(o.status),
    })),
);
const recentTransactions = computed(() =>
    state.recentTransactions.map((t) => ({
        ...t,
        amount: formatMoney(t.amount),
    })),
);

// ---------------- COMPUTED PHỤ TRỢ CHO BIỂU ĐỒ ----------------
const financeMax = computed(() =>
    Math.max(
        1,
        ...state.monthlyFinance.map((r) => Math.max(r.revenue, r.purchase)),
    ),
);
const warehouseMax = computed(() =>
    Math.max(
        1,
        ...state.warehouseFlow.map((r) => Math.max(r.import, r.export)),
    ),
);
const topCustomersMax = computed(() =>
    Math.max(1, ...state.topCustomers.map((c) => c.value)),
);
const topSuppliersMax = computed(() =>
    Math.max(1, ...state.topSuppliers.map((s) => s.value)),
);

function toSvgPoints(values, allValuesForMax) {
    if (!values.length) return "";
    const max = Math.max(1, ...allValuesForMax);
    const stepX = 600 / Math.max(values.length - 1, 1);
    return values
        .map((v, i) => {
            const x = i * stepX;
            const y = 190 - (v / max) * 180;
            return `${x},${y}`;
        })
        .join(" ");
}
const cashInPoints = computed(() =>
    toSvgPoints(
        state.cashFlow.map((r) => r.in),
        state.cashFlow.flatMap((r) => [r.in, r.out]),
    ),
);
const cashOutPoints = computed(() =>
    toSvgPoints(
        state.cashFlow.map((r) => r.out),
        state.cashFlow.flatMap((r) => [r.in, r.out]),
    ),
);
const receivablePoints = computed(() =>
    toSvgPoints(
        state.debtTrend.map((r) => r.receivable),
        state.debtTrend.flatMap((r) => [r.receivable, r.payable]),
    ),
);
const payablePoints = computed(() =>
    toSvgPoints(
        state.debtTrend.map((r) => r.payable),
        state.debtTrend.flatMap((r) => [r.receivable, r.payable]),
    ),
);

const orderStatusTotal = computed(() =>
    state.orderStatus.reduce((sum, s) => sum + s.value, 0),
);

const orderStatusGradient = computed(() => {
    const total = Math.max(orderStatusTotal.value, 1);

    let cursor = 0;

    return `conic-gradient(${state.orderStatus
        .map((s) => {
            const start = (cursor / total) * 360;
            cursor += s.value;
            const end = (cursor / total) * 360;
            return `${s.color} ${start}deg ${end}deg`;
        })
        .join(", ")})`;
});

// ---------------- HELPER ----------------
function barHeight(value, max) {
    if (!max) return "0%";
    return `${Math.max((value / max) * 100, 3)}%`;
}

// ---------------- NẠP DỮ LIỆU TỪ API THẬT ----------------
async function loadDashboard() {
    loading.value = true;
    loadError.value = false;

    try {
        const res = await axios.get("/api/dashboard/overview");
        const d = res.data.data;

        state.financeMetrics = [
            {
                title: "Doanh thu tháng này",
                value: formatMoney(d.finance.revenue_this_month),
                icon: "ti-report-money",
                bgColor: "bg-brand-500",
                trendUp: d.finance.revenue_trend_percent >= 0,
                trendLabel: `${d.finance.revenue_trend_percent >= 0 ? "+" : ""}${d.finance.revenue_trend_percent}% so với tháng trước`,
            },
            {
                title: "Chi phí mua hàng tháng này",
                value: formatMoney(d.finance.purchase_this_month),
                icon: "ti-shopping-cart",
                bgColor: "bg-warning-500",
                trendUp: d.finance.purchase_trend_percent >= 0,
                trendLabel: `${d.finance.purchase_trend_percent >= 0 ? "+" : ""}${d.finance.purchase_trend_percent}% so với tháng trước`,
            },
            {
                title: "Công nợ phải thu (KH)",
                value: formatMoney(d.finance.receivable_debt),
                icon: "ti-cash-banknote",
                bgColor: "bg-blue-500",
                trendUp: true,
                trendLabel: "Tổng công nợ khách hàng hiện tại",
            },
            {
                title: "Công nợ phải trả (NCC)",
                value: formatMoney(d.finance.payable_debt),
                icon: "ti-wallet",
                bgColor: "bg-orange-500",
                trendUp: false,
                trendLabel: "Tổng công nợ nhà cung cấp hiện tại",
            },
        ];

        state.operationStats = [
            {
                title: "Nhân sự",
                value: String(d.operations.users),
                icon: "ti-users",
                bgColor: "bg-blue-50 dark:bg-blue-500/10",
                textColor: "text-blue-600",
            },
            {
                title: "Khách hàng",
                value: String(d.operations.customers),
                icon: "ti-user-heart",
                bgColor: "bg-brand-50 dark:bg-brand-500/10",
                textColor: "text-brand-600",
            },
            {
                title: "Nhà cung cấp",
                value: String(d.operations.suppliers),
                icon: "ti-truck-delivery",
                bgColor: "bg-warning-50 dark:bg-warning-500/10",
                textColor: "text-warning-600",
            },
       
            {
                title: "Kho hàng",
                value: String(d.operations.warehouses),
                icon: "ti-building-warehouse",
                bgColor: "bg-purple-50 dark:bg-purple-500/10",
                textColor: "text-purple-600",
            },
            {
                title: "Đơn hàng tháng này",
                value: String(d.operations.orders_this_month),
                icon: "ti-file-invoice",
                bgColor: "bg-rose-50 dark:bg-rose-500/10",
                textColor: "text-rose-600",
            },
        ];

        state.monthlyFinance = d.monthly_finance;
        state.cashFlow = d.cash_flow;
        state.debtTrend = d.debt_trend;
        state.warehouseFlow = d.warehouse_flow;
        state.orderStatus = d.order_status;
        state.topCustomers = d.top_customers;
        state.topSuppliers = d.top_suppliers;
        state.recentSalesOrders = d.recent_sales_orders;
        state.recentPurchaseOrders = d.recent_purchase_orders;
        state.recentTransactions = d.recent_transactions;
        state.lowStockProducts = d.low_stock_products;
    } catch (error) {
        loadError.value = true;
        console.error("Không tải được dữ liệu dashboard:", error);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadDashboard();
});
</script>

<style scoped>
.text-title-md {
    font-size: 1.5rem;
    line-height: 1.4;
}
</style>
