import { onMounted, onUnmounted } from "vue";

export const useRealtimeRefresh = (refresh, delay = 300) => {
    let timer = null;

    const handleDataChanged = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(() => refresh(), delay);
    };

    onMounted(() => {
        window.addEventListener("company-data-changed", handleDataChanged);
    });

    onUnmounted(() => {
        window.clearTimeout(timer);
        window.removeEventListener("company-data-changed", handleDataChanged);
    });
};
