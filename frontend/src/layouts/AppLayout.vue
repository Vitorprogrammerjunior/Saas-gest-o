<template>
  <div class="app-layout">
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" :class="{ visible: sidebarOpen }" @click="sidebarOpen = false"></div>

    <!-- Mobile Toggle -->
    <button class="mobile-menu-btn" @click="sidebarOpen = !sidebarOpen">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
        <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
      </svg>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" :class="{ open: sidebarOpen }">
      <div class="sidebar-header">
        <router-link to="/" class="logo">
          <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 11 12 14 22 4"></polyline>
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
          </div>
          <span class="logo-text">TaskFlow</span>
        </router-link>
      </div>

      <nav class="sidebar-nav">
        <div class="nav-section-title">Menu</div>

        <router-link to="/" class="nav-item" exact-active-class="active">
          <span class="nav-icon-wrap">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
          </span>
          <span>Workspaces</span>
        </router-link>

        <template v-if="currentWorkspaceId">
          <div class="nav-divider"></div>
          <div class="nav-section-title">Workspace</div>

          <router-link
            :to="`/workspace/${currentWorkspaceId}`"
            class="nav-item"
            active-class="active"
          >
            <span class="nav-icon-wrap">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><line x1="2" y1="10" x2="22" y2="10"/>
              </svg>
            </span>
            <span>Boards</span>
          </router-link>

          <router-link
            :to="`/workspace/${currentWorkspaceId}/dashboard`"
            class="nav-item"
            active-class="active"
          >
            <span class="nav-icon-wrap">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>
              </svg>
            </span>
            <span>Dashboard</span>
          </router-link>
        </template>
      </nav>

      <div class="sidebar-footer">
        <NotificationBell />
        <div class="user-card">
          <div class="avatar" :style="{ background: getUserColor(auth.user?.name) }">
            {{ getInitials(auth.user?.name) }}
          </div>
          <div class="user-details">
            <div class="user-name">{{ auth.user?.name }}</div>
            <div class="user-email">{{ auth.user?.email }}</div>
          </div>
          <button class="logout-btn" @click="handleLogout" title="Sair">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <router-view v-slot="{ Component }">
        <Transition name="page-fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </router-view>
    </main>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/NotificationBell.vue'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const sidebarOpen = ref(false)

const currentWorkspaceId = computed(() => route.params.workspaceId)

// Close sidebar on route change (mobile)
watch(() => route.path, () => { sidebarOpen.value = false })

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function getUserColor(name) {
  if (!name) return '#94a3b8'
  const colors = ['#6366f1', '#ec4899', '#f59e0b', '#22c55e', '#3b82f6', '#8b5cf6', '#ef4444']
  const index = name.charCodeAt(0) % colors.length
  return colors[index]
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.app-layout {
  display: flex;
  min-height: 100vh;
}

/* ─── Sidebar ─── */
.sidebar {
  width: var(--sidebar-width);
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  z-index: 100;
  border-right: 1px solid rgba(255, 255, 255, 0.06);
}

.sidebar-header {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: inherit;
}

.logo-icon {
  width: 40px;
  height: 40px;
  background: var(--gradient-primary);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3);
  transition: all 0.3s ease;
}
.logo:hover .logo-icon {
  box-shadow: 0 4px 24px rgba(99, 102, 241, 0.5);
  transform: scale(1.05);
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 900;
  background: linear-gradient(135deg, #fff, rgba(255,255,255,0.7));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: -0.02em;
}

/* ─── Navigation ─── */
.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 14px;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 500;
  font-size: 0.9375rem;
  transition: all 0.2s ease;
  margin-bottom: 2px;
  text-decoration: none;
  position: relative;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: rgba(255, 255, 255, 0.9);
}

.nav-item.active {
  background: rgba(99, 102, 241, 0.15);
  color: var(--primary-300);
  font-weight: 600;
}

.nav-item.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 20px;
  background: var(--primary-400);
  border-radius: 0 3px 3px 0;
}

.nav-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  flex-shrink: 0;
  opacity: 0.7;
}
.nav-item.active .nav-icon-wrap { opacity: 1; }
.nav-item:hover .nav-icon-wrap { opacity: 1; }

.nav-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.06);
  margin: 14px 8px;
}

.nav-section-title {
  font-size: 0.6875rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: rgba(255, 255, 255, 0.25);
  font-weight: 700;
  padding: 0 14px;
  margin-bottom: 6px;
}

/* ─── Footer ─── */
.sidebar-footer {
  padding: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.user-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px;
  border-radius: 12px;
  transition: background 0.2s;
}
.user-card:hover { background: rgba(255, 255, 255, 0.04); }

.user-card .avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8125rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  font-weight: 600;
  font-size: 0.8125rem;
  color: rgba(255, 255, 255, 0.85);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 0.6875rem;
  color: rgba(255, 255, 255, 0.35);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.logout-btn {
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.3);
  padding: 6px;
  border-radius: 8px;
  display: flex;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}
.logout-btn:hover {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
}

/* ─── Main Content ─── */
.main-content {
  flex: 1;
  margin-left: var(--sidebar-width);
  height: 100vh;
  overflow-x: hidden;
  overflow-y: auto;
  background: var(--gray-50);
}

/* ─── Page Transition ─── */
.page-fade-enter-active {
  transition: all 0.3s ease;
}
.page-fade-leave-active {
  transition: all 0.2s ease;
}
.page-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

/* ─── Mobile Menu Button ─── */
.mobile-menu-btn {
  display: none;
  position: fixed;
  top: 14px;
  left: 14px;
  z-index: 200;
  width: 42px;
  height: 42px;
  border-radius: 12px;
  border: none;
  background: var(--gray-800);
  color: white;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: all 0.2s;
}
.mobile-menu-btn:hover {
  background: var(--gray-700);
  transform: scale(1.05);
}

/* ─── Sidebar Overlay ─── */
.sidebar-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.5);
  z-index: 99;
  opacity: 0;
  transition: opacity 0.3s;
  backdrop-filter: blur(4px);
}
.sidebar-overlay.visible {
  opacity: 1;
}

/* ─── Mobile Responsive ─── */
@media (max-width: 768px) {
  .mobile-menu-btn { display: flex; }
  .sidebar-overlay { display: block; pointer-events: none; }
  .sidebar-overlay.visible { pointer-events: auto; }

  .sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .sidebar.open {
    transform: translateX(0);
  }

  .main-content {
    margin-left: 0 !important;
  }
}
</style>
