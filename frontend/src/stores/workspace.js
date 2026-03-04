import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useWorkspaceStore = defineStore('workspace', () => {
  const workspaces = ref([])
  const currentWorkspace = ref(null)
  const loading = ref(false)

  async function fetchWorkspaces() {
    loading.value = true
    try {
      const { data } = await api.get('/workspaces')
      workspaces.value = data
    } finally {
      loading.value = false
    }
  }

  async function fetchWorkspace(id) {
    loading.value = true
    try {
      const { data } = await api.get(`/workspaces/${id}`)
      currentWorkspace.value = data
      return data
    } finally {
      loading.value = false
    }
  }

  async function createWorkspace(payload) {
    const { data } = await api.post('/workspaces', payload)
    workspaces.value.push(data)
    return data
  }

  async function updateWorkspace(id, payload) {
    const { data } = await api.put(`/workspaces/${id}`, payload)
    const index = workspaces.value.findIndex((w) => w.id === id)
    if (index !== -1) workspaces.value[index] = data
    return data
  }

  async function deleteWorkspace(id) {
    await api.delete(`/workspaces/${id}`)
    workspaces.value = workspaces.value.filter((w) => w.id !== id)
  }

  async function inviteMember(workspaceId, payload) {
    const { data } = await api.post(`/workspaces/${workspaceId}/invite`, payload)
    return data
  }

  return {
    workspaces, currentWorkspace, loading,
    fetchWorkspaces, fetchWorkspace, createWorkspace,
    updateWorkspace, deleteWorkspace, inviteMember,
  }
})
