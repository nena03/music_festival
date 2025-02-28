<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}


$query = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['user_email']]);
$user = $stmt->fetch();

?>

<h1>Profil Korisnika</h1>
<p>Ime: <?php echo htmlspecialchars($user['username']); ?></p>
<p>Email: <?php echo htmlspecialchars($user['email']); ?></p>

<form action="edit_profile.php" method="POST">
    <input type="submit" value="Izmeni profil" />
</form>
