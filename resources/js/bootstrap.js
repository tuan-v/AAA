import axios from 'axios';
// cấu hình security cho axios
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// Cấu hình CSRF token cho axios
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found in meta tag');
}
window.axios = axios;

let csrfRefreshRequest = null;

async function refreshCsrfToken() {
    if (!csrfRefreshRequest) {
        csrfRefreshRequest = axios.get('/csrf-token', { headers: { 'X-CSRF-Refresh': '1' } })
            .then(({ data }) => {
                const freshToken = data?.token;
                if (!freshToken) throw new Error('Không thể làm mới CSRF token.');

                const meta = document.head.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', freshToken);
                axios.defaults.headers.common['X-CSRF-TOKEN'] = freshToken;
                return freshToken;
            })
            .finally(() => { csrfRefreshRequest = null; });
    }

    return csrfRefreshRequest;
}

axios.interceptors.response.use(
    response => response,
    async error => {
        const request = error.config;
        const isCsrfMismatch = error.response?.status === 419
            || error.response?.data?.message === 'CSRF token mismatch.';

        if (!isCsrfMismatch || !request || request.__csrfRetried || request.url === '/csrf-token') {
            return Promise.reject(error);
        }

        request.__csrfRetried = true;
        try {
            const freshToken = await refreshCsrfToken();
            request.headers = request.headers || {};
            request.headers['X-CSRF-TOKEN'] = freshToken;
            return axios(request);
        } catch (_) {
            return Promise.reject(error);
        }
    }
);

