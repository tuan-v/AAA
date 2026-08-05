<template>
    <Head title="Tổng quan doanh nghiệp"></Head>
    <AdminLayout>
        <PageBreadcrumb
            title="Tổng quan"
            :items="[{ text: 'Tổng quan', link: null }]"
        />

        <DashboardDateFilter
            v-model:date-from="dateFrom"
            v-model:date-to="dateTo"
            :loading="loading"
            class="mb-6"
            @apply="loadDashboard"
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
                    role="img"
                    aria-label="Biểu đồ dòng tiền thu và chi"
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
                    <g v-if="hasCashFlowData">
                    <g v-for="(group, index) in cashBarGroups" :key="`cash-group-${index}`">
                        <rect
                            :x="group.inX"
                            :y="cashBarY(group.inValue)"
                            width="22"
                            :height="cashBarHeight(group.inValue)"
                            rx="5"
                            fill="#16a34a"
                            class="drop-shadow-sm"
                        >
                            <title>{{ cashFlow[index].month }} · Tiền vào: {{ formatMoney(group.inValue) }}</title>
                        </rect>
                        <rect
                            :x="group.outX"
                            :y="cashBarY(group.outValue)"
                            width="22"
                            :height="cashBarHeight(group.outValue)"
                            rx="5"
                            fill="#dc2626"
                            class="drop-shadow-sm"
                        >
                            <title>{{ cashFlow[index].month }} · Tiền ra: {{ formatMoney(group.outValue) }}</title>
                        </rect>
                        <text :x="group.inX + 11" :y="Math.max(cashBarY(group.inValue) - 7, 12)" text-anchor="middle" class="fill-green-700 text-[10px] font-semibold dark:fill-green-300">
                            {{ formatCompactMoney(group.inValue) }}
                        </text>
                        <text :x="group.outX + 11" :y="Math.max(cashBarY(group.outValue) - 7, 12)" text-anchor="middle" class="fill-red-700 text-[10px] font-semibold dark:fill-red-300">
                            {{ formatCompactMoney(group.outValue) }}
                        </text>
                    </g>
                    </g>
                    <text
                        v-else
                        x="300"
                        y="105"
                        text-anchor="middle"
                        class="fill-gray-400 text-sm dark:fill-gray-500"
                    >
                        Chưa có phiếu thu hoặc chi đã duyệt trong kỳ
                    </text>
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
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3
                            class="text-base font-semibold text-gray-800 dark:text-white/90"
                        >
                            Biến động công nợ
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Phát sinh ròng theo từng tháng
                        </p>
                    </div>
                    <div v-if="latestDebtPoint" class="flex flex-wrap gap-2">
                        <div
                            class="rounded-xl border border-blue-100 bg-blue-50/70 px-3 py-2 dark:border-blue-500/20 dark:bg-blue-500/10"
                            :title="`Phải thu ${latestDebtPoint.month}: ${formatMoney(latestDebtPoint.receivable)}`"
                        >
                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-300">
                                <i class="h-2 w-2 rounded-full bg-blue-500"></i>
                                Phát sinh phải thu
                            </div>
                            <strong class="mt-0.5 block text-sm font-semibold text-slate-800 dark:text-white">
                                {{ formatCompactMoney(latestDebtPoint.receivable) }}
                            </strong>
                        </div>
                        <div
                            class="rounded-xl border border-orange-100 bg-orange-50/70 px-3 py-2 dark:border-orange-500/20 dark:bg-orange-500/10"
                            :title="`Phải trả ${latestDebtPoint.month}: ${formatMoney(latestDebtPoint.payable)}`"
                        >
                            <div class="flex items-center gap-1.5 text-[11px] font-medium text-orange-600 dark:text-orange-300">
                                <i class="h-2 w-2 rounded-full bg-orange-500"></i>
                                Phát sinh phải trả
                            </div>
                            <strong class="mt-0.5 block text-sm font-semibold text-slate-800 dark:text-white">
                                {{ formatCompactMoney(latestDebtPoint.payable) }}
                            </strong>
                        </div>
                    </div>
                </div>

                <svg
                    viewBox="0 0 600 200"
                    class="w-full h-52"
                    preserveAspectRatio="none"
                    role="img"
                    aria-label="Biểu đồ biến động công nợ phải thu và phải trả"
                >
                    <line
                        v-for="n in 4"
                        :key="'grid2-' + n"
                        x1="0"
                        :y1="n * 40"
                        x2="600"
                        :y2="n * 40"
                        stroke="currentColor"
                        class="text-slate-100 dark:text-gray-800"
                        stroke-width="1"
                        stroke-dasharray="4 6"
                    />
                    <line
                        x1="0"
                        y1="100"
                        x2="600"
                        y2="100"
                        stroke="currentColor"
                        class="text-slate-200 dark:text-gray-700"
                        stroke-width="1.25"
                    />
                    <g v-if="debtTrend.length === 1 && hasDebtData">
                        <rect
                            x="252"
                            :y="debtBarY(debtTrend[0].receivable)"
                            width="36"
                            :height="debtBarHeight(debtTrend[0].receivable)"
                            rx="6"
                            fill="#3b82f6"
                            class="drop-shadow-sm"
                        >
                            <title>Phải thu: {{ formatMoney(debtTrend[0].receivable) }}</title>
                        </rect>
                        <rect
                            x="312"
                            :y="debtBarY(debtTrend[0].payable)"
                            width="36"
                            :height="debtBarHeight(debtTrend[0].payable)"
                            rx="6"
                            fill="#f97316"
                            class="drop-shadow-sm"
                        >
                            <title>Phải trả: {{ formatMoney(debtTrend[0].payable) }}</title>
                        </rect>
                    </g>
                    <g v-else-if="hasDebtData">
                    <polyline
                        :points="receivablePoints"
                        fill="none"
                        stroke="#3b82f6"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <polyline
                        :points="payablePoints"
                        fill="none"
                        stroke="#f97316"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <circle
                        v-for="(point, index) in receivableChartPoints"
                        :key="`receivable-${index}`"
                        :cx="point.x"
                        :cy="point.y"
                        :r="index === receivableChartPoints.length - 1 ? 5 : 3.5"
                        fill="#3b82f6"
                        stroke="white"
                        stroke-width="2"
                    >
                        <title>{{ debtTrend[index].month }} · Phải thu: {{ formatMoney(point.value) }}</title>
                    </circle>
                    <circle
                        v-for="(point, index) in payableChartPoints"
                        :key="`payable-${index}`"
                        :cx="point.x"
                        :cy="point.y"
                        :r="index === payableChartPoints.length - 1 ? 5 : 3.5"
                        fill="#f97316"
                        stroke="white"
                        stroke-width="2"
                    >
                        <title>{{ debtTrend[index].month }} · Phải trả: {{ formatMoney(point.value) }}</title>
                    </circle>
                    </g>
                    <text
                        v-else
                        x="300"
                        y="105"
                        text-anchor="middle"
                        class="fill-gray-400 text-sm dark:fill-gray-500"
                    >
                        Chưa phát sinh công nợ trong kỳ
                    </text>
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
                        href="/accountant/transactions"
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
import DashboardDateFilter from "@/components/dashboard/DashboardDateFilter.vue";
import { computed, h, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { formatMoney as money, formatQuantity } from "@/config/helpers";
import { getOrderStatusMeta } from "@/config/status";
import { useRealtimeRefresh } from "@/composables/useRealtimeRefresh";

// ==========================================================================
// Dữ liệu dashboard được nạp từ API thật: GET /api/dashboard/overview
// (xem DashboardController@overview + DashboardService + DashboardRepository)
// Mảng mock bên dưới CHỈ dùng làm giá trị khởi tạo / fallback khi API lỗi,
// để giao diện luôn có gì đó để hiển thị ngay cả khi mất mạng hoặc lỗi 500.
// ==========================================================================

const loading = ref(true);
const loadError = ref(false);
const today = new Date();
const toDateInput = (date) => {
    const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
};
const dateFrom = ref(toDateInput(new Date(today.getFullYear(), today.getMonth(), 1)));
const dateTo = ref(toDateInput(today));
const dashboardCurrency = ref({ code: "VND", symbol: "₫" });
const formatMoney = (value) => money(value, dashboardCurrency.value);

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
const latestDebtPoint = computed(() => state.debtTrend.at(-1) ?? null);
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
    {
        label: "Trạng thái",
        key: "status",
        render: (row) => renderOrderStatus(row.status),
    },
];
const purchaseOrderColumns = [
    { label: "Mã đơn", key: "code" },
    { label: "Nhà cung cấp", key: "supplier" },
    { label: "Ngày tạo", key: "date" },
    { label: "Tổng tiền", key: "total", align: "text-right" },
    {
        label: "Trạng thái",
        key: "status",
        render: (row) => renderOrderStatus(row.status),
    },
];
const transactionColumns = [
    { label: "Mã GD", key: "code" },
    {
        label: "Hướng",
        key: "type",
        render: (row) => renderTransactionDirection(row.type),
    },
    {
        label: "Nghiệp vụ",
        key: "business_type",
        render: (row) => renderTransactionBusinessType(row),
    },
    { label: "Đối tượng", key: "target" },
    { label: "Số tiền", key: "amount", align: "text-right" },
    { label: "Ngày", key: "date" },
    {
        label: "Trạng thái",
        key: "status",
        align: "text-center",
        render: (row) => renderTransactionStatus(row.status),
    },
];

const renderOrderStatus = (status) => {
    const meta = getOrderStatusMeta(status);
    return h(
        "span",
        {
            class: `${meta.class} inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold`,
        },
        meta.label,
    );
};

const renderTransactionStatus = (status) => {
    const statuses = {
        pending: {
            label: "Chờ duyệt",
            class: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300",
        },
        approved: {
            label: "Đã duyệt",
            class: "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300",
        },
        rejected: {
            label: "Từ chối",
            class: "border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300",
        },
    };
    const meta = statuses[status] ?? {
        label: status || "Không xác định",
        class: "border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300",
    };

    return h(
        "span",
        {
            class: `${meta.class} inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold`,
        },
        meta.label,
    );
};

const renderTransactionDirection = (type) => {
    const directions = {
        "Thu tiền": {
            icon: "↙",
            class: "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300",
        },
        "Chi tiền": {
            icon: "↗",
            class: "border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300",
        },
        "Chuyển quỹ": {
            icon: "↔",
            class: "border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300",
        },
    };
    const meta = directions[type] ?? {
        icon: "•",
        class: "border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-300",
    };

    return h(
        "span",
        {
            class: `${meta.class} inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold`,
        },
        [h("span", { class: "text-sm leading-none" }, meta.icon), type],
    );
};

const renderTransactionBusinessType = (row) => {
    const styles = {
        CHI_NCC: "bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-300",
        CHI_KHAC: "bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-300",
        TAM_UNG_NCC: "bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-300",
        HOAN_TAM_UNG_NCC: "bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-300",
        THU_KH: "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300",
        THU_KHAC: "bg-teal-50 text-teal-700 dark:bg-teal-500/10 dark:text-teal-300",
        TAM_UNG_KH: "bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-300",
        HOAN_TAM_UNG_KH: "bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-300",
    };

    return h(
        "span",
        {
            class: `${styles[row.category_code] ?? "bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-300"} inline-flex whitespace-nowrap rounded-lg px-2.5 py-1 text-xs font-medium`,
        },
        row.business_type,
    );
};

const recentSalesOrders = computed(() =>
    state.recentSalesOrders.map((o) => ({
        ...o,
        total: formatMoney(o.total),
    })),
);
const recentPurchaseOrders = computed(() =>
    state.recentPurchaseOrders.map((o) => ({
        ...o,
        total: formatMoney(o.total),
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

function toSvgChartPoints(values, allValuesForMax) {
    if (!values.length) return [];
    const max = Math.max(1, ...allValuesForMax);
    const chartPadding = 48;
    const stepX = (600 - chartPadding * 2) / Math.max(values.length - 1, 1);
    return values.map((v, i) => {
            const x = values.length === 1 ? 300 : chartPadding + i * stepX;
            const y = 190 - (v / max) * 180;
            return { x, y, value: v };
        });
}

function toSignedSvgChartPoints(values, allValues) {
    if (!values.length) return [];
    const maxAbsolute = Math.max(1, ...allValues.map((value) => Math.abs(value)));
    const chartPadding = 48;
    const stepX = (600 - chartPadding * 2) / Math.max(values.length - 1, 1);

    return values.map((value, index) => ({
        x: values.length === 1 ? 300 : chartPadding + index * stepX,
        y: 100 - (value / maxAbsolute) * 80,
        value,
    }));
}
const cashFlowValues = computed(() =>
    state.cashFlow.flatMap((row) => [Number(row.in) || 0, Number(row.out) || 0]),
);
const cashFlowMax = computed(() => Math.max(1, ...cashFlowValues.value));
const hasCashFlowData = computed(() => cashFlowValues.value.some((value) => value > 0));
const cashBarHeight = (value) => value > 0 ? Math.max((value / cashFlowMax.value) * 170, 4) : 0;
const cashBarY = (value) => 190 - cashBarHeight(value);
const formatCompactMoney = (value) => new Intl.NumberFormat("vi-VN", {
    notation: "compact",
    maximumFractionDigits: 1,
}).format(Number(value) || 0);
const cashBarGroups = computed(() => {
    const count = state.cashFlow.length;
    const padding = 48;
    const step = (600 - padding * 2) / Math.max(count - 1, 1);

    return state.cashFlow.map((row, index) => {
        const center = count === 1 ? 300 : padding + index * step;
        return {
            inX: center - 25,
            outX: center + 3,
            inValue: Number(row.in) || 0,
            outValue: Number(row.out) || 0,
        };
    });
});
const debtValues = computed(() =>
    state.debtTrend.flatMap((row) => [
        Number(row.receivable) || 0,
        Number(row.payable) || 0,
    ]),
);
const debtMax = computed(() => Math.max(1, ...debtValues.value.map((value) => Math.abs(value))));
const hasDebtData = computed(() => debtValues.value.some((value) => value !== 0));
const debtBarHeight = (value) => value !== 0 ? Math.max((Math.abs(value) / debtMax.value) * 80, 4) : 0;
const debtBarY = (value) => value >= 0 ? 100 - debtBarHeight(value) : 100;
const receivableChartPoints = computed(() =>
    toSignedSvgChartPoints(
        state.debtTrend.map((row) => Number(row.receivable) || 0),
        debtValues.value,
    ),
);
const payableChartPoints = computed(() =>
    toSignedSvgChartPoints(
        state.debtTrend.map((row) => Number(row.payable) || 0),
        debtValues.value,
    ),
);
const receivablePoints = computed(() =>
    receivableChartPoints.value.map(({ x, y }) => `${x},${y}`).join(" "),
);
const payablePoints = computed(() =>
    payableChartPoints.value.map(({ x, y }) => `${x},${y}`).join(" "),
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
        const res = await axios.get("/api/dashboard/overview", {
            params: { date_from: dateFrom.value, date_to: dateTo.value },
        });
        const d = res.data.data;
        dashboardCurrency.value = d.currency || dashboardCurrency.value;

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
        console.error("Không tải được dữ liệu tổng quan:", error);
    } finally {
        loading.value = false;
    }
}

useRealtimeRefresh(loadDashboard);

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
