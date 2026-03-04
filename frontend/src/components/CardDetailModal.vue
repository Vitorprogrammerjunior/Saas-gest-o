<template>
  <Transition name="modal">
    <div class="modal-overlay" @click.self="$emit('close')">
      <div class="card-detail-modal" @click.stop>
        <!-- Header -->
        <div class="modal-header">
          <div class="flex items-center gap-3">
            <div class="modal-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
            </div>
            <div>
              <h3 class="modal-title">Detalhes do Card</h3>
              <p class="modal-subtitle">{{ card.title }}</p>
            </div>
          </div>
          <button class="close-btn" @click="$emit('close')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div class="modal-body">
          <!-- Title -->
          <div class="detail-section">
            <label class="detail-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Título
            </label>
            <input v-model="form.title" class="form-input" />
          </div>

          <!-- Description -->
          <div class="detail-section">
            <label class="detail-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
              Descrição
            </label>
            <textarea v-model="form.description" class="form-textarea" rows="4" placeholder="Adicionar descrição..."></textarea>
          </div>

          <div class="detail-grid">
            <!-- Priority -->
            <div class="detail-section">
              <label class="detail-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                Prioridade
              </label>
              <div class="select-wrap">
                <select v-model="form.priority" class="form-select">
                  <option value="low">Baixa</option>
                  <option value="medium">Média</option>
                  <option value="high">Alta</option>
                  <option value="urgent">Urgente</option>
                </select>
                <div class="priority-dot" :class="`dot-${form.priority}`"></div>
              </div>
            </div>

            <!-- Due Date -->
            <div class="detail-section">
              <label class="detail-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Data de Entrega
              </label>
              <input v-model="form.due_date" type="date" class="form-input" />
            </div>

            <!-- Assignee -->
            <div class="detail-section">
              <label class="detail-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Responsável
              </label>
              <select v-model="form.assigned_to" class="form-select">
                <option :value="null">Ninguém</option>
                <option v-for="member in members" :key="member.id" :value="member.id">
                  {{ member.name }}
                </option>
              </select>
            </div>

            <!-- Completed -->
            <div class="detail-section">
              <label class="detail-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Status
              </label>
              <label class="toggle-label">
                <div class="toggle" :class="{ active: form.completed }" @click="form.completed = !form.completed">
                  <div class="toggle-knob"></div>
                </div>
                <span>{{ form.completed ? 'Concluído' : 'Pendente' }}</span>
              </label>
            </div>
          </div>

          <!-- Labels -->
          <div class="detail-section">
            <div class="detail-label-row">
              <label class="detail-label">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                Labels
              </label>
              <button class="label-manage-btn" @click="showLabelManager = !showLabelManager" :title="showLabelManager ? 'Fechar gerenciador' : 'Gerenciar labels'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
              </button>
            </div>
            <div class="labels-picker">
              <button
                v-for="label in availableLabels"
                :key="label.id"
                class="label-chip"
                :class="{ active: selectedLabelIds.includes(label.id) }"
                :style="{ '--label-color': label.color }"
                @click="toggleLabel(label.id)"
              >
                <svg v-if="selectedLabelIds.includes(label.id)" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                {{ label.name }}
              </button>

              <!-- Botão criar nova label (inline) -->
              <button class="label-chip label-chip-add" @click="showNewLabelForm = !showNewLabelForm">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nova
              </button>
            </div>

            <!-- Formulário criar label -->
            <div v-if="showNewLabelForm" class="new-label-form">
              <input
                v-model="newLabelName"
                class="new-label-input"
                placeholder="Nome da label..."
                maxlength="50"
                @keyup.enter="createLabel"
                @keyup.escape="showNewLabelForm = false"
              />
              <div class="color-picker-row">
                <button
                  v-for="color in labelColors"
                  :key="color"
                  class="color-swatch"
                  :class="{ active: newLabelColor === color }"
                  :style="{ background: color }"
                  @click="newLabelColor = color"
                />
              </div>
              <div class="flex gap-2">
                <button class="btn btn-primary btn-sm" @click="createLabel" :disabled="!newLabelName.trim() || creatingLabel">
                  {{ creatingLabel ? 'Criando...' : 'Criar Label' }}
                </button>
                <button class="btn btn-ghost btn-sm" @click="showNewLabelForm = false">Cancelar</button>
              </div>
            </div>

            <!-- Gerenciador de labels (editar/deletar) -->
            <div v-if="showLabelManager && availableLabels.length" class="label-manager">
              <div v-for="label in availableLabels" :key="'manage-' + label.id" class="label-manager-item">
                <div class="label-manager-preview" :style="{ background: label.color }">{{ label.name }}</div>
                <div class="label-manager-actions">
                  <button class="label-action-btn" @click="startEditLabel(label)" title="Editar">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  </button>
                  <button class="label-action-btn label-action-danger" @click="deleteLabel(label)" title="Deletar">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </div>

              <!-- Edit label inline form -->
              <div v-if="editingLabel" class="new-label-form" style="margin-top: 8px;">
                <input
                  v-model="editLabelName"
                  class="new-label-input"
                  placeholder="Novo nome..."
                  maxlength="50"
                  @keyup.enter="updateLabel"
                  @keyup.escape="editingLabel = null"
                />
                <div class="color-picker-row">
                  <button
                    v-for="color in labelColors"
                    :key="'edit-' + color"
                    class="color-swatch"
                    :class="{ active: editLabelColor === color }"
                    :style="{ background: color }"
                    @click="editLabelColor = color"
                  />
                </div>
                <div class="flex gap-2">
                  <button class="btn btn-primary btn-sm" @click="updateLabel" :disabled="!editLabelName.trim()">Salvar</button>
                  <button class="btn btn-ghost btn-sm" @click="editingLabel = null">Cancelar</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="detail-actions">
            <button class="btn btn-primary" @click="saveCard" :disabled="saving">
              <svg v-if="!saving" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <div v-else class="btn-spinner"></div>
              {{ saving ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
            <button class="btn btn-danger-outline" @click="deleteCard">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              Excluir
            </button>
          </div>

          <div class="divider"></div>

          <!-- Comments -->
          <div class="detail-section">
            <label class="detail-label">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              Comentários ({{ comments.length }})
            </label>

            <div class="comment-form">
              <textarea
                v-model="newComment"
                class="form-textarea"
                rows="2"
                placeholder="Escrever um comentário..."
              ></textarea>
              <button
                class="btn btn-primary btn-sm mt-2"
                @click="addComment"
                :disabled="!newComment.trim()"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Enviar
              </button>
            </div>

            <div class="comments-list">
              <div v-for="comment in comments" :key="comment.id" class="comment-item">
                <div class="avatar avatar-sm" :style="{ background: getUserColor(comment.user?.name) }">
                  {{ comment.user?.name?.[0] || '?' }}
                </div>
                <div class="comment-body">
                  <div class="comment-header">
                    <strong>{{ comment.user?.name }}</strong>
                    <span class="comment-time">{{ formatRelative(comment.created_at) }}</span>
                  </div>
                  <p>{{ comment.body }}</p>
                </div>
              </div>
              <div v-if="!comments.length" class="empty-comments">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p>Nenhum comentário ainda</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, watch } from 'vue'
import api from '@/services/api'
import { useBoardStore } from '@/stores/board'

const props = defineProps({
  card: Object,
  workspaceId: [String, Number],
  boardId: [String, Number],
})
const emit = defineEmits(['close', 'updated', 'deleted'])

const boardStore = useBoardStore()
const saving = ref(false)
const comments = ref([])
const newComment = ref('')
const members = ref([])
const availableLabels = ref([])
const selectedLabelIds = ref([])

// ─── Label Management ───
const showNewLabelForm = ref(false)
const showLabelManager = ref(false)
const newLabelName = ref('')
const newLabelColor = ref('#6366f1')
const creatingLabel = ref(false)
const editingLabel = ref(null)
const editLabelName = ref('')
const editLabelColor = ref('#6366f1')

const labelColors = [
  '#6366f1', '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e',
  '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
  '#22c55e', '#14b8a6', '#06b6d4', '#3b82f6', '#2563eb',
  '#7c3aed', '#c026d3', '#64748b',
]

const form = reactive({
  title: props.card.title,
  description: props.card.description || '',
  priority: props.card.priority || 'medium',
  due_date: props.card.due_date ? props.card.due_date.split('T')[0] : '',
  assigned_to: props.card.assigned_to || null,
  completed: !!props.card.completed_at,
})

onMounted(async () => {
  // Lock body scroll when modal is open
  document.body.style.overflow = 'hidden'

  selectedLabelIds.value = (props.card.labels || []).map(l => l.id)

  const [commentsRes, membersRes, labelsRes] = await Promise.all([
    api.get(`/workspaces/${props.workspaceId}/boards/${props.boardId}/cards/${props.card.id}/comments`),
    api.get(`/workspaces/${props.workspaceId}`),
    api.get(`/workspaces/${props.workspaceId}/labels`),
  ])

  comments.value = commentsRes.data.data || commentsRes.data
  members.value = membersRes.data.members || membersRes.data.data?.members || []
  availableLabels.value = labelsRes.data.data || labelsRes.data
})

onUnmounted(() => {
  // Restore body scroll when modal closes
  document.body.style.overflow = ''
})

function toggleLabel(labelId) {
  const i = selectedLabelIds.value.indexOf(labelId)
  if (i === -1) selectedLabelIds.value.push(labelId)
  else selectedLabelIds.value.splice(i, 1)
}

async function createLabel() {
  if (!newLabelName.value.trim()) return
  creatingLabel.value = true
  try {
    const { data } = await api.post(`/workspaces/${props.workspaceId}/labels`, {
      name: newLabelName.value.trim(),
      color: newLabelColor.value,
    })
    const label = data.data || data
    availableLabels.value.push(label)
    selectedLabelIds.value.push(label.id)
    newLabelName.value = ''
    newLabelColor.value = '#6366f1'
    showNewLabelForm.value = false
  } finally {
    creatingLabel.value = false
  }
}

function startEditLabel(label) {
  editingLabel.value = label
  editLabelName.value = label.name
  editLabelColor.value = label.color
}

async function updateLabel() {
  if (!editLabelName.value.trim() || !editingLabel.value) return
  try {
    const { data } = await api.put(
      `/workspaces/${props.workspaceId}/labels/${editingLabel.value.id}`,
      { name: editLabelName.value.trim(), color: editLabelColor.value }
    )
    const updated = data.data || data
    const idx = availableLabels.value.findIndex(l => l.id === updated.id)
    if (idx !== -1) availableLabels.value[idx] = updated
    editingLabel.value = null
  } catch (e) {
    console.error('Erro ao atualizar label', e)
  }
}

async function deleteLabel(label) {
  if (!confirm(`Deletar label "${label.name}"?`)) return
  try {
    await api.delete(`/workspaces/${props.workspaceId}/labels/${label.id}`)
    availableLabels.value = availableLabels.value.filter(l => l.id !== label.id)
    selectedLabelIds.value = selectedLabelIds.value.filter(id => id !== label.id)
  } catch (e) {
    console.error('Erro ao deletar label', e)
  }
}

async function saveCard() {
  saving.value = true
  try {
    const payload = { ...form, label_ids: selectedLabelIds.value }
    if (!payload.due_date) delete payload.due_date
    if (!payload.assigned_to) delete payload.assigned_to

    await api.put(
      `/workspaces/${props.workspaceId}/boards/${props.boardId}/cards/${props.card.id}`,
      payload
    )
    emit('updated')
  } finally {
    saving.value = false
  }
}

async function deleteCard() {
  if (!confirm('Excluir este card?')) return
  await api.delete(
    `/workspaces/${props.workspaceId}/boards/${props.boardId}/cards/${props.card.id}`
  )
  emit('deleted')
}

async function addComment() {
  if (!newComment.value.trim()) return
  const res = await api.post(
    `/workspaces/${props.workspaceId}/boards/${props.boardId}/cards/${props.card.id}/comments`,
    { body: newComment.value }
  )
  comments.value.unshift(res.data.data || res.data)
  newComment.value = ''
}

function formatRelative(date) {
  const diff = Date.now() - new Date(date).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60) return `${mins}m atrás`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h atrás`
  const days = Math.floor(hours / 24)
  return `${days}d atrás`
}

function getUserColor(name) {
  if (!name) return '#999'
  const colors = ['#6366f1', '#ec4899', '#f59e0b', '#22c55e', '#3b82f6', '#8b5cf6']
  return colors[name.charCodeAt(0) % colors.length]
}
</script>

<style scoped>
/* Modal Overlay */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  overflow: hidden;
}

.card-detail-modal {
  width: 660px;
  max-width: 92vw;
  max-height: 88vh;
  overflow: hidden;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  animation: modalSlideIn 0.3s ease;
  display: flex;
  flex-direction: column;
}

@keyframes modalSlideIn {
  from { opacity: 0; transform: translateY(20px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Transitions */
.modal-enter-active { transition: all 0.3s ease; }
.modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from .card-detail-modal { transform: translateY(20px) scale(0.97); }
.modal-leave-to { opacity: 0; }

/* Header */
.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid var(--gray-100);
}

.modal-icon {
  width: 40px;
  height: 40px;
  background: rgba(99,102,241,0.1);
  color: var(--primary);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-title {
  font-size: 1rem;
  font-weight: 800;
  color: var(--gray-800);
}

.modal-subtitle {
  font-size: 0.8125rem;
  color: var(--gray-500);
  margin-top: 1px;
}

.close-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: none;
  background: var(--gray-100);
  color: var(--gray-500);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.close-btn:hover { background: var(--gray-200); color: var(--gray-700); }

/* Body */
.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 20px;
  overflow-y: auto;
  overscroll-behavior: contain;
  flex: 1;
  min-height: 0;
}

/* Modal body scrollbar */
.modal-body::-webkit-scrollbar { width: 6px; }
.modal-body::-webkit-scrollbar-track { background: transparent; }
.modal-body::-webkit-scrollbar-thumb {
  background: var(--gray-200);
  border-radius: 100px;
}
.modal-body::-webkit-scrollbar-thumb:hover { background: var(--gray-300); }

.detail-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.detail-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--gray-500);
  text-transform: uppercase;
  letter-spacing: 0.06em;
  display: flex;
  align-items: center;
  gap: 6px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

/* Form elements */
.form-input, .form-select {
  width: 100%;
  padding: 10px 14px;
  border: 1.5px solid var(--gray-200);
  border-radius: 10px;
  font-size: 0.875rem;
  font-family: inherit;
  color: var(--gray-800);
  background: white;
  transition: all 0.2s;
  outline: none;
}
.form-input:focus, .form-select:focus {
  border-color: var(--primary-400);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.form-textarea {
  width: 100%;
  padding: 12px 14px;
  border: 1.5px solid var(--gray-200);
  border-radius: 10px;
  font-size: 0.875rem;
  resize: vertical;
  font-family: inherit;
  color: var(--gray-800);
  transition: all 0.2s;
  outline: none;
}
.form-textarea:focus {
  border-color: var(--primary-400);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

/* Priority dot */
.select-wrap {
  position: relative;
}
.priority-dot {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  width: 10px;
  height: 10px;
  border-radius: 50%;
  pointer-events: none;
}
.dot-low { background: #22c55e; }
.dot-medium { background: #f59e0b; }
.dot-high { background: #f97316; }
.dot-urgent { background: #ef4444; }

/* Toggle */
.toggle-label {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.875rem;
  color: var(--gray-600);
  font-weight: 500;
  cursor: pointer;
  padding: 6px 0;
}

.toggle {
  width: 44px;
  height: 24px;
  background: var(--gray-200);
  border-radius: 100px;
  position: relative;
  cursor: pointer;
  transition: background 0.2s;
}
.toggle.active { background: var(--primary); }
.toggle-knob {
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: transform 0.2s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.toggle.active .toggle-knob { transform: translateX(20px); }

/* Labels */
.detail-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.label-manage-btn {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: none;
  background: var(--gray-100);
  color: var(--gray-400);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.label-manage-btn:hover {
  background: var(--gray-200);
  color: var(--gray-600);
}

.labels-picker {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.label-chip {
  padding: 5px 14px;
  border-radius: 100px;
  font-size: 0.75rem;
  font-weight: 700;
  border: 2px solid var(--label-color);
  background: transparent;
  color: var(--label-color);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 4px;
  font-family: inherit;
}
.label-chip:hover { transform: translateY(-1px); }
.label-chip.active {
  background: var(--label-color);
  color: white;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.label-chip-add {
  --label-color: var(--gray-400);
  border-style: dashed;
}
.label-chip-add:hover {
  --label-color: var(--primary);
}

/* New Label Form */
.new-label-form {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 14px;
  background: var(--gray-50);
  border-radius: 12px;
  border: 1px solid var(--gray-100);
  margin-top: 8px;
  animation: fadeIn 0.2s ease;
}

.new-label-input {
  width: 100%;
  padding: 8px 12px;
  border: 1.5px solid var(--gray-200);
  border-radius: 8px;
  font-size: 0.8125rem;
  font-family: inherit;
  color: var(--gray-800);
  background: white;
  outline: none;
  transition: all 0.2s;
}
.new-label-input:focus {
  border-color: var(--primary-400);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

.color-picker-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.color-swatch {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2.5px solid transparent;
  cursor: pointer;
  transition: all 0.15s;
  padding: 0;
}
.color-swatch:hover {
  transform: scale(1.15);
}
.color-swatch.active {
  border-color: var(--gray-800);
  box-shadow: 0 0 0 2px white, 0 0 0 4px var(--gray-300);
  transform: scale(1.15);
}

/* Label Manager */
.label-manager {
  display: flex;
  flex-direction: column;
  gap: 6px;
  padding: 12px;
  background: var(--gray-50);
  border-radius: 12px;
  border: 1px solid var(--gray-100);
  margin-top: 8px;
  animation: fadeIn 0.2s ease;
}

.label-manager-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 6px 8px;
  border-radius: 8px;
  transition: background 0.15s;
}
.label-manager-item:hover {
  background: var(--gray-100);
}

.label-manager-preview {
  padding: 3px 12px;
  border-radius: 100px;
  font-size: 0.6875rem;
  font-weight: 700;
  color: white;
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.label-manager-actions {
  display: flex;
  gap: 4px;
  flex-shrink: 0;
}

.label-action-btn {
  width: 26px;
  height: 26px;
  border: none;
  background: none;
  border-radius: 6px;
  color: var(--gray-400);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.label-action-btn:hover {
  background: var(--gray-200);
  color: var(--gray-600);
}
.label-action-danger:hover {
  background: rgba(239,68,68,0.1);
  color: var(--danger);
}

/* Actions */
.detail-actions {
  display: flex;
  gap: 12px;
  padding-top: 4px;
}

.btn-danger-outline {
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.875rem;
  font-family: inherit;
  border: 1.5px solid var(--danger);
  background: transparent;
  color: var(--danger);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 6px;
}
.btn-danger-outline:hover {
  background: var(--danger);
  color: white;
}

.btn-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.divider {
  height: 1px;
  background: var(--gray-100);
  border: none;
}

/* Comments */
.comment-form { margin-bottom: 16px; }

.comments-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.comment-item {
  display: flex;
  gap: 10px;
}

.comment-body {
  flex: 1;
  background: var(--gray-50);
  border-radius: 12px;
  padding: 12px 16px;
}

.comment-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 4px;
}
.comment-header strong {
  font-size: 0.8125rem;
  color: var(--gray-800);
}
.comment-time {
  font-size: 0.6875rem;
  color: var(--gray-400);
}

.comment-body p {
  font-size: 0.875rem;
  color: var(--gray-700);
  line-height: 1.5;
  margin: 0;
}

.empty-comments {
  text-align: center;
  padding: 24px 0;
  color: var(--gray-400);
}
.empty-comments p {
  font-size: 0.8125rem;
  margin-top: 8px;
}
</style>
