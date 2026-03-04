<template>
  <div class="notification-bell" ref="bellRef">
    <button class="bell-btn" @click="toggleOpen" :class="{ active: open }" title="Notificações">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <span v-if="notifications.pendingCount > 0" class="badge">
        {{ notifications.pendingCount > 9 ? '9+' : notifications.pendingCount }}
      </span>
    </button>

    <Transition name="dropdown">
      <div v-if="open" class="notification-dropdown">
        <div class="dropdown-header">
          <span class="dropdown-title">Convites Pendentes</span>
          <span class="dropdown-count">{{ notifications.pendingCount }}</span>
        </div>

        <div v-if="notifications.loading" class="dropdown-empty">
          <div class="mini-spinner"></div>
        </div>

        <div v-else-if="notifications.invitations.length === 0" class="dropdown-empty">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <p>Nenhum convite pendente</p>
        </div>

        <ul v-else class="invitation-list">
          <li v-for="inv in notifications.invitations" :key="inv.id" class="invitation-item">
            <div class="inv-workspace-icon">
              {{ inv.workspace?.name?.charAt(0)?.toUpperCase() }}
            </div>
            <div class="inv-details">
              <p class="inv-workspace">{{ inv.workspace?.name }}</p>
              <p class="inv-from">Convidado por <strong>{{ inv.inviter?.name }}</strong></p>
              <p class="inv-role">Papel: {{ inv.role === 'admin' ? 'Administrador' : 'Membro' }}</p>
            </div>
            <div class="inv-actions">
              <button
                class="inv-btn accept"
                :disabled="actionLoading === inv.token"
                @click="handleAccept(inv.token)"
                title="Aceitar"
              >
                <svg v-if="actionLoading === inv.token" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin">
                  <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
                <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </button>
              <button
                class="inv-btn decline"
                :disabled="actionLoading === inv.token"
                @click="handleDecline(inv.token)"
                title="Recusar"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
          </li>
        </ul>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useNotificationsStore } from '@/stores/notifications'
import { useWorkspaceStore } from '@/stores/workspace'
import { useRouter } from 'vue-router'

const notifications = useNotificationsStore()
const workspaceStore = useWorkspaceStore()
const router = useRouter()

const open = ref(false)
const bellRef = ref(null)
const actionLoading = ref(null)

onMounted(() => {
  notifications.fetchInvitations()
  // Poll every 30 seconds
  const interval = setInterval(() => notifications.fetchInvitations(), 30000)
  onUnmounted(() => clearInterval(interval))

  // Close on outside click
  document.addEventListener('click', onOutsideClick)
  onUnmounted(() => document.removeEventListener('click', onOutsideClick))
})

function toggleOpen() {
  open.value = !open.value
  if (open.value) notifications.fetchInvitations()
}

function onOutsideClick(e) {
  if (bellRef.value && !bellRef.value.contains(e.target)) {
    open.value = false
  }
}

async function handleAccept(token) {
  actionLoading.value = token
  try {
    const data = await notifications.acceptInvitation(token)
    // Refresh workspace list so the new workspace appears
    await workspaceStore.fetchWorkspaces()
    open.value = false
    // Navigate to the accepted workspace if available
    if (data.workspace?.id) {
      router.push(`/workspace/${data.workspace.id}`)
    }
  } finally {
    actionLoading.value = null
  }
}

async function handleDecline(token) {
  actionLoading.value = token
  try {
    await notifications.declineInvitation(token)
  } finally {
    actionLoading.value = null
  }
}
</script>

<style scoped>
.notification-bell {
  position: relative;
}

.bell-btn {
  position: relative;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: rgba(255, 255, 255, 0.6);
  transition: all 0.2s;
}

.bell-btn:hover,
.bell-btn.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.4);
  color: #818cf8;
}

.badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  min-width: 18px;
  height: 18px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 4px;
  border: 2px solid #1e1b4b;
}

/* Dropdown */
.notification-dropdown {
  position: absolute;
  bottom: calc(100% + 8px);
  left: 0;
  width: 320px;
  background: #1e1b4b;
  border: 1px solid rgba(99, 102, 241, 0.2);
  border-radius: 14px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  overflow: hidden;
  z-index: 1000;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.dropdown-title {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
}

.dropdown-count {
  background: rgba(99, 102, 241, 0.2);
  color: #818cf8;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 10px;
}

.dropdown-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  padding: 28px 16px;
  color: rgba(255, 255, 255, 0.3);
  font-size: 13px;
}

/* List */
.invitation-list {
  list-style: none;
  margin: 0;
  padding: 0;
  max-height: 360px;
  overflow-y: auto;
}

.invitation-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.04);
  transition: background 0.15s;
}

.invitation-item:last-child {
  border-bottom: none;
}

.invitation-item:hover {
  background: rgba(255, 255, 255, 0.03);
}

.inv-workspace-icon {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.inv-details {
  flex: 1;
  min-width: 0;
}

.inv-workspace {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin: 0 0 2px;
}

.inv-from,
.inv-role {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
  margin: 0;
}

.inv-from strong {
  color: rgba(255, 255, 255, 0.6);
}

.inv-actions {
  display: flex;
  gap: 6px;
  flex-shrink: 0;
}

.inv-btn {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  border: none;
}

.inv-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.inv-btn.accept {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
}

.inv-btn.accept:hover:not(:disabled) {
  background: rgba(34, 197, 94, 0.3);
}

.inv-btn.decline {
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
}

.inv-btn.decline:hover:not(:disabled) {
  background: rgba(239, 68, 68, 0.3);
}

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

.spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.mini-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(99, 102, 241, 0.2);
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
</style>
