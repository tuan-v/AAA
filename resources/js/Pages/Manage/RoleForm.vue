<template>
    <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
            <div><h2 class="text-xl font-bold text-slate-800">{{ role ? "Cập nhật vai trò" : "Thêm vai trò" }}</h2><p class="mt-1 text-sm text-slate-500">Thiết lập thông tin và phạm vi quyền cho vai trò.</p></div>
            <button
                @click="$emit('close')"
                type="button"
                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
            >
                <i class="ti ti-x text-xl"></i>
            </button>
        </div>

        <form novalidate @submit.prevent="save" class="flex flex-col flex-1 min-h-0">
            <!-- Body (scrollable) -->
            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1"
                        >Tên vai trò</label
                    >
                    <input
                        v-model="form.name"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    />
                    <p v-if="errors.name" class="text-red-500 text-xs mt-1">
                        {{ errors.name[0] }}
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Mô tả</label>
                    <input
                        v-model="form.description"
                        class="w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    />
                    <p
                        v-if="errors.description"
                        class="text-red-500 text-xs mt-1"
                    >
                        {{ errors.description[0] }}
                    </p>
                </div>

                <!-- Quyền -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium">Quyền</label>
                        <span class="text-xs text-gray-500">
                            Đã chọn {{ form.permissions.length }}/{{
                                permissions.length
                            }}
                        </span>
                    </div>

                    <!-- Search + select all tổng -->
                    <div class="flex items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <i
                                class="ti ti-search absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm"
                            ></i>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Tìm quyền..."
                                class="w-full rounded-xl border border-slate-300 py-2.5 pl-8 pr-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                            />
                        </div>
                        <button
                            type="button"
                            @click="toggleAll"
                            class="whitespace-nowrap rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            {{
                                isAllSelected ? "Bỏ chọn tất cả" : "Chọn tất cả"
                            }}
                        </button>
                    </div>

                    <p
                        v-if="errors.permissions"
                        class="text-red-500 text-xs mb-2"
                    >
                        {{ errors.permissions[0] }}
                    </p>

                    <!-- Danh sách nhóm -->
                    <div
                        class="max-h-[320px] divide-y overflow-y-auto rounded-xl border border-slate-200"
                    >
                        <div
                            v-for="group in filteredGroups"
                            :key="group.name"
                            class="p-3"
                        >
                            <div class="flex items-center justify-between mb-2">
                                <span
                                    class="text-sm font-semibold text-gray-700 capitalize"
                                >
                                    {{ group.label }}
                                </span>
                                <button
                                    type="button"
                                    @click="toggleGroup(group)"
                                    class="text-xs text-blue-600 hover:underline"
                                >
                                    {{
                                        isGroupSelected(group)
                                            ? "Bỏ chọn nhóm"
                                            : "Chọn nhóm"
                                    }}
                                </button>
                            </div>

                            <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
                                <label
                                    v-for="permission in group.items"
                                    :key="permission.id"
                                    class="flex items-center gap-2 text-sm cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="permission.name"
                                        v-model="form.permissions"
                                    />
                                    {{ permission.description || permission.name }}
                                </label>
                            </div>
                        </div>

                        <div
                            v-if="filteredGroups.length === 0"
                            class="p-4 text-sm text-gray-400 text-center"
                        >
                            Không tìm thấy quyền phù hợp
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                <button
                    type="button"
                    @click="$emit('close')"
                    class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100"
                >
                    Hủy
                </button>
                <button
                    :disabled="saving"
                    class="min-w-28 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ saving ? "Đang lưu..." : "Lưu thay đổi" }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { reactive, ref, computed, onMounted } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

const errors = ref({});
const saving = ref(false);
const search = ref("");

const props = defineProps({
    role: Object,
});

const emit = defineEmits(["saved", "close"]);

const permissions = ref([]);

const form = reactive({
    name: "",
    description: "",
    permissions: [],
});

// Nhóm quyền theo tiền tố trước dấu "." (vd: "users.view" -> nhóm "users")
// Nếu quyền không có dấu "." thì gom vào nhóm "Khác"
const groupedPermissions = computed(() => {
    const groups = {};

    for (const permission of permissions.value) {
        const [prefix] = permission.name.includes(".")
            ? permission.name.split(".")
            : ["Khác"];

        if (!groups[prefix]) {
            groups[prefix] = [];
        }

        groups[prefix].push(permission);
    }

    return Object.entries(groups).map(([name, items]) => ({
        name,
        label: moduleLabels[name] || name.replaceAll("_", " "),
        items,
    }));
});

const moduleLabels = {
    nhan_su: "Nhân sự", vai_tro: "Vai trò", quyen: "Quyền", nhat_ky: "Nhật ký hoạt động",
    tai_khoan: "Tài khoản", ngan_hang: "Ngân hàng", tien_te: "Tiền tệ",
    cong_no_khach_hang: "Công nợ khách hàng", cong_no_nha_cung_cap: "Công nợ nhà cung cấp",
    danh_muc_mua_hang: "Danh mục mua hàng", don_mua: "Đơn mua", san_pham_mua_hang: "Sản phẩm mua hàng",
    don_vi_mua_hang: "Đơn vị tính mua hàng", khach_hang: "Khách hàng", don_ban: "Đơn bán",
    nha_cung_cap: "Nhà cung cấp", giao_dich: "Giao dịch", loai_giao_dich: "Loại giao dịch",
    kho: "Kho", danh_muc_kho: "Danh mục kho", san_pham_kho: "Sản phẩm kho", phieu_kho: "Phiếu kho",
    chuyen_kho: "Chuyển kho", don_vi_kho: "Đơn vị tính kho",
};

function normalizeSearch(value) {
    return String(value || "")
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/đ/g, "d")
        .replace(/Đ/g, "D")
        .toLowerCase();
}

const filteredGroups = computed(() => {
    if (!search.value.trim()) {
        return groupedPermissions.value;
    }

    const keyword = normalizeSearch(search.value.trim());

    return groupedPermissions.value
        .map((group) => {
            const groupMatches = normalizeSearch(
                `${group.name} ${group.label}`,
            ).includes(keyword);

            return {
                ...group,
                items: groupMatches
                    ? group.items
                    : group.items.filter((permission) =>
                          normalizeSearch(
                              `${permission.name} ${permission.description || ""}`,
                          ).includes(keyword),
                      ),
            };
        })
        .filter((group) => group.items.length > 0);
});

const isAllSelected = computed(
    () =>
        permissions.value.length > 0 &&
        form.permissions.length === permissions.value.length,
);

function isGroupSelected(group) {
    return group.items.every((p) => form.permissions.includes(p.name));
}

function toggleGroup(group) {
    const groupNames = group.items.map((p) => p.name);

    if (isGroupSelected(group)) {
        form.permissions = form.permissions.filter(
            (name) => !groupNames.includes(name),
        );
    } else {
        const merged = new Set([...form.permissions, ...groupNames]);
        form.permissions = Array.from(merged);
    }
}

function toggleAll() {
    if (isAllSelected.value) {
        form.permissions = [];
    } else {
        form.permissions = permissions.value.map((p) => p.name);
    }
}

onMounted(async () => {
    const res = await axios.get("/api/permissions/all");

    permissions.value = res.data;

    if (props.role) {
        form.name = props.role.name;
        form.description = props.role.description;
        form.permissions = props.role.permissions.map((x) => x.name);
    }
});

async function save() {
    errors.value = {};
    saving.value = true;

    try {
        if (props.role?.id) {
            await axios.put(`/api/roles/${props.role.id}`, form);
        } else {
            await axios.post("/api/roles", form);
        }

        toast.success("Lưu vai trò thành công!");

        emit("saved");
        emit("close");
    } catch (error) {
        if (error.response && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            console.error(error);
        }
    } finally {
        saving.value = false;
    }
}
</script>
