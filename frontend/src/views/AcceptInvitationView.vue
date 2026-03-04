<template>
  <div class="accept-page">
    <!-- Animated Background -->
    <div class="bg-shapes">
      <div class="bg-shape shape-1"></div>
      <div class="bg-shape shape-2"></div>
      <div class="bg-shape shape-3"></div>
    </div>

    <div class="accept-card">
      <!-- Loading State -->
      <div v-if="loading" class="state-content">
        <div class="loader-ring">
          <svg width="56" height="56" viewBox="0 0 56 56">
            <circle cx="28" cy="28" r="24" fill="none" stroke="rgba(99,102,241,0.15)" stroke-width="4"/>
            <circle cx="28" cy="28" r="24" fill="none" stroke="var(--primary)" stroke-width="4" stroke-linecap="round" stroke-dasharray="120" stroke-dashoffset="80" class="loader-circle"/>
          </svg>
        </div>
        <h3>Processando convite...</h3>
        <p>Aguarde enquanto verificamos seu convite</p>
      </div>

      <!-- Success State -->
      <div v-else-if="success" class="state-content">
        <div class="result-icon success-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h3>Convite Aceito!</h3>
        <p>Você foi adicionado ao workspace com sucesso.</p>
        <router-link to="/workspaces" class="btn btn-primary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          Ir para Workspaces
        </router-link>
      </div>

      <!-- Error State -->
      <div v-else class="state-content">
        <div class="result-icon error-icon">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <h3>Erro no Convite</h3>
        <p>{{ errorMessage }}</p>
        <router-link to="/workspaces" class="btn btn-secondary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Voltar
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const loading = ref(true)
const success = ref(false)
const errorMessage = ref('')

onMounted(async () => {
  const token = route.params.token
  if (!token) {
    errorMessage.value = 'Token de convite inválido.'
    loading.value = false
    return
  }

  try {
    await api.post(`/invitations/${token}/accept`)
    success.value = true
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Convite inválido ou expirado.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.accept-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
  padding: 24px;
  position: relative;
  overflow: hidden;
}

/* Animated Background */
.bg-shapes { position: absolute; inset: 0; overflow: hidden; }
.bg-shape {
  position: absolute;
  border-radius: 50%;
  opacity: 0.08;
  filter: blur(60px);
}
.shape-1 {
  width: 400px; height: 400px;
  background: #6366f1;
  top: -100px; right: -100px;
  animation: floatShape 20s ease-in-out infinite;
}
.shape-2 {
  width: 350px; height: 350px;
  background: #a855f7;
  bottom: -80px; left: -80px;
  animation: floatShape 25s ease-in-out infinite reverse;
}
.shape-3 {
  width: 250px; height: 250px;
  background: #3b82f6;
  top: 50%; left: 50%;
  animation: floatShape 18s ease-in-out infinite;
}

@keyframes floatShape {
  0%, 100% { transform: translate(0, 0) scale(1); }
  33% { transform: translate(30px, -30px) scale(1.1); }
  66% { transform: translate(-20px, 20px) scale(0.95); }
}

/* Card */
.accept-card {
  max-width: 440px;
  width: 100%;
  padding: 48px 40px;
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 24px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 1;
  animation: cardEntrance 0.5s ease;
}

@keyframes cardEntrance {
  from { opacity: 0; transform: translateY(16px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.state-content {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.state-content h3 {
  color: white;
  font-size: 1.375rem;
  font-weight: 800;
}

.state-content p {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.9375rem;
  line-height: 1.5;
  max-width: 300px;
}

/* Loader */
.loader-ring {
  margin-bottom: 8px;
}
.loader-circle {
  animation: loaderSpin 1.2s linear infinite;
  transform-origin: center;
}
@keyframes loaderSpin {
  to { transform: rotate(360deg); }
}

/* Result Icons */
.result-icon {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
  animation: popIn 0.4s ease;
}

@keyframes popIn {
  0% { transform: scale(0); opacity: 0; }
  60% { transform: scale(1.15); }
  100% { transform: scale(1); opacity: 1; }
}

.success-icon {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  box-shadow: 0 8px 24px rgba(34, 197, 94, 0.3);
}

.error-icon {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  box-shadow: 0 8px 24px rgba(239, 68, 68, 0.3);
}

/* Buttons */
.btn-lg {
  padding: 12px 28px;
  font-size: 0.9375rem;
  border-radius: 12px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 8px;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-lg:hover { transform: translateY(-2px); }
</style>
