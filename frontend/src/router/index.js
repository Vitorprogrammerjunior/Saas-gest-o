import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { guest: true, title: 'Login' },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { guest: true, title: 'Criar Conta' },
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
        meta: { title: 'Meus Workspaces' },
      },
      {
        path: '/workspace/:workspaceId',
        name: 'WorkspaceBoards',
        component: () => import('@/views/WorkspaceBoardsView.vue'),
        props: true,
        meta: { title: 'Boards' },
      },
      {
        path: '/workspace/:workspaceId/board/:boardId',
        name: 'Board',
        component: () => import('@/views/BoardView.vue'),
        props: true,
        meta: { title: 'Board' },
      },
      {
        path: '/workspace/:workspaceId/dashboard',
        name: 'WorkspaceDashboard',
        component: () => import('@/views/DashboardView.vue'),
        props: true,
        meta: { title: 'Dashboard' },
      },
      {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('@/views/DashboardView.vue'),
        meta: { title: 'Dashboard' },
      },
    ],
  },
  {
    path: '/invitations/:token',
    name: 'AcceptInvitation',
    component: () => import('@/views/AcceptInvitationView.vue'),
    meta: { auth: true, title: 'Convite' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Set page title
  const title = to.meta.title || to.matched.slice().reverse().find(r => r.meta?.title)?.meta.title
  document.title = title ? `${title} | TaskFlow` : 'TaskFlow'

  if (to.meta.auth && !authStore.isAuthenticated) {
    next({ name: 'Login' })
  } else if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'Workspaces' })
  } else {
    next()
  }
})

export default router
