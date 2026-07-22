import { reactive } from 'vue';

const state = reactive({
    visible: false,
    title: 'Xác nhận thao tác',
    message: 'Bạn có chắc chắn muốn thực hiện thao tác này?',
    confirmText: 'Xác nhận',
    cancelText: 'Quay lại',
    tone: 'warning',
    inputLabel: '',
    inputPlaceholder: '',
    inputRequired: false,
    inputValue: '',
    error: '',
});

let resolver = null;

function open(options = {}) {
    Object.assign(state, {
        visible: true,
        title: options.title || 'Xác nhận thao tác',
        message: options.message || 'Bạn có chắc chắn muốn thực hiện thao tác này?',
        confirmText: options.confirmText || 'Xác nhận',
        cancelText: options.cancelText || 'Quay lại',
        tone: options.tone || 'warning',
        inputLabel: options.inputLabel || '',
        inputPlaceholder: options.inputPlaceholder || '',
        inputRequired: Boolean(options.inputRequired),
        inputValue: options.inputValue || '',
        error: '',
    });

    return new Promise(resolve => { resolver = resolve; });
}

function accept() {
    if (state.inputRequired && !state.inputValue.trim()) {
        state.error = 'Vui lòng nhập nội dung trước khi xác nhận.';
        return;
    }
    state.visible = false;
    resolver?.(state.inputLabel ? state.inputValue.trim() : true);
    resolver = null;
}

function cancel() {
    state.visible = false;
    resolver?.(state.inputLabel ? null : false);
    resolver = null;
}

export function useActionConfirm() {
    return {
        confirmAction: open,
        promptAction: open,
    };
}

export const actionConfirmState = state;
export const acceptActionConfirm = accept;
export const cancelActionConfirm = cancel;
