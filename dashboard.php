<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['is_admin']) && $_GET['admin'] == 1) {
    header("Location: logout.php");
    exit();
}

require_once 'db.php';
if ($_GET['admin']==2) {
    $sql = "SELECT id, name FROM groups WHERE user_id=".$_SESSION['user_id'];
} else if ($_GET['admin']==3) {
    $sql = "SELECT id, name FROM groups WHERE id=16";
} else {
    $sql = "SELECT id, name FROM groups";
}
$result = queryDB($sql);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Выбор группы</title>
     <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
    <form method="post" action=<?php echo $_GET['admin'] ? "info.php?admin=".$_GET['admin'] : "completion.php"?> class="group-form">
      <label for="group">Выберите группу:</label><br></br>
        <select name="group_id" id="group" class="group-select">
          <?php
          if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<option value="' . $row["id"] . '">' . $row["name"] . '</option>';
              }
            } else {
                echo '<option value="">Нет групп</option>';
            }
          ?>
        </select>
        <button type="submit">Выбрать</button>
        </form>
        <form method="post" action="logout.php" class="logout-form">
          <button type="submit">Выйти</button>
        </form>
    </div>
</body>
</html>