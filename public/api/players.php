<?php
require_once 'config.php';

$room_code = isset($_GET['room_code']) ? $_GET['room_code'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (empty($room_code)) sendResponse(false, null, 'Missing room code');

$db = getDB();

if ($action === 'join') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $client_id = $data['clientId'];
    $name = $data['name'];
    $team = $data['team'];
    $is_admin = $data['isAdmin'] ? 1 : 0;

    $stmt = $db->prepare("REPLACE INTO room_players (room_code, client_id, name, team, is_admin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $room_code, $client_id, $name, $team, $is_admin);
    $stmt->execute();
    sendResponse(true);
} elseif ($action === 'list') {
    $stmt = $db->prepare("SELECT client_id as clientId, name, team, is_admin as isAdmin FROM room_players WHERE room_code = ? AND updated_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $players = [];
    while ($row = $result->fetch_assoc()) {
        $row['isAdmin'] = (bool)$row['isAdmin'];
        $players[] = $row;
    }
    sendResponse(true, $players);
} elseif ($action === 'leave') {
    $client_id = isset($_GET['client_id']) ? $_GET['client_id'] : '';
    $stmt = $db->prepare("DELETE FROM room_players WHERE room_code = ? AND client_id = ?");
    $stmt->bind_param("ss", $room_code, $client_id);
    $stmt->execute();
    sendResponse(true);
}
?>
