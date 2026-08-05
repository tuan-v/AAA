import { onMounted, onUnmounted } from "vue";

/**
 * Làm mới dữ liệu nền khi trang đang hiển thị, không tải lại trình duyệt.
 */
export function useAutoApiRefresh(refresh, interval = 20000, options = {}) {
    let timer = null;
    let running = false;

    async function run() {
        if (running || document.hidden) return;
        running = true;
        try {
            await refresh({ silent: true });
        } finally {
            running = false;
        }
    }

    function startTimer() {
        if (!timer) timer = window.setInterval(run, interval);
    }

    function handleVisibility() {
        if (document.hidden) return;
        run();
    }

    onMounted(() => {
        document.addEventListener("visibilitychange", handleVisibility);
        window.addEventListener("focus", run);
        startTimer();
        if (options.immediate) run();
    });

    onUnmounted(() => {
        if (timer) window.clearInterval(timer);
        document.removeEventListener("visibilitychange", handleVisibility);
        window.removeEventListener("focus", run);
    });

    return { refreshNow: run };
}
