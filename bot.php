<?php
require_once 'db.php';

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    $botToken = $_GET['token'];
    $apiUrl = 'https://api.telegram.org/bot' . $botToken;

    $update = json_decode(file_get_contents('php://input'), true);

    $chatId = $update['message']['chat']['id'];
    $sql = "INSERT INTO chats (chat_id, bot_token) VALUES ('{$chatId}', '{$botToken}')";
    $result = queryDB($sql);
    sendMessage($chatId, "Вы зарегистрированы!");
}

function mailing($group_id, $student_id) {
    $sql = "SELECT bot_token FROM groups WHERE id = {$group_id}";
    $result = queryDB($sql);
    $botToken = $result->fetch_assoc()['bot_token'];
    $apiUrl = 'https://api.telegram.org/bot' . $botToken;
    
    $sql = "SELECT chat_id FROM chats WHERE bot_token = '{$botToken}'";
    $result = queryDB($sql);
    while ($row = $result->fetch_assoc()) {
        $sql = "SELECT name FROM students WHERE id = {$student_id}";
        $res = queryDB($sql);
        $name = $res->fetch_assoc()['name'];
        $url = $apiUrl . '/sendMessage?chat_id=' . $row['chat_id'] . '&text=' . $name . ' отсутствует';
        file_get_contents($url);
    }
}

function sendMessage($chatId, $text) {
    global $apiUrl;
    $url = $apiUrl . '/sendMessage?chat_id=' . $chatId . '&text=' . urlencode($text);
    file_get_contents($url);
}

?>