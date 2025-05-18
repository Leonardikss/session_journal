<?php

function isTimeBetween (DateTime $timeToCheck, string $startTime, string $endTime): bool {
  $timeToCheckTimestamp = $timeToCheck->getTimestamp();
  $startTimeTimestamp = strtotime($timeToCheck->format('Y-m-d') . ' ' . $startTime);
  $endTimeTimestamp = strtotime($timeToCheck->format('Y-m-d') . ' ' . $endTime);
   if ($endTimeTimestamp < $startTimeTimestamp) {
        return $timeToCheckTimestamp >= $startTimeTimestamp || $timeToCheckTimestamp <= $endTimeTimestamp;
    }
    return $timeToCheckTimestamp >= $startTimeTimestamp && $timeToCheckTimestamp <= $endTimeTimestamp;
};

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
require_once 'db.php';
$group_id = $_POST['group_id']; 

$sql = "SELECT id, name FROM students WHERE group_id = $group_id";
$result = queryDB($sql);
$students = [];
if($result){
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

$dateTime = new DateTime(); 

$couple = ['1 пара ' => ['08:00', '09:44'],
           '2 пара ' => ['09:45', '11:49'],
           '3 пара ' => ['11:50', '13:54'],
           '4 пара ' => ['13:55', '15:39'],
           '5 пара ' => ['15:40', '17:24'],
           '6 пара ' => ['17:25', '19:00'],
           'вне пар' => ['19:01', '23.59'],
           'вне пар2' => ['00:00', '07:59']];

foreach ($couple as $name => $couple_time) {
    if (isTimeBetween($dateTime, $couple_time[0], $couple_time[1])) {
    $dateTimeString = $dateTime->format('Y-m-d '.$couple_time[0].':00');
    $dateTimeString2 = $dateTime->format('Y-m-d '.$couple_time[1].':00');
    }
}

$sql = "SELECT * FROM evaluations WHERE group_id=".$group_id." AND user_id=".$_SESSION['user_id']." AND date>'".$dateTimeString."' AND date<'".$dateTimeString2."'";
$result = queryDB($sql);

$AllEvaluations = [];
while($row = $result->fetch_assoc()) {
    if(!isset($AllEvaluations[$row['student_id']])) {
        $AllEvaluations[$row['student_id']] = [];
    }
    if ($row['evaluation'][0] == '1') {
    $AllEvaluations[$row['student_id']][1] = $row;
} else {
    $AllEvaluations[$row['student_id']][2] = $row;
}
}
$_POST['AllEvaluations'] = $AllEvaluations;

$sql = "SELECT name FROM groups WHERE id = $group_id";
$result = queryDB($sql);
$group = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Посещаемость группы <?php echo $group['name'];?></h2>
        <form id="gradeForm" method="post" action="submit_grades.php?group_id=<?php echo $group_id ?>">
            <input name="dateTimeString" value='<?php echo $dateTimeString?>' type="hidden" />
            <input name="dateTimeString2" value='<?php echo $dateTimeString2?>' type="hidden" />
            <table id="gradeTable">
                <thead>
                    <tr>
                        <th>Студент</th>
                        <th>Первая половина</th>
                        <th>Вторая половина</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td>
                                <select name="grade_<?php echo (isset($AllEvaluations[$student['id']][1])?'1':'0').$student['id']; ?>">
                                    <option value=""></option>
                                    <option value="H" <?php  echo isset($AllEvaluations[$student['id']][1])?"selected":''?>>Н</option>
                                </select>
                            </td>
                            <td>
                                <select name="2grade_<?php echo (isset($AllEvaluations[$student['id']][2])?'1':'0').$student['id']; ?>">
                                    <option value=""></option>
                                    <option value="H" <?php  echo isset($AllEvaluations[$student['id']][2])?"selected":''?>>Н</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($students)): ?>
                        <tr><td colspan="2">В этой группе нет студентов.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br></br>
            <button type="submit">Сохранить</button>
            </form>
            <form method="post" action="dashboard.php?admin=0">
            <button type="submit">Назад</button>
        </form>
    </div>
</body>
</html>