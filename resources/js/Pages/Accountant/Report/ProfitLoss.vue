<template>
    <Head title="Báo cáo mua bán và lãi lỗ" />
    <AdminLayout>
        <PageBreadcrumb
            title=""
            :items="[
                { text: 'Kế toán', link: '/accountant' },
                { text: 'Báo cáo lãi lỗ', link: null },
            ]"
        />

        <div class="report-heading">
            <div>
                <p class="eyebrow">BÁO CÁO KẾ TOÁN</p>
                <h1>Tổng hợp mua bán &amp; lãi lỗ</h1>
                <p>Theo dõi hiệu quả bán hàng dựa trên số lượng nhập, xuất thực tế đã duyệt.</p>
            </div>
        </div>

        <section class="filter-card">
            <SearchPage
                :filters="searchFilters"
                :default-params="defaultFilterParams"
                @filter="handleFilter"
            />
        </section>

        <div v-if="error" class="error-box"><i class="ti ti-alert-circle"></i>{{ error }}</div>

        <template v-if="report">
            <section class="summary-grid">
                <article class="summary-card purchase">
                    <div class="summary-icon"><i class="ti ti-shopping-cart-down"></i></div>
                    <div><span>Giá trị hàng đã nhập</span><strong>{{ money(summary.purchase_value) }}</strong><small>{{ summary.import_slips_count || 0 }} phiếu nhập đã duyệt · {{ summary.imported_products_count || 0 }} mặt hàng</small></div>
                </article>
                <article class="summary-card revenue">
                    <div class="summary-icon"><i class="ti ti-cash"></i></div>
                    <div><span>Doanh thu hàng đã xuất</span><strong>{{ money(summary.revenue) }}</strong><small>{{ summary.sales_count || 0 }} phiếu xuất đã duyệt · {{ summary.sold_products_count || 0 }} mặt hàng</small></div>
                </article>
                <article class="summary-card cost">
                    <div class="summary-icon"><i class="ti ti-packages"></i></div>
                    <div><span>Giá vốn hàng đã xuất</span><strong>{{ money(summary.cost_of_goods) }}</strong><small>Tính theo giá vốn thực tế tại thời điểm xuất kho</small></div>
                </article>
                <article class="summary-card" :class="summary.gross_profit >= 0 ? 'profit' : 'loss'">
                    <div class="summary-icon"><i class="ti ti-chart-line"></i></div>
                    <div><span>Lợi nhuận gộp</span><strong>{{ money(summary.gross_profit) }}</strong><small>Biên lợi nhuận {{ number(summary.margin) }}%</small></div>
                </article>
            </section>

            <section class="content-grid">
                <article class="panel trend-panel">
                    <div class="panel-header">
                        <div><h2>Xu hướng doanh thu và lãi gộp</h2><p>Số liệu thực tế theo ngày xuất kho</p></div>
                        <div class="legend"><span class="revenue-dot"></span>Doanh thu <span class="profit-dot"></span>Lãi gộp</div>
                    </div>
                    <div v-if="trend.length" class="chart">
                        <div v-for="item in trend" :key="item.date" class="chart-column" :title="`${date(item.date)}: ${money(item.revenue)}`">
                            <div class="bars">
                                <div class="bar-series">
                                    <strong>{{ compactMoney(item.revenue) }}</strong>
                                    <div class="bar revenue-bar" :style="{ height: barHeight(item.revenue) }"></div>
                                </div>
                                <div class="bar-series">
                                    <strong :class="{ 'text-loss': item.profit < 0 }">{{ compactMoney(item.profit) }}</strong>
                                    <div class="bar profit-bar" :class="{ negative: item.profit < 0 }" :style="{ height: barHeight(Math.abs(item.profit)) }"></div>
                                </div>
                            </div>
                            <span>{{ shortDate(item.date) }}</span>
                        </div>
                    </div>
                    <div v-if="trend.length" class="daily-detail">
                        <div class="daily-detail-title">Chi tiết theo ngày</div>
                        <div class="daily-detail-scroll">
                            <div v-for="item in trend" :key="`detail-${item.date}`" class="daily-row">
                                <strong>{{ date(item.date) }}</strong>
                                <span>Doanh thu <b>{{ money(item.revenue) }}</b></span>
                                <span>Giá vốn <b>{{ money(item.cost) }}</b></span>
                                <span>Lãi gộp <b :class="{ 'text-loss': item.profit < 0 }">{{ money(item.profit) }}</b></span>
                                <span>Biên lãi <b>{{ marginOf(item) }}%</b></span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state">Chưa có dữ liệu bán hàng trong khoảng thời gian này.</div>
                </article>

                <article class="panel margin-panel">
                    <div class="panel-header"><div><h2>Cơ cấu doanh thu</h2><p>Tỷ trọng giá vốn và lãi gộp</p></div></div>
                    <div class="donut" :style="donutStyle"><div><strong>{{ number(summary.margin) }}%</strong><span>Biên lãi gộp</span></div></div>
                    <div class="profit-formula">Doanh thu − Giá vốn = Lãi gộp</div>
                    <div class="margin-lines formula-lines">
                        <div><span><i class="revenue-key"></i>Doanh thu</span><strong>{{ money(summary.revenue) }}</strong></div>
                        <div><span><i class="cost-key"></i>Trừ giá vốn</span><strong>− {{ money(summary.cost_of_goods) }}</strong></div>
                        <div class="formula-result"><span><i class="profit-key"></i>Bằng lãi gộp</span><strong :class="summary.gross_profit < 0 ? 'text-loss' : ''">{{ money(summary.gross_profit) }}</strong></div>
                    </div>
                </article>
            </section>

            <section class="panel table-panel">
                <div class="panel-header"><div><h2>Sản phẩm mang lại lợi nhuận</h2><p>Top 10 sản phẩm theo lãi gộp thực tế</p></div></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Sản phẩm</th><th class="right">SL đã bán</th><th class="right">Doanh thu</th><th class="right">Giá vốn</th><th class="right">Lãi gộp</th></tr></thead>
                        <tbody>
                            <tr v-for="item in report.top_products" :key="item.sku || item.product">
                                <td><strong>{{ item.product }}</strong><small>{{ item.sku || 'Không có mã' }}</small></td>
                                <td class="right">{{ quantity(item.quantity) }} {{ item.unit }}</td>
                                <td class="right">{{ money(item.revenue) }}</td>
                                <td class="right">{{ money(item.cost) }}</td>
                                <td class="right profit-value" :class="{ 'text-loss': item.profit < 0 }">{{ money(item.profit) }}</td>
                            </tr>
                            <tr v-if="!report.top_products.length"><td colspan="5" class="empty-cell">Chưa có sản phẩm đã xuất kho.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel table-panel">
                <div class="panel-header"><div><h2>Chi tiết bán hàng</h2><p>Doanh thu và giá vốn theo từng phiếu xuất đã duyệt</p></div><span class="record-count">{{ report.sales.length }} phiếu</span></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Ngày</th><th>Phiếu xuất</th><th>Đơn bán</th><th>Khách hàng</th><th>Kho</th><th class="right">Doanh thu</th><th class="right">Giá vốn</th><th class="right">Lãi gộp</th></tr></thead>
                        <tbody>
                            <tr v-for="row in report.sales" :key="row.slip_code">
                                <td>{{ date(row.date) }}</td><td><strong class="code">{{ row.slip_code }}</strong></td><td>{{ row.order_code || '-' }}</td><td>{{ row.partner }}</td><td>{{ row.warehouse }}</td>
                                <td class="right">{{ money(row.revenue) }}</td><td class="right">{{ money(row.cost) }}</td><td class="right profit-value" :class="{ 'text-loss': row.profit < 0 }">{{ money(row.profit) }}</td>
                            </tr>
                            <tr v-if="!report.sales.length"><td colspan="8" class="empty-cell">Chưa có phiếu xuất đã duyệt.</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
            <p class="report-note"><i class="ti ti-info-circle"></i>{{ report.note }}</p>
        </template>
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PageBreadcrumb from '@/components/common/PageBreadcrumb.vue';
import SearchPage from '@/components/SearchPage.vue';

const localToday = () => {
    const value = new Date();
    value.setMinutes(value.getMinutes() - value.getTimezoneOffset());
    return value.toISOString().slice(0, 10);
};
const firstDay = () => `${localToday().slice(0, 8)}01`;
const today = localToday();
const defaultFilterParams = { from_date: firstDay(), to_date: today };
const activeFilters = ref({ from_date: firstDay(), to_date: today });
const report = ref(null);
const loading = ref(false);
const error = ref('');
const summary = computed(() => report.value?.summary ?? {});
const trend = computed(() => report.value?.trend ?? []);
const warehouseOptions = computed(() => (report.value?.warehouses ?? []).map(item => ({ value: item.id, label: item.name })));
const searchFilters = computed(() => [
    {
        name: 'from_date',
        type: 'date',
        placeholder: 'Từ ngày',
        config: { maxDate: activeFilters.value.to_date || today },
    },
    {
        name: 'to_date',
        type: 'date',
        placeholder: 'Đến ngày',
        config: {
            minDate: activeFilters.value.from_date || null,
            maxDate: 'today',
        },
    },
    {
        name: 'warehouse_id',
        type: 'select',
        placeholder: 'Tất cả kho',
        options: warehouseOptions.value,
    },
]);
const maximumChartValue = computed(() => Math.max(1, ...trend.value.flatMap(item => [item.revenue, Math.abs(item.profit)])));
const donutStyle = computed(() => {
    const safeMargin = Math.min(100, Math.max(0, Number(summary.value.margin || 0)));
    return { background: `conic-gradient(#16a34a 0 ${safeMargin}%, #f59e0b ${safeMargin}% 100%)` };
});

const number = value => new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 2 }).format(Number(value || 0));
const quantity = number;
const money = value => `${number(value)} ${report.value?.currency?.symbol || '₫'}`;
const compactMoney = value => new Intl.NumberFormat('vi-VN', { notation: 'compact', maximumFractionDigits: 1 }).format(Number(value || 0));
const date = value => value ? new Intl.DateTimeFormat('vi-VN').format(new Date(`${value}T00:00:00`)) : '-';
const shortDate = value => value ? value.slice(8, 10) + '/' + value.slice(5, 7) : '';
const barHeight = value => `${Math.max(3, Number(value || 0) / maximumChartValue.value * 150)}px`;
const marginOf = item => item.revenue > 0 ? number(item.profit / item.revenue * 100) : '0';

async function fetchReport(params = activeFilters.value) {
    loading.value = true;
    error.value = '';
    try {
        const { data } = await axios.get('/api/accountant/profit-loss-report', { params });
        report.value = data;
    } catch (exception) {
        error.value = exception.response?.data?.message || 'Không thể tải báo cáo lãi lỗ.';
        toast.error(error.value);
    } finally {
        loading.value = false;
    }
}
function handleFilter(params) {
    activeFilters.value = {
        from_date: params.from_date || firstDay(),
        to_date: params.to_date || today,
        warehouse_id: params.warehouse_id || null,
    };
    fetchReport(activeFilters.value);
}
onMounted(fetchReport);
</script>

<style scoped>
.report-heading,.panel-header,.margin-lines>div{display:flex;align-items:center;justify-content:space-between}.report-heading{margin-bottom:20px}.eyebrow{font-size:11px;font-weight:800;letter-spacing:.14em;color:#2563eb}.report-heading h1{font-size:26px;font-weight:800;color:#172033;margin:4px 0}.report-heading p,.panel-header p{color:#64748b;font-size:13px}.filter-card{position:relative;z-index:40;background:#fff;padding:16px;border:1px solid #e2e8f0;border-radius:14px;margin-bottom:18px;box-shadow:0 5px 18px rgba(15,23,42,.04)}.summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}.summary-card{background:#fff;border:1px solid #e2e8f0;border-radius:15px;padding:18px;display:flex;gap:13px;box-shadow:0 5px 18px rgba(15,23,42,.04)}.summary-card>div:last-child{min-width:0}.summary-icon{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;font-size:21px;flex:none}.summary-card span{display:block;font-size:12px;color:#64748b;font-weight:700}.summary-card strong{display:block;font-size:21px;color:#172033;margin:4px 0;white-space:nowrap}.summary-card small{font-size:11px;color:#64748b}.purchase .summary-icon{background:#eff6ff;color:#2563eb}.revenue .summary-icon{background:#ecfdf5;color:#059669}.cost .summary-icon{background:#fff7ed;color:#ea580c}.profit .summary-icon{background:#f0fdf4;color:#16a34a}.loss .summary-icon{background:#fef2f2;color:#dc2626}.content-grid{display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:16px}.panel{background:#fff;border:1px solid #e2e8f0;border-radius:15px;box-shadow:0 5px 18px rgba(15,23,42,.04)}.panel-header{padding:18px 20px;border-bottom:1px solid #edf1f5}.panel-header h2{font-size:16px;font-weight:800;color:#1e293b}.legend{font-size:11px;color:#64748b;display:flex;align-items:center;gap:6px}.legend span{width:8px;height:8px;border-radius:50%;margin-left:8px}.revenue-dot{background:#2563eb}.profit-dot{background:#16a34a}.chart{height:245px;display:flex;align-items:flex-end;gap:8px;padding:22px 20px 14px;overflow-x:auto}.chart-column{height:205px;min-width:76px;flex:1;display:flex;flex-direction:column;justify-content:flex-end;align-items:center}.bars{height:180px;display:flex;align-items:flex-end;gap:8px}.bar-series{height:180px;display:flex;flex-direction:column;justify-content:flex-end;align-items:center}.bar-series>strong{font-size:10px;color:#334155;margin-bottom:5px;white-space:nowrap}.bar{width:14px;min-height:3px;border-radius:5px 5px 1px 1px}.revenue-bar{background:#3b82f6}.profit-bar{background:#22c55e}.profit-bar.negative{background:#ef4444}.chart-column>span{font-size:10px;color:#64748b;margin-top:7px}.daily-detail{border-top:1px solid #e2e8f0;padding:14px 18px 18px}.daily-detail-title{font-size:12px;font-weight:800;color:#334155;margin-bottom:8px}.daily-detail-scroll{max-height:190px;overflow:auto;border:1px solid #e2e8f0;border-radius:10px}.daily-row{display:grid;grid-template-columns:100px repeat(4,minmax(125px,1fr));gap:12px;align-items:center;padding:10px 12px;border-top:1px solid #eef2f7;font-size:11px}.daily-row:first-child{border-top:0}.daily-row>strong{color:#334155}.daily-row span{color:#64748b}.daily-row b{display:block;color:#1e293b;margin-top:2px}.empty-state,.empty-cell{text-align:center!important;color:#94a3b8;padding:42px!important}.donut{width:150px;height:150px;border-radius:50%;margin:22px auto 14px;display:grid;place-items:center}.donut:before{content:'';position:absolute}.donut>div{width:105px;height:105px;border-radius:50%;background:#fff;display:flex;flex-direction:column;align-items:center;justify-content:center}.donut strong{font-size:25px;color:#1e293b}.donut span{font-size:11px;color:#64748b}.profit-formula{text-align:center;margin:0 20px 12px;padding:9px;border-radius:8px;background:#f8fafc;color:#475569;font-size:11px;font-weight:800}.margin-lines{padding:0 20px 20px}.margin-lines>div{padding:9px 0;border-top:1px solid #f1f5f9;font-size:12px}.margin-lines i{display:inline-block;width:9px;height:9px;border-radius:3px;margin-right:7px}.revenue-key{background:#3b82f6}.cost-key{background:#f59e0b}.profit-key{background:#16a34a}.formula-result{border-top:2px solid #cbd5e1!important;font-size:13px!important}.formula-result strong{color:#15803d}.table-panel{margin-bottom:16px}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:760px}th{font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#64748b;background:#f8fafc;text-align:left;padding:12px 16px}td{font-size:13px;color:#334155;padding:13px 16px;border-top:1px solid #edf1f5}td small{display:block;color:#94a3b8;margin-top:2px}.right{text-align:right}.profit-value{font-weight:800;color:#15803d}.text-loss{color:#dc2626!important}.code{color:#2563eb}.record-count{font-size:12px;font-weight:700;background:#eff6ff;color:#2563eb;padding:5px 10px;border-radius:20px}.report-note,.error-box{font-size:12px;color:#64748b;display:flex;gap:7px;align-items:center}.report-note{margin:4px 2px 20px}.error-box{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca;padding:12px;border-radius:10px;margin-bottom:16px}@media(max-width:1100px){.summary-grid{grid-template-columns:repeat(2,1fr)}.content-grid{grid-template-columns:1fr}}@media(max-width:700px){.report-heading{align-items:flex-start}.summary-grid{grid-template-columns:1fr}.report-heading h1{font-size:21px}.daily-row{min-width:680px}}
</style>
