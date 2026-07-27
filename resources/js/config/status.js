export const STATUS_LABELS = {
    active: "Đang hoạt động",
    inactive: "Ngừng hoạt động",

    pending: "Chờ xử lý",
    approved: "Đã duyệt",
    rejected: "Đã từ chối",

    draft: "Nháp",

    completed: "Hoàn thành",
    partial: "Nhập một phần",
    cancelled: "Đã hủy",

    paid: "Đã thanh toán",
    unpaid: "Chưa thanh toán",
};
export function getStatusLabel(status) {
    return STATUS_LABELS[status] ?? status;
}

export const ORDER_STATUS_META = {
    pending: {
        label: "Chờ xử lý",
        class: "bg-yellow-100 text-yellow-700 border-yellow-200",
    },
    approved: {
        label: "Đã duyệt",
        class: "bg-blue-100 text-blue-700 border-blue-200",
    },
    partial: {
        label: "Đã duyệt",
        class: "bg-blue-100 text-blue-700 border-blue-200",
    },
    completed: {
        label: "Đã duyệt",
        class: "bg-blue-100 text-blue-700 border-blue-200",
    },
    cancelled: {
        label: "Đã hủy",
        class: "bg-red-100 text-red-700 border-red-200",
    },
};

export function getOrderStatusMeta(status) {
    return ORDER_STATUS_META[status] ?? {
        label: status || "Không rõ",
        class: "bg-gray-100 text-gray-700 border-gray-200",
    };
}
