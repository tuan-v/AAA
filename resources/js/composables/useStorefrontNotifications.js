import axios from "axios";
import { onUnmounted } from "vue";
import { storefrontToast } from "@/utils/storefrontToast";

export function useStorefrontNotifications(slug) {
    let timer = null;
    let loading = false;

    async function check() {
        if (loading) return;
        loading = true;
        try {
            const { data } = await axios.get(`/shop/${slug}/account/notifications`);
            for (const notification of data.notifications || []) {
                const message = [notification.title, notification.message].filter(Boolean).join(" — ");
                const type = notification.data?.toast_type;
                if (type === "error") storefrontToast.error(message);
                else if (type === "success") storefrontToast.success(message);
                else if (type === "info") storefrontToast.info(message);
                else storefrontToast.warning(message);
            }
            if ((data.notifications || []).length) {
                window.dispatchEvent(new CustomEvent("storefront-notifications-changed"));
            }
        } catch (error) {
            if (![401, 403].includes(error.response?.status)) {
                console.error("Không thể tải thông báo khách hàng.", error);
            }
        } finally {
            loading = false;
        }
    }

    function start() {
        check();
        if (!timer) timer = window.setInterval(check, 15000);
    }
    function stop() {
        if (timer) window.clearInterval(timer);
        timer = null;
    }

    onUnmounted(stop);
    return { start, stop, check };
}
