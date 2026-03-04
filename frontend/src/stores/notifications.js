import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import api from '@/services/api'

export const useNotificationsStore = defineStore('notifications', () => {
  const invitations = ref([])
  const loading = ref(false)

  const pendingCount = computed(() => invitations.value.length)

  async function fetchInvitations() {
    try {
      loading.value = true
      const { data } = await api.get('/invitations/pending')
      invitations.value = data
    } catch {
      // silently fail
    } finally {
      loading.value = false
    }
  }

  async function acceptInvitation(token) {
    const { data } = await api.post(`/invitations/${token}/accept`)
    invitations.value = invitations.value.filter(i => i.token !== token)
    return data
  }

  async function declineInvitation(token) {
    const { data } = await api.post(`/invitations/${token}/decline`)
    invitations.value = invitations.value.filter(i => i.token !== token)
    return data
  }

  return { invitations, loading, pendingCount, fetchInvitations, acceptInvitation, declineInvitation }
})
