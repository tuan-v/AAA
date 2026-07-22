<template>
    <ThemeProvider>
        <SidebarProvider>
            <AdminLayoutContent>
                <Head :title="title"> </Head>
                <slot></slot>
            </AdminLayoutContent>
            <ActionConfirmDialog />
        </SidebarProvider>
    </ThemeProvider>
</template>

<script setup>
import { defineComponent, h } from "vue";
import { defineProps, computed } from "vue";

import ThemeProvider from "@/components/layout/ThemeProvider.vue";
import SidebarProvider from "@/components/layout/SidebarProvider.vue";
import AppSidebar from "@/components/layout/AppSidebar.vue";
import AppHeader from "@/components/layout/AppHeader.vue";
import Backdrop from "@/components/layout/Backdrop.vue";
import ActionConfirmDialog from "@/components/ui/ActionConfirmDialog.vue";
import { useSidebar } from "@/composables/useSidebar";
import "../../css/main.css";
import { Head, usePage } from "@inertiajs/vue3";

const props = defineProps({
    title: {
        type: String,
        default: "",
    },
});
const page = usePage();
const title = computed(() => page.props.title || props.title || "");

const AdminLayoutContent = defineComponent({
    setup(props, { slots }) {
        const { isExpanded, isHovered } = useSidebar();

        return () =>
            h("div", { class: "app-shell min-h-screen bg-slate-50 dark:bg-gray-950" }, [
                h(AppSidebar),
                h(Backdrop),
                h(
                    "div",
                    {
                        class: [
                            "flex flex-col min-h-screen transition-all duration-300 ease-in-out",
                            isExpanded.value || isHovered.value
                                ? "lg:ml-[290px]"
                                : "lg:ml-[90px]",
                        ],
                    },
                    [
                        h(AppHeader),
                        h(
                            "div",
                            {
                                class: "app-page flex-1 w-full max-w-[1920px] mx-auto overflow-visible px-4 pb-10 pt-5 sm:px-5 md:px-7 md:pt-7 dark:bg-gray-950",
                            },
                            slots.default?.(),
                        ),
                    ],
                ),
            ]);
    },
});
</script>
