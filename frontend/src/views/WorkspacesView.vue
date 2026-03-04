<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Workspaces</h1>
        <p class="page-subtitle">Gerencie seus espaços de trabalho</p>
      </div>
      <button class="btn btn-primary btn-lg" @click="showCreateModal = true">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Novo Workspace
      </button>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="store.loading" class="workspace-grid">
      <div v-for="n in 3" :key="n" class="workspace-card-skeleton">
        <div class="skel-header">
          <div class="skeleton skeleton-avatar" style="width:52px;height:52px;border-radius:14px;"></div>
          <div style="flex:1">
            <div class="skeleton skeleton-text" style="width:60%;height:16px;"></div>
            <div class="skeleton skeleton-text" style="width:85%;height:12px;margin-top:8px;"></div>
          </div>
        </div>
        <div class="skel-stats">
          <div class="skeleton" style="width:60px;height:36px;border-radius:8px;"></div>
          <div class="skeleton" style="width:60px;height:36px;border-radius:8px;"></div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="store.workspaces.length === 0" class="empty-state">
      <div class="empty-illustration">
        <svg width="120" height="120" viewBox="0 0 120 120" fill="none">
          <circle cx="60" cy="60" r="56" fill="var(--primary-50)" stroke="var(--primary-200)" stroke-width="2" stroke-dasharray="8 4"/>
          <rect x="35" y="38" width="50" height="44" rx="6" fill="white" stroke="var(--primary-300)" stroke-width="2"/>
          <rect x="42" y="50" width="20" height="3" rx="1.5" fill="var(--primary-200)"/>
          <rect x="42" y="57" width="32" height="3" rx="1.5" fill="var(--gray-200)"/>
          <rect x="42" y="64" width="26" height="3" rx="1.5" fill="var(--gray-200)"/>
          <circle cx="85" cy="75" r="14" fill="var(--primary-500)"/>
          <line x1="85" y1="70" x2="85" y2="80" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
          <line x1="80" y1="75" x2="90" y2="75" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
        </svg>
      </div>
      <h3>Nenhum workspace ainda</h3>
      <p>Crie seu primeiro workspace para começar a organizar suas tarefas</p>
      <button class="btn btn-primary btn-lg" @click="showCreateModal = true">Criar Workspace</button>
    </div>

    <!-- Workspace Grid -->
    <div v-else class="workspace-grid">
      <router-link
        v-for="(workspace, index) in store.workspaces"
        :key="workspace.id"
        :to="`/workspace/${workspace.id}`"
        class="workspace-card stagger-item"
        :style="{ animationDelay: `${index * 80}ms` }"
      >
        <div class="workspace-card-inner">
          <div class="workspace-card-bg" :style="{ background: getGradient(workspace.name) }"></div>
          <div class="workspace-card-content">
            <div class="workspace-card-header">
              <div class="workspace-icon" :style="{ background: getColor(workspace.name) }">
                {{ workspace.name[0] }}
              </div>
              <div class="workspace-info">
                <h3>{{ workspace.name }}</h3>
                <p>{{ workspace.description || 'Sem descrição' }}</p>
              </div>
              <div class="workspace-arrow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
              </div>
            </div>
            <div class="workspace-card-stats">
              <div class="ws-stat">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                <span class="ws-stat-value">{{ workspace.boards_count || 0 }}</span>
                <span class="ws-stat-label">boards</span>
              </div>
              <div class="ws-stat">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <span class="ws-stat-value">{{ workspace.members_count || 0 }}</span>
                <span class="ws-stat-label">membros</span>
              </div>
            </div>
          </div>
        </div>
      </router-link>
    </div>

    <!-- Create Modal -->
    <Transition name="modal">
      <div v-if="showCreateModal" class="modal-overlay" @click.self="showCreateModal = false">
        <div class="modal-content">
          <div class="modal-header">
            <h3>Novo Workspace</h3>
            <button class="btn btn-ghost btn-icon" @click="showCreateModal = false">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>
          <form @submit.prevent="handleCreate">
            <div class="modal-body">
              <div class="form-group">
                <label class="form-label">Nome</label>
                <input v-model="newWorkspace.name" type="text" class="form-input" placeholder="Ex: Minha Equipe" required autofocus />
              </div>
              <div class="form-group">
                <label class="form-label">Descrição (opcional)</label>
                <textarea v-model="newWorkspace.description" class="form-textarea" placeholder="Descreva o workspace..." rows="3"></textarea>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="creating">
                <span v-if="creating" class="spinner"></span>
                <span v-else>Criar Workspace</span>
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

const store = useWorkspaceStore()
const showCreateModal = ref(false)
const creating = ref(false)
const newWorkspace = reactive({ name: '', description: '' })

onMounted(() => store.fetchWorkspaces())

const gradients = [
  'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
  'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
  'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
  'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
  'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
  'linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)',
  'linear-gradient(135deg, #fccb90 0%, #d57eeb 100%)',
]

function getColor(name) {
  const colors = ['#6366f1', '#ec4899', '#f59e0b', '#22c55e', '#3b82f6', '#8b5cf6', '#0ea5e9']
  return colors[name.charCodeAt(0) % colors.length]
}

function getGradient(name) {
  return gradients[name.charCodeAt(0) % gradients.length]
}

async function handleCreate() {
  creating.value = true
  try {
    await store.createWorkspace(newWorkspace)
    showCreateModal.value = false
    newWorkspace.name = ''
    newWorkspace.description = ''
    await store.fetchWorkspaces()
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
.page { padding: 36px 40px; max-width: 1200px; animation: fadeInUp 0.5s ease; }

.page-title {
  font-size: 2rem;
  font-weight: 900;
  letter-spacing: -0.03em;
  background: linear-gradient(135deg, var(--gray-900), var(--gray-600));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
.page-subtitle { color: var(--gray-500); font-size: 0.9375rem; margin-top: 4px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 36px; }

/* ─── Grid ─── */
.workspace-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 24px;
}

@media (max-width: 480px) {
  .workspace-grid {
    grid-template-columns: 1fr;
  }
}

/* ─── Card ─── */
.workspace-card {
  text-decoration: none;
  color: inherit;
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.workspace-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.15);
}

.workspace-card-inner {
  position: relative;
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 20px;
  overflow: hidden;
}

.workspace-card-bg {
  height: 6px;
  transition: height 0.3s ease;
}
.workspace-card:hover .workspace-card-bg { height: 8px; }

.workspace-card-content { padding: 24px; }

.workspace-card-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}

.workspace-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 1.375rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: transform 0.3s ease;
}
.workspace-card:hover .workspace-icon { transform: scale(1.05); }

.workspace-info { flex: 1; min-width: 0; }
.workspace-info h3 { font-size: 1.125rem; font-weight: 700; margin-bottom: 2px; }
.workspace-info p { font-size: 0.8125rem; color: var(--gray-500); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.workspace-arrow {
  color: var(--gray-300);
  transition: all 0.3s ease;
  flex-shrink: 0;
}
.workspace-card:hover .workspace-arrow {
  color: var(--primary-500);
  transform: translateX(4px);
}

/* ─── Stats ─── */
.workspace-card-stats {
  display: flex;
  gap: 24px;
  padding-top: 16px;
  border-top: 1px solid var(--gray-100);
}

.ws-stat {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--gray-500);
}
.ws-stat svg { flex-shrink: 0; }
.ws-stat-value { font-weight: 700; font-size: 1rem; color: var(--gray-700); }
.ws-stat-label { font-size: 0.75rem; }

/* ─── Skeleton ─── */
.workspace-card-skeleton {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 20px;
  padding: 24px;
  animation: pulse 1.5s ease infinite alternate;
}
.skel-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
.skel-stats { display: flex; gap: 16px; padding-top: 16px; border-top: 1px solid var(--gray-100); }
@keyframes pulse { from { opacity: 1; } to { opacity: 0.6; } }

/* ─── Empty State ─── */
.empty-illustration { margin-bottom: 24px; animation: float 3s ease-in-out infinite; }
@keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

/* ─── Modal Transition ─── */
.modal-enter-active { transition: opacity 0.25s ease; }
.modal-enter-active .modal-content { animation: modalSlideIn 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-leave-active { transition: opacity 0.2s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

@keyframes modalSlideIn {
  from { opacity: 0; transform: scale(0.9) translateY(20px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes fadeInUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
</style>
