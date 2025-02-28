<?php
session_start();

// Provera CSRF tokena
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
        echo "Forma je uspešno poslata!";
    } else {
        echo "Nevalidan CSRF token!";
        exit();
    }
}
?>

<form method="POST" action="submit_form.php">
    <?php include 'csrf_protection.php'; ?>
    <input type="text" name="user_input" required />
    <input type="submit" value="Submit" />
</form>
