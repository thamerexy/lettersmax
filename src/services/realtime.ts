import { useRoomStore } from '../store/roomStore';
import type { PlayerPresence, BuzzEvent, Team, GameBroadcast } from '../store/roomStore';

const API_BASE = 'https://lettersmax.acamix.com/api';

let syncInterval: ReturnType<typeof setInterval> | null = null;
let playersInterval: ReturnType<typeof setInterval> | null = null;

/**
 * Connect to the GoDaddy PHP backend for this room using Polling.
 */
export const subscribeToRoom = async (roomCode: string, presence: PlayerPresence): Promise<void> => {
  stopPolling();

  // 1. Initial Join
  try {
    // Register as a player in the DB
    await fetch(`${API_BASE}/players.php?room_code=${roomCode}&action=join`, {
      method: 'POST',
      body: JSON.stringify(presence)
    });

    // If Admin, ensure room exists
    if (presence.isAdmin) {
      await fetch(`${API_BASE}/sync.php?room_code=${roomCode}&action=create&admin_id=${presence.clientId}`);
    }

    // 2. Start Polling
    startPolling(roomCode, presence.isAdmin);
    return Promise.resolve();
  } catch (error) {
    console.error("Failed to join room:", error);
    return Promise.reject(error);
  }
};

const startPolling = (roomCode: string, isAdmin: boolean) => {
  // Sync Players List (Common for both)
  playersInterval = setInterval(async () => {
    try {
      const resp = await fetch(`${API_BASE}/players.php?room_code=${roomCode}&action=list`);
      const result = await resp.json();
      if (result.success) {
        useRoomStore.getState().setPlayers(result.data);
      }
    } catch (e) { console.error("Player sync error", e); }
  }, 3000);

  if (isAdmin) {
    // Admin: Push state periodically
    syncInterval = setInterval(async () => {
      const store = useRoomStore.getState();
      const broadcastData: GameBroadcast = {
        board: store.syncedBoard,
        currentTurn: store.syncedTurn,
        team1RoundsWon: store.syncedTeam1Rounds,
        team2RoundsWon: store.syncedTeam2Rounds,
        winner: store.winner,
        matchWinner: store.matchWinner,
        hideQuestionFromPlayers: store.hideQuestionFromPlayers,
        questionActive: store.questionActive,
        currentQuestion: store.currentQuestion,
        answerRevealed: store.answerRevealed,
        awardedTeam: store.awardedTeam,
        revealedAnswer: store.revealedAnswer,
        gamePhase: store.gamePhase,
        buzzQueue: store.buzzQueue
      };

      try {
        await fetch(`${API_BASE}/sync.php?room_code=${roomCode}&action=update`, {
          method: 'POST',
          body: JSON.stringify(broadcastData)
        });
      } catch (e) { console.error("Admin sync push error", e); }
    }, 2000);
  } else {
    // Player: Pull state periodically
    syncInterval = setInterval(async () => {
      try {
        const resp = await fetch(`${API_BASE}/sync.php?room_code=${roomCode}&action=get`);
        const result = await resp.json();
        if (result.success && result.data && result.data.game_state) {
          const state = JSON.parse(result.data.game_state);
          useRoomStore.getState().applyGameBroadcast(state);
        }
      } catch (e) { console.error("Player sync pull error", e); }
    }, 1500);
  }
};

const stopPolling = () => {
  if (syncInterval) clearInterval(syncInterval);
  if (playersInterval) clearInterval(playersInterval);
  syncInterval = null;
  playersInterval = null;
};

export const unsubscribeFromRoom = (): void => {
  stopPolling();
  const store = useRoomStore.getState();
  if (store.roomCode) {
     fetch(`${API_BASE}/players.php?room_code=${store.roomCode}&action=leave&client_id=${store.clientId}`);
  }
};

/** Admin assigns a player to a team. In polling, we just update local state and wait for push. */
export const broadcastTeamAssign = async (clientId: string, team: Team): Promise<void> => {
  const store = useRoomStore.getState();
  store.setPlayers(store.players.map(p => p.clientId === clientId ? { ...p, team } : p));
  // The next syncInterval will push this to the DB via room_state
};

/** Admin broadcasts game state. In polling, we just update local state and wait for push. */
export const broadcastGameState = async (data: GameBroadcast): Promise<void> => {
  const store = useRoomStore.getState();
  store.applyGameBroadcast(data);
  // The next syncInterval will push this to the DB
};

/** Player sends a buzz event. */
export const broadcastBuzz = async (roomCode: string, buzzData: BuzzEvent): Promise<void> => {
  try {
    await fetch(`${API_BASE}/buzz.php?room_code=${roomCode}&action=send`, {
      method: 'POST',
      body: JSON.stringify(buzzData)
    });
  } catch (e) {
    console.error("Buzz send error", e);
  }
};
