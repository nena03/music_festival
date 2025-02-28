<?php
session_start();
require 'db_connection.php';


if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}


function validate_tickets($data) {
    return filter_var($data, FILTER_VALIDATE_INT);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $num_tickets = $_POST['num_tickets'];

    if (!validate_tickets($num_tickets) || $num_tickets < 1) {
        echo "Nevažeći broj karata!";
        exit();
    }

    
    $query = "INSERT INTO ticket_purchases (user_id, num_tickets) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $num_tickets]);

    echo "Ulaznice su uspešno kupljene!";
}
?>

<form method="POST">
    <input type="number" name="num_tickets" min="1" required placeholder="Broj karata" />
    <input type="submit" value="Kupite karte" />
</form>
