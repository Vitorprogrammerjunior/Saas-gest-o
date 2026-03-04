<template>
  <div class="page">
    <div class="page-header">
      <div>
        <h2 class="gradient-title">Dashboard</h2>
        <p class="text-muted">Visão geral da produtividade</p>
      </div>
      <div class="workspace-select-wrap">
        <svg class="select-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
        <select v-model="selectedWorkspace" class="workspace-select" @change="fetchDashboard">
          <option v-for="ws in workspaces" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
        </select>
        <svg class="select-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </div>
    </div>

    <!-- Loading Skeletons -->
    <div v-if="loading" class="dash-loading">
      <div class="stats-grid">
        <div v-for="n in 4" :key="n" class="stat-card-skel">
          <div class="skeleton" style="width:44px;height:44px;border-radius:14px;"></div>
          <div style="flex:1">
            <div class="skeleton" style="width:50%;height:28px;border-radius:8px;margin-bottom:6px;"></div>
            <div class="skeleton" style="width:75%;height:14px;border-radius:6px;"></div>
          </div>
        </div>
      </div>
      <div class="charts-grid" style="margin-top:24px;">
        <div v-for="n in 2" :key="n" class="skeleton" style="height:280px;border-radius:16px;"></div>
      </div>
    </div>

    <template v-else-if="stats">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div v-for="(stat, i) in statCards" :key="stat.label" class="stat-card" :style="{ animationDelay: `${i * 0.08}s` }">
          <div class="stat-icon-wrap" :style="{ background: stat.bg }">
            <svg v-if="stat.icon === 'cards'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="stat.color" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/></svg>
            <svg v-else-if="stat.icon === 'check'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="stat.color" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <svg v-else-if="stat.icon === 'clock'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="stat.color" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <svg v-else-if="stat.icon === 'percent'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="stat.color" stroke-width="2"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
          </div>
          <div>
            <span class="stat-value">{{ stat.value }}</span>
            <span class="stat-label">{{ stat.label }}</span>
          </div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="charts-grid">
        <div class="chart-card" style="animation-delay:0.1s">
          <div class="chart-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
            <h4>Cards por Coluna</h4>
          </div>
          <Bar v-if="columnChartData" :data="columnChartData" :options="barOptions" />
        </div>

        <div class="chart-card" style="animation-delay:0.15s">
          <div class="chart-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><line x1="12" y1="12" x2="12" y2="2"/><line x1="12" y1="12" x2="22" y2="12"/></svg>
            <h4>Distribuição por Prioridade</h4>
          </div>
          <Doughnut v-if="priorityChartData" :data="priorityChartData" :options="doughnutOptions" />
        </div>

        <div class="chart-card chart-wide" style="animation-delay:0.2s">
          <div class="chart-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <h4>Tendência de Conclusão (7 dias)</h4>
          </div>
          <Line v-if="trendChartData" :data="trendChartData" :options="lineOptions" />
        </div>

        <div class="chart-card" style="animation-delay:0.25s">
          <div class="chart-header">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <h4>Produtividade por Membro</h4>
          </div>
          <Bar v-if="memberChartData" :data="memberChartData" :options="horizontalBarOptions" />
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="activity-card">
        <div class="chart-header" style="margin-bottom:16px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          <h4>Atividade Recente</h4>
        </div>
        <div class="activity-feed">
          <div v-for="(activity, i) in activities" :key="activity.id" class="activity-item" :style="{ animationDelay: `${i * 0.05}s` }">
            <div class="activity-dot-line">
              <div class="activity-dot" :style="{ background: getUserColor(activity.user?.name) }"></div>
              <div v-if="i < activities.length - 1" class="activity-line"></div>
            </div>
            <div class="avatar avatar-sm" :style="{ background: getUserColor(activity.user?.name) }">
              {{ activity.user?.name?.[0] || '?' }}
            </div>
            <div class="activity-content">
              <div class="activity-text">
                <strong>{{ activity.user?.name }}</strong>
                <span>{{ activity.description }}</span>
              </div>
              <span class="activity-time">{{ formatRelative(activity.created_at) }}</span>
            </div>
          </div>
          <div v-if="!activities.length" class="empty-activity">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--gray-300)" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <p>Nenhuma atividade recente</p>
          </div>
        </div>
      </div>
    </template>

    <!-- Empty State -->
    <div v-else class="empty-state">
      <div class="empty-icon-wrap">
        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
      </div>
      <h3>Selecione um workspace</h3>
      <p>Escolha um workspace para ver o dashboard.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Bar, Doughnut, Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler,
} from 'chart.js'
import api from '@/services/api'
import { useWorkspaceStore } from '@/stores/workspace'

ChartJS.register(
  CategoryScale, LinearScale, BarElement, ArcElement,
  PointElement, LineElement, Title, Tooltip, Legend, Filler
)

const workspaceStore = useWorkspaceStore()
const workspaces = ref([])
const selectedWorkspace = ref(null)
const stats = ref(null)
const activities = ref([])
const loading = ref(false)

const statCards = computed(() => {
  if (!stats.value) return []
  return [
    { icon: 'cards', value: stats.value.total_cards, label: 'Total de Cards', bg: 'rgba(99,102,241,0.1)', color: '#6366f1' },
    { icon: 'check', value: stats.value.completed_cards, label: 'Concluídos', bg: 'rgba(34,197,94,0.1)', color: '#22c55e' },
    { icon: 'clock', value: stats.value.overdue_cards, label: 'Atrasados', bg: 'rgba(239,68,68,0.1)', color: '#ef4444' },
    { icon: 'percent', value: `${stats.value.completion_rate}%`, label: 'Taxa de Conclusão', bg: 'rgba(245,158,11,0.1)', color: '#f59e0b' },
  ]
})

onMounted(async () => {
  await workspaceStore.fetchWorkspaces()
  workspaces.value = workspaceStore.workspaces
  if (workspaces.value.length) {
    selectedWorkspace.value = workspaces.value[0].id
    await fetchDashboard()
  }
})

async function fetchDashboard() {
  if (!selectedWorkspace.value) return
  loading.value = true
  try {
    const [statsRes, activityRes] = await Promise.all([
      api.get(`/workspaces/${selectedWorkspace.value}/dashboard/stats`),
      api.get(`/workspaces/${selectedWorkspace.value}/dashboard/activity`),
    ])
    stats.value = statsRes.data.data || statsRes.data
    activities.value = activityRes.data.data || activityRes.data || []
  } finally {
    loading.value = false
  }
}

const columnChartData = computed(() => {
  if (!stats.value?.cards_by_column) return null
  const data = stats.value.cards_by_column
  return {
    labels: data.map(c => c.name || c.column),
    datasets: [{
      label: 'Cards',
      data: data.map(c => c.count || c.total),
      backgroundColor: ['#6366f1', '#3b82f6', '#f59e0b', '#22c55e', '#ec4899', '#8b5cf6'],
      borderRadius: 10,
      borderSkipped: false,
    }],
  }
})

const priorityChartData = computed(() => {
  if (!stats.value?.cards_by_priority) return null
  const data = stats.value.cards_by_priority
  const colorMap = { low: '#22c55e', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' }
  const labelMap = { low: 'Baixa', medium: 'Média', high: 'Alta', urgent: 'Urgente' }
  return {
    labels: data.map(p => labelMap[p.priority] || p.priority),
    datasets: [{
      data: data.map(p => p.count || p.total),
      backgroundColor: data.map(p => colorMap[p.priority] || '#999'),
      borderWidth: 0,
      hoverOffset: 8,
    }],
  }
})

const trendChartData = computed(() => {
  if (!stats.value?.completed_per_day) return null
  const completed = stats.value.completed_per_day
  const created = stats.value.created_per_day || []
  return {
    labels: completed.map(d => {
      const date = new Date(d.date)
      return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' })
    }),
    datasets: [
      {
        label: 'Concluídos',
        data: completed.map(d => d.count || d.total),
        borderColor: '#22c55e',
        backgroundColor: 'rgba(34, 197, 94, 0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#22c55e',
        pointRadius: 4,
        pointHoverRadius: 6,
      },
      {
        label: 'Criados',
        data: created.map(d => d.count || d.total),
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.08)',
        fill: true,
        tension: 0.4,
        pointBackgroundColor: '#6366f1',
        pointRadius: 4,
        pointHoverRadius: 6,
      },
    ],
  }
})

const memberChartData = computed(() => {
  if (!stats.value?.member_productivity) return null
  const data = stats.value.member_productivity
  return {
    labels: data.map(m => m.name),
    datasets: [{
      label: 'Cards Concluídos',
      data: data.map(m => m.completed || m.count),
      backgroundColor: '#6366f1',
      borderRadius: 10,
      borderSkipped: false,
    }],
  }
})

const chartFont = { family: 'Inter, system-ui, sans-serif', size: 12 }

const barOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: { legend: { display: false }, tooltip: { cornerRadius: 8, padding: 10, bodyFont: chartFont } },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1, font: chartFont }, grid: { color: 'rgba(0,0,0,0.04)' } },
    x: { ticks: { font: chartFont }, grid: { display: false } },
  },
}

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: true,
  cutout: '65%',
  plugins: { legend: { position: 'bottom', labels: { padding: 16, font: chartFont, usePointStyle: true, pointStyle: 'circle' } }, tooltip: { cornerRadius: 8, padding: 10 } },
}

const lineOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: { legend: { position: 'top', labels: { font: chartFont, usePointStyle: true, pointStyle: 'circle', padding: 16 } }, tooltip: { cornerRadius: 8, padding: 10 } },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1, font: chartFont }, grid: { color: 'rgba(0,0,0,0.04)' } },
    x: { ticks: { font: chartFont }, grid: { display: false } },
  },
}

const horizontalBarOptions = {
  responsive: true,
  maintainAspectRatio: true,
  indexAxis: 'y',
  plugins: { legend: { display: false }, tooltip: { cornerRadius: 8, padding: 10 } },
  scales: {
    x: { beginAtZero: true, ticks: { stepSize: 1, font: chartFont }, grid: { color: 'rgba(0,0,0,0.04)' } },
    y: { ticks: { font: chartFont }, grid: { display: false } },
  },
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
.gradient-title {
  background: linear-gradient(135deg, var(--primary) 0%, #a855f7 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Workspace Select */
.workspace-select-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.select-icon {
  position: absolute;
  left: 14px;
  color: var(--gray-400);
  pointer-events: none;
  z-index: 1;
}
.select-chevron {
  position: absolute;
  right: 14px;
  color: var(--gray-400);
  pointer-events: none;
}
.workspace-select {
  appearance: none;
  padding: 10px 40px 10px 42px;
  border: 1.5px solid var(--gray-200);
  border-radius: 12px;
  background: white;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--gray-700);
  cursor: pointer;
  min-width: 240px;
  transition: all 0.2s;
  font-family: inherit;
}
.workspace-select:focus {
  outline: none;
  border-color: var(--primary-400);
  box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
}

/* Loading */
.dash-loading { animation: fadeIn 0.3s ease; }
.stat-card-skel {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 22px;
  background: white;
  border-radius: 16px;
  border: 1px solid var(--gray-100);
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-bottom: 24px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 22px;
  background: white;
  border-radius: 16px;
  border: 1px solid var(--gray-100);
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  animation: fadeInUp 0.4s ease both;
  transition: all 0.25s;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(0,0,0,0.06);
}

.stat-icon-wrap {
  width: 50px;
  height: 50px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stat-value {
  display: block;
  font-size: 1.75rem;
  font-weight: 800;
  color: var(--gray-900);
  line-height: 1;
}

.stat-label {
  font-size: 0.8125rem;
  color: var(--gray-500);
  font-weight: 500;
  margin-top: 2px;
  display: block;
}

/* Charts */
.charts-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 24px;
}

.chart-card {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  animation: fadeInUp 0.4s ease both;
  transition: box-shadow 0.25s;
}
.chart-card:hover {
  box-shadow: 0 6px 16px rgba(0,0,0,0.06);
}

.chart-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}
.chart-header h4 {
  font-size: 0.9375rem;
  font-weight: 700;
  color: var(--gray-700);
}

.chart-wide {
  grid-column: span 2;
}

/* Activity */
.activity-card {
  background: white;
  border: 1px solid var(--gray-100);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  animation: fadeInUp 0.5s ease both;
}

.activity-feed {
  display: flex;
  flex-direction: column;
  gap: 0;
  max-height: 420px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 0;
  animation: fadeInUp 0.3s ease both;
}

.activity-dot-line {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 6px;
  min-width: 12px;
}
.activity-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
}
.activity-line {
  width: 2px;
  flex: 1;
  background: var(--gray-100);
  margin-top: 4px;
  min-height: 24px;
}

.activity-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.activity-text {
  font-size: 0.875rem;
  line-height: 1.5;
  color: var(--gray-600);
}
.activity-text strong {
  color: var(--gray-800);
  margin-right: 4px;
}
.activity-time {
  font-size: 0.6875rem;
  color: var(--gray-400);
  font-weight: 500;
}

.empty-activity {
  text-align: center;
  padding: 40px 0;
  color: var(--gray-400);
}
.empty-activity p {
  margin-top: 8px;
  font-size: 0.875rem;
}

.empty-icon-wrap {
  width: 80px;
  height: 80px;
  background: rgba(99,102,241,0.08);
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 768px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .charts-grid { grid-template-columns: 1fr; }
  .chart-wide { grid-column: span 1; }
}
</style>
