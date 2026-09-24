import { createRouter, createWebHistory } from 'vue-router'
import LandingPage   from '@/views/LandingPage.vue'
import LoginPage     from '@/views/LoginPage.vue'
import DashboardPage from '@/views/DashboardPage.vue'
import PosPage       from '@/views/PosPage.vue'
import ProductsPage  from '@/views/ProductsPage.vue'
import CategoriesPage from '@/views/CategoriesPage.vue'
import OrdersPage    from '@/views/OrdersPage.vue'
import BranchesPage from '@/views/BranchesPage.vue'
import WarehousePage from '@/views/WarehousePage.vue'
import TenantsPage   from '@/views/TenantsPage.vue'
import UsersPage     from '@/views/UsersPage.vue'
import StockPage     from '@/views/StockPage.vue'
import SettingsPage  from '@/views/SettingsPage.vue'
import ReportsPage   from '@/views/ReportsPage.vue'
import CashShiftsReportPage from '@/views/CashShiftsReportPage.vue'
import ActivityLogsPage from '@/views/ActivityLogsPage.vue'
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
      component: CategoriesPage,
      meta: { requiresAuth: true, denyRoles: ['super_admin'] },
    },
    {
      path: '/tenants',
      name: 'tenants',
      component: TenantsPage,
      meta: { requiresAuth: true, allowRoles: ['super_admin'] },
    },
    {
      path: '/activity-logs',
      name: 'activity-logs',
      component: ActivityLogsPage,
      meta: { requiresAuth: true, allowRoles: ['super_admin'] },
    },
    {
      path: '/stock',
      name: 'stock',
      component: StockPage,
      meta: { requiresAuth: true, denyRoles: ['super_admin'] },
    },
    { path: '/orders',     name: 'orders',     component: OrdersPage,    meta: { requiresAuth: true, denyRoles: ['super_admin'] } },
    { path: '/branches',   name: 'branches',   component: BranchesPage,  meta: { requiresAuth: true, allowRoles: ['admin'] } },
    { path: '/warehouses', name: 'warehouses', component: WarehousePage, meta: { requiresAuth: true, denyRoles: ['super_admin'] } },
    { path: '/reports',    name: 'reports',    component: ReportsPage,   meta: { requiresAuth: true, denyRoles: ['super_admin'] } },
    { path: '/reports/cash-shifts', name: 'cash-shifts-report', component: CashShiftsReportPage, meta: { requiresAuth: true, allowRoles: ['admin'] } },
    { path: '/users',      name: 'users',      component: UsersPage,     meta: { requiresAuth: true } },
    { path: '/settings',   name: 'settings',   component: SettingsPage,  meta: { requiresAuth: true, allowRoles: ['admin'] } },
  ],
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  
  if (!to.meta.requiresAuth) {
    next()
    return
  }
  
  if (!auth.isLoggedIn()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }
  
  if (!auth.user) {
    try {
      await auth.fetchUser()
      const userRole = (auth.user as { role?: string } | null)?.role
      
      const denyRoles = to.meta.denyRoles as string[] | undefined
      if (denyRoles && userRole && denyRoles.includes(userRole)) {
        next({ name: 'dashboard' })
        return
      }
      
      const allowRoles = to.meta.allowRoles as string[] | undefined
      if (allowRoles && userRole && !allowRoles.includes(userRole)) {
        next({ name: 'dashboard' })
        return
      }
      
      next()
      
    } catch (error) {
      console.error('Error fetching user:', error)
      next({ name: 'login' })
    }
    return
  }
  
  const userRole = auth.user?.role
  
  const denyRoles = to.meta.denyRoles as string[] | undefined
  if (denyRoles && userRole && denyRoles.includes(userRole)) {
    next({ name: 'dashboard' })
    return
  }
  
  const allowRoles = to.meta.allowRoles as string[] | undefined
  if (allowRoles && userRole && !allowRoles.includes(userRole)) {
    next({ name: 'dashboard' })
    return
  }
  
  next()
})

export default router