<template>
  <div class="board-page">
    <!-- Board Header -->
    <div class="board-header">
      <div class="flex items-center gap-3">
        <router-link :to="`/workspace/${workspaceId}`" class="back-btn">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </router-link>
        <div class="board-color-dot" :style="{ background: board?.color }"></div>
        <h2 class="board-title">{{ board?.name || 'Carregando...' }}</h2>
        <span v-if="board" class="board-badge">{{ totalCards }} cards</span>
      </div>
      <div class="flex gap-2">
        <button class="btn btn-secondary btn-sm" @click="showAddColumn = true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Coluna
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="board-loading">
      <div class="board-loading-columns">
        <div v-for="n in 3" :key="n" class="skeleton-column">
          <div class="skeleton" style="height:24px;width:60%;margin-bottom:16px;border-radius:6px;"></div>
          <div v-for="m in (4 - n)" :key="m" class="skeleton skeleton-card" :style="{ height: `${70 + m * 20}px`, marginBottom: '10px' }"></div>
        </div>
      </div>
    </div>

    <!-- Kanban Board -->
    <div v-else ref="kanbanRef" class="kanban-container">
      <div class="kanban-board">
        <draggable
          v-model="columns"
          group="columns"
          item-key="id"
          class="kanban-columns"
          handle=".column-drag-handle"
          @end="onColumnDragEnd"
          :animation="250"
        >
          <template #item="{ element: column }">
            <div class="kanban-column">
              <div class="column-header">
                <div class="flex items-center gap-2">
                  <span class="column-drag-handle" title="Arrastar coluna">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="5" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                  </span>
                  <h4 class="column-title">{{ column.name }}</h4>
                  <span class="column-count">{{ column.cards?.length || 0 }}</span>
                </div>
                <div class="column-actions">
                  <button class="col-action-btn" @click="startAddCard(column.id)" title="Adicionar card">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  </button>
                  <button class="col-action-btn col-action-danger" @click="confirmDeleteColumn(column)" title="Deletar coluna">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </div>
              </div>

              <!-- Cards -->
              <draggable
                v-model="column.cards"
                group="cards"
                item-key="id"
                class="kanban-cards"
                :data-column-id="column.id"
                @end="onCardDragEnd"
                :animation="250"
                ghost-class="card-ghost"
                drag-class="card-dragging"
              >
                <template #item="{ element: card }">
                  <div class="kanban-card" @click="openCardDetail(card)">
                    <!-- Labels -->
                    <div v-if="card.labels?.length" class="card-labels">
                      <span
                        v-for="label in card.labels"
                        :key="label.id"
                        class="card-label"
                        :style="{ background: label.color }"
                      >{{ label.name }}</span>
                    </div>

                    <h5 class="card-title">{{ card.title }}</h5>

                    <div class="card-meta">
                      <span class="badge" :class="`badge-priority-${card.priority}`">{{ card.priority }}</span>
                      <span v-if="card.due_date" class="card-due" :class="{ overdue: isOverdue(card.due_date) }">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ formatDate(card.due_date) }}
                      </span>
                    </div>

                    <div v-if="card.assignee || card.comments_count" class="card-footer">
                      <div v-if="card.assignee" class="flex items-center gap-2">
                        <div class="avatar avatar-sm" :style="{ background: getUserColor(card.assignee.name) }">
                          {{ card.assignee.name[0] }}
                        </div>
                        <span class="assignee-name">{{ card.assignee.name.split(' ')[0] }}</span>
                      </div>
                      <span v-if="card.comments_count" class="comment-count">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        {{ card.comments_count }}
                      </span>
                    </div>

                    <!-- Completed indicator -->
                    <div v-if="card.completed_at" class="card-completed-badge">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                  </div>
                </template>
              </draggable>

              <!-- Add Card Form -->
              <div v-if="addingCardColumnId === column.id" class="add-card-form">
                <input
                  v-model="newCardTitle"
                  ref="newCardInput"
                  class="add-card-input"
                  placeholder="Título do card..."
                  @keyup.enter="handleAddCard(column.id)"
                  @keyup.escape="addingCardColumnId = null"
                  autofocus
                />
                <div class="flex gap-2 mt-2">
                  <button class="btn btn-primary btn-sm" @click="handleAddCard(column.id)">Adicionar</button>
                  <button class="btn btn-ghost btn-sm" @click="addingCardColumnId = null">Cancelar</button>
                </div>
              </div>

              <button
                v-else
                class="add-card-btn"
                @click="startAddCard(column.id)"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Adicionar Card
              </button>
            </div>
          </template>
        </draggable>

        <!-- Add Column -->
        <div class="add-column">
          <div v-if="showAddColumn" class="add-column-form">
            <input
              v-model="newColumnName"
              class="add-card-input"
              placeholder="Nome da coluna..."
              @keyup.enter="handleAddColumn"
              @keyup.escape="showAddColumn = false"
              autofocus
            />
            <div class="flex gap-2 mt-2">
              <button class="btn btn-primary btn-sm" @click="handleAddColumn">Criar</button>
              <button class="btn btn-ghost btn-sm" @click="showAddColumn = false">Cancelar</button>
            </div>
          </div>
          <button v-else class="add-column-btn" @click="showAddColumn = true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nova Coluna
          </button>
        </div>
      </div>
    </div>

    <!-- Card Detail Modal -->
    <CardDetailModal
      v-if="selectedCard"
      :card="selectedCard"
      :workspace-id="workspaceId"
      :board-id="boardId"
      @close="selectedCard = null"
      @updated="refreshBoard"
      @deleted="handleCardDeleted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import draggable from 'vuedraggable'
import { useBoardStore } from '@/stores/board'
import CardDetailModal from '@/components/CardDetailModal.vue'

const props = defineProps({
  workspaceId: [String, Number],
  boardId: [String, Number],
})

const boardStore = useBoardStore()
const loading = ref(true)
const board = ref(null)
const columns = ref([])
const selectedCard = ref(null)
const showAddColumn = ref(false)
const newColumnName = ref('')
const addingCardColumnId = ref(null)
const newCardTitle = ref('')

const totalCards = computed(() => columns.value.reduce((sum, c) => sum + (c.cards?.length || 0), 0))
const kanbanRef = ref(null)

// ─── Smooth Scroll com Inércia (estilo Canva) ───
let targetScrollLeft = 0
let currentScrollLeft = 0
let animFrameId = null
const LERP_FACTOR = 0.12       // Suavidade (0.05 = muito suave, 0.2 = mais responsivo)
const SCROLL_SPEED = 2.0       // Multiplicador de velocidade do wheel
const STOP_THRESHOLD = 0.5     // Quando parar de animar (em pixels)

function smoothScrollTick() {
  const container = kanbanRef.value
  if (!container) { animFrameId = null; return }

  const diff = targetScrollLeft - currentScrollLeft
  if (Math.abs(diff) < STOP_THRESHOLD) {
    currentScrollLeft = targetScrollLeft
    container.scrollLeft = currentScrollLeft
    animFrameId = null
    return
  }

  currentScrollLeft += diff * LERP_FACTOR
  container.scrollLeft = currentScrollLeft
  animFrameId = requestAnimationFrame(smoothScrollTick)
}

function startSmoothScroll() {
  if (!animFrameId) {
    animFrameId = requestAnimationFrame(smoothScrollTick)
  }
}

function addScrollDelta(delta) {
  const container = kanbanRef.value
  if (!container) return

  // Sincroniza caso o user tenha scrollado de outra forma
  currentScrollLeft = container.scrollLeft
  targetScrollLeft = currentScrollLeft + delta

  // Clamp nos limites
  const maxScroll = container.scrollWidth - container.clientWidth
  targetScrollLeft = Math.max(0, Math.min(targetScrollLeft, maxScroll))

  startSmoothScroll()
}

function handleWheelScroll(e) {
  const container = kanbanRef.value
  if (!container) return

  // Shift + Scroll → horizontal suave (estilo Canva)
  if (e.shiftKey && e.deltaY !== 0) {
    e.preventDefault()
    addScrollDelta(e.deltaY * SCROLL_SPEED)
    return
  }

  // Scroll horizontal nativo (trackpad) → deixa o browser lidar
  if (e.deltaX !== 0) {
    return
  }

  // Scroll vertical fora dos cards → converte para horizontal suave
  const isInsideScrollableColumn = e.target.closest('.kanban-cards')
  if (!isInsideScrollableColumn && e.deltaY !== 0) {
    if (container.scrollWidth > container.clientWidth) {
      e.preventDefault()
      addScrollDelta(e.deltaY * SCROLL_SPEED)
    }
  }
}

// ─── Drag to Pan com Inércia (click-and-drag estilo Canva) ───
const isDraggingBoard = ref(false)
let dragStartX = 0
let dragStartY = 0
let scrollStartX = 0
let scrollStartY = 0
let lastDragX = 0
let lastDragTime = 0
let dragVelocity = 0
let momentumFrameId = null

function handleMouseDown(e) {
  const container = kanbanRef.value
  if (!container) return

  // Só ativa pan com botão do meio OU clique esquerdo no fundo do board
  const isMiddleClick = e.button === 1
  const isBackgroundClick = e.button === 0 && (
    e.target === container ||
    e.target.closest('.kanban-board') === e.target ||
    e.target.classList.contains('kanban-board') ||
    e.target.classList.contains('kanban-columns')
  )

  if (!isMiddleClick && !isBackgroundClick) return

  e.preventDefault()

  // Cancela animações em andamento
  if (animFrameId) { cancelAnimationFrame(animFrameId); animFrameId = null }
  if (momentumFrameId) { cancelAnimationFrame(momentumFrameId); momentumFrameId = null }

  isDraggingBoard.value = true
  dragStartX = e.clientX
  dragStartY = e.clientY
  scrollStartX = container.scrollLeft
  scrollStartY = container.scrollTop
  lastDragX = e.clientX
  lastDragTime = performance.now()
  dragVelocity = 0
  container.style.cursor = 'grabbing'
  container.style.userSelect = 'none'

  document.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('mouseup', handleMouseUp)
}

function handleMouseMove(e) {
  if (!isDraggingBoard.value) return
  const container = kanbanRef.value
  if (!container) return

  const dx = e.clientX - dragStartX
  const dy = e.clientY - dragStartY
  container.scrollLeft = scrollStartX - dx
  container.scrollTop = scrollStartY - dy

  // Calcula velocidade para inércia
  const now = performance.now()
  const dt = now - lastDragTime
  if (dt > 0) {
    dragVelocity = (lastDragX - e.clientX) / dt * 16 // px por frame (~16ms)
  }
  lastDragX = e.clientX
  lastDragTime = now
}

function handleMouseUp() {
  isDraggingBoard.value = false
  const container = kanbanRef.value
  if (container) {
    container.style.cursor = ''
    container.style.userSelect = ''
  }
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)

  // Inércia pós-drag (momentum)
  if (container && Math.abs(dragVelocity) > 0.5) {
    let velocity = dragVelocity
    const FRICTION = 0.92 // Desacelera a cada frame

    function momentumTick() {
      if (Math.abs(velocity) < 0.3) { momentumFrameId = null; return }
      velocity *= FRICTION
      container.scrollLeft += velocity
      momentumFrameId = requestAnimationFrame(momentumTick)
    }
    momentumFrameId = requestAnimationFrame(momentumTick)
  }
}

// Watch the template ref — fires when v-else mounts/unmounts the element
watch(kanbanRef, (el, oldEl) => {
  if (oldEl) {
    oldEl.removeEventListener('wheel', handleWheelScroll)
    oldEl.removeEventListener('mousedown', handleMouseDown)
  }
  if (el) {
    el.addEventListener('wheel', handleWheelScroll, { passive: false })
    el.addEventListener('mousedown', handleMouseDown)
    // Sincroniza o scroll atual
    currentScrollLeft = el.scrollLeft
    targetScrollLeft = el.scrollLeft
  }
})

onMounted(async () => {
  await refreshBoard()
})

onUnmounted(() => {
  if (kanbanRef.value) {
    kanbanRef.value.removeEventListener('wheel', handleWheelScroll)
    kanbanRef.value.removeEventListener('mousedown', handleMouseDown)
  }
  if (animFrameId) cancelAnimationFrame(animFrameId)
  if (momentumFrameId) cancelAnimationFrame(momentumFrameId)
  document.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('mouseup', handleMouseUp)
})

async function refreshBoard() {
  loading.value = true
  try {
    const data = await boardStore.fetchBoard(props.workspaceId, props.boardId)
    board.value = data
    columns.value = data.columns || []
  } finally {
    loading.value = false
  }
}

function startAddCard(columnId) {
  addingCardColumnId.value = columnId
  newCardTitle.value = ''
}

async function handleAddCard(columnId) {
  if (!newCardTitle.value.trim()) return
  await boardStore.createCard(props.workspaceId, props.boardId, columnId, {
    title: newCardTitle.value,
  })
  newCardTitle.value = ''
  addingCardColumnId.value = null
  await refreshBoard()
}

async function handleAddColumn() {
  if (!newColumnName.value.trim()) return
  await boardStore.createColumn(props.workspaceId, props.boardId, {
    name: newColumnName.value,
  })
  newColumnName.value = ''
  showAddColumn.value = false
  await refreshBoard()
}

async function confirmDeleteColumn(column) {
  if (confirm(`Deletar coluna "${column.name}" e todos os cards?`)) {
    await boardStore.deleteColumn(props.workspaceId, props.boardId, column.id)
    await refreshBoard()
  }
}

function openCardDetail(card) {
  selectedCard.value = card
}

async function handleCardDeleted() {
  selectedCard.value = null
  await refreshBoard()
}

async function onColumnDragEnd() {
  const orderedIds = columns.value.map((c) => c.id)
  await boardStore.reorderColumns(props.workspaceId, props.boardId, orderedIds)
}

async function onCardDragEnd(event) {
  const cardId = parseInt(event.item.__draggable_context?.element?.id)
  const toColumnEl = event.to
  const newColumnId = parseInt(toColumnEl.dataset.columnId)
  const newIndex = event.newIndex

  if (!cardId || !newColumnId) return

  const newColumnData = columns.value.find((c) => c.id === newColumnId)
  if (newColumnData) {
    const orderedIds = newColumnData.cards.map((c) => c.id)
    await boardStore.moveCard(props.workspaceId, props.boardId, cardId, {
      column_id: newColumnId,
      position: newIndex,
    })
    await boardStore.reorderCards(props.workspaceId, props.boardId, newColumnId, orderedIds)
  }
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
}

function isOverdue(date) {
  return new Date(date) < new Date()
}

function getUserColor(name) {
  const colors = ['#6366f1', '#ec4899', '#f59e0b', '#22c55e', '#3b82f6', '#8b5cf6']
  return colors[name.charCodeAt(0) % colors.length]
}
</script>

<style scoped>
.board-page {
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: fadeIn 0.3s ease;
}

/* ─── Header ─── */
.board-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 24px;
  background: white;
  border-bottom: 1px solid var(--gray-100);
  flex-shrink: 0;
}

.back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  color: var(--gray-400);
  transition: all 0.2s;
}
.back-btn:hover {
  background: var(--gray-100);
  color: var(--gray-700);
}

.board-color-dot {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.board-title {
  font-size: 1.125rem;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.board-badge {
  background: var(--gray-100);
  color: var(--gray-500);
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 100px;
}

/* ─── Loading ─── */
.board-loading {
  flex: 1;
  padding: 24px;
  background: var(--gray-50);
}
.board-loading-columns {
  display: flex;
  gap: 20px;
  height: 100%;
}
.skeleton-column {
  width: 320px;
  min-width: 320px;
  background: white;
  border-radius: 16px;
  padding: 20px 16px;
  border: 1px solid var(--gray-100);
}

/* ─── Kanban ─── */
.kanban-container {
  flex: 1;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 24px;
  padding-bottom: 8px;
  background: linear-gradient(180deg, var(--gray-50) 0%, #eef2ff 100%);
  cursor: grab;
  -webkit-overflow-scrolling: touch;
}

.kanban-container:active {
  cursor: grabbing;
}

.kanban-board {
  display: inline-flex;
  gap: 20px;
  height: 100%;
  align-items: flex-start;
  min-width: max-content;
  padding-right: 24px;
}

.kanban-columns {
  display: flex;
  gap: 20px;
  height: 100%;
}

/* ─── Column ─── */
.kanban-column {
  width: 330px;
  min-width: 330px;
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 16px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  max-height: calc(100vh - 170px);
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  transition: box-shadow 0.2s;
}
.kanban-column:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}

.column-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 6px 12px;
}

.column-drag-handle {
  cursor: grab;
  color: var(--gray-300);
  display: flex;
  padding: 2px;
  border-radius: 4px;
  transition: all 0.2s;
}
.column-drag-handle:hover {
  color: var(--gray-500);
  background: var(--gray-100);
}

.column-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: var(--gray-700);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.column-count {
  background: var(--gray-100);
  color: var(--gray-500);
  font-size: 0.6875rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 100px;
  min-width: 22px;
  text-align: center;
}

.column-actions { display: flex; gap: 2px; }
.col-action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 30px;
  height: 30px;
  border: none;
  background: none;
  cursor: pointer;
  border-radius: 8px;
  color: var(--gray-400);
  transition: all 0.15s;
}
.col-action-btn:hover { background: var(--gray-100); color: var(--gray-600); }
.col-action-danger:hover { background: var(--danger-light); color: var(--danger); }

/* ─── Cards ─── */
.kanban-cards {
  flex: 1;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 10px;
  min-height: 40px;
  padding: 2px;
}

.kanban-card {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 12px;
  padding: 14px 16px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

.kanban-card:hover {
  border-color: var(--primary-200);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.08);
  transform: translateY(-1px);
}

.card-ghost {
  opacity: 0.35;
  background: var(--primary-50) !important;
  border: 2px dashed var(--primary-300) !important;
  border-radius: 12px !important;
  box-shadow: none !important;
}

.card-dragging {
  transform: rotate(3deg) scale(1.03);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
  z-index: 999;
}

.card-labels {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}

.card-label {
  padding: 2px 8px;
  border-radius: 100px;
  font-size: 0.625rem;
  font-weight: 700;
  color: white;
  letter-spacing: 0.02em;
}

.card-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-800);
  margin-bottom: 10px;
  line-height: 1.4;
}

.card-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.card-due {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.6875rem;
  color: var(--gray-500);
  font-weight: 500;
}

.card-due.overdue {
  color: var(--danger);
  font-weight: 700;
}

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 8px;
  border-top: 1px solid var(--gray-200);
}

.assignee-name {
  font-size: 0.6875rem;
  color: var(--gray-500);
  font-weight: 500;
}

.comment-count {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.6875rem;
  color: var(--gray-400);
  font-weight: 600;
}

.card-completed-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 22px;
  height: 22px;
  background: var(--success);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ─── Add Card ─── */
.add-card-form { padding: 8px 0; }

.add-card-input {
  width: 100%;
  padding: 10px 14px;
  background: var(--gray-50);
  border: 1.5px solid var(--gray-200);
  border-radius: 10px;
  font-size: 0.875rem;
  color: var(--gray-800);
  font-family: inherit;
  transition: all 0.2s;
  outline: none;
}
.add-card-input:focus {
  border-color: var(--primary-400);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  background: white;
}

.add-card-btn {
  width: 100%;
  padding: 10px 14px;
  background: transparent;
  border: 1.5px dashed var(--gray-200);
  color: var(--gray-400);
  font-size: 0.8125rem;
  font-weight: 600;
  font-family: inherit;
  text-align: left;
  border-radius: 10px;
  transition: all 0.2s;
  margin-top: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}
.add-card-btn:hover {
  border-color: var(--primary-300);
  background: rgba(99, 102, 241, 0.04);
  color: var(--primary-500);
}

/* ─── Add Column ─── */
.add-column { min-width: 330px; }

.add-column-form {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.add-column-btn {
  width: 100%;
  padding: 16px;
  background: rgba(99, 102, 241, 0.04);
  border: 2px dashed rgba(99, 102, 241, 0.2);
  border-radius: 16px;
  color: var(--primary-400);
  font-weight: 700;
  font-size: 0.875rem;
  font-family: inherit;
  transition: all 0.2s;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.add-column-btn:hover {
  background: rgba(99, 102, 241, 0.08);
  border-color: var(--primary-300);
  color: var(--primary-500);
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

@media (max-width: 768px) {
  .board-header {
    padding-left: 64px;
  }
}
</style>

<!-- Non-scoped styles for scrollbar pseudo-elements -->
<style>
.kanban-container::-webkit-scrollbar {
  height: 16px;
}
.kanban-container::-webkit-scrollbar-track {
  background: #e2e5eb;
  border-radius: 100px;
  margin: 0 16px;
}
.kanban-container::-webkit-scrollbar-thumb {
  background: #8b95a8;
  border-radius: 100px;
  border: 3px solid #e2e5eb;
  min-width: 80px;
}
.kanban-container::-webkit-scrollbar-thumb:hover {
  background: #6b7589;
}
.kanban-container::-webkit-scrollbar-thumb:active {
  background: #4b5567;
}
</style>
