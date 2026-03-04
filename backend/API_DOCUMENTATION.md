# 📘 TaskFlow API Documentation

> RESTful API for the TaskFlow SaaS work management platform.  
> Built with **Laravel 12** + **Sanctum** token authentication.

**Base URL:** `http://localhost:8000/api`

---

## Table of Contents

- [Authentication](#authentication)
  - [Register](#register)
  - [Login](#login)
  - [Logout](#logout)
  - [Get Current User](#get-current-user)
- [Workspaces](#workspaces)
  - [List Workspaces](#list-workspaces)
  - [Create Workspace](#create-workspace)
  - [Show Workspace](#show-workspace)
  - [Update Workspace](#update-workspace)
  - [Delete Workspace](#delete-workspace)
- [Invitations](#invitations)
  - [Send Invitation](#send-invitation)
  - [Accept Invitation](#accept-invitation)
  - [List Invitations](#list-invitations)
  - [Cancel Invitation](#cancel-invitation)
- [Labels](#labels)
  - [List Labels](#list-labels)
  - [Create Label](#create-label)
  - [Update Label](#update-label)
  - [Delete Label](#delete-label)
- [Boards](#boards)
  - [List Boards](#list-boards)
  - [Create Board](#create-board)
  - [Show Board](#show-board)
  - [Update Board](#update-board)
  - [Delete Board](#delete-board)
- [Columns](#columns)
  - [Create Column](#create-column)
  - [Update Column](#update-column)
  - [Reorder Columns](#reorder-columns)
  - [Delete Column](#delete-column)
- [Cards](#cards)
  - [Create Card](#create-card)
  - [Show Card](#show-card)
  - [Update Card](#update-card)
  - [Move Card](#move-card)
  - [Reorder Cards](#reorder-cards)
  - [Delete Card](#delete-card)
- [Comments](#comments)
  - [List Comments](#list-comments)
  - [Add Comment](#add-comment)
  - [Delete Comment](#delete-comment)
- [Dashboard](#dashboard)
  - [Get Stats](#get-stats)
  - [Get Activity](#get-activity)
- [Real-time Events](#real-time-events)
- [Error Handling](#error-handling)

---

## Authentication

All protected endpoints require a Bearer token in the `Authorization` header:

```
Authorization: Bearer {token}
```

### Register

Create a new user account.

```
POST /api/register
```

**Body:**

| Field                | Type   | Required | Rules                     |
| -------------------- | ------ | -------- | ------------------------- |
| `name`               | string | ✅       | max: 255                  |
| `email`              | string | ✅       | valid email, unique       |
| `password`           | string | ✅       | min: 8, must be confirmed |
| `password_confirmation` | string | ✅    | must match `password`     |

**Response:** `201 Created`

```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com",
    "created_at": "2026-03-04T10:00:00.000000Z",
    "updated_at": "2026-03-04T10:00:00.000000Z"
  },
  "token": "1|abc123def456..."
}
```

---

### Login

Authenticate and receive an API token. Revokes all previous tokens.

```
POST /api/login
```

**Body:**

| Field      | Type   | Required |
| ---------- | ------ | -------- |
| `email`    | string | ✅       |
| `password` | string | ✅       |

**Response:** `200 OK`

```json
{
  "user": {
    "id": 1,
    "name": "João Silva",
    "email": "joao@example.com"
  },
  "token": "2|xyz789ghi012..."
}
```

**Error:** `422 Unprocessable Entity`

```json
{
  "message": "As credenciais estão incorretas.",
  "errors": {
    "email": ["As credenciais estão incorretas."]
  }
}
```

---

### Logout

🔒 **Requires authentication**

Revoke the current access token.

```
POST /api/logout
```

**Response:** `200 OK`

```json
{
  "message": "Logout realizado com sucesso."
}
```

---

### Get Current User

🔒 **Requires authentication**

Retrieve the authenticated user's information.

```
GET /api/user
```

**Response:** `200 OK`

```json
{
  "id": 1,
  "name": "João Silva",
  "email": "joao@example.com",
  "created_at": "2026-03-04T10:00:00.000000Z",
  "updated_at": "2026-03-04T10:00:00.000000Z"
}
```

---

## Workspaces

### List Workspaces

🔒 **Requires authentication**

List all workspaces the authenticated user belongs to.

```
GET /api/workspaces
```

**Response:** `200 OK`

```json
[
  {
    "id": 1,
    "name": "Minha Empresa",
    "slug": "minha-empresa",
    "description": "Workspace principal",
    "owner_id": 1,
    "boards_count": 3,
    "members_count": 5,
    "created_at": "2026-03-04T10:00:00.000000Z"
  }
]
```

---

### Create Workspace

🔒 **Requires authentication**

Create a new workspace. The creator is automatically added as owner.

```
POST /api/workspaces
```

**Body:**

| Field         | Type   | Required | Rules     |
| ------------- | ------ | -------- | --------- |
| `name`        | string | ✅       | max: 255  |
| `description` | string | ❌       | max: 1000 |

**Response:** `201 Created`

```json
{
  "id": 1,
  "name": "Minha Empresa",
  "slug": "minha-empresa",
  "description": "Workspace principal",
  "owner_id": 1,
  "members": [
    {
      "id": 1,
      "name": "João Silva",
      "email": "joao@example.com",
      "pivot": { "role": "owner" }
    }
  ]
}
```

---

### Show Workspace

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

```
GET /api/workspaces/{workspace}
```

**Response:** `200 OK`

```json
{
  "id": 1,
  "name": "Minha Empresa",
  "slug": "minha-empresa",
  "description": "Workspace principal",
  "owner_id": 1,
  "boards_count": 3,
  "members_count": 5,
  "members": [
    { "id": 1, "name": "João Silva", "email": "joao@example.com", "pivot": { "role": "owner" } },
    { "id": 2, "name": "Maria", "email": "maria@example.com", "pivot": { "role": "member" } }
  ],
  "boards": [
    { "id": 1, "name": "Sprint 1", "color": "#6366f1", "is_archived": false }
  ]
}
```

---

### Update Workspace

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
PUT /api/workspaces/{workspace}
```

**Body:**

| Field         | Type   | Required | Rules     |
| ------------- | ------ | -------- | --------- |
| `name`        | string | ❌       | max: 255  |
| `description` | string | ❌       | max: 1000 |

**Response:** `200 OK`

---

### Delete Workspace

🔒 **Requires authentication** · 🛡️ **Policy:** owner only

```
DELETE /api/workspaces/{workspace}
```

**Response:** `204 No Content`

---

## Invitations

### Send Invitation

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Send an email invitation to join a workspace. Expires in 7 days.

```
POST /api/workspaces/{workspace}/invite
```

**Body:**

| Field  | Type   | Required | Rules                         |
| ------ | ------ | -------- | ----------------------------- |
| `email` | string | ✅      | valid email, max: 255         |
| `role`  | string | ❌      | `admin` or `member` (default: `member`) |

**Response:** `201 Created`

```json
{
  "message": "Convite enviado com sucesso.",
  "invitation": {
    "id": 1,
    "email": "convidado@example.com",
    "workspace_id": 1,
    "invited_by": 1,
    "role": "member",
    "status": "pending",
    "token": "abc123...",
    "expires_at": "2026-03-11T10:00:00.000000Z"
  }
}
```

**Errors:**
- `422` — User is already a member
- `422` — Pending invitation already exists

---

### Accept Invitation

🔒 **Requires authentication**

Accept a workspace invitation using its token. The authenticated user's email must match the invitation email.

```
POST /api/invitations/{token}/accept
```

**Response:** `200 OK`

```json
{
  "message": "Convite aceito! Você agora faz parte do workspace.",
  "workspace": { "id": 1, "name": "Minha Empresa" }
}
```

**Errors:**
- `403` — Invitation email doesn't match authenticated user
- `410` — Invitation has expired

---

### List Invitations

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

List pending (non-expired) invitations for a workspace.

```
GET /api/workspaces/{workspace}/invitations
```

**Response:** `200 OK`

```json
[
  {
    "id": 1,
    "email": "convidado@example.com",
    "role": "member",
    "status": "pending",
    "expires_at": "2026-03-11T10:00:00.000000Z",
    "inviter": { "id": 1, "name": "João Silva" }
  }
]
```

---

### Cancel Invitation

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
DELETE /api/workspaces/{workspace}/invitations/{invitation}
```

**Response:** `200 OK`

```json
{
  "message": "Convite cancelado."
}
```

---

## Labels

Labels are scoped to a workspace and can be attached to any card within that workspace.

### List Labels

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

```
GET /api/workspaces/{workspace}/labels
```

**Response:** `200 OK`

```json
[
  { "id": 1, "name": "Bug", "color": "#ef4444", "workspace_id": 1 },
  { "id": 2, "name": "Feature", "color": "#22c55e", "workspace_id": 1 },
  { "id": 3, "name": "Urgente", "color": "#f97316", "workspace_id": 1 }
]
```

---

### Create Label

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
POST /api/workspaces/{workspace}/labels
```

**Body:**

| Field   | Type   | Required | Rules                       |
| ------- | ------ | -------- | --------------------------- |
| `name`  | string | ✅       | max: 50                     |
| `color` | string | ✅       | hex color, e.g. `#ef4444`   |

**Response:** `201 Created`

```json
{
  "id": 4,
  "name": "Design",
  "color": "#8b5cf6",
  "workspace_id": 1
}
```

---

### Update Label

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
PUT /api/workspaces/{workspace}/labels/{label}
```

**Body:**

| Field   | Type   | Required | Rules                       |
| ------- | ------ | -------- | --------------------------- |
| `name`  | string | ❌       | max: 50                     |
| `color` | string | ❌       | hex color, e.g. `#ef4444`   |

**Response:** `200 OK`

---

### Delete Label

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Deletes the label and removes it from all associated cards.

```
DELETE /api/workspaces/{workspace}/labels/{label}
```

**Response:** `204 No Content`

---

## Boards

### List Boards

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

```
GET /api/workspaces/{workspace}/boards
```

**Query Parameters:**

| Param      | Type    | Default | Description                    |
| ---------- | ------- | ------- | ------------------------------ |
| `archived` | boolean | `false` | If `true`, return only archived boards |

**Response:** `200 OK`

```json
[
  {
    "id": 1,
    "name": "Sprint 1",
    "description": "Sprint de março",
    "color": "#6366f1",
    "is_archived": false,
    "workspace_id": 1,
    "columns_count": 4,
    "cards_count": 12
  }
]
```

---

### Create Board

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Creates a board with **4 default columns**: "A Fazer", "Em Progresso", "Revisão", "Concluído".

```
POST /api/workspaces/{workspace}/boards
```

**Body:**

| Field         | Type   | Required | Rules                     |
| ------------- | ------ | -------- | ------------------------- |
| `name`        | string | ✅       | max: 255                  |
| `description` | string | ❌       | max: 1000                 |
| `color`       | string | ❌       | hex color, e.g. `#6366f1` |

**Response:** `201 Created`

```json
{
  "id": 1,
  "name": "Sprint 1",
  "description": null,
  "color": "#6366f1",
  "is_archived": false,
  "columns": [
    { "id": 1, "name": "A Fazer", "position": 0, "color": "#94a3b8" },
    { "id": 2, "name": "Em Progresso", "position": 1, "color": "#3b82f6" },
    { "id": 3, "name": "Revisão", "position": 2, "color": "#f59e0b" },
    { "id": 4, "name": "Concluído", "position": 3, "color": "#22c55e" }
  ]
}
```

---

### Show Board

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

Returns the board with all columns and cards (fully nested), including each card's assignee, labels, creator, and comment count.

```
GET /api/workspaces/{workspace}/boards/{board}
```

**Response:** `200 OK`

```json
{
  "id": 1,
  "name": "Sprint 1",
  "description": "Sprint de março",
  "color": "#6366f1",
  "columns": [
    {
      "id": 1,
      "name": "A Fazer",
      "position": 0,
      "color": "#94a3b8",
      "cards": [
        {
          "id": 1,
          "title": "Implementar login",
          "description": "Usar Sanctum para auth",
          "priority": "high",
          "position": 0,
          "due_date": "2026-03-10",
          "completed_at": null,
          "column_id": 1,
          "assigned_to": 2,
          "created_by": 1,
          "comments_count": 3,
          "assignee": { "id": 2, "name": "Maria", "email": "maria@example.com" },
          "creator": { "id": 1, "name": "João Silva" },
          "labels": [
            { "id": 1, "name": "Feature", "color": "#22c55e" }
          ]
        }
      ]
    }
  ]
}
```

---

### Update Board

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
PUT /api/workspaces/{workspace}/boards/{board}
```

**Body:**

| Field          | Type    | Required | Rules                     |
| -------------- | ------- | -------- | ------------------------- |
| `name`         | string  | ❌       | max: 255                  |
| `description`  | string  | ❌       | max: 1000                 |
| `color`        | string  | ❌       | hex color, e.g. `#6366f1` |
| `is_archived`  | boolean | ❌       |                           |

**Response:** `200 OK`

---

### Delete Board

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Deletes the board and all its columns/cards (CASCADE).

```
DELETE /api/workspaces/{workspace}/boards/{board}
```

**Response:** `204 No Content`

---

## Columns

### Create Column

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Position is automatically set to the end.

```
POST /api/workspaces/{workspace}/boards/{board}/columns
```

**Body:**

| Field   | Type   | Required | Rules                     |
| ------- | ------ | -------- | ------------------------- |
| `name`  | string | ✅       | max: 255                  |
| `color` | string | ❌       | hex color, e.g. `#3b82f6` |

**Response:** `201 Created`

```json
{
  "id": 5,
  "name": "Backlog",
  "position": 4,
  "color": null,
  "board_id": 1,
  "cards": []
}
```

---

### Update Column

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
PUT /api/workspaces/{workspace}/boards/{board}/columns/{column}
```

**Body:**

| Field   | Type   | Required | Rules                     |
| ------- | ------ | -------- | ------------------------- |
| `name`  | string | ❌       | max: 255                  |
| `color` | string | ❌       | hex color                 |

**Response:** `200 OK`

---

### Reorder Columns

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Batch reorder all columns in a board. Uses optimized SQL `CASE WHEN` for O(k) updates.

```
PUT /api/workspaces/{workspace}/boards/{board}/columns/reorder
```

**Body:**

| Field         | Type     | Required | Rules                              |
| ------------- | -------- | -------- | ---------------------------------- |
| `ordered_ids` | int[]    | ✅       | array of column IDs in new order   |

**Example:**

```json
{
  "ordered_ids": [3, 1, 4, 2]
}
```

**Response:** `200 OK`

```json
{
  "message": "Colunas reordenadas com sucesso."
}
```

---

### Delete Column

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Deletes the column and all its cards (CASCADE).

```
DELETE /api/workspaces/{workspace}/boards/{board}/columns/{column}
```

**Response:** `204 No Content`

---

## Cards

### Create Card

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Position is automatically set to the end of the column.

```
POST /api/workspaces/{workspace}/boards/{board}/columns/{column}/cards
```

**Body:**

| Field         | Type     | Required | Rules                                    |
| ------------- | -------- | -------- | ---------------------------------------- |
| `title`       | string   | ✅       | max: 255                                 |
| `description` | string   | ❌       | max: 5000                                |
| `assigned_to` | int      | ❌       | must exist in `users` table              |
| `priority`    | string   | ❌       | `low`, `medium`, `high`, `urgent` (default: `medium`) |
| `due_date`    | date     | ❌       | format: `YYYY-MM-DD`, must be ≥ today   |
| `label_ids`   | int[]    | ❌       | array of label IDs                       |

**Response:** `201 Created`

```json
{
  "id": 1,
  "title": "Implementar login",
  "description": null,
  "priority": "medium",
  "position": 0,
  "due_date": null,
  "completed_at": null,
  "column_id": 1,
  "assigned_to": null,
  "created_by": 1,
  "assignee": null,
  "creator": { "id": 1, "name": "João Silva" },
  "labels": []
}
```

---

### Show Card

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

```
GET /api/workspaces/{workspace}/boards/{board}/cards/{card}
```

**Response:** `200 OK`

```json
{
  "id": 1,
  "title": "Implementar login",
  "description": "Usar Sanctum para autenticação",
  "priority": "high",
  "position": 0,
  "due_date": "2026-03-10",
  "completed_at": null,
  "column_id": 1,
  "assigned_to": 2,
  "created_by": 1,
  "assignee": { "id": 2, "name": "Maria", "email": "maria@example.com" },
  "creator": { "id": 1, "name": "João Silva" },
  "labels": [
    { "id": 1, "name": "Feature", "color": "#22c55e" }
  ],
  "comments": [
    {
      "id": 1,
      "body": "Revisado e aprovado!",
      "created_at": "2026-03-04T12:00:00.000000Z",
      "user": { "id": 2, "name": "Maria" }
    }
  ],
  "column": { "id": 1, "name": "A Fazer" }
}
```

---

### Update Card

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
PUT /api/workspaces/{workspace}/boards/{board}/cards/{card}
```

**Body:**

| Field         | Type     | Required | Rules                                    |
| ------------- | -------- | -------- | ---------------------------------------- |
| `title`       | string   | ❌       | max: 255                                 |
| `description` | string   | ❌       | max: 5000                                |
| `assigned_to` | int      | ❌       | must exist in `users` table              |
| `priority`    | string   | ❌       | `low`, `medium`, `high`, `urgent`        |
| `due_date`    | date     | ❌       | format: `YYYY-MM-DD`                     |
| `completed`   | boolean  | ❌       | sets `completed_at` to now or null       |
| `label_ids`   | int[]    | ❌       | syncs labels (replaces all)              |

> **Note:** The `completed` field is a convenience boolean. When `true`, `completed_at` is set to the current timestamp. When `false`, `completed_at` is set to `null`.

**Response:** `200 OK`

📡 **Broadcasts:** `CardUpdated` event on `private-board.{boardId}`

---

### Move Card

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Move a card to a different column and/or position. Used for drag & drop between columns.

```
PUT /api/workspaces/{workspace}/boards/{board}/cards/{card}/move
```

**Body:**

| Field       | Type | Required | Rules                              |
| ----------- | ---- | -------- | ---------------------------------- |
| `column_id` | int  | ✅       | target column (must belong to board) |
| `position`  | int  | ✅       | target position (0-indexed)        |

**Response:** `200 OK`

📡 **Broadcasts:** `BoardUpdated` event on `private-board.{boardId}`

---

### Reorder Cards

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

Batch reorder cards within a single column. Uses optimized SQL `CASE WHEN` for O(k) updates.

```
PUT /api/workspaces/{workspace}/boards/{board}/columns/{column}/cards/reorder
```

**Body:**

| Field         | Type     | Required | Rules                           |
| ------------- | -------- | -------- | ------------------------------- |
| `ordered_ids` | int[]    | ✅       | array of card IDs in new order  |

**Response:** `200 OK`

```json
{
  "message": "Cards reordenados com sucesso."
}
```

📡 **Broadcasts:** `BoardUpdated` event on `private-board.{boardId}`

---

### Delete Card

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
DELETE /api/workspaces/{workspace}/boards/{board}/cards/{card}
```

**Response:** `204 No Content`

📡 **Broadcasts:** `BoardUpdated` event on `private-board.{boardId}`

---

## Comments

### List Comments

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

Paginated list (20 per page), newest first.

```
GET /api/workspaces/{workspace}/boards/{board}/cards/{card}/comments
```

**Query Parameters:**

| Param  | Type | Default | Description     |
| ------ | ---- | ------- | --------------- |
| `page` | int  | `1`     | Page number     |

**Response:** `200 OK` (Paginated)

```json
{
  "data": [
    {
      "id": 1,
      "body": "Revisado e aprovado!",
      "card_id": 1,
      "user_id": 2,
      "created_at": "2026-03-04T12:00:00.000000Z",
      "user": { "id": 2, "name": "Maria", "email": "maria@example.com" }
    }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 20,
  "total": 1
}
```

---

### Add Comment

🔒 **Requires authentication** · 🛡️ **Policy:** owner or admin

```
POST /api/workspaces/{workspace}/boards/{board}/cards/{card}/comments
```

**Body:**

| Field  | Type   | Required | Rules    |
| ------ | ------ | -------- | -------- |
| `body` | string | ✅       | max: 5000 |

**Response:** `201 Created`

```json
{
  "id": 2,
  "body": "Precisa de ajustes no layout.",
  "card_id": 1,
  "user_id": 1,
  "created_at": "2026-03-04T14:30:00.000000Z",
  "user": { "id": 1, "name": "João Silva", "email": "joao@example.com" }
}
```

📡 **Broadcasts:** `CardUpdated` event on `private-board.{boardId}`

---

### Delete Comment

🔒 **Requires authentication**

Only the comment author or workspace admin/owner can delete.

```
DELETE /api/workspaces/{workspace}/boards/{board}/cards/{card}/comments/{comment}
```

**Response:** `204 No Content`

---

## Dashboard

### Get Stats

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

Returns aggregated statistics for the workspace. **Cached for 60 seconds** in Redis.

```
GET /api/workspaces/{workspace}/dashboard/stats
```

**Response:** `200 OK`

```json
{
  "total_cards": 45,
  "completed_cards": 18,
  "overdue_cards": 3,
  "completion_rate": 40.0,
  "cards_by_column": [
    { "name": "A Fazer", "count": 12 },
    { "name": "Em Progresso", "count": 8 },
    { "name": "Revisão", "count": 7 },
    { "name": "Concluído", "count": 18 }
  ],
  "cards_by_priority": [
    { "priority": "low", "count": 10 },
    { "priority": "medium", "count": 20 },
    { "priority": "high", "count": 10 },
    { "priority": "urgent", "count": 5 }
  ],
  "completed_per_day": [
    { "date": "2026-03-01", "count": 3 },
    { "date": "2026-03-02", "count": 5 },
    { "date": "2026-03-03", "count": 2 }
  ],
  "created_per_day": [
    { "date": "2026-03-01", "count": 4 },
    { "date": "2026-03-02", "count": 6 },
    { "date": "2026-03-03", "count": 3 }
  ],
  "member_productivity": [
    { "name": "João Silva", "completed": 8 },
    { "name": "Maria", "completed": 6 },
    { "name": "Pedro", "completed": 4 }
  ]
}
```

---

### Get Activity

🔒 **Requires authentication** · 🛡️ **Policy:** member of workspace

Returns the last 50 activity log entries for the workspace.

```
GET /api/workspaces/{workspace}/dashboard/activity
```

**Response:** `200 OK`

```json
[
  {
    "id": 1,
    "user_id": 1,
    "workspace_id": 1,
    "action": "card_created",
    "description": "criou um card",
    "subject_type": "App\\Models\\Card",
    "subject_id": 5,
    "properties": { "column": "A Fazer" },
    "created_at": "2026-03-04T14:30:00.000000Z",
    "user": { "id": 1, "name": "João Silva", "email": "joao@example.com" }
  }
]
```

**Action types:**

| Action           | Description             |
| ---------------- | ----------------------- |
| `board_created`  | criou um board          |
| `board_deleted`  | deletou um board        |
| `card_created`   | criou um card           |
| `card_updated`   | atualizou um card       |
| `card_moved`     | moveu um card           |
| `card_deleted`   | deletou um card         |
| `comment_added`  | adicionou um comentário |

---

## Real-time Events

TaskFlow uses **Pusher** via Laravel Broadcasting for real-time updates.

### Channels

| Channel                      | Type    | Description                      |
| ---------------------------- | ------- | -------------------------------- |
| `private-board.{boardId}`    | Private | Board-level updates              |

### Events

#### `BoardUpdated`

Fired when columns or cards are structurally changed (created, deleted, moved, reordered).

```json
{
  "event": "BoardUpdated",
  "channel": "private-board.1",
  "data": {
    "board": { "id": 1, "name": "Sprint 1" }
  }
}
```

#### `CardUpdated`

Fired when a card is updated or a comment is added.

```json
{
  "event": "CardUpdated",
  "channel": "private-board.1",
  "data": {
    "card": { "id": 5, "title": "Implementar login" },
    "board_id": 1
  }
}
```

---

## Error Handling

All errors follow a consistent format.

### Validation Error — `422 Unprocessable Entity`

```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email has already been taken."]
  }
}
```

### Unauthorized — `401 Unauthorized`

```json
{
  "message": "Unauthenticated."
}
```

### Forbidden — `403 Forbidden`

```json
{
  "message": "This action is unauthorized."
}
```

### Not Found — `404 Not Found`

```json
{
  "message": "No query results for model [App\\Models\\Card] 999."
}
```

### Gone — `410 Gone`

```json
{
  "message": "Este convite expirou."
}
```

---

## Authorization Matrix

| Resource        | View       | Create/Update | Delete     |
| --------------- | ---------- | ------------- | ---------- |
| Workspace       | Any member | Owner + Admin | Owner only |
| Board           | Any member | Owner + Admin | Owner + Admin |
| Column          | Any member | Owner + Admin | Owner + Admin |
| Card            | Any member | Owner + Admin | Owner + Admin |
| Comment         | Any member | Any member    | Author or Admin |
| Label           | Any member | Owner + Admin | Owner + Admin |
| Invitation      | Any member | Owner + Admin | Owner + Admin |
| Dashboard       | Any member | —             | —          |

---

## Rate Limits

Laravel's default throttle middleware applies:
- **60 requests/minute** for authenticated users
- **10 requests/minute** for guest endpoints (login/register)

---

> **Version:** 1.0.0  
> **Last updated:** March 4, 2026
