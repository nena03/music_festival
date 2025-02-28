<?php
session_start();
include 'db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Greška u pripremi SQL upita: " . $conn->error);
    }

    $stmt->bind_param("s", $username); 

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email']; 
            $_SESSION['role'] = $user['role']; 

            header("Location: festival.php"); 
            exit();
        } else {
            $error = "Pogrešna lozinka!";
        }
    } else {
        $error = "Korisnik sa ovim korisničkim imenom ne postoji!";
    }

    $stmt->close();
}
?>
