<?php
require_once 'config.php';

$code = isset($_GET['code']) ? $_GET['code'] : '';

if (empty($code)) {
    sendResponse(false, null, 'يرجى إدخال الرمز');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM admin_passcodes WHERE code = ? AND CURDATE() BETWEEN start_date AND end_date");
$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    sendResponse(true, ['authenticated' => true]);
} else {
    sendResponse(false, null, 'رمز غير صحيح أو منتهي الصلاحية');
}
?>
