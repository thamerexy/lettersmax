<?php
require_once 'config.php';

$db = getDB();
$result = $db->query("SELECT * FROM questions");

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = [
        'id' => $row['id'],
        'letter' => $row['letter'],
        'question' => $row['question'],
        'answer' => $row['answer']
    ];
}

sendResponse(true, $questions);
?>
