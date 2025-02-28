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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment']) && !empty($_POST['comment'])) {
    $comment = htmlspecialchars($_POST['comment']);
    $performer_id = intval($_POST['performer_id']);
    $event_id = intval($_POST['event_id']);
    $user_id = $_SESSION['user_id']; 

    try {
        $stmt = $conn->prepare("INSERT INTO comments (user_id, performer_id,event_id, content) VALUES (:user_id, :performer_id,:event_id, :content)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':performer_id', $performer_id);
        $stmt->bindParam(':event_id', $event_id);
        $stmt->bindParam(':content', $comment); 
        $stmt->execute();


        header("Location: festival1.php?festival_id=" . $festival_id);
        exit();
    } catch (PDOException $e) {
        die("Greška prilikom unosa komentara: " . $e->getMessage());
    }
} else {
    header("Location: festival1.php?festival_id=" . $festival_id);
    exit();
}
?>
