<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';
$user_id = $_SESSION['user_id'];
$sql = "SELECT role FROM users WHERE id = $user_id";
$result = queryDB($sql);
if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
    if ($row['role'] !== 'admin') {
        header("Location: index.php");
        exit();
    }
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "DELETE FROM users WHERE id = $user_id";
    $result = queryDB($sql);
}
header("Location: admin_users.php");
?>