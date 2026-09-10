import { createRouter, createWebHistory } from 'vue-router'
import LandingPage   from '@/views/LandingPage.vue'
import LoginPage     from '@/views/LoginPage.vue'
import DashboardPage from '@/views/DashboardPage.vue'
import PosPage       from '@/views/PosPage.vue'
import ProductsPage  from '@/views/ProductsPage.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/',           name: 'landing',    component: LandingPage },
    { path: '/login',      name: 'login',      component: LoginPage },
    { path: '/dashboard',  name: 'dashboard',  component: DashboardPage, meta: { requiresAuth: true } },
    { path: '/pos',        name: 'pos',        component: PosPage,       meta: { requiresAuth: true } },
    { path: '/products',   name: 'products',   component: ProductsPage,  meta: { requiresAuth: true } },
    // Alias categories ke ProductsPage (tab kategori)
    { path: '/categories', name: 'categories', component: ProductsPage,  meta: { requiresAuth: true } },
    // Placeholder routes untuk sidebar
    { path: '/orders',     name: 'orders',     component: DashboardPage, meta: { requiresAuth: true } },
    { path: '/users',      name: 'users',      component: DashboardPage, meta: { requiresAuth: true } },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!to.meta.requiresAuth) return
  if (!auth.isLoggedIn()) return { name: 'login' }
  if (!auth.user) await auth.fetchUser()
})

export default router
