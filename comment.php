<?php

require 'db_connection.php'; 
function validate_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data); // Zaštita od XSS
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $comment = validate_input($_POST['comment']);

    $query = "INSERT INTO comments (user_id, comment) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $comment]);

    echo "Komentar uspešno dodat!";
}
?>

<form method="POST">
    <textarea name="comment" required placeholder="Write your comment..."></textarea>
    <input type="submit" value="Post Comment" />
</form>
