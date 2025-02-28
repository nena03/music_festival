<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_GET['festival_id'])) {
    $festival_id = intval($_GET['festival_id']); 
    $_SESSION['festival_id']=$festival_id;

} else {
    die("Festival ID nije prosleđen!");
}

$user_id = $_SESSION['user_id'];
$performer_id = intval($_POST['performer_id']);
$event_id = intval($_POST['event_id']);
$action = $_POST['action'];


try {
    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT IGNORE INTO favourite_performers (user_id, performer_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $performer_id]);
         if ($stmt->rowCount() > 0) {
            $notification_stmt = $conn->prepare("INSERT INTO performer_notifications (user_id, performer_id, event_id) VALUES (?, ?, ?)");
            $notification_stmt->execute([$user_id, $performer_id, $event_id]);
        }
    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM favourite_performers WHERE user_id = ? AND performer_id = ?");
        $stmt->execute([$user_id, $performer_id]);
    }
    header("Location: festival1.php?festival_id=" . $festival_id);
    exit(); 
} catch (PDOException $e) {
    die("Грешка при обради: " . $e->getMessage());
}
