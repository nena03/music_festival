<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);

    $query = "UPDATE users SET username = ?, email = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$username, $email, $_SESSION['user_email']]);

    echo "Profil je uspešno izmenjen!";
}
?>

<form method="POST">
    <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username']); ?>" />
    <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>" />
    <input type="submit" value="Spasi izmene" />
</form>
