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

$date = $_POST['date'] ?? ($_GET['date'] ?? null);

if ($date) {
    $dateTimeString =  strlen($date)==10?$date.' 23:59:59':$date;
} else {
    $dateTime = new DateTime();
    $dateTimeString = $dateTime->format('Y-m-d 23:59:59');
    $dateTimeString2 = $dateTime->format('Y-m-d 00:00:00');
}

$time = $_POST['time'] ?? ($_GET['time'] ?? 0);
$groupId = $_POST['group_id'] ?? $_GET['group_id'];

if ($time=='all') {
    $sql = "SELECT * FROM evaluations WHERE group_id=".$groupId;
    $result = queryDB($sql);
} else {

$dateTime = new DateTime($dateTimeString); 
$dateTime->modify('-'.$time.' day');
$dateTimeString2 = $dateTime->format('Y-m-d 00:00:00');

$sql = "SELECT * FROM evaluations WHERE group_id=".$groupId." AND date>'".$dateTimeString2."' AND date<'".$dateTimeString."'";
$result = queryDB($sql);
}


$sqlStudents = "SELECT id, name FROM students WHERE group_id = ".$groupId;
$studentsResult = queryDB($sqlStudents);

$students = [];
while($student = $studentsResult->fetch_assoc()){
     $students[$student['id']] = $student['name'];
}

$sqlTeachers = "SELECT id, username FROM users";
$TeachersResult = queryDB($sqlTeachers);

$Teachers = [];
while($Teacher = $TeachersResult->fetch_assoc()){
    $Teachers[$Teacher['id']] = $Teacher['username'];
} 

$couple = ['1 пара ' => ['08:00', '09:35'],
           '2 пара ' => ['09:45', '11:20'],
           '3 пара ' => ['11:50', '13:25'],
           '4 пара ' => ['13:55', '15:30'],
           '5 пара ' => ['15:40', '17:15'],
           '6 пара ' => ['17:25', '19:00']];

$AllEvaluations = [];
$columns = [];
while ($res = $result->fetch_assoc()) {
    if (!isset($AllEvaluations[$res['student_id']])) {
        $AllEvaluations[$res['student_id']] = [];
    }
    $resdate = new DateTime($res['date']);
    if (isset($Teachers[$res['user_id']])) {
        $teacher = $Teachers[$res['user_id']];
    } else {
        $teacher = '[удаленный преподаватель]';
    }
    
    if ($time==30 || $time=='all') {
        $AllEvaluations[$res['student_id']][substr($res['date'], 0, 10) ][] = ["value" => $res['evaluation'][2], "id" => $res['id'], "cause" => $res['cause']] ?? null;
        $columns[substr($res['date'], 0, 10)] = 1;
        continue;
    }
    $is_coupe = false;
    foreach ($couple as $name => $coupe_time) {
        if (isTimeBetween($resdate, $coupe_time[0], $coupe_time[1])) {
        $AllEvaluations[$res['student_id']][$name.substr($res['date'], 0, 10). "\n".$teacher][] = ["value" => $res['evaluation'][2], "id" => $res['id'], "cause" => $res['cause']] ?? null;
        $columns[$name.substr($res['date'], 0, 10). "\n".$teacher] = 1;
        $is_coupe = true;
        }
    } 
    if ($is_coupe==false) {
    $AllEvaluations[$res['student_id']]["Вне пар ".substr($res['date'], 0, 10). "\n".$teacher][] = ["value" => $res['evaluation'][2], "id" => $res['id'], "cause" => $res['cause']] ?? null;
    $columns["Вне пар ".substr($res['date'], 0, 10). "\n".$teacher] = 1;
        
    }
}
// print_r($AllEvaluations);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Evaluations</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 800px; 
    width: 100%;
    margin: auto;
    overflow: auto;
}

.container table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.container table, .container th, .container td {
    border: 1px solid #ddd;
    text-align: center;
}

.container th, .container td {
    padding: 8px;
}
.button-row {
    display: flex;
}

.button-row button {
    display: inline-block;
    margin-right: 5px;
}
    </style>
</head>
<body>
    <?php if (!empty($students)): ?>
    <div class="container">
        <h1><?php echo $time!="all" ? substr($dateTimeString, 0, 10) : "За все время"?></h1>
        <!-- дата -->
        <form method="post">
            <input type="hidden" name="group_id" value="<?php echo htmlspecialchars($groupId); ?>">
            <label for="date">Выберите дату:</label>
            <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($dateTimeString); ?>">
            <button type="submit">Применить</button>
            <input type="hidden" name="time" value=<?php echo $time; ?> />
        </form>
        <form method="post" class="button-row">
            <button name="time" type="submit" value=0>За день</button>
            <button name="time" type="submit" value=7>За неделю</button>
            <button name="time" tye="submit" value=30>За месяц</button>
            <button name="time" type="submit" value='all'>За все время</button>
            <input type="hidden" name="date" value=<?php echo $dateTimeString; ?> />
            <input type="hidden" name="group_id" value=<?php echo $groupId; ?> />
        </form>
        <form method="post" action="cause.php?admin=<?php echo $_GET['admin']; ?>&group_id=<?php echo $groupId; ?>">
        <table id='table', name='table'>
            <thead>
                <tr>
                    <th>Студент</th>
                    <?php foreach ($columns as $column => $value): ?>
                        <th><?php echo $column; ?></th>
                    <?php endforeach; ?>
                    <th>Всего</th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($students as $studentId => $studentName): ?>
        <tr>
            <td><?php echo htmlspecialchars($studentName); ?></td>
            <?php foreach ($columns as $column => $value): ?>
            <?php if (isset($AllEvaluations[$studentId][$column][0]['cause']) && $AllEvaluations[$studentId][$column][0]['cause']==1) {
                        $color="#00ff00";
                    } else if (isset($AllEvaluations[$studentId][$column][0]['cause'])) {
                        $color="#ff0000";
                    } else {
                        $color="#ffffff";
                    }
                    ?>
                <td style='background-color: <?php echo $color;?>'>
                    <?php
                        if (isset($AllEvaluations[$studentId][$column])) {
                            foreach ($AllEvaluations[$studentId][$column] as $n_id) {
                                echo " ".$n_id['value']." ";
                                if ($_GET['admin'] != 3) {
                                echo '<a class="button-link" style="padding: 0px; width: 10px; height: 25px; display: inline;" href="delevaluation.php?id='.$n_id['id'].'&admin='.$_GET['admin'].'&group_id='.$groupId.'&date='.$dateTimeString.'&time='.$time.'">удалить</a>';
                                }
                            }
                            if ($_GET['admin'] != 3) {
                            echo ' <select name='.$AllEvaluations[$studentId][$column][0]['id'].'>
                            <option style="color: #000000; background-color:  #ff0000;" value=0>не уваж.</option>
                            <option style="color: #000000; background-color: #00ff00;" '.(($AllEvaluations[$studentId][$column][0]['cause']==1)?"selected":"").' value=1>уваж.</option></select>';
                            }
                        } else {
                            echo "-";
                        }
                    ?>
                </td>
            <?php endforeach; ?>
             <td><?php if (isset($AllEvaluations[$studentId])) {
                    $count = 0;
                    foreach ($AllEvaluations[$studentId] as $student) {
                       $count += count($student);
                    }
                    echo $count;
             } ?></td>
        </tr>
    <?php endforeach; ?>
</tbody>
        </table>
        <br></br>
        <button type="submit">Сохранить</button>
        </form>
        <form method="post" action="dashboard.php?admin=<?php echo $_GET['admin'];?>">
        <button type="submit">Назад</button>
        </form>
    </div>
     <?php else: ?>
         <p>нет студентов</p>
     <?php endif; ?>
</body>
</html>