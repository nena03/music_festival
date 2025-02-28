<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $festival_id = $_GET['id'];

    $query = "DELETE FROM festivals WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$festival_id]);

    echo "Festival je uspešno obrisan!";
} else {
    echo "Festival ID nije prosleđen!";
}
?>
