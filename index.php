<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id']) && null !== $_GET['tid']) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT role FROM users WHERE id = $user_id";
    $result = queryDB($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['role'] == 'admin') {
            header("Location: admin_users.php");
            exit();
        } else {
            if (!isset($_GET['tid'])) {
                header("Location: logout.php");
                exit();
            }
            $sql = "SELECT * FROM users WHERE telegram_id =".$_GET['tid'];
            $result = queryDB($sql);
            if ($result) {
            header("Location: dashboard.php?admin=0");
            exit();
            }
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
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 20px;
}

.container {
    max-width: 600px;
    margin: auto;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    color: #333;
}

ul {
    list-style-type: none;
    padding: 0;
}

li {
    margin: 10px 0;
}

a {
    text-decoration: none;
    color: #4337a0; /* Цвет ссылки */
    padding: 10px;
    display: block; /* Чтобы ссылка занимала всю ширину */
    border-radius: 5px;
    transition: background-color 0.3s, color 0.3s; /* Плавный переход */
}

a:hover {
    background-color: #4337a0; /* Цвет фона при наведении */
    color: white; /* Цвет текста при наведении */
}

.admin {
    font-weight: bold; /* Выделение администраторов */
    color: #FF5733; /* Цвет для администраторов */
}
    </style>
    <script src="https://telegram.org/js/telegram-web-app.js?56"></script>
    <script>
    let tg = window.Telegram.WebApp;
    let tid = tg.initDataUnsafe.user.id
    </script>
    <?php
    if (!isset($_GET['tid'])) {
    echo '<script type="text/javascript">';
    echo 'document.location.href="' . $_SERVER['REQUEST_URI'] . '?tid=" + tid';
    echo '</script>';
}
?>
</head>
<body>
    <div class="container">
        <h2>Выберите пользователя</h2>
        <form method="post" class="button-row">
        <input type="text" id="qwery" name="query" value='' placeholder="Поиск">
        <button type="submit">Искать</button>
        </form>
        <?php
        $query = isset($_POST['query']) ? $_POST['query'] : '';

            $sql = "SELECT id, username, role FROM users WHERE username LIKE '%".$query."%'";
            $result = queryDB($sql);
            if ($result->num_rows > 0) {
                echo '<ul>';
                while ($row = $result->fetch_assoc()) {
                  if ($row['role'] == 'admin'){
                      echo '<li><a href="select_user.php?id=' . $row['id'] . '&tid='.$_GET['tid'].'&admin=1">' . $row['username'] . ' (Админ)</a></li>';
                  } else if ($row['role'] == 'tutor') {
                      echo '<li><a href="select_user.php?id=' . $row['id'] . '&tid='.$_GET['tid'].'&admin=2">' . $row['username'] . ' (Куратор)</a></li>';
                  } else if ($row['role'] == 'spectater') {
                      echo '<li><a href="select_user.php?id=' . $row['id'] . '&tid='.$_GET['tid'].'&admin=3">' . $row['username'] . ' (Наблюдатель)</a></li>';
                  } else {
                      echo '<li><a href="select_user.php?id=' . $row['id'] . '&tid='.$_GET['tid'].'">' . $row['username'] . '</a></li>';
                  }

                }
                echo '</ul>';
            } else {
                echo "<p>Нет доступных пользователей.</p>";
            }
        ?>
    </div>
</body>
</html>