<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_email']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $festival_id = $_GET['id'];

    $query = "SELECT * FROM festivals WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$festival_id]);
    $festival = $stmt->fetch();

    if (!$festival) {
        echo "Festival nije pronađen!";
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $festival_name = htmlspecialchars($_POST['name']);
    $festival_date = $_POST['date'];

    $query = "UPDATE festivals SET name = ?, date = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$festival_name, $festival_date, $festival_id]);

    echo "Festival je uspešno izmenjen!";
}
?>

<form method="POST">
    <input type="text" name="name" value="<?php echo htmlspecialchars($festival['name']); ?>" required />
    <input type="date" name="date" value="<?php echo htmlspecialchars($festival['date']); ?>" required />
    <input type="submit" value="Spasi izmene" />
</form>
