<template>
    <aside
        :class="[
            'fixed left-0 top-0 z-999 mt-16 flex h-screen flex-col border-r border-slate-200/80 bg-white px-4 text-slate-900 shadow-[8px_0_30px_rgba(15,23,42,0.025)] transition-all duration-300 ease-in-out dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 lg:mt-0',
            {
                'lg:w-[290px]': isExpanded || isMobileOpen || isHovered,
                'lg:w-[90px]': !isExpanded && !isHovered,
                'translate-x-0 w-[290px]': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded && (isHovered = true)"
        @mouseleave="isHovered = false"
    >
        <div
            :class="[
                'flex border-b border-slate-100 py-6 dark:border-gray-800',
                !isExpanded && !isHovered
                    ? 'lg:justify-center'
                    : 'justify-start',
            ]"
        >
            <Link href="/dashboard" class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    <!-- LOGO -->
                    <img
                        v-if="
                            user?.company?.logo &&
                            (isExpanded || isHovered || isMobileOpen)
                        "
                        :src="user.company.logo"
                            class="h-10 w-10 rounded-xl border border-slate-200 object-cover shadow-sm"
                    />

                    <!-- fallback logo -->
                    <img
                        v-else-if="isExpanded || isHovered || isMobileOpen"
                        src="/resource/asfy-images/asfy-logo.png"
                        class="h-9"
                    />

                    <!-- mini mode -->
                    <img
                        v-else
                        src="/resource/asfy-images/asfy-logo.png"
                        width="32"
                        height="32"
                    />

                    <!-- COMPANY NAME -->
                    <span
                        v-if="isExpanded || isHovered || isMobileOpen"
                        class="max-w-[180px] truncate text-sm font-bold text-slate-800 dark:text-gray-200"
                    >
                        {{ user?.company?.name }}
                    </span>
                </div>
            </Link>
        </div>
        <div
            v-if="isExpanded || isHovered || isMobileOpen"
            class="mt-4 flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-500 dark:bg-gray-800 dark:text-gray-400"
        >
            <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span>

            <div class="text-bolder text-gray-400 mt-1">
                {{ moduleName }}
            </div>
        </div>
        <div
            class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar"
        >
            <nav class="mb-6">
                <div class="flex flex-col gap-4">
                    <div
                        v-for="(menuGroup, groupIndex) in menuGroups"
                        :key="groupIndex"
                    >
                        <h2
                            :class="[
                                'mb-4 text-xs uppercase flex leading-[20px] text-gray-400',
                                !isExpanded && !isHovered
                                    ? 'lg:justify-center'
                                    : 'justify-start',
                            ]"
                        >
                            <template
                                v-if="isExpanded || isHovered || isMobileOpen"
                            >
                                <!-- {{ menuGroup.title }} -->
                            </template>
                            <HorizontalDots v-else />
                        </h2>
                        <ul class="flex flex-col gap-2">
                            <template
                                v-for="(item, index) in menuGroup.items"
                                :key="item.name"
                            >
                                <!-- LABEL tên công ty của trang chủ =)) -->
                                <li
                                    v-if="item.name === 'Hành chính nhân sự'"
                                    class="menu-company-label"
                                    :class="{
                                        expanded:
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen,
                                        collapsed: !(
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        ),
                                    }"
                                >
                                    <div
                                        class="company-label-wrapper"
                                        v-if="
                                            isExpanded ||
                                            isHovered ||
                                            isMobileOpen
                                        "
                                    >
                                        <div class="company-label-content">
                                            <span class="company-label-text">{{
                                                user?.company?.name
                                            }}</span>
                                        </div>
                                    </div>
                                    <div v-else class="company-mini-view">
                                        <WarehouseIcon
                                            class="w-5 h-5 text-gray-400"
                                        />
                                    </div>
                                </li>

                                <!-- MENU ITEM -->
                                <li>
                                    <!-- MENU CHA CÓ SUBMENU -->
                                    <button
                                        v-if="item.subItems"
                                        @click="
                                            toggleSubmenu(groupIndex, index)
                                        "
                                        :class="[
                                            'menu-item group w-full',
                                            {
                                                'menu-item-active':
                                                    isSubmenuOpen(
                                                        groupIndex,
                                                        index,
                                                    ),
                                                'menu-item-inactive':
                                                    !isSubmenuOpen(
                                                        groupIndex,
                                                        index,
                                                    ),
                                            },
                                            !isExpanded && !isHovered
                                                ? 'lg:justify-center'
                                                : 'lg:justify-start',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                isSubmenuOpen(groupIndex, index)
                                                    ? 'menu-item-icon-active'
                                                    : 'menu-item-icon-inactive',
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getIconComponent(item.icon)
                                                "
                                            />
                                        </span>

                                        <span
                                            v-if="
                                                isExpanded ||
                                                isHovered ||
                                                isMobileOpen
                                            "
                                            class="menu-item-text"
                                        >
                                            {{ item.name }}
                                        </span>

                                        <ChevronDownIcon
                                            v-if="
                                                isExpanded ||
                                                isHovered ||
                                                isMobileOpen
                                            "
                                            :class="[
                                                'ml-auto w-5 h-5 transition-transform duration-200',
                                                {
                                                    'rotate-180 text-brand-500':
                                                        isSubmenuOpen(
                                                            groupIndex,
                                                            index,
                                                        ),
                                                },
                                            ]"
                                        />
                                    </button>

                                    <!-- MENU KHÔNG CÓ SUBMENU -->
                                    <Link
                                        v-else-if="item.path"
                                        :href="item.path"
                                        :class="[
                                            'menu-item group',
                                            {
                                                'menu-item-active': isActive(
                                                    item.path,
                                                    item.none_active || false,
                                                ),
                                                'menu-item-inactive': !isActive(
                                                    item.path,
                                                    item.none_active || false,
                                                ),
                                            },
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                isActive(
                                                    item.path,
                                                    item.none_active || false,
                                                )
                                                    ? 'menu-item-icon-active'
                                                    : 'menu-item-icon-inactive',
                                            ]"
                                        >
                                            <component
                                                :is="
                                                    getIconComponent(item.icon)
                                                "
                                            />
                                        </span>

                                        <span
                                            v-if="
                                                isExpanded ||
                                                isHovered ||
                                                isMobileOpen
                                            "
                                            class="menu-item-text"
                                        >
                                            {{ item.name }}
                                        </span>
                                    </Link>

                                    <!-- SUBMENU -->
                                    <transition
                                        @enter="startTransition"
                                        @after-enter="endTransition"
                                        @before-leave="startTransition"
                                        @after-leave="endTransition"
                                    >
                                        <div
                                            v-show="
                                                isSubmenuOpen(
                                                    groupIndex,
                                                    index,
                                                ) &&
                                                (isExpanded ||
                                                    isHovered ||
                                                    isMobileOpen)
                                            "
                                        >
                                            <ul class="mt-2 space-y-1 ml-9">
                                                <li
                                                    v-for="subItem in item.subItems"
                                                    :key="subItem.name"
                                                >
                                                    <Link
                                                        :href="subItem.path"
                                                        :class="[
                                                            'menu-dropdown-item',
                                                            {
                                                                'menu-dropdown-item-active':
                                                                    isActive(
                                                                        subItem.path,
                                                                        subItem.none_active ||
                                                                            false,
                                                                    ),
                                                                'menu-dropdown-item-inactive':
                                                                    !isActive(
                                                                        subItem.path,
                                                                        subItem.none_active ||
                                                                            false,
                                                                    ),
                                                            },
                                                        ]"
                                                    >
                                                        <span v-if="subItem.icon" class="h-5 w-5 shrink-0">
                                                            <component :is="getIconComponent(subItem.icon)" />
                                                        </span>
                                                        {{ subItem.name }}

                                                        <span
                                                            class="flex items-center gap-1 ml-auto"
                                                        >
                                                            <span
                                                                v-if="
                                                                    subItem.new
                                                                "
                                                                :class="[
                                                                    'menu-dropdown-badge',
                                                                    {
                                                                        'menu-dropdown-badge-active':
                                                                            isActive(
                                                                                subItem.path,
                                                                                subItem.none_active ||
                                                                                    false,
                                                                            ),
                                                                        'menu-dropdown-badge-inactive':
                                                                            !isActive(
                                                                                subItem.path,
                                                                                subItem.none_active ||
                                                                                    false,
                                                                            ),
                                                                    },
                                                                ]"
                                                            >
                                                                new
                                                            </span>

                                                            <span
                                                                v-if="
                                                                    subItem.pro
                                                                "
                                                                :class="[
                                                                    'menu-dropdown-badge',
                                                                    {
                                                                        'menu-dropdown-badge-active':
                                                                            isActive(
                                                                                subItem.path,
                                                                                subItem.none_active ||
                                                                                    false,
                                                                            ),
                                                                        'menu-dropdown-badge-inactive':
                                                                            !isActive(
                                                                                subItem.path,
                                                                                subItem.none_active ||
                                                                                    false,
                                                                            ),
                                                                    },
                                                                ]"
                                                            >
                                                                pro
                                                            </span>
                                                        </span>
                                                    </Link>
                                                </li>
                                            </ul>
                                        </div>
                                    </transition>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

import { ChevronDownIcon, HorizontalDots, WarehouseIcon } from "../../icons";
import { useSidebar } from "@/composables/useSidebar";
import { useModule } from "@/composables/useModule";

const { currentModule } = useModule();
const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();
const page = usePage();
const routeName = computed(() => page.props.routeName);

const moduleName = computed(() => {
    const url = page.url;

    if (url.includes("/warehouse")) return "Kho hàng";
    if (url.includes("/purchase")) return "Mua hàng";
    if (url.includes("/sale")) return "Bán hàng";
    if (url.includes("/address")) return "Địa chỉ";
    if (url.includes("/users")) return "Người dùng";
    if (url.includes("/roles")) return "Phân quyền";

    return "Hệ thống";
});
const user = computed(() => page.props.auth?.user);
const menus = computed(() => {
    return page.props.auth?.menuItems || [];
});
const menuGroups = computed(() => page.props.auth.menuItems || []);
const companies = computed(() => page.props.auth.companies || []);
const isActive = (path, noneActive = false) => {
    if (noneActive) {
        return false;
    }
    const currentUrl = page.url.split("?")[0];
    const pathClean = new URL(path, window.location.origin).pathname;
    return currentUrl === pathClean || currentUrl.startsWith(pathClean + "/");
};

const toggleSubmenu = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    openSubmenu.value = openSubmenu.value === key ? null : key;
};
const isAnySubmenuRouteActive = computed(() => {
    return menuGroups.value.some((group) =>
        group.items.some(
            (item) =>
                item.subItems &&
                item.subItems.some((subItem) =>
                    isActive(subItem.path, subItem.none_active || false),
                ),
        ),
    );
});
const iconModules = import.meta.glob("../../icons/*.vue", { eager: true });
const getIconComponent = (iconName) => {
    // Tìm module tương ứng với tên icon
    const modulePath = `../../icons/${iconName}.vue`;

    if (iconModules[modulePath]) {
        return iconModules[modulePath].default;
    }

    // Fallback nếu không tìm thấy icon
    console.warn(`Icon ${iconName} not found`);
    return null;
};

const isSubmenuOpen = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    return (
        openSubmenu.value === key ||
        (isAnySubmenuRouteActive.value &&
            menuGroups.value[groupIndex].items[itemIndex].subItems?.some(
                (subItem) => isActive(subItem.path),
            ))
    );
};

const startTransition = (el) => {
    el.style.height = "auto";
    const height = el.scrollHeight;
    el.style.height = "0px";
    el.offsetHeight; // force reflow
    el.style.height = height + "px";
};

const endTransition = (el) => {
    el.style.height = "";
};
const activeCompany = computed(() => {
    return companies.value.find((c) => c.is_active === 1);
});
</script>
<style>
.menu-company-label {
    position: relative;
    margin-top: 16px;
    margin-bottom: 4px;
}

.menu-company-label.expanded::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(209, 213, 219, 0.6) 20%,
        rgba(209, 213, 219, 0.8) 50%,
        rgba(209, 213, 219, 0.6) 80%,
        transparent 100%
    );
}

.company-label-wrapper {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0 8px;
    margin-top: 12px;
}

.company-label-content {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 12px;
}

.company-label-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 4px 8px;
    background-color: rgba(249, 250, 251, 0.7);
    border-radius: 4px;
}

.company-mini-view {
    display: flex;
    justify-content: center;
    padding: 12px 0 8px 0;
    position: relative;
}

.company-mini-view::before {
    content: "";
    position: absolute;
    top: 0;
    left: 8px;
    right: 8px;
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(209, 213, 219, 0.6) 20%,
        rgba(209, 213, 219, 0.8) 50%,
        rgba(209, 213, 219, 0.6) 80%,
        transparent 100%
    );
}
</style>
