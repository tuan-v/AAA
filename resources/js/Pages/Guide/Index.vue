<template>
    <Head title="Hướng dẫn sử dụng" />
    <AdminLayout>
        <div class="mx-auto max-w-[1500px]">
            <section class="overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-700 px-6 py-7 text-white shadow-lg md:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Trung tâm trợ giúp</p>
                <h1 class="mt-2 text-2xl font-bold md:text-3xl">Hướng dẫn sử dụng hệ thống</h1>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-blue-100">
                    Tra cứu quy trình theo từng phân hệ. Chức năng hiển thị trên tài khoản phụ thuộc quyền được cấp.
                </p>
                <div class="relative mt-5 max-w-2xl">
                    <MagnifyingGlassIcon class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model.trim="keyword"
                        type="search"
                        placeholder="Tìm hướng dẫn, ví dụ: tạo đơn mua, duyệt phiếu kho, tỷ giá..."
                        class="w-full rounded-xl border-0 bg-white py-3 pl-12 pr-4 text-sm text-slate-800 shadow-sm outline-none ring-0 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-300"
                    />
                </div>
            </section>

            <div class="mt-6 grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
                <aside class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm lg:sticky lg:top-24">
                    <p class="px-3 pb-2 pt-1 text-xs font-bold uppercase tracking-wider text-slate-400">Mục lục</p>
                    <nav class="max-h-[calc(100vh-180px)] space-y-1 overflow-y-auto">
                        <button
                            v-for="section in filteredSections"
                            :key="section.id"
                            type="button"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-blue-50 hover:text-blue-700"
                            @click="scrollTo(section.id)"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-500">{{ section.number }}</span>
                            {{ section.title }}
                        </button>
                    </nav>
                </aside>

                <main class="space-y-5">
                    <div v-if="!filteredSections.length" class="rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
                        Không tìm thấy nội dung phù hợp với “{{ keyword }}”.
                    </div>
                    <article
                        v-for="section in filteredSections"
                        :id="section.id"
                        :key="section.id"
                        class="scroll-mt-24 rounded-xl border border-slate-200 bg-white p-5 shadow-sm md:p-7"
                    >
                        <div class="flex items-start gap-4 border-b border-slate-100 pb-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 font-bold text-blue-700">{{ section.number }}</span>
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">{{ section.title }}</h2>
                                <p class="mt-1 text-sm leading-6 text-slate-500">{{ section.description }}</p>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-4 xl:grid-cols-2">
                            <section v-for="topic in section.topics" :key="topic.title" class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                                <h3 class="font-semibold text-slate-800">{{ topic.title }}</h3>
                                <ol v-if="topic.steps" class="mt-3 space-y-2 text-sm leading-6 text-slate-600">
                                    <li v-for="(step, index) in topic.steps" :key="step" class="flex gap-2">
                                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[11px] font-bold text-blue-700">{{ index + 1 }}</span>
                                        <span>{{ step }}</span>
                                    </li>
                                </ol>
                                <ul v-else class="mt-3 space-y-2 text-sm leading-6 text-slate-600">
                                    <li v-for="item in topic.items" :key="item" class="flex gap-2">
                                        <CheckCircleIcon class="mt-1 h-4 w-4 shrink-0 text-emerald-500" />
                                        <span>{{ item }}</span>
                                    </li>
                                </ul>
                                <Link v-if="topic.path" :href="topic.path" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-blue-700 hover:text-blue-800">
                                    Mở chức năng <ArrowRightIcon class="h-4 w-4" />
                                </Link>
                            </section>
                        </div>
                    </article>
                </main>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, ref } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import { ArrowRightIcon, CheckCircleIcon, MagnifyingGlassIcon } from "@heroicons/vue/24/outline";
import AdminLayout from "@/Layouts/AdminLayout.vue";

const keyword = ref("");

const sections = [
    {
        id: "bat-dau", number: "01", title: "Bắt đầu sử dụng", description: "Cách di chuyển trong hệ thống và các nguyên tắc chung.",
        topics: [
            { title: "Điều hướng", items: ["Chọn phân hệ ở thanh trên cùng: Trang chủ, Mua hàng, Bán hàng, Kho hoặc Kế toán.", "Menu bên trái thay đổi theo phân hệ và quyền của tài khoản.", "Chuông thông báo hiển thị các phiếu, đơn và công việc cần xử lý."] },
            { title: "Trạng thái chứng từ", items: ["Chờ xử lý: có thể sửa hoặc gửi xử lý theo quyền.", "Đã duyệt: đã ghi nhận nghiệp vụ và thường không còn được sửa.", "Từ chối: không tác động số liệu; xem lý do để điều chỉnh và tạo lại khi cần."] },
        ],
    },
    {
        id: "mua-hang", number: "02", title: "Mua hàng", description: "Quản lý nhà cung cấp, sản phẩm mua và đơn mua hàng.",
        topics: [
            { title: "Tạo đơn mua", path: "/purchase/orders", steps: ["Khai báo nhà cung cấp và sản phẩm nếu chưa có.", "Vào Mua hàng → Đơn mua, chọn thêm mới.", "Chọn nhà cung cấp, tiền tệ, tỷ giá, sản phẩm, số lượng, đơn giá và VAT.", "Lưu đơn và duyệt theo quyền. Đơn được duyệt mới chuyển sang kho để lập phiếu nhập."] },
            { title: "Theo dõi nhà cung cấp", path: "/purchase/suppliers", items: ["Trang chi tiết hiển thị thông tin, đơn mua gần đây và lịch sử thanh toán.", "Mỗi khoản thanh toán thể hiện tài khoản chi, tiền mặt/chuyển khoản và đơn mua liên quan.", "Nhà cung cấp đã phát sinh dữ liệu nên khóa thay vì xóa."] },
        ],
    },
    {
        id: "ban-hang", number: "03", title: "Bán hàng", description: "Quản lý khách hàng, đơn bán và quá trình giao hàng.",
        topics: [
            { title: "Tạo đơn bán", path: "/sale/orders", steps: ["Khai báo khách hàng nếu chưa có.", "Vào Bán hàng → Đơn hàng và chọn thêm mới.", "Nhập sản phẩm, số lượng, giá bán, VAT, tiền tệ và địa chỉ giao.", "Duyệt đơn để kho có thể lập phiếu xuất; duyệt đơn chưa làm giảm tồn kho."] },
            { title: "Theo dõi khách hàng", path: "/sale/customers", items: ["Xem đơn bán gần đây và lịch sử thanh toán tại trang chi tiết.", "Khoản thu hiển thị tài khoản thu, phương thức thanh toán và đơn bán liên quan.", "Công nợ chỉ phát sinh theo lượng hàng thực xuất đã được kế toán duyệt."] },
        ],
    },
    {
        id: "kho", number: "04", title: "Kho hàng", description: "Nhập, xuất, chuyển kho và theo dõi biến động tồn.",
        topics: [
            { title: "Nhập hoặc xuất kho", path: "/warehouse/orders", steps: ["Mở danh sách đơn hàng đã duyệt và chọn đơn cần nhập/xuất.", "Chọn kho, số lượng thực hiện và tạo phiếu.", "Bộ phận kho kiểm tra rồi xác nhận gửi kế toán.", "Kế toán duyệt phiếu; chỉ lúc này hệ thống mới cập nhật tồn kho và công nợ."] },
            { title: "Quy tắc giá trị tồn", path: "/warehouse/inventory-movements", items: ["Giá trị nhập kho bao gồm VAT của lượng hàng thực nhập.", "Giá vốn xuất kho dùng giá nhập của đơn mua gần nhất, không tính bình quân.", "Không cho xuất âm kho; mọi thay đổi đều được ghi vào sổ biến động tồn.", "Chuyển kho không phát sinh công nợ."] },
        ],
    },
    {
        id: "ke-toan", number: "05", title: "Kế toán", description: "Tiền tệ, tài khoản, giao dịch, duyệt phiếu kho và công nợ.",
        topics: [
            { title: "Tiền tệ và tỷ giá", path: "/accountant/currencies", items: ["VND là tiền cơ sở, tỷ giá luôn bằng 1 và bị khóa chỉnh sửa.", "Ngoại tệ được phép thay đổi tỷ giá; mỗi lần thay đổi được lưu vào lịch sử theo ngày.", "Tỷ giá mới không làm thay đổi chứng từ đã lưu trước đó."] },
            { title: "Giao dịch thu, chi", path: "/accountant/transactions", steps: ["Chọn loại thu tiền, chi tiền hoặc chuyển nội bộ.", "Chọn tiền mặt/chuyển khoản và đúng tài khoản nguồn hoặc tài khoản nhận.", "Nếu thanh toán công nợ, chọn khách hàng/NCC và đơn mua/đơn bán liên quan.", "Sau khi duyệt, hệ thống cập nhật số dư, sổ giao dịch và công nợ."] },
            { title: "Duyệt phiếu kho", path: "/accountant/warehouse-slips", items: ["Kế toán không được thao tác khi phiếu còn chờ kho xác nhận.", "Sau khi kho xác nhận, kế toán nhận thông báo và có thể duyệt hoặc từ chối.", "Duyệt thành công mới chính thức nhập/xuất kho và ghi nhận công nợ."] },
            { title: "Lịch sử và báo cáo", path: "/accountant/account-ledgers", items: ["Lịch sử giao dịch thể hiện tài khoản, thu, chi, số dư sau giao dịch và đơn liên quan.", "Chi tiết giao dịch cho biết tài khoản nguồn/đích, phương thức, người duyệt và đơn hàng.", "Báo cáo lãi lỗ và công nợ tổng hợp theo dữ liệu đã được duyệt."] },
        ],
    },
    {
        id: "quan-tri", number: "06", title: "Quản trị hệ thống", description: "Nhân sự, quyền truy cập và lịch sử hoạt động.",
        topics: [
            { title: "Nhân sự và phân quyền", path: "/user", items: ["Nhân sự mới được kích hoạt ngay sau khi tạo, không có bước gửi phiếu hoặc duyệt nhân sự.", "Gán vai trò phù hợp; hệ thống không còn vai trò HR và Manager.", "Quyền quyết định màn hình và thao tác mà từng tài khoản được sử dụng."] },
            { title: "Nhật ký hoạt động", path: "/audit-logs", items: ["Theo dõi người thực hiện, thời gian và nội dung thay đổi dữ liệu.", "Dùng nhật ký để truy vết khi chứng từ hoặc dữ liệu bị thay đổi.", "Chỉ người có quyền xem nhật ký mới truy cập được mục này."] },
        ],
    },
];

const normalize = (value) => value.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
const filteredSections = computed(() => {
    const query = normalize(keyword.value);
    if (!query) return sections;
    return sections.filter((section) => normalize(JSON.stringify(section)).includes(query));
});

function scrollTo(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: "smooth", block: "start" });
}
</script>
