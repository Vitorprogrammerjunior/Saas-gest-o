<template>
  <div class="auth-page">
    <div class="bg-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>

    <div class="auth-container">
      <div class="auth-card">
        <div class="auth-header">
          <div class="logo-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 11 12 14 22 4"></polyline>
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
            </svg>
          </div>
          <h1>Criar Conta</h1>
          <p class="auth-subtitle">Comece a gerenciar suas tarefas gratuitamente</p>
        </div>

        <form @submit.prevent="handleRegister" class="auth-form">
          <div class="input-group" :class="{ focused: focusedField === 'name', filled: form.name, error: errors.name }">
            <div class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <input v-model="form.name" type="text" placeholder="Seu nome completo" required @focus="focusedField = 'name'" @blur="focusedField = null" />
            <label>Nome</label>
            <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>
          </div>

          <div class="input-group" :class="{ focused: focusedField === 'email', filled: form.email, error: errors.email }">
            <div class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            </div>
            <input v-model="form.email" type="email" placeholder="seu@email.com" required @focus="focusedField = 'email'" @blur="focusedField = null" />
            <label>Email</label>
            <p v-if="errors.email" class="field-error">{{ errors.email[0] }}</p>
          </div>

          <div class="input-group" :class="{ focused: focusedField === 'password', filled: form.password, error: errors.password }">
            <div class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <input v-model="form.password" :type="showPassword ? 'text' : 'password'" placeholder="Mínimo 8 caracteres" required @focus="focusedField = 'password'" @blur="focusedField = null" />
            <label>Senha</label>
            <button type="button" class="toggle-password" @click="showPassword = !showPassword" tabindex="-1">
              <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
            <p v-if="errors.password" class="field-error">{{ errors.password[0] }}</p>
          </div>

          <div class="input-group" :class="{ focused: focusedField === 'confirm', filled: form.password_confirmation }">
            <div class="input-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <input v-model="form.password_confirmation" type="password" placeholder="Repita a senha" required @focus="focusedField = 'confirm'" @blur="focusedField = null" />
            <label>Confirmar Senha</label>
          </div>

          <button type="submit" class="submit-btn" :disabled="auth.loading" :class="{ loading: auth.loading }">
            <span v-if="auth.loading" class="btn-spinner"></span>
            <span v-else class="btn-content">
              Criar Conta
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
          </button>
        </form>

        <div class="auth-footer">
          <p>Já tem conta? <router-link to="/login" class="auth-link">Fazer login</router-link></p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const router = useRouter()

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const errors = computed(() => auth.errors)
const focusedField = ref(null)
const showPassword = ref(false)

async function handleRegister() {
  const success = await auth.register(form)
  if (success) router.push('/')
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh; display: flex; align-items: center; justify-content: center;
  background: linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%);
  padding: 20px; position: relative; overflow: hidden;
}
.bg-shapes { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
.shape {
  position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.4;
  animation: floatShape 20s ease-in-out infinite;
}
.shape-1 { width: 500px; height: 500px; background: radial-gradient(circle, #764ba2, transparent 70%); top: -150px; left: -100px; }
.shape-2 { width: 400px; height: 400px; background: radial-gradient(circle, #667eea, transparent 70%); bottom: -100px; right: -100px; animation-delay: -7s; }
.shape-3 { width: 300px; height: 300px; background: radial-gradient(circle, #f093fb, transparent 70%); top: 40%; right: 30%; animation-delay: -12s; }
@keyframes floatShape {
  0%, 100% { transform: translate(0, 0) scale(1); }
  25% { transform: translate(30px, -50px) scale(1.1); }
  50% { transform: translate(-20px, 20px) scale(0.95); }
  75% { transform: translate(40px, 30px) scale(1.05); }
}

.auth-container { width: 100%; max-width: 440px; position: relative; z-index: 1; animation: cardEntrance 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); }
@keyframes cardEntrance { from { opacity: 0; transform: translateY(40px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }

.auth-card {
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(24px) saturate(1.8); -webkit-backdrop-filter: blur(24px) saturate(1.8);
  border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 24px;
  padding: 44px 40px;
  box-shadow: 0 32px 64px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.auth-header { text-align: center; margin-bottom: 36px; }
.logo-icon {
  width: 64px; height: 64px; background: var(--gradient-primary); border-radius: 20px;
  display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;
  box-shadow: 0 8px 32px rgba(99, 102, 241, 0.4);
}
.auth-header h1 {
  font-size: 2rem; font-weight: 900;
  background: linear-gradient(135deg, #fff 0%, rgba(255,255,255,0.7) 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.auth-subtitle { color: rgba(255, 255, 255, 0.5); font-size: 0.9375rem; margin-top: 6px; }

.auth-form { margin-bottom: 24px; }
.input-group { position: relative; margin-bottom: 18px; }
.input-group input {
  width: 100%; padding: 14px 16px 14px 48px;
  background: rgba(255, 255, 255, 0.06); border: 1.5px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px; font-size: 0.9375rem; color: white; font-family: inherit;
  transition: all 0.3s ease; outline: none;
}
.input-group input::placeholder { color: rgba(255, 255, 255, 0.25); }
.input-group.focused input { border-color: rgba(99, 102, 241, 0.6); background: rgba(99, 102, 241, 0.08); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }
.input-group.error input { border-color: rgba(239, 68, 68, 0.6); box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1); }
.input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.3); transition: color 0.3s; display: flex; z-index: 1; }
.input-group.focused .input-icon { color: var(--primary-400); }
.input-group label {
  position: absolute; left: 48px; top: -9px; font-size: 0.6875rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255, 255, 255, 0.4);
  padding: 0 6px; opacity: 0; transform: translateY(6px); transition: all 0.3s; pointer-events: none;
}
.input-group.focused label, .input-group.filled label { opacity: 1; transform: translateY(0); color: var(--primary-400); }
.toggle-password {
  position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: rgba(255, 255, 255, 0.3); padding: 4px; display: flex; cursor: pointer;
}
.toggle-password:hover { color: rgba(255, 255, 255, 0.6); }
.field-error { color: #fca5a5; font-size: 0.8125rem; margin-top: 8px; font-weight: 500; padding-left: 4px; }

.submit-btn {
  width: 100%; padding: 15px 24px; background: var(--gradient-primary); background-size: 200% auto;
  color: white; border: none; border-radius: 14px; font-size: 1rem; font-weight: 700;
  font-family: inherit; cursor: pointer; transition: all 0.4s; position: relative; overflow: hidden;
  box-shadow: 0 4px 24px rgba(99, 102, 241, 0.4);
}
.submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(99, 102, 241, 0.5); }
.submit-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-content { display: flex; align-items: center; justify-content: center; gap: 8px; }
.btn-spinner { width: 22px; height: 22px; border: 2.5px solid rgba(255,255,255,0.2); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; margin: 0 auto; }
@keyframes spin { to { transform: rotate(360deg); } }

.auth-footer { text-align: center; font-size: 0.875rem; color: rgba(255, 255, 255, 0.4); }
.auth-link { color: var(--primary-300); font-weight: 600; }
.auth-link:hover { color: var(--primary-200); }
</style>
