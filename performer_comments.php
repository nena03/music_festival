<?php
session_start(); 
require_once 'config.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}
if (isset($_GET['festival_id'])) {
    $festival_id = intval($_GET['festival_id']);
} else {
    
    header("Location: festival1.php");
    exit();
}


if (isset($_GET['performer_id'])) {
    $performer_id = intval($_GET['performer_id']);
} else {
    header("Location: festival1.php");
    exit();
}
if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);
} else {
    header("Location: festival1.php");
    exit();
}



try {
    $stmt = $conn->prepare("
    SELECT comments.content, users.username, events.name AS event_name
    FROM comments
    JOIN users ON comments.user_id = users.user_id
    JOIN events ON comments.event_id = events.event_id
    WHERE comments.performer_id = :performer_id
    ");
    $stmt->bindParam(':performer_id', $performer_id);
    $stmt->execute();
    $content = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {
    die("Greška prilikom učitavanja komentara: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Komentari</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            flex-direction: column;
        }
        .comment-box {
            background: white;
            border-radius: 15px;
            padding: 15px;
            width: 300px;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
        .comment-box input {
            width: 100%;
            padding: 8px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .comment-box button {
            margin-top: 10px;
            padding: 8px 12px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    
    <div id="commentsContainer">
        <?php foreach ($content as $content): ?>
            <div class="comment-box">
                <strong><?= htmlspecialchars($content['event_name']) ?></strong>
                <ul>
                    <li><strong><?= htmlspecialchars($content['username']) ?>:</strong> <?= htmlspecialchars($content['content']) ?></li>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        function addComment() {
            let commentText = document.getElementById("commentInput").value;
            if (commentText.trim() === "") return;
            
            let commentBox = document.createElement("div");
            commentBox.classList.add("comment-box");
            commentBox.textContent = commentText;
            
            document.getElementById("commentsContainer").appendChild(commentBox);
            document.getElementById("commentInput").value = "";
        }
    </script>
</body>
</html>
