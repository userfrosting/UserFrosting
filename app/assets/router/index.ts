import AccountRoutes from '@userfrosting/sprinkle-account/routes'
import AdminRoutes from '@userfrosting/sprinkle-admin/routes'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '',
            redirect: { name: 'home' },
            component: () => import('../layouts/LayoutPage.vue'),
            children: [
                {
                    path: '/',
                    name: 'home',
                    component: () => import('../views/HomeView.vue')
                },
                {
                    path: '/about',
                    name: 'about',
                    meta: {
                        title: 'ABOUT'
                    },
                    component: () => import('../views/AboutView.vue')
                },
                // Include sprinkles routes
                ...AccountRoutes
            ]
        },
        {
            path: '/admin',
            component: () => import('../layouts/LayoutDashboard.vue'),
            children: [...AdminRoutes],
            meta: {
                title: 'ADMIN_PANEL'
            }
        }
    ]
})

export default router
