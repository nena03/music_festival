<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = $_POST['username'];
    $new_email = $_POST['new_email'];

    
    $sql = "UPDATE users SET email = :new_email WHERE username = :username";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':new_email', $new_email);
    $stmt->bindParam(':username', $username);

    if ($stmt->execute()) {
        echo "Podaci su uspešno ažurirani!";
    } else {
        echo "Došlo je do greške pri ažuriranju podataka.";
    }
}
?>
