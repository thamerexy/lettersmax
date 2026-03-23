<?php
require_once 'config.php';

$room_code = isset($_GET['room_code']) ? $_GET['room_code'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (empty($room_code)) sendResponse(false, null, 'Missing room code');

$db = getDB();

if ($action === 'create') {
    $admin_id = isset($_GET['admin_id']) ? $_GET['admin_id'] : '';
    // Use ON DUPLICATE KEY UPDATE to handle existing rooms
    $stmt = $db->prepare("INSERT INTO rooms (room_code, admin_id, game_state) VALUES (?, ?, '') ON DUPLICATE KEY UPDATE admin_id = VALUES(admin_id)");
    $stmt->bind_param("ss", $room_code, $admin_id);
    $stmt->execute();
    sendResponse(true);
} elseif ($action === 'update') {
    $json = file_get_contents('php://input');
    $stmt = $db->prepare("UPDATE rooms SET game_state = ?, updated_at = CURRENT_TIMESTAMP WHERE room_code = ?");
    $stmt->bind_param("ss", $json, $room_code);
    $stmt->execute();
    sendResponse(true);
} elseif ($action === 'get') {
    $stmt = $db->prepare("SELECT game_state FROM rooms WHERE room_code = ?");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        sendResponse(true, $row);
    } else {
        sendResponse(false, null, 'Room not found');
    }
}
?>
