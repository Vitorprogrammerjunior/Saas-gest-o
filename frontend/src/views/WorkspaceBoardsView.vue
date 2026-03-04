<template>
  <div class="page">
    <div class="page-header">
      <div>
        <div class="breadcrumb">
          <router-link to="/" class="breadcrumb-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Workspaces
          </router-link>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" opacity="0.3"><polyline points="9 18 15 12 9 6"/></svg>
          <span class="breadcrumb-current">{{ workspace?.name }}</span>
        </div>
        <h1 class="page-title">{{ workspace?.name || 'Boards' }}</h1>
        <p class="page-subtitle">{{ workspace?.description || 'Gerencie os boards deste workspace' }}</p>
      </div>
      <div class="flex gap-3">
        <button class="btn btn-secondary" @click="showInviteModal = true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
          Convidar
        </button>
        <button class="btn btn-primary" @click="showCreateModal = true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Novo Board
        </button>
      </div>
    </div>

    <!-- Members Bar -->
    <div v-if="workspace?.members?.length" class="members-bar">
      <div class="members-avatars">
        <div
          v-for="member in workspace.members.slice(0, 6)"
          :key="member.id"
          class="avatar avatar-sm"
          :style="{ background: getUserColor(member.name) }"
          :title="member.name"
        >
          {{ member.name[0] }}
        </div>
        <div v-if="workspace.members.length > 6" class="avatar avatar-sm avatar-more">
          +{{ workspace.members.length - 6 }}
        </div>
      </div>
      <span class="members-count">{{ workspace.members.length }} membros</span>
    </div>

    <!-- Loading Skeletons -->
    <div v-if="boardStore.loading" class="boards-grid">
      <div v-for="n in 4" :key="n" class="board-skeleton">
        <div class="skeleton" style="height:8px;border-radius:8px 8px 0 0;"></div>
        <div style="padding:20px;">
          <div class="skeleton skeleton-text" style="width:60%;height:18px;"></div>
          <div class="skeleton skeleton-text" style="width:90%;height:12px;margin-top:10px;"></div>
          <div style="display:flex;gap:16px;margin-top:16px;">
            <div class="skeleton" style="width:70px;height:12px;border-radius:4px;"></div>
            <div class="skeleton" style="width:70px;height:12px;border-radius:4px;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="boardStore.boards.length === 0" class="empty-state">
      <div class="empty-illustration">
        <svg width="100" height="100" viewBox="0 0 100 100" fill="none">
          <rect x="10" y="20" width="24" height="60" rx="4" fill="var(--primary-100)" stroke="var(--primary-300)" stroke-width="1.5"/>
          <rect x="38" y="15" width="24" height="65" rx="4" fill="var(--info-light)" stroke="var(--info)" stroke-width="1.5" opacity="0.8"/>
          <rect x="66" y="25" width="24" height="55" rx="4" fill="var(--success-light)" stroke="var(--success)" stroke-width="1.5" opacity="0.6"/>
          <rect x="16" y="28" width="12" height="8" rx="2" fill="var(--primary-200)"/>
          <rect x="16" y="40" width="12" height="8" rx="2" fill="var(--primary-200)"/>
          <rect x="44" y="23" width="12" height="8" rx="2" fill="var(--info)" opacity="0.3"/>
          <rect x="44" y="35" width="12" height="8" rx="2" fill="var(--info)" opacity="0.3"/>
        </svg>
      </div>
      <h3>Nenhum board ainda</h3>
      <p>Crie seu primeiro board para começar a organizar as tarefas</p>
      <button class="btn btn-primary btn-lg" @click="showCreateModal = true">Criar Board</button>
    </div>

    <!-- Boards Grid -->
    <div v-else class="boards-grid">
      <router-link
        v-for="(board, index) in boardStore.boards"
        :key="board.id"
        :to="`/workspace/${workspaceId}/board/${board.id}`"
        class="board-card stagger-item"
        :style="{ animationDelay: `${index * 70}ms` }"
      >
        <div class="board-color-bar" :style="{ background: board.color || '#6366f1' }"></div>
        <div class="board-card-body">
          <div class="board-card-top">
            <h3>{{ board.name }}</h3>
            <div class="board-card-arrow">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
          </div>
          <p class="board-description">{{ board.description || 'Sem descrição' }}</p>
          <div class="board-card-stats">
            <div class="bstat">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
              {{ board.columns_count || 0 }} colunas
            </div>
            <div class="bstat">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
              {{ board.cards_count || 0 }} cards
            </div>
          </div>
        </div>
      </router-link>
    </div>

    <!-- Create Board Modal -->
    <Transition name="modal">
      <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Novo Board</h3>
            <button class="btn btn-ghost btn-icon" @click="showCreateModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="handleCreateBoard">
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label">Nome</label>
                <input v-model="newBoard.name" type="text" class="form-input" placeholder="Ex: Sprint 01" required autofocus />
              </div>
              <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea v-model="newBoard.description" class="form-textarea" placeholder="Descreva o board..." rows="3"></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Cor do Board</label>
                <div class="color-picker">
                  <button
                    v-for="color in colors"
                    :key="color"
                    type="button"
                    class="color-option"
                    :class="{ active: newBoard.color === color }"
                    :style="{ background: color }"
                    @click="newBoard.color = color"
                  >
                    <svg v-if="newBoard.color === color" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Cancelar</button>
              <button type="submit" class="btn btn-primary">Criar Board</button>
            </div>
          </form>
        </div>
      </div>
    </Transition>

    <!-- Invite Modal -->
    <Transition name="modal">
      <div v-if="showInviteModal" class="modal-overlay" @click.self="showInviteModal = false">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Convidar Membro</h3>
            <button class="btn btn-ghost btn-icon" @click="showInviteModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="handleInvite">
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label">Email</label>
                <input v-model="inviteEmail" type="email" class="form-input" placeholder="colega@email.com" required />
              </div>
              <div class="form-group">
                <label class="form-label">Papel</label>
                <select v-model="inviteRole" class="form-select">
                  <option value="member">Membro</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>
              <Transition name="fade">
                <div v-if="inviteMessage" class="invite-message" :class="inviteError ? 'error' : 'success'">
                  <svg v-if="!inviteError" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                  {{ inviteMessage }}
                </div>
              </Transition>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showInviteModal = false">Fechar</button>
              <button type="submit" class="btn btn-primary" :disabled="inviting">
                <span v-if="inviting" class="spinner"></span>
                <span v-else>Enviar Convite</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useWorkspaceStore } from '@/stores/workspace'
import { useBoardStore } from '@/stores/board'

const props = defineProps({ workspaceId: [String, Number] })

const workspaceStore = useWorkspaceStore()
const boardStore = useBoardStore()

const workspace = ref(null)
const showCreateModal = ref(false)
const showInviteModal = ref(false)
const inviteEmail = ref('')
const inviteRole = ref('member')
const inviting = ref(false)
const inviteMessage = ref('')
const inviteError = ref(false)

const colors = ['#6366f1', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444', '#ec4899', '#8b5cf6', '#0ea5e9']
const newBoard = reactive({ name: '', description: '', color: '#6366f1' })

onMounted(async () => {
  workspace.value = await workspaceStore.fetchWorkspace(props.workspaceId)
  await boardStore.fetchBoards(props.workspaceId)
})

function getUserColor(name) {
  const colors = ['#6366f1', '#ec4899', '#f59e0b', '#22c55e', '#3b82f6', '#8b5cf6']
  return colors[name.charCodeAt(0) % colors.length]
}

async function handleCreateBoard() {
  await boardStore.createBoard(props.workspaceId, newBoard)
  showCreateModal.value = false
  newBoard.name = ''
  newBoard.description = ''
  newBoard.color = '#6366f1'
  await boardStore.fetchBoards(props.workspaceId)
}

async function handleInvite() {
  inviting.value = true
  inviteMessage.value = ''
  try {
    const result = await workspaceStore.inviteMember(props.workspaceId, {
      email: inviteEmail.value,
      role: inviteRole.value,
    })
    inviteMessage.value = result.message || 'Convite enviado!'
    inviteError.value = false
    inviteEmail.value = ''
  } catch (e) {
    inviteMessage.value = e.response?.data?.message || 'Erro ao enviar convite'
    inviteError.value = true
  } finally {
    inviting.value = false
  }
}
</script>

<style scoped>
.page { padding: 36px 40px; max-width: 1200px; animation: fadeInUp 0.5s ease; }
.page-title { font-size: 2rem; font-weight: 900; letter-spacing: -0.03em; margin-bottom: 4px; }
.page-subtitle { color: var(--gray-500); font-size: 0.9375rem; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; }

/* ─── Breadcrumb ─── */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 12px;
}
.breadcrumb-link {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--gray-400);
  text-decoration: none;
  transition: color 0.2s;
}
.breadcrumb-link:hover { color: var(--primary-500); }
.breadcrumb-current { font-size: 0.8125rem; font-weight: 600; color: var(--gray-600); }

/* ─── Members ─── */
.members-bar {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 28px;
  padding: 14px 20px;
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 14px;
}
.members-avatars {
  display: flex;
}
.members-avatars .avatar {
  margin-left: -6px;
  border: 2.5px solid white;
  transition: all 0.2s ease;
}
.members-avatars .avatar:first-child { margin-left: 0; }
.members-avatars .avatar:hover { transform: scale(1.15) translateY(-2px); z-index: 1; }
.avatar-more { background: var(--gray-200) !important; color: var(--gray-600) !important; font-size: 0.625rem !important; font-weight: 700; }
.members-count { font-size: 0.8125rem; color: var(--gray-500); font-weight: 500; }

/* ─── Board Grid ─── */
.boards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

@media (max-width: 480px) {
  .boards-grid {
    grid-template-columns: 1fr;
  }
}

/* ─── Board Card ─── */
.board-card {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 16px;
  overflow: hidden;
  color: inherit;
  text-decoration: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.board-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 32px -8px rgba(0, 0, 0, 0.12);
  border-color: var(--gray-200);
}

.board-color-bar {
  height: 6px;
  transition: height 0.25s ease;
}
.board-card:hover .board-color-bar { height: 8px; }

.board-card-body { padding: 22px; }

.board-card-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 6px;
}
.board-card-top h3 { font-size: 1.0625rem; font-weight: 700; }

.board-card-arrow {
  color: var(--gray-300);
  transition: all 0.25s;
  flex-shrink: 0;
}
.board-card:hover .board-card-arrow { color: var(--primary-500); transform: translateX(3px); }

.board-description {
  font-size: 0.8125rem;
  color: var(--gray-500);
  margin-bottom: 16px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.board-card-stats { display: flex; gap: 20px; }
.bstat {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: var(--gray-500);
  font-weight: 500;
}

/* ─── Color Picker ─── */
.color-picker { display: flex; gap: 10px; flex-wrap: wrap; }
.color-option {
  width: 36px; height: 36px; border-radius: 50%;
  border: 3px solid transparent; cursor: pointer;
  transition: all 0.2s ease;
  display: flex; align-items: center; justify-content: center;
}
.color-option:hover { transform: scale(1.2); }
.color-option.active { border-color: var(--gray-800); transform: scale(1.2); box-shadow: 0 2px 8px rgba(0,0,0,0.2); }

/* ─── Skeleton ─── */
.board-skeleton {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 16px;
  overflow: hidden;
  animation: pulse 1.5s ease infinite alternate;
}
@keyframes pulse { from { opacity: 1; } to { opacity: 0.6; } }

/* ─── Invite Message ─── */
.invite-message {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
}
.invite-message.success { background: var(--success-light); color: #065f46; }
.invite-message.error { background: var(--danger-light); color: #991b1b; }

/* ─── Empty ─── */
.empty-illustration { margin-bottom: 20px; animation: float 3s ease-in-out infinite; }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

/* ─── Transitions ─── */
.modal-enter-active { transition: opacity 0.25s ease; }
.modal-enter-active .modal-content { animation: modalSlideIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }

@keyframes modalSlideIn { from { opacity: 0; transform: scale(0.9) translateY(20px); } to { opacity: 1; transform: scale(1) translateY(0); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
</style>
