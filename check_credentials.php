<?php
session_start();

include('./db_connection.php');

$usuario = $_POST['usuario'];
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
$stmt->bindParam(':usuario', $usuario);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $password === $user['password']) {
    $_SESSION['user'] = $user;
    echo json_encode(array("success" => true, "redirect" => "./user_registration.php"));
    exit();
} else {
    echo json_encode(array("success" => false));
    exit();
}
?>
