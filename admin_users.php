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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    if ($role == "admin" && empty($password)) {
            $message = "придумайте пароль для админисратора";
    } else {
        $sql = "INSERT INTO users (username, pass, role) VALUES ('$username', '$password', '$role')";
        if (queryDB($sql) === TRUE) {
            $message =  "Пользователь успешно добавлен.";
        } else {
            $message = "Ошибка при добавлении преподавателя: ".queryDB($sql) ;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Управление преподавателями</title>
</head>
<body>
    <div class="container">
        <h2>Управление преподавателями</h2>
        <form method="post">
            <input type="text" name="username" placeholder="Имя пользователя" required>
            <input type="password" name="password" placeholder="Пароль (только для администраторов)">
            <select name="role">
                <option value="teacher">Преподаватель</option>
                <option value="admin">Администратор</option>
                <option value="tutor">Куратор</option>
                <option value="spectater">Наблюдатель</option>
            </select>
            <button type="submit", name="act", value="01">Добавить</button>
            <p><?php if (isset($message)) echo $message; ?></p>
        </form>
    <h3>Список преподавателей</h3>
        <?php
            $sql = "SELECT id, username, role FROM users";
            $result = queryDB($sql);
            if ($result->num_rows > 0) {
                echo '<ul>';
                while ($row = $result->fetch_assoc()) {
                    echo '<li>'.$row['username'].' - '.$row['role'].' '.'<a href="del.php?id=' . $row['id'] . '">' . 'Удалить</a></li>';
                }
                echo '</ul>';
            } else {
                echo "<p>Нет доступных преподавателей.</p>";
            }
        ?>
    </div>
    <div class="container">
      <nav>
          <ul>
              <li><a href="info2.php?admin=1">Посмотреть успеваемость</a></li>
              <li><a href="logout.php">Выйти</a></li>
          </ul>
      </nav>
    </div>
</body>
</html>