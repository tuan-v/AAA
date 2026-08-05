import { toast } from "vue3-toastify";

function normalizeMessage(message, fallback) {
    if (Array.isArray(message)) message = message.filter(Boolean).join("\n");
    if (message && typeof message === "object") {
        message = message.message || message.text || "";
    }
    const normalized = String(message ?? "").trim();
    return normalized || fallback;
}

const options = {
    position: "top-right",
    theme: "light",
    autoClose: 3500,
};

export const storefrontToast = {
    success(message) {
        return toast.success(normalizeMessage(message, "Thao tác thành công."), options);
    },
    error(message) {
        return toast.error(normalizeMessage(message, "Có lỗi xảy ra. Vui lòng thử lại."), options);
    },
    warning(message) {
        return toast.warning(normalizeMessage(message, "Vui lòng kiểm tra lại thông tin."), options);
    },
    info(message) {
        return toast.info(normalizeMessage(message, "Thông tin đã được cập nhật."), options);
    },
};
