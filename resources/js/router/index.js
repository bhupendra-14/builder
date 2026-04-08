import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../views/auth/LoginView.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../views/auth/ForgotPasswordView.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('../views/auth/ResetPasswordView.vue'),
        meta: { guestOnly: true }
    },
    {
        path: '/',
        name: 'public',
        component: () => import('../views/PublicView.vue'),
        meta: { guestOnly: false }
    },
    {
        path: '/preview',
        name: 'preview',
        component: () => import('../views/PreviewView.vue'),
        meta: { guestOnly: false }
    },
    {
        path: '/admin',
        component: () => import('../layouts/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('../views/DashboardView.vue'),
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('../views/ProfileView.vue'),
            },
            {
                path: 'media',
                name: 'media',
                component: () => import('../views/AssetManagerView.vue'),
            },
            {
                path: 'builder',
                name: 'builder',
                component: () => import('../views/PageBuilderView.vue'),
            },
            {
                path: 'builder/:id/edit',
                name: 'builder.edit',
                component: () => import('../views/BuilderWorkspace.vue'),
            },
            {
                path: 'publish',
                name: 'publish',
                component: () => import('../views/PublishView.vue'),
            },
            {
                path: 'users',
                name: 'users',
                component: () => import('../views/UsersView.vue'),
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('../views/SettingsView.vue'),
            },
            {
                path: 'audit',
                name: 'audit',
                component: () => import('../views/AuditView.vue'),
            }
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // Check local token first
    if (!authStore.user && localStorage.getItem('token')) {
        await authStore.fetchUser();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guestOnly && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
