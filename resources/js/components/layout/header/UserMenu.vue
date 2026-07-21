<template>
    <div class="relative" ref="dropdownRef">
        <!-- User Avatar Button -->
        <button
            @click.prevent="toggleDropdown"
            class="relative flex items-center gap-2 p-2 pr-3.5 rounded-xl transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-800 group border border-gray-200 dark:border-gray-700 min-w-[52px] xs:min-w-[60px] sm:min-w-[180px] md:min-w-[220px] lg:min-w-[240px] ml-1.5 sm:ml-1.5 md:ml-2 lg:ml-2"
            :class="{ 'bg-gray-50 dark:bg-gray-800 shadow-sm': dropdownOpen }"
        >
            <!-- Avatar + status dot -->
            <div class="relative flex-shrink-0">
                <div
                    class="w-8 h-8 rounded-full overflow-hidden border-2 border-white dark:border-gray-800 bg-gradient-to-br from-blue-100 to-purple-100"
                >
                    <img
                        :src="user?.avatar || defaultAvatar"
                        :alt="user?.name || 'User'"
                        class="w-full h-full object-cover"
                    />
                </div>
                <div
                    class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-green-500 rounded-full border-2 border-white dark:border-gray-800"
                ></div>
            </div>

            <!-- Tên user + vai trò - chỉ hiển thị từ sm trở lên -->
            <div class="hidden sm:flex flex-col items-start flex-1 min-w-0">
                <span
                    class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-tight truncate max-w-[140px] md:max-w-[160px] lg:max-w-[180px]"
                >
                    {{ user?.name || "User" }}
                </span>

                <span
                    class="hidden md:block text-xs text-gray-500 dark:text-gray-400 leading-tight truncate max-w-[140px] lg:max-w-[160px]"
                >
                    {{ roleLabel }}
                </span>
            </div>

            <!-- Icon mũi tên -->
            <ChevronDownIcon
                class="w-4 h-4 sm:w-5 sm:h-5 text-gray-500 dark:text-gray-400 transition-transform duration-200 group-hover:text-gray-700 dark:group-hover:text-gray-300 flex-shrink-0 ml-auto"
                :class="{ 'rotate-180': dropdownOpen }"
            />

            <!-- Badge thông báo -->
            <div
                v-if="notificationsCount > 0"
                class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-1 border-2 border-white dark:border-gray-900"
            >
                {{ notificationsCount > 99 ? "99+" : notificationsCount }}
            </div>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="dropdownOpen"
                class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-900 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
            >
                <!-- User Info Section -->
                <div
                    class="p-4 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800"
                >
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div
                                class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-100 dark:border-gray-800 bg-gradient-to-br from-blue-100 to-purple-100"
                            >
                                <img
                                    v-if="user?.avatar"
                                    :src="user.avatar"
                                    :alt="user.name"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-purple-500"
                                >
                                    <span
                                        class="text-base font-bold text-white"
                                        >{{
                                            getUserInitials(
                                                user?.name || "User",
                                            )
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3
                                class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"
                            >
                                {{ user?.name || "User Name" }}
                            </h3>
                            <p
                                class="text-sm text-gray-600 dark:text-gray-400 truncate mt-0.5"
                            >
                                {{ user?.email || "user@example.com" }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Menu Items - Chỉ giữ "Cài đặt" -->
                <div class="p-2">
                    <div class="space-y-0.5">
                        <a
                            href="/settings"
                            class="group w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        >
                            <div
                                class="w-7 h-7 rounded-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center group-hover:bg-gray-200 dark:group-hover:bg-gray-700 transition-colors"
                            >
                                <SettingsIcon
                                    class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400 group-hover:text-blue-500 transition-colors"
                                />
                            </div>
                            <span class="text-sm font-medium">Cài đặt</span>
                        </a>
                    </div>
                </div>

                <!-- Logout Button -->
                <div class="p-3 border-t border-gray-100 dark:border-gray-800">
                    <a
                        href="/logout"
                        @click="signOut"
                        class="group w-full flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                    >
                        <LogoutIcon class="w-3.5 h-3.5" />
                        <span class="text-sm font-semibold">Đăng xuất</span>
                    </a>
                </div>
            </div>
        </Transition>
    </div>

    <!-- Company Switch Modal -->
    <Teleport to="body">
        <div
            v-if="modalOpen"
            class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto"
        >
            <div
                class="fixed inset-0 bg-black/70 z-[99999]"
                @click="closeModal"
            ></div>

            <div class="relative z-[100000] w-full max-w-md mx-4 my-8">
                <div
                    class="bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700"
                >
                    <!-- Header -->
                    <div
                        class="sticky top-0 z-10 p-5 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h3
                                    class="text-lg font-bold text-gray-900 dark:text-gray-100"
                                >
                                    Chuyển đổi công ty quản lý
                                </h3>
                                <p
                                    class="text-sm text-gray-600 dark:text-gray-400 mt-1"
                                >
                                    Chọn công ty bạn muốn quản lý
                                </p>
                            </div>
                            <button
                                @click="closeModal"
                                class="p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                            >
                                <svg
                                    class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Company List -->
                    <div class="max-h-[60vh] overflow-y-auto asfy-modal-scroll">
                        <div
                            v-if="companies.length === 0"
                            class="py-8 text-center"
                        >
                            <div
                                class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                    />
                                </svg>
                            </div>
                            <p
                                class="text-gray-700 dark:text-gray-300 font-medium mb-1"
                            >
                                Không có công ty nào
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Thêm công ty để bắt đầu sử dụng
                            </p>
                        </div>

                        <div v-else class="p-4 space-y-2">
                            <div
                                v-for="company in companies"
                                :key="company.id"
                                @click="
                                    company.id !== user?.company_id &&
                                    viewCompany(company)
                                "
                                :class="[
                                    'group p-3 rounded-lg border cursor-pointer transition-all',
                                    company.id === user?.company_id
                                        ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800'
                                        : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600',
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <!-- Company Avatar -->
                                    <div
                                        :class="[
                                            'w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold text-sm',
                                            company.id === user?.company_id
                                                ? 'bg-gradient-to-br from-blue-500 to-blue-600'
                                                : 'bg-gradient-to-br from-gray-500 to-gray-600 group-hover:from-blue-500 group-hover:to-blue-600',
                                        ]"
                                    >
                                        {{ getCompanyInitials(company.name) }}
                                    </div>

                                    <!-- Company Info -->
                                    <div class="flex-1 min-w-0">
                                        <div
                                            class="flex items-center justify-between mb-1"
                                        >
                                            <h4
                                                class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate"
                                            >
                                                {{ company.name }}
                                            </h4>
                                            <span
                                                v-if="
                                                    company.id ===
                                                    user?.company_id
                                                "
                                                class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 px-2 py-0.5 rounded"
                                            >
                                                Đang sử dụng
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Arrow for non-active companies -->
                                    <svg
                                        v-if="company.id !== user?.company_id"
                                        class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors flex-shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5l7 7-7 7"
                                        />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        class="sticky bottom-0 p-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                Hiển thị
                                <span
                                    class="font-semibold text-gray-800 dark:text-gray-200"
                                    >{{ companies.length }}</span
                                >
                                công ty
                            </div>
                            <button
                                @click="closeModal"
                                class="px-4 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                            >
                                Đóng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Loading Modal - Clean and Simple -->
    <Teleport to="body">
        <div
            v-if="switchingCompany"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/60 backdrop-blur-sm"
        >
            <div class="flex flex-col items-center gap-8">
                <div class="relative w-40 h-40 md:w-48 md:h-48">
                    <div
                        class="absolute inset-0 rounded-full border-4 border-t-blue-500 border-r-blue-400 border-b-purple-500 border-l-cyan-400 animate-spin-slow"
                    ></div>
                    <div
                        class="absolute inset-6 rounded-full border-4 border-t-green-400 border-r-yellow-400 border-b-pink-500 border-l-blue-300 animate-spin-reverse"
                    ></div>
                    <div
                        class="absolute inset-0 flex items-center justify-center"
                    >
                        <img
                            src="/resource/asfy-images/asfy-logo.png"
                            alt="ASFY Logo"
                            class="w-24 h-24 md:w-32 md:h-32 object-contain drop-shadow-2xl"
                        />
                    </div>
                </div>

                <div class="text-center text-white">
                    <h3 class="text-2xl md:text-3xl font-bold">
                        Đang chuyển đổi công ty...
                    </h3>
                    <p class="mt-3 text-lg md:text-xl text-gray-200">
                        Vui lòng đợi trong giây lát
                    </p>
                    <p class="mt-4 text-base md:text-lg text-gray-300">
                        {{ targetCompany?.name || "Đang kết nối..." }}
                    </p>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ChevronDownIcon, LogoutIcon, SettingsIcon } from "@/icons";
import { usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed } from "vue";
import axios from "axios";

const page = usePage();
const user = computed(() => page.props.auth?.user);
const companies = computed(() => page.props.auth.companies || []);
const roleLabel = computed(() => {
    const roles = page.props.auths?.user?.roles || user.value?.roles || [];
    const names = roles.map((role) => role.name).filter(Boolean);

    return names.length ? names.join(", ") : "Chưa có vai trò";
});
const defaultAvatar =
    "https://ui-avatars.com/api/?name=" +
    encodeURIComponent(user.value?.name || "User") +
    "&background=465fff&color=fff";

const dropdownOpen = ref(false);
const dropdownRef = ref(null);
const modalOpen = ref(false);
const switchingCompany = ref(false);
const targetCompany = ref(null);
const notificationsCount = ref(0);
let loadingTimeout = null;

const fetchNotificationsCount = async () => {
    try {
        const response = await axios.get("/api/notifications/unread-count");
        const counts = response.data?.by_category || {};
        notificationsCount.value = Object.values(counts).reduce(
            (total, value) => total + Number(value || 0),
            0,
        );
    } catch {
        notificationsCount.value = 0;
    }
};

const handleNotificationReceived = () => fetchNotificationsCount();

const quickSwitchCompanies = computed(() => {
    return companies.value
        .filter((company) => company.id !== user.value?.company_id)
        .slice(0, 3);
});

const getCompanyInitials = (name) => {
    if (!name || name.trim() === "") return "CT";
    const words = name.trim().split(/\s+/);
    if (words.length >= 2)
        return (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
    return name.substring(0, 2).toUpperCase();
};

const getUserInitials = (name) => {
    if (!name || name.trim() === "") return "U";
    const words = name.trim().split(/\s+/);
    if (words.length >= 2)
        return (
            words[0].charAt(0) + words[words.length - 1].charAt(0)
        ).toUpperCase();
    return name.charAt(0).toUpperCase();
};

const getCurrentCompanyEmail = () => {
    // Tìm công ty hiện tại trong danh sách companies
    if (user.value?.company_id) {
        const currentCompany = companies.value.find(
            (c) => c.id === user.value.company_id,
        );
        if (currentCompany?.email) {
            return currentCompany.email;
        }
    }
    // Fallback về email từ user.company nếu có
    return user.value?.company?.email || "Không có email";
};

const openModal = () => {
    dropdownOpen.value = false;
    modalOpen.value = true;
};

const closeModal = () => {
    modalOpen.value = false;
};

const startCompanySwitch = (company) => {
    targetCompany.value = company;
    switchingCompany.value = true;

    closeModal();
    closeDropdown();

    loadingTimeout = setTimeout(() => {
        if (targetCompany.value) {
            router.get(route("change-company", targetCompany.value.id));
        }
        switchingCompany.value = false;
        targetCompany.value = null;
    }, 3200);
};

const viewCompany = (company) => startCompanySwitch(company);
const switchToCompany = (company) => startCompanySwitch(company);

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const closeDropdown = () => {
    dropdownOpen.value = false;
};

const signOut = () => {
    closeDropdown();
};

const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        closeDropdown();
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
    window.addEventListener("notification-received", handleNotificationReceived);
    fetchNotificationsCount();
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
    window.removeEventListener("notification-received", handleNotificationReceived);
    if (loadingTimeout) clearTimeout(loadingTimeout);
});
</script>

<style scoped>
/* Custom scrollbar */
.custom-scrollbar::-webkit-scrollbar,
.asfy-modal-scroll::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track,
.asfy-modal-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb,
.asfy-modal-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover,
.asfy-modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: rgba(0, 0, 0, 0.5);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb,
.dark .asfy-modal-scroll::-webkit-scrollbar-thumb {
    background-color: rgba(255, 255, 255, 0.2);
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover,
.dark .asfy-modal-scroll::-webkit-scrollbar-thumb:hover {
    background-color: rgba(255, 255, 255, 0.4);
}

/* Loading animations */
@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

.animate-spin-slow {
    animation: spin-slow 3s linear infinite;
}

@keyframes spin-reverse {
    from {
        transform: rotate(360deg);
    }

    to {
        transform: rotate(0deg);
    }
}

.animate-spin-reverse {
    animation: spin-reverse 4.8s linear infinite;
}
</style>
