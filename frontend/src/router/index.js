import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { auth: true },
    children: [
      {
        path: '',
        name: 'Workspaces',
        component: () => import('@/views/WorkspacesView.vue'),
      },
      {
        path: '/workspace/:workspaceId',
        name: 'WorkspaceBoards',
        component: () => import('@/views/WorkspaceBoardsView.vue'),
        props: true,
      },
      {
        path: '/workspace/:workspaceId/board/:boardId',
        name: 'Board',
        component: () => import('@/views/BoardView.vue'),
        props: true,
      },
      {
        path: '/workspace/:workspaceId/dashboard',
        name: 'WorkspaceDashboard',
        component: () => import('@/views/DashboardView.vue'),
        props: true,
      },
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue'),
      },
    ],
  },
  {
    path: '/invitations/:token',
    name: 'AcceptInvitation',
    component: () => import('@/views/AcceptInvitationView.vue'),
    meta: { auth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.auth && !authStore.isAuthenticated) {
    next({ name: 'Login' })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'Workspaces' })
  } else {
    next()
  }
})

export default router
