<?php
require_once 'config.php';

$room_code = isset($_GET['room_code']) ? $_GET['room_code'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (empty($room_code)) sendResponse(false, null, 'Missing room code');

$db = getDB();

if ($action === 'send') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $client_id = $data['clientId'];
    $player_name = $data['playerName'];
    $team = $data['team'];
    $timestamp = $data['timestamp'];

    // For buzzing, we can just append to a dedicated queue in rooms or update a field
    // But for simplicity in this implementation, we'll let the ADMIN handle the "buzz queue"
    // by polling a small temporary table or just adding to room_state.
    
    // Actually, let's update the room's buzz_queue field (handling JSON array)
    $stmt = $db->prepare("SELECT game_state FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $state = json_decode($row['game_state'], true);
        if (!$state) $state = [];
        if (!isset($state['buzzQueue'])) $state['buzzQueue'] = [];
        
        // Prevent duplicates
        $exists = false;
        foreach($state['buzzQueue'] as $buzz) {
            if ($buzz['clientId'] === $client_id) { $exists = true; break; }
        }
        
        if (!$exists) {
            $state['buzzQueue'][] = [
                'clientId' => $client_id,
                'playerName' => $player_name,
                'team' => $team,
                'timestamp' => $timestamp
            ];
            $json_update = json_encode($state);
            $upd = $db->prepare("UPDATE rooms SET game_state = ? WHERE room_code = ?");
            $upd->bind_param("ss", $json_update, $room_code);
            $upd->execute();
            sendResponse(true);
        } else {
            sendResponse(true); // Already buzzed
        }
    }
}
?>
