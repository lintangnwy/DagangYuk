import { createRouter, createWebHistory } from 'vue-router'
import LandingPage   from '@/views/LandingPage.vue'
import LoginPage     from '@/views/LoginPage.vue'
import DashboardPage from '@/views/DashboardPage.vue'
import PosPage       from '@/views/PosPage.vue'
import ProductsPage  from '@/views/ProductsPage.vue'
import TenantsPage   from '@/views/TenantsPage.vue'
import UsersPage     from '@/views/UsersPage.vue'
import OrdersPage    from '@/views/OrdersPage.vue'
import StockPage     from '@/views/StockPage.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/',           name: 'landing',    component: LandingPage },
    { path: '/login',      name: 'login',      component: LoginPage },
    { path: '/dashboard',  name: 'dashboard',  component: DashboardPage, meta: { requiresAuth: true } },
    {
      path: '/pos',
      name: 'pos',
      component: PosPage,
      meta: { requiresAuth: true, denyRoles: ['super_admin'] },
    },
    {
      path: '/products',
      name: 'products',
      component: ProductsPage,
      meta: { requiresAuth: true, denyRoles: ['super_admin'] },
    },
    {
      path: '/categories',
      name: 'categories',
      component: ProductsPage,
      meta: { requiresAuth: true, denyRoles: ['super_admin'] },
    },
    {
      path: '/tenants',
      name: 'tenants',
      component: TenantsPage,
      meta: { requiresAuth: true, allowRoles: ['super_admin'] },
    },
    {
      path: '/stock',
      name: 'stock',
      component: StockPage,
      meta: { requiresAuth: true, denyRoles: ['super_admin'] },
    },
    { path: '/orders',     name: 'orders',     component: OrdersPage,    meta: { requiresAuth: true, denyRoles: ['super_admin'] } },
    { path: '/users',      name: 'users',      component: UsersPage,     meta: { requiresAuth: true } },
  ],
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!to.meta.requiresAuth) return
  if (!auth.isLoggedIn()) return { name: 'login' }
  if (!auth.user) await auth.fetchUser()

  const userRole = auth.user?.role

  // Cek denyRoles: jika role user termasuk dalam daftar yang dilarang, redirect ke dashboard
  const denyRoles = to.meta.denyRoles as string[] | undefined
  if (denyRoles && userRole && denyRoles.includes(userRole)) {
    return { name: 'dashboard' }
  }

  // Cek allowRoles: jika ditentukan, hanya role yang tercantum yang boleh akses
  const allowRoles = to.meta.allowRoles as string[] | undefined
  if (allowRoles && userRole && !allowRoles.includes(userRole)) {
    return { name: 'dashboard' }
  }
})

export default router
