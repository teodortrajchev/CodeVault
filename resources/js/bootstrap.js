import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 403) {
            window.dispatchEvent(new CustomEvent('forbidden'));
        }
        return Promise.reject(error);
    }
);
