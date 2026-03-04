# 🚀 TaskFlow — SaaS de Gestão de Trabalho

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel" />
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?logo=vue.js" />
  <img src="https://img.shields.io/badge/Vite-8-646CFF?logo=vite" />
  <img src="https://img.shields.io/badge/Sanctum-Auth-6366f1" />
  <img src="https://img.shields.io/badge/Pusher-Realtime-300d4f?logo=pusher" />
</p>

Aplicação SaaS completa de gerenciamento de tarefas estilo **Kanban** (inspirada no Trello), desenvolvida com foco em **código limpo**, **otimização Big-O** e **arquitetura profissional**.

---

## 📸 Features

| Feature | Descrição |
|---------|-----------|
| **🔐 Autenticação** | Login/Registro com Laravel Sanctum (token-based SPA) |
| **📋 Kanban Board** | Drag & drop de cards entre colunas (vuedraggable) |
| **⚡ Real-time** | Atualizações em tempo real via WebSocket (Pusher + Laravel Echo) |
| **👥 Workspaces** | Criação de workspaces com convite de membros por email |
| **📊 Dashboard** | Gráficos de produtividade com Chart.js (barras, donut, linhas) |
| **🏷️ Labels** | Sistema de tags coloridas para categorização |
| **💬 Comentários** | Comentários em cards com feed em tempo real |
| **📱 Responsivo** | Interface adaptativa para desktop e mobile |

---

## 🏗️ Arquitetura

```
┌─────────────────┐     ┌─────────────────┐     ┌──────────────┐
│  Vue 3 + Vite   │────▶│  Laravel 12 API │────▶│   SQLite     │
│  (SPA Frontend) │◀────│  (REST + WS)    │◀────│              │
│                 │     │                 │     └──────────────┘
│  • Pinia Store  │     │  • Sanctum Auth │
│  • Vue Router   │     │  • Pusher WS    │     ┌──────────────┐
│  • vuedraggable │     │  • Policies     │────▶│    Redis     │
│  • Chart.js     │     │  • Events       │     │   (Cache)    │
│  • Laravel Echo │     │  • Big-O Opt.   │     └──────────────┘
└─────────────────┘     └─────────────────┘
```

### Otimizações Big-O

| Operação | Complexidade | Técnica |
|----------|-------------|---------|
| Reordenar colunas/cards | **O(k)** | Batch CASE WHEN SQL |
| Mover card entre colunas | **O(1)** | UPDATE direto |
| Dashboard stats | **O(1)** amortizado | Cache 60s com Redis |
| Busca por workspaces | **O(log n)** | Índices compostos |
| Listar cards por coluna | **O(log n)** | Índice position + column_id |

---

## 🗃️ Modelo de Dados (ERD)

```
User ──┬── Workspace (owner)
       └──── workspace_user (pivot: role)
                  │
                  ├── Board
                  │     └── Column (position)
                  │           └── Card (position, priority, due_date)
                  │                 ├── Comment
                  │                 └── card_label (pivot)
                  │
                  ├── Label (color, name)
                  ├── Invitation (token, status, expires_at)
                  └── ActivityLog (polymorphic subject)
```

---

## 🚀 Setup Local

### Pré-requisitos

- PHP 8.2+
- Composer
- Node.js 18+
- npm / yarn

### Backend (Laravel)

```bash
cd backend

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
php artisan key:generate

# Criar banco SQLite
touch database/database.sqlite

# Rodar migrações + seed (dados demo)
php artisan migrate:fresh --seed

# Iniciar servidor
php artisan serve
```

### Frontend (Vue.js)

```bash
cd frontend

# Instalar dependências
npm install

# Iniciar dev server
npm run dev
```

### 🔑 Credenciais Demo

Após rodar o seeder, use:

| Email | Senha | Papel |
|-------|-------|-------|
| `admin@taskflow.com` | `password` | Owner |
| `ana@taskflow.com` | `password` | Admin |
| `pedro@taskflow.com` | `password` | Membro |

---

## � API Documentation (Swagger / OpenAPI)

A API possui documentação interativa completa via **Swagger UI**, onde é possível visualizar, testar e explorar todos os endpoints diretamente no navegador.

```
📍 http://localhost:8000/docs
```

<p align="center">
  <img src="https://img.shields.io/badge/OpenAPI-3.0-6BA539?logo=openapiinitiative" />
  <img src="https://img.shields.io/badge/Swagger_UI-5.x-85EA2D?logo=swagger" />
</p>

**Funcionalidades:**
- 🔍 Visualização de todos os **28 endpoints** organizados por categoria
- 🔐 Botão **Authorize** para testar com token de autenticação
- ▶️ **Try it out** — execute requests reais direto na página
- 📋 Schemas completos de request/response com exemplos
- 🔎 Filtro de busca para encontrar endpoints

> **Nota:** A documentação Swagger está disponível apenas em ambiente de desenvolvimento (`APP_ENV=local`).
> Em produção a rota retorna 404 por segurança.

---

## �📡 API Endpoints

### Autenticação
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `/api/register` | Criar conta |
| POST | `/api/login` | Login (retorna token) |
| POST | `/api/logout` | Logout (revoga token) |
| GET | `/api/user` | Dados do usuário autenticado |

### Workspaces
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/workspaces` | Listar workspaces |
| POST | `/api/workspaces` | Criar workspace |
| GET | `/api/workspaces/{id}` | Detalhe (com membros) |
| PUT | `/api/workspaces/{id}` | Atualizar |
| DELETE | `/api/workspaces/{id}` | Deletar |

### Boards
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/api/workspaces/{ws}/boards` | Listar boards |
| POST | `/api/workspaces/{ws}/boards` | Criar board (+4 colunas padrão) |
| GET | `/api/workspaces/{ws}/boards/{id}` | Board com colunas e cards |
| PUT | `/api/workspaces/{ws}/boards/{id}` | Atualizar |
| DELETE | `/api/workspaces/{ws}/boards/{id}` | Deletar |

### Colunas & Cards
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `.../columns` | Criar coluna |
| PUT | `.../columns/reorder` | Reordenar colunas O(k) |
| POST | `.../columns/{col}/cards` | Criar card |
| PUT | `.../cards/{id}/move` | Mover card entre colunas O(1) |
| PUT | `.../columns/{col}/cards/reorder` | Reordenar cards O(k) |

### Dashboard
| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `.../dashboard/stats` | Estatísticas (cached) |
| GET | `.../dashboard/activity` | Feed de atividades |

### Convites & Labels
| Método | Rota | Descrição |
|--------|------|-----------|
| POST | `.../invitations` | Convidar membro por email |
| POST | `/api/invitations/{token}/accept` | Aceitar convite |
| GET/POST/PUT/DELETE | `.../labels` | CRUD de labels |

---

## 🛠️ Stack Tecnológica

### Backend
- **Laravel 12** — Framework PHP
- **Sanctum 4** — Autenticação SPA token-based
- **Pusher** — WebSocket broadcasting
- **SQLite** — Banco de dados zero-config
- **Redis** — Cache do dashboard
- **Laravel Events** — Broadcast de mudanças em real-time

### Frontend
- **Vue 3** — Composition API
- **Vite** — Build tool
- **Pinia** — State management
- **Vue Router 4** — SPA routing com guards
- **vuedraggable** — Drag & drop nativo
- **Chart.js + vue-chartjs** — Gráficos do dashboard
- **Laravel Echo + Pusher.js** — Real-time client
- **Axios** — HTTP client com interceptors

---

## 📁 Estrutura do Projeto

```
├── backend/
│   ├── app/
│   │   ├── Events/          # BoardUpdated, CardUpdated
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AuthController
│   │   │   ├── BoardController
│   │   │   ├── CardController
│   │   │   ├── ColumnController
│   │   │   ├── CommentController
│   │   │   ├── DashboardController
│   │   │   ├── InvitationController
│   │   │   ├── LabelController
│   │   │   └── WorkspaceController
│   │   ├── Mail/             # WorkspaceInvitation
│   │   ├── Models/           # 9 models com Big-O docs
│   │   └── Policies/        # WorkspacePolicy
│   ├── database/migrations/  # 9 migrations
│   └── routes/
│       ├── api.php           # RESTful API routes
│       └── channels.php      # Broadcast channels
│
├── frontend/
│   └── src/
│       ├── components/       # CardDetailModal
│       ├── layouts/          # AppLayout (sidebar)
│       ├── router/           # Routes + auth guards
│       ├── services/         # api.js, echo.js
│       ├── stores/           # auth, workspace, board (Pinia)
│       └── views/
│           ├── auth/         # Login, Register
│           ├── BoardView     # Kanban drag & drop
│           ├── DashboardView # Charts + stats
│           ├── WorkspacesView
│           └── WorkspaceBoardsView
│
└── README.md
```

---

## ☁️ Deploy no Railway

O projeto está pronto para deploy no [Railway](https://railway.app) com **um único serviço**, usando **Nixpacks** e **SQLite** — sem Docker, sem banco externo, sem CORS.

> O build do frontend (Vue) acontece automaticamente e é servido pelo próprio Laravel.

### Passo a passo

**1. Crie um projeto no Railway:**
```
Railway Dashboard → New Project
```

**2. Crie o serviço:**
```
Add Service → GitHub Repo → Selecione o repo
Root Directory: backend
```

**3. Configure as variáveis de ambiente:**
```env
APP_NAME=TaskFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://SEU-APP.up.railway.app
DB_CONNECTION=sqlite
BCRYPT_ROUNDS=12
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
LOG_CHANNEL=stack
LOG_LEVEL=error
```

**4. Gere o domain público:**
```
Settings → Networking → Generate Domain
```

**5. Atualize a URL** — Copie o domain gerado e coloque em `APP_URL`.

### A cada deploy
O Railway irá automaticamente:
1. Instalar dependências PHP (Composer)
2. Buildar o frontend Vue (`npm run build`)
3. Copiar os assets para o `public/` do Laravel
4. Criar o banco SQLite
5. Rodar migrations + seeder (dados demo)

### Credenciais Demo (após seed)
| Email | Senha | Papel |
|-------|-------|-------|
| `admin@taskflow.com` | `password` | Owner |
| `ana@taskflow.com` | `password` | Admin |
| `pedro@taskflow.com` | `password` | Membro |

> **Nota:** Como o SQLite roda em disco efêmero no Railway, os dados resetam a cada re-deploy. Isso é ideal para portfólio — o recrutador sempre verá dados demo limpos.

---

## 📝 Licença

Projeto para portfólio — uso livre para aprendizado e referência.

**Desenvolvido com** ❤️ **usando Laravel + Vue.js**
