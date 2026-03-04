import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

let echoInstance = null

export function getEcho() {
  if (echoInstance) return echoInstance

  const token = localStorage.getItem('token')

  echoInstance = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'app-key',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    forceTLS: true,
    authEndpoint: '/api/broadcasting/auth',
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    },
  })

  return echoInstance
}

/**
 * Subscribe to real-time board updates
 * O(1) — single WebSocket subscription
 *
 * @param {number} boardId
 * @param {Function} onBoardUpdated - callback when board/columns change
 * @param {Function} onCardUpdated  - callback when a card changes
 * @returns {Function} unsubscribe function
 */
export function subscribeToBoardUpdates(boardId, onBoardUpdated, onCardUpdated) {
  const echo = getEcho()

  const channel = echo.private(`board.${boardId}`)
    .listen('.board.updated', (event) => {
      if (onBoardUpdated) onBoardUpdated(event)
    })
    .listen('.card.updated', (event) => {
      if (onCardUpdated) onCardUpdated(event)
    })

  // Return unsubscribe function
  return () => {
    echo.leave(`board.${boardId}`)
  }
}

export function disconnectEcho() {
  if (echoInstance) {
    echoInstance.disconnect()
    echoInstance = null
  }
}
