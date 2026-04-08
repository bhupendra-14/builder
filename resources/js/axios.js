import axios from 'axios';
import { useAuthStore } from './stores/auth';
import router from './router';

axios.defaults.withCredentials = true;
axios.defaults.baseURL = '/api';

axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

axios.interceptors.response.use(
    (response) => response,
    (error) => {
        const authStore = useAuthStore();
        
        if (error.response?.status === 401) {
            // Prevent infinite loop if /logout or /login also returns 401
            const skipPaths = ['/logout', '/api/logout', '/login', '/api/login'];
            const url = error.config?.url || '';
            
            if (skipPaths.some(path => url.includes(path))) {
                if (url.includes('logout')) {
                    authStore.clearAuth();
                }
                return Promise.reject(error);
            }

            authStore.logout();
            router.push({ name: 'login' });
        }

        if (error.response?.status === 403) {
            router.push({ name: 'dashboard' });
        }

        return Promise.reject(error);
    }
);

export default axios;
