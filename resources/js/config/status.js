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
