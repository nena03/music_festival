<?php
include('db_connection.php');

session_start();
if (!isset($_SESSION['user_id'])) {
    die("Prvo morate da se prijavite.");
}
if (isset($_GET['festival_id'])) {
    $festival_id = intval($_GET['festival_id']); 
    $_SESSION['festival_id']=$festival_id;

} else {
    die("Festival ID nije prosleđen!");
}

if (isset($_POST['id'])) {
    $notification_id = $_POST['id'];
    $user_id = $_SESSION['user_id']; 
    $stmt = $conn->prepare("UPDATE performer_notifications SET notified = TRUE WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notification_id, $user_id); 

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            header("Location: festival1.php?festival_id=" . $festival_id);
            exit();
        } else {
            echo "Greška pri označavanju obaveštenja kao pročitano.";
        }
    } else {
        echo "Greška pri izvršavanju upita.";
    }
} else {
    echo "Nema ID obaveštenja.";
}
?>
