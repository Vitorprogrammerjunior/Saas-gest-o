import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useBoardStore = defineStore('board', () => {
  const boards = ref([])
  const currentBoard = ref(null)
  const loading = ref(false)

  async function fetchBoards(workspaceId) {
    loading.value = true
    try {
      const { data } = await api.get(`/workspaces/${workspaceId}/boards`)
      boards.value = data
    } finally {
      loading.value = false
    }
  }

  async function fetchBoard(workspaceId, boardId) {
    loading.value = true
    try {
      const { data } = await api.get(`/workspaces/${workspaceId}/boards/${boardId}`)
      currentBoard.value = data
      return data
    } finally {
      loading.value = false
    }
  }

  async function createBoard(workspaceId, payload) {
    const { data } = await api.post(`/workspaces/${workspaceId}/boards`, payload)
    boards.value.push(data)
    return data
  }

  async function updateBoard(workspaceId, boardId, payload) {
    const { data } = await api.put(`/workspaces/${workspaceId}/boards/${boardId}`, payload)
    const index = boards.value.findIndex((b) => b.id === boardId)
    if (index !== -1) boards.value[index] = data
    return data
  }

  async function deleteBoard(workspaceId, boardId) {
    await api.delete(`/workspaces/${workspaceId}/boards/${boardId}`)
    boards.value = boards.value.filter((b) => b.id !== boardId)
  }

  // ─── Columns ──────────────────────────────────

  async function createColumn(workspaceId, boardId, payload) {
    const { data } = await api.post(`/workspaces/${workspaceId}/boards/${boardId}/columns`, payload)
    if (currentBoard.value) {
      currentBoard.value.columns.push(data)
    }
    return data
  }

  async function updateColumn(workspaceId, boardId, columnId, payload) {
    const { data } = await api.put(`/workspaces/${workspaceId}/boards/${boardId}/columns/${columnId}`, payload)
    if (currentBoard.value) {
      const col = currentBoard.value.columns.find((c) => c.id === columnId)
      if (col) Object.assign(col, data)
    }
    return data
  }

  async function deleteColumn(workspaceId, boardId, columnId) {
    await api.delete(`/workspaces/${workspaceId}/boards/${boardId}/columns/${columnId}`)
    if (currentBoard.value) {
      currentBoard.value.columns = currentBoard.value.columns.filter((c) => c.id !== columnId)
    }
  }

  async function reorderColumns(workspaceId, boardId, orderedIds) {
    await api.put(`/workspaces/${workspaceId}/boards/${boardId}/columns/reorder`, {
      ordered_ids: orderedIds,
    })
  }

  // ─── Cards ────────────────────────────────────

  async function createCard(workspaceId, boardId, columnId, payload) {
    const { data } = await api.post(
      `/workspaces/${workspaceId}/boards/${boardId}/columns/${columnId}/cards`,
      payload
    )
    if (currentBoard.value) {
      const col = currentBoard.value.columns.find((c) => c.id === columnId)
      if (col) col.cards.push(data)
    }
    return data
  }

  async function updateCard(workspaceId, boardId, cardId, payload) {
    const { data } = await api.put(
      `/workspaces/${workspaceId}/boards/${boardId}/cards/${cardId}`,
      payload
    )
    return data
  }

  async function moveCard(workspaceId, boardId, cardId, payload) {
    const { data } = await api.put(
      `/workspaces/${workspaceId}/boards/${boardId}/cards/${cardId}/move`,
      payload
    )
    return data
  }

  async function reorderCards(workspaceId, boardId, columnId, orderedIds) {
    await api.put(
      `/workspaces/${workspaceId}/boards/${boardId}/columns/${columnId}/cards/reorder`,
      { ordered_ids: orderedIds }
    )
  }

  async function deleteCard(workspaceId, boardId, cardId) {
    await api.delete(`/workspaces/${workspaceId}/boards/${boardId}/cards/${cardId}`)
  }

  async function fetchCard(workspaceId, boardId, cardId) {
    const { data } = await api.get(`/workspaces/${workspaceId}/boards/${boardId}/cards/${cardId}`)
    return data
  }

  // ─── Comments ─────────────────────────────────

  async function fetchComments(workspaceId, boardId, cardId) {
    const { data } = await api.get(
      `/workspaces/${workspaceId}/boards/${boardId}/cards/${cardId}/comments`
    )
    return data
  }

  async function addComment(workspaceId, boardId, cardId, body) {
    const { data } = await api.post(
      `/workspaces/${workspaceId}/boards/${boardId}/cards/${cardId}/comments`,
      { body }
    )
    return data
  }

  return {
    boards, currentBoard, loading,
    fetchBoards, fetchBoard, createBoard, updateBoard, deleteBoard,
    createColumn, updateColumn, deleteColumn, reorderColumns,
    createCard, updateCard, moveCard, reorderCards, deleteCard, fetchCard,
    fetchComments, addComment,
  }
})
