import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";

/**
 * Cấu trúc dữ liệu share từ Laravel qua Inertia:
 *
 * auth: {
 *   permissions: string[], // vd: ["users.view", "users.create", "roles.edit"]
 * }
 *
 * Composable này thay thế cho cách viết thủ công:
 *   const permissionss = usePage().props.auth?.permissions || [];
 *   const can = (permission) => permissionss.includes(permission);
 *
 * để dùng chung được ở nhiều trang/component mà không phải lặp lại.
 */

export function usePermission() {
    const page = usePage();

    const permissions = computed(() => page.props.auth?.permissions || []);

    /**
     * Kiểm tra có 1 quyền cụ thể không.
     * can("users.view")
     */
    function can(permission) {
        if (!permission) return false;
        return permissions.value.includes(permission);
    }

    /**
     * Có ÍT NHẤT 1 trong danh sách quyền.
     * canAny(["users.view", "users.edit"])
     */
    function canAny(list) {
        if (!list?.length) return false;
        return list.some((permission) => can(permission));
    }

    /**
     * Có ĐỦ TẤT CẢ quyền trong danh sách.
     * canAll(["users.view", "users.edit"])
     */
    function canAll(list) {
        if (!list?.length) return false;
        return list.every((permission) => can(permission));
    }

    return {
        permissions,
        can,
        canAny,
        canAll,
    };
}
