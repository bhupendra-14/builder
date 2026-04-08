import { defineStore } from 'pinia';
import axios from '../axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(credentials) {
            await axios.get('/sanctum/csrf-cookie', { baseURL: '' });
            const response = await axios.post('/login', credentials);
            
            this.setAuth(response.data.data);
            return response;
        },
        async logout() {
            try {
                await axios.post('/logout');
            } catch (error) {
                // Ignore error on logout
            }
            this.clearAuth();
        },
        async fetchUser() {
            try {
                const response = await axios.get('/user');
                this.user = response.data.data;
            } catch (error) {
                this.clearAuth();
            }
        },
        setAuth(data) {
            this.user = data.user;
            this.token = data.token;
            localStorage.setItem('token', data.token);
        },
        clearAuth() {
            this.user = null;
            this.token = null;
            localStorage.removeItem('token');
        }
    }
});
