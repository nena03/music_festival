<?php

session_start();
require_once 'db_connection.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['role'] !== 'artist') {
    header("Location: unauthorized.php");
    exit;
}

$name = "";
$genre = "";
$bio = "";
$image_url = "";
$message = "";
$events = [];

$events_query = "SELECT event_id, name FROM events";
$events_result = $conn->query($events_query);
if ($events_result) {
    while ($event = $events_result->fetch_assoc()) {
        $events[] = $event;
    }
}

$query = "SELECT performer_id FROM user_performer WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user_performer = $result->fetch_assoc();
    $performer_id = $user_performer['performer_id'];

    $query = "SELECT name, genre, bio, image_url FROM performers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $performer_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $performer = $result->fetch_assoc();
        $name = $performer['name'];
        $genre = $performer['genre'];
        $bio = $performer['bio'];
        $image_url = $performer['image_url'];
    }

    $comments_query = "SELECT content, created_at FROM comments WHERE performer_id = ?";
    $stmt = $conn->prepare($comments_query);
    $stmt->bind_param("i", $performer_id);
    $stmt->execute();
    $comments_result = $stmt->get_result();

    $comments = [];
    while ($comment = $comments_result->fetch_assoc()) {
        $comments[] = $comment;
    }

    $ratings_query = "SELECT AVG(rating) as average_rating, events.name as event_name FROM event_ratings 
                      JOIN events ON event_ratings.event_id = events.event_id 
                      WHERE event_ratings.event_id IN (SELECT event_id FROM event_performers WHERE performer_id = ?) 
                      GROUP BY events.name";
    $stmt = $conn->prepare($ratings_query);
    $stmt->bind_param("i", $performer_id);
    $stmt->execute();
    $ratings_result = $stmt->get_result();

    $ratings = [];
    while ($rating = $ratings_result->fetch_assoc()) {
        $ratings[] = $rating;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $event_id = $_POST['event_id'] ?? null;

    if ($name && $genre && $bio) 
    {
        $query_check_name = "SELECT id FROM performers WHERE name = ?";
        $stmt_check_name = $conn->prepare($query_check_name);
        $stmt_check_name->bind_param("s", $name);
        $stmt_check_name->execute();
        $result_check_name = $stmt_check_name->get_result();
    
        if ($result_check_name->num_rows > 0) {
            $performer = $result_check_name->fetch_assoc();
            $performer_id = $performer['id'];
        
            $update_query = "UPDATE performers SET name = ?, genre = ?, bio = ?, image_url = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $name, $genre, $bio, $image_url, $performer_id);
        
            if ($stmt->execute()) {
                $message = "Podaci izvođača su uspešno ažurirani!";
        
                if (!empty($event_id)) {
                    $check_event_query = "SELECT * FROM event_performers WHERE event_id = ? AND performer_id = ?";
                    $stmt_check_event = $conn->prepare($check_event_query);
                    $stmt_check_event->bind_param("ii", $event_id, $performer_id);
                    $stmt_check_event->execute();
                    $result_check_event = $stmt_check_event->get_result();
        
                    if ($result_check_event->num_rows === 0) {
                        $event_link_query = "INSERT INTO event_performers (event_id, performer_id) VALUES (?, ?)";
                        $stmt_event_link = $conn->prepare($event_link_query);
                        $stmt_event_link->bind_param("ii", $event_id, $performer_id);
                        $stmt_event_link->execute();
                    }
                }
        
                $check_user_query = "SELECT * FROM user_performer WHERE user_id = ? AND performer_id = ?";
                $stmt_check_user = $conn->prepare($check_user_query);
                $stmt_check_user->bind_param("ii", $user_id, $performer_id);
                $stmt_check_user->execute();
                $result_check_user = $stmt_check_user->get_result();
        
                if ($result_check_user->num_rows === 0) {
                    $user_link_query = "INSERT INTO user_performer (user_id, performer_id) VALUES (?, ?)";
                    $stmt_user_link = $conn->prepare($user_link_query);
                    $stmt_user_link->bind_param("ii", $user_id, $performer_id);
                    $stmt_user_link->execute();
                }
            }
            else 
            {
                $message = "Greška pri ažuriranju podataka: " . $conn->error;
            }
        }
        else 
        {
           
        $insert_query = "INSERT INTO performers (name, genre, bio, image_url) VALUES (?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("ssss", $name, $genre, $bio, $image_url);

        if ($stmt_insert->execute()) {
            $performer_id = $stmt_insert->insert_id;

            if (!$performer_id) {
                die("Greška: Novi ID izvođača nije generisan.");
            }

            $link_query = "INSERT INTO user_performer (user_id, performer_id) VALUES (?, ?)";
            $stmt_link = $conn->prepare($link_query);
            $stmt_link->bind_param("ii", $user_id, $performer_id);
            $stmt_link->execute();

            $message = "Podaci izvođača su uspešno sačuvani!";
        } else {
            $message = "Greška pri unosu podataka: " . $conn->error;
        }

        $check_event_query = "SELECT * FROM event_performers WHERE event_id = ? AND performer_id = ?";
        $stmt_check_event = $conn->prepare($check_event_query);
        $stmt_check_event->bind_param("ii", $event_id, $performer_id);
        $stmt_check_event->execute();
        $result_check_event = $stmt_check_event->get_result();

        if ($result_check_event->num_rows === 0) {
            $event_link_query = "INSERT INTO event_performers (event_id, performer_id) VALUES (?, ?)";
            $stmt_event_link = $conn->prepare($event_link_query);
            $stmt_event_link->bind_param("ii", $event_id, $performer_id);
            $stmt_event_link->execute();
        }
        }
    }else{$message='Pogresan unos';}
} 
    


$ratings_niz = "SELECT AVG(rating) as average_rating, events.name as event_name, event_ratings.event_id 
                  FROM event_ratings 
                  JOIN events ON event_ratings.event_id = events.event_id 
                  GROUP BY events.event_id";
$stmt = $conn->prepare($ratings_niz);
$stmt->execute();
$ratings_result1 = $stmt->get_result();

$ratings1 = [];
while ($rating1 = $ratings_result1->fetch_assoc()) {
    $ratings1[] = $rating1;
}

$event_names1 = [];
$average_ratings1 = [];
foreach ($ratings1 as $rating1) {
    $event_names1[] = $rating1['event_name'];
    $average_ratings1[] = (float)$rating1['average_rating'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izvođač - Unos podataka</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color:rgb(245, 106, 106);
            background-image: url('trumpets_background.jpg');
            background-size: cover;
            color: #333;
        }
        .form-container {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
        }
        .form-container input, .form-container textarea, .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 2px solid #ccc;
            border-radius: 4px;
            align:center;
        }
        .form-container button {
            padding: 10px 15px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #ff3333;
        }
        .comments-container, .ratings-container {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
        }
        .comments-container h3, .ratings-container h3 {
            margin-top: 0;
        }
        #chart-container {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .animate-button {
        background-color: #4CAF50; 
        border: none;
        color: white;
        padding: 15px 32px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
        transition: transform 0.3s ease;
        }

        .animate-button:hover {
        transform: scale(1.1); /* Efekat uvećanja na hover */
        }

        .animate-button:active {
        transform: scale(0.9); /* Efekat smanjenja prilikom klika */
        }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<button id="myButton" class="animate-button">Idi na festival</button>
<script>
document.getElementById('myButton').addEventListener('click', function() {
  window.location.href = 'festival.php';  // Preusmeravanje na festival.php
});
</script>


    <div class="form-container">
        <h2>Dodavanje podataka izvođača</h2>
        <form method="POST">
            <label for="name">Ime:</label>
            <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>

            <label for="genre">Žanr:</label>
            <input type="text" name="genre" id="genre" value="<?php echo htmlspecialchars($genre); ?>" required>

            <label for="bio">Biografija:</label>
            <textarea name="bio" id="bio" rows="4" required><?php echo htmlspecialchars($bio); ?></textarea>

            <label for="image_url">URL slike:</label>
            <input type="text" name="image_url" id="image_url" value="<?php echo htmlspecialchars($image_url); ?>">

            <label for="event_id">Izaberi događaj:</label>
            <select name="event_id" id="event_id" required>
                <option value="">-- Izaberite događaj --</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event['event_id']; ?>">
                        <?php echo htmlspecialchars($event['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Sačuvaj podatke</button>
        </form>
        <?php if ($message) echo "<p>$message</p>"; ?>
    </div>

    <div class="comments-container">
        <h3>Komentari</h3>
        <?php if (!empty($comments)): ?>
            <ul>
                <?php foreach ($comments as $comment): ?>
                    <li><strong><?php echo htmlspecialchars($comment['created_at']); ?>:</strong> <?php echo htmlspecialchars($comment['content']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nema komentara za vas.</p>
        <?php endif; ?>
    </div>

    <div class="ratings-container">
        <h3>Ocene događaja</h3>
        <?php if (!empty($ratings)): ?>
            <ul>
                <?php foreach ($ratings as $rating): ?>
                    <li>Događaj: <?php echo htmlspecialchars($rating['event_name']); ?> - Prosečna ocena: <?php echo number_format($rating['average_rating'], 2); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Nema dostupnih ocena.</p>
        <?php endif; ?>
    </div>
    <!-- Grafikon za prosečne ocene -->
    <div id="chart-container">
        <h3>Statistika ocena svih nastupa</h3>
        <canvas id="ratingsChart"></canvas>
    </div>

    <script>
        var eventNames = <?php echo json_encode($event_names1); ?>;
        var averageRatings = <?php echo json_encode($average_ratings1); ?>;

        var ctx = document.getElementById('ratingsChart').getContext('2d');
        var ratingsChart = new Chart(ctx, {
            type: 'bar', // Tip grafikona
            data: {
                labels: eventNames, // Nazivi događaja
                datasets: [{
                    label: 'Prosečna ocena',
                    data: averageRatings, // Prosečne ocene
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5
                    }
                }
            }
        });
    </script>
    <div style="text-align: right; padding: 10px;">
        <a href="logout.php" style="font-size: 24px; color:rgb(252, 247, 247); text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Odjavi se
        </a>
    </div>

</body>
</html>
