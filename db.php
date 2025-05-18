<?php
require_once 'config.php';

function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }
    return $conn;
}

function closeDB($conn) {
    $conn->close();
}

function queryDB($sql) {
  $conn = connectDB();
  $result = $conn->query($sql);
  if ($result == false) {
      $error = mysqli_error($conn);
      closeDB($conn);
      return $error;
  }
  closeDB($conn);
  return $result;
}
?>