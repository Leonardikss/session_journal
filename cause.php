<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'db.php';

$admin = $_GET['admin'];
$group_id = $_GET['group_id'];

foreach($_POST as $id => $value) {
    echo $id.' ';
    echo $value.' | ';
$sql = "UPDATE evaluations SET cause=".$value." WHERE id=".$id;
$result = queryDB($sql);
}

header("Location: info".($admin==1?'2':'').".php?admin=".$admin."&group_id=".$group_id);