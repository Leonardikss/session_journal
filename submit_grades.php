<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'db.php';
require_once 'bot.php';

$group_id = $_GET['group_id']; 
$dateTime = new DateTime();
$dateTimeString = $_POST['dateTimeString'];
$dateTimeString2 = $_POST['dateTimeString2'];
$dateTimeString3 = $dateTime->format('Y-m-d H:i:s');

foreach($_POST as $key => $value){

    if($key[5] == "_" && !empty($value) && $key[6] == '0') {
        $sql = "INSERT INTO evaluations (student_id, group_id, user_id, evaluation, date, cause) VALUES (".substr($key, 7).", ".$group_id.", ".$_SESSION['user_id'].", '1_".$value."', '".$dateTimeString3."', 0)";
        $result = queryDB($sql);
        echo $result;
        mailing($group_id, substr($key, 7));
    } else if($key[5] == "_" && empty($value) && $key[6] == '1') {
        $sql = "DELETE FROM evaluations WHERE student_id=".substr($key, 7)." AND group_id=".$group_id." AND user_id=".$_SESSION['user_id']." AND date>='".$dateTimeString."' AND date<='".$dateTimeString2."'";
        $result = queryDB($sql);
        echo $result;
    } else if($key[6] == "_" && !empty($value) && $key[7] == '0') {
        $sql = "INSERT INTO evaluations (student_id, group_id, user_id, evaluation, date, cause) VALUES (".substr($key, 8).", ".$group_id.", ".$_SESSION['user_id'].", '2_".$value."', '".$dateTimeString3."', 0)";
        $result = queryDB($sql);
        echo $result;
        mailing($group_id, substr($key, 8));
    } else if($key[6] == "_" && empty($value) && $key[7] == '1') {
        $sql = "DELETE FROM evaluations WHERE student_id=".substr($key, 8)." AND group_id=".$group_id." AND user_id=".$_SESSION['user_id']." AND date>='".$dateTimeString."' AND date<='".$dateTimeString2."'";
        $result = queryDB($sql);
        echo $result;
    }
}
$sql = "INSERT INTO teacher_orders (user_id, group_id, date) VALUES (".$_SESSION['user_id'].", ".$group_id.", '".$dateTimeString3."')";
$result = queryDB($sql);

header("Location: dashboard.php?admin=0");
?>