<template>
    <div
        :class="[
            'pagination-wrapper',
            theme === 'hungpv'
                ? 'pagination-hungpv'
                : 'border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]',
        ]"
    >
        <!-- Thông tin kết quả -->

        <div class="pagination-content">
            <!-- Controls bên trái -->
            <div class="controls-left">
                <div class="items-per-page">
                    <span class="label">Số dòng:</span>
                    <div class="select-wrapper">
                        <select
                            v-model="localItemsPerPage"
                            @change="handleItemsPerPageChange"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                            <option :value="100">100</option>
                        </select>
                        <svg
                            class="select-arrow"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <path
                                d="M6 9L12 15L18 9"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Jump to page -->
                <div class="jump-to-page">
                    <span class="info-text">Đang hiển thị</span>
                    <span class="highlight"
                        >{{ doingShow }} / {{ totalItems }}</span
                    >
                    <span class="info-text">kết quả</span>
                </div>
            </div>

            <!-- Pagination buttons -->
            <div class="pagination-buttons" v-if="showPagination">
                <!-- Previous button -->
                <button
                    class="nav-btn prev-btn"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                    title="Trang trước"
                >
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M15 19l-7-7 7-7"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>

                <!-- Page numbers -->
                <div class="page-numbers">
                    <!-- First page -->
                    <button
                        v-if="showFirstPage"
                        class="page-btn"
                        :class="{ active: currentPage === 1 }"
                        @click="goToPage(1)"
                    >
                        1
                    </button>

                    <!-- Start dots -->
                    <span v-if="showStartDots" class="page-dots">•••</span>

                    <!-- Middle pages -->
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        class="page-btn"
                        :class="{ active: currentPage === page }"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>

                    <!-- End dots -->
                    <span v-if="showEndDots" class="page-dots">•••</span>

                    <!-- Last page -->
                    <button
                        v-if="showLastPage"
                        class="page-btn"
                        :class="{ active: currentPage === totalPages }"
                        @click="goToPage(totalPages)"
                    >
                        {{ totalPages }}
                    </button>
                </div>

                <!-- Next button -->
                <button
                    class="nav-btn next-btn"
                    :disabled="currentPage === totalPages"
                    @click="goToPage(currentPage + 1)"
                    title="Trang sau"
                >
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M9 5l7 7-7 7"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>

            <!-- Page info (mobile) -->
            <div class="page-info-mobile">
                Trang <span class="current">{{ currentPage }}</span> /
                <span class="total">{{ totalPages }}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Pagination",
    props: {
        // DataTable props
        theme: {
            type: String,
            default: "default",
        },
        totalItems: {
            type: Number,
            required: true,
            default: 100,
        },
        itemsPerPage: {
            type: Number,
            default: 10,
        },
        currentPage: {
            type: Number,
            default: 1,
        },
        maxVisiblePages: {
            type: Number,
            default: 5,
        },
        doingShow: {
            type: Number,
            default: 0,
        },
    },
    data() {
        return {
            localItemsPerPage: this.itemsPerPage,
            jumpPage: this.currentPage,
        };
    },
    computed: {
        totalPages() {
            return Math.ceil(this.totalItems / this.localItemsPerPage);
        },
        startItem() {
            return (this.currentPage - 1) * this.localItemsPerPage + 1;
        },
        endItem() {
            const end = this.currentPage * this.localItemsPerPage;
            return end > this.totalItems ? this.totalItems : end;
        },
        visiblePages() {
            const pages = [];
            const half = Math.floor(this.maxVisiblePages / 2);

            let start = Math.max(2, this.currentPage - half);
            let end = Math.min(this.totalPages - 1, this.currentPage + half);

            if (this.currentPage <= half + 1) {
                end = Math.min(this.maxVisiblePages + 1, this.totalPages - 1);
            }
            if (this.currentPage >= this.totalPages - half) {
                start = Math.max(2, this.totalPages - this.maxVisiblePages);
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        },
        showFirstPage() {
            return this.totalPages > 1 && !this.visiblePages.includes(1);
        },
        showLastPage() {
            return (
                this.totalPages > 1 &&
                !this.visiblePages.includes(this.totalPages)
            );
        },
        showStartDots() {
            return this.visiblePages.length > 0 && this.visiblePages[0] > 2;
        },
        showEndDots() {
            return (
                this.visiblePages.length > 0 &&
                this.visiblePages[this.visiblePages.length - 1] <
                    this.totalPages - 1
            );
        },
        showPagination() {
            return this.totalPages > 1;
        },
    },
    methods: {
        goToPage(page) {
            if (
                page < 1 ||
                page > this.totalPages ||
                page === this.currentPage
            ) {
                return;
            }
            this.jumpPage = page;
            this.$emit("page-change", page);
        },
        jumpToPage() {
            let page = parseInt(this.jumpPage);
            if (!page || page < 1) page = 1;
            if (page > this.totalPages) page = this.totalPages;

            if (page !== this.currentPage) {
                this.goToPage(page);
            } else {
                this.jumpPage = this.currentPage;
            }
        },
        handleItemsPerPageChange() {
            this.$emit("page-change", 1);
            this.$emit("items-per-page-change", this.localItemsPerPage);
            this.jumpPage = 1;
        },
    },
    watch: {
        itemsPerPage(newVal) {
            this.localItemsPerPage = newVal;
        },
        currentPage(newVal) {
            this.jumpPage = newVal;
        },
    },
};
</script>

<style scoped>
.pagination-wrapper {
    border-radius: 1rem;
    padding: 10px 12px;
}

.pagination-hungpv {
    border: none !important;
    padding: 0 !important;
    background: transparent !important;
}

@media (max-width: 768px) {
    .pagination-hungpv {
        padding: 0 !important;
    }
}

.pagination-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #666;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
}

.dark .pagination-info {
    color: #999;
    border-bottom-color: #2d2d2d;
}

.info-text {
    color: #888;
    font-size: 13px;
}

.dark .info-text {
    color: #666;
}

.highlight {
    color: #4f46e5;
    font-weight: 700;
    font-size: 13px;
}

.dark .highlight {
    color: #3b82f6;
}

.pagination-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.controls-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.items-per-page,
.jump-to-page {
    display: flex;
    align-items: center;
    gap: 8px;
}

.label {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
}

.dark .label {
    color: #999;
}

.select-wrapper {
    position: relative;
    min-width: 80px;
}

select {
    width: 100%;
    padding: 8px 30px 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    background: white;
    color: #333;
    font-size: 13px;
    cursor: pointer;
    outline: none;
    appearance: none;
    transition: all 0.2s;
}

.dark select {
    background: #2a2a2a;
    border-color: #3d3d3d;
    color: #ddd;
}

select:hover {
    border-color: #c0c0c0;
}

.dark select:hover {
    border-color: #555;
}

select:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}

.dark select:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.select-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: #666;
    pointer-events: none;
}

.dark .select-arrow {
    color: #999;
}

.input-wrapper {
    position: relative;
    width: 120px;
}

.input-wrapper input {
    width: 100%;
    padding: 8px 40px 8px 12px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    color: #333;
    font-size: 13px;
    outline: none;
    transition: all 0.2s;
}

.dark .input-wrapper input {
    background: #2a2a2a;
    border-color: #3d3d3d;
    color: #ddd;
}

.input-wrapper input:hover {
    border-color: #c0c0c0;
}

.dark .input-wrapper input:hover {
    border-color: #555;
}

.input-wrapper input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.dark .input-wrapper input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.input-wrapper input::placeholder {
    color: #aaa;
}

.dark .input-wrapper input::placeholder {
    color: #666;
}

.jump-btn {
    position: absolute;
    right: 4px;
    top: 50%;
    transform: translateY(-50%);
    width: 28px;
    height: 28px;
    border-radius: 6px;
    background: transparent;
    color: #666;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.jump-btn:hover {
    background: rgba(37, 99, 235, 0.1);
    color: #2563eb;
}

.dark .jump-btn {
    color: #999;
}

.dark .jump-btn:hover {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.jump-btn svg {
    width: 16px;
    height: 16px;
}

.pagination-buttons {
    display: flex;
    align-items: center;
    gap: 4px;
}

.nav-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    background: white;
    color: #666;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.dark .nav-btn {
    background: #2a2a2a;
    border-color: #3d3d3d;
    color: #999;
}

.nav-btn:hover:not(:disabled) {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4f46e5;
}

.dark .nav-btn:hover:not(:disabled) {
    background: #3a3a3a;
    border-color: #555;
    color: #ddd;
}

.nav-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.nav-btn svg {
    width: 18px;
    height: 18px;
}

.page-numbers {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0 8px;
}

.page-btn {
    min-width: 36px;
    height: 36px;
    padding: 0 4px;
    border-radius: 8px;
    border: 1px solid transparent;
    background: transparent;
    color: #666;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.dark .page-btn {
    color: #999;
}

.page-btn:hover:not(.active) {
    background: #f5f5f5;
    color: #333;
}

.dark .page-btn:hover:not(.active) {
    background: #3a3a3a;
    color: #ddd;
}

.page-btn.active {
    background: #2563eb;
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.dark .page-btn.active {
    background: #3b82f6;
}

.page-dots {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #aaa;
    font-size: 12px;
    letter-spacing: 2px;
}

.dark .page-dots {
    color: #666;
}

.page-info-mobile {
    display: none;
    font-size: 13px;
    color: #666;
    align-items: center;
    gap: 4px;
}

.dark .page-info-mobile {
    color: #999;
}

.page-info-mobile .current {
    color: #2563eb;
    font-weight: 600;
}

.dark .page-info-mobile .current {
    color: #3b82f6;
}

.page-info-mobile .total {
    color: #333;
    font-weight: 500;
}

.dark .page-info-mobile .total {
    color: #ddd;
}

input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>
