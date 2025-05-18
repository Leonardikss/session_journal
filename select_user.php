<?php
session_start();
require_once 'db.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

  if (isset($_GET['admin']) && $_GET['admin'] == 1) {
    ?>
    <!DOCTYPE html>
      <html lang="ru">
      <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <link rel="stylesheet" href="css/style.css">
          <title>Ввод пароля</title>
      </head>
      <body>
          <div class="container">
              <h2>Введите пароль администратора</h2>
              <form action="select_user.php?id=<?php echo $user_id; ?>&admin=1" method="post">
                  <input type="password" name="password" placeholder="Пароль" required>
                  <button type="submit">Войти</button>
              </form>
          </div>
      </body>
      </html>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];

        $sql = "SELECT pass FROM users WHERE id = $user_id";
        $result = queryDB($sql);
        if ($result->num_rows == 1) {
          $row = $result->fetch_assoc();
            if ($row['pass'] === $password) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['is_admin'] = true;
                header("Location: admin_users.php");
                exit();
            } else {
                echo "<p class='container'>Неверный пароль.</p>";
            }
        } else {
            echo "<p class='container'>Пользователь не найден.</p>";
        }
    }
    exit();
  } else if ($_GET['admin'] == 3) {
        $_SESSION['user_id'] = $user_id;
        header("Location: dashboard.php?admin=".$_GET['admin']);
        exit();
  } else {
        $sql = "SELECT telegram_id FROM users WHERE id = $user_id";
        $result = queryDB($sql);
        $row = $result->fetch_assoc()['telegram_id'];
        if (!isset($row) || $_GET['tid']==$row) {
            
        $sql = "UPDATE users SET telegram_id=".$_GET['tid']." WHERE id = $user_id";
        $result = queryDB($sql);
        
        $_SESSION['user_id'] = $user_id;
        header("Location: dashboard.php?admin=".$_GET['admin']);
        exit();
        } else {
            header("Location: index.php");
            exit();
        }
  }

} else {
    header("Location: index.php");
    exit();
}
?>