<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

$id = $_GET['id'];
$admin = $_GET['admin'];
$group_id = $_GET['group_id'];
$time = $_GET['time'];
$dateTimeString = $_GET['date'];

$sql = "SELECT * FROM evaluations WHERE id=".$id;
$result = queryDB($sql);

if ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
    $date = $row['date'];
    $delete = new DateTime();
    $delete_date = $delete->format('Y-m-d H:i:s');
}

$sql = "DELETE FROM evaluations WHERE id=".$id;
$result = queryDB($sql);

$sql = "INSERT INTO tutor_delete_orders (user_id, student_id, date, delete_date) VALUES (".$_SESSION['user_id'].", ".$student_id.", '".$date."', '".$delete_date."')";
$result = queryDB($sql);

header('Location: info'.($admin==1?'2':'').'.php?admin='.$admin.'&group_id='.$group_id.'&date='.$dateTimeString.'&time='.$time);