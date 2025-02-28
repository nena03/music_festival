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

if (!$user || $user['role'] !== 'organizer') {
    
    header("Location: unauthorized.php");
    exit;
}
$query = "SELECT organizator_id FROM organizator WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    
    $insert_query = "INSERT INTO organizator (user_id) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("i", $user_id);
    $insert_stmt->execute();
    
}

$query = "SELECT o.organizator_id
          FROM users u
          JOIN organizator o ON u.user_id = o.user_id
          WHERE u.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: unauthorized.php");
    exit;
}

$organizer = $result->fetch_assoc();
$organizer_id = $organizer['organizator_id'];





if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $event_name = $_POST['event_name'];
    $description = $_POST['description'];
    $event_time = $_POST['event_time'];
    $scene = $_POST['scene'];
    $artist_names = $_POST['artist_names'];
    $festival_id = $_POST['festival_id'];


    
    if (!empty($_POST['event_id'])) {
        $event_id = $_POST['event_id'];
        $festival_id = $_POST['festival_id'];
        
        if (!empty($festival_id)){
        $update_query = "UPDATE events SET festival_id = ? WHERE event_id = ?";

        
        $stmt = $conn->prepare($update_query);

        
        $stmt->bind_param("ii", $festival_id, $event_id);

        
        $stmt->execute();
        }
        $update_query = "UPDATE events SET name = ?, description = ?, event_time = ?, scene = ?, festival_id=? WHERE event_id = ? AND organizator_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssiii", $event_name, $description, $event_time, $scene, $event_id, $organizer_id,$festival_id);
        $stmt->execute();

        

        
        foreach ($artist_names as $artist_name) {
            
            if (empty(trim($artist_name))) {
                continue; 
            }
            $check_performer_query = "SELECT id FROM performers WHERE name = ?";
            $stmt = $conn->prepare($check_performer_query);
            $stmt->bind_param("s", $artist_name);
            $stmt->execute();
            $performer_result = $stmt->get_result();
        
            if ($performer_result->num_rows > 0) {
                
                $performer = $performer_result->fetch_assoc();
                $performer_id = $performer['id'];
            } else {
                
                $insert_performer_query = "INSERT INTO performers (name) VALUES (?)";
                $stmt = $conn->prepare($insert_performer_query);
                $stmt->bind_param("s", $artist_name);
                $stmt->execute();
        
                
                $performer_id = $conn->insert_id;
            }
        
            $check_event_query = "SELECT * FROM event_performers WHERE event_id = ? AND performer_id = ?";
            $stmt_check_event = $conn->prepare($check_event_query);
            $stmt_check_event->bind_param("ii", $event_id, $performer_id);
            $stmt_check_event->execute();
            $result_check_event = $stmt_check_event->get_result();

            if ($result_check_event->num_rows === 0) {
                $insert_performer_query = "INSERT INTO event_performers (event_id, performer_id) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_performer_query);
                $stmt->bind_param("ii", $event_id, $performer_id);
                if ($stmt->execute()) {
                    echo "Izvođač je uspešno povezan sa događajem.";
                } else {
                    echo "Greška pri povezivanju izvođača sa događajem: " . $conn->error;
                }
            } else {
                echo "Ovaj izvođač je već povezan sa događajem.";
            }

        }
        
    } else {
        
        $insert_query = "INSERT INTO events (name, description, event_time, scene, organizator_id,festival_id) VALUES (?, ?, ?, ?, ?,?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ssssii", $event_name, $description, $event_time, $scene, $organizer_id, $festival_id);
        $stmt->execute();

        $event_id = $conn->insert_id;

        
        
    }
}

$events_query = "SELECT  e.event_id, e.name, e.description, e.event_time, e.scene,e.festival_id FROM events e WHERE e.organizator_id = ?";
$stmt = $conn->prepare($events_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events = $stmt->get_result();



$ratings_query = "
    SELECT er.event_id, e.name, AVG(er.rating) AS average_rating,COUNT(er.rating) AS rating_count
    FROM event_ratings er
    JOIN events e ON e.event_id = er.event_id
    WHERE e.organizator_id = ?  -- Dodajemo filtriranje po organizatoru
    GROUP BY er.event_id, e.name";

$ratings_stmt = $conn->prepare($ratings_query);
$ratings_stmt->bind_param("i", $organizer_id);  
$ratings_stmt->execute();
$ratings = $ratings_stmt->get_result();

$comments_query = "SELECT c.content, p.name AS performer_name, c.created_at
                   FROM comments c
                   JOIN performers p ON c.performer_id = p.id
                   WHERE c.event_id = ?";
$comment_stmt = $conn->prepare($comments_query);

$sqlfestivali = "SELECT festival_id, name FROM festivals";
$resultfestivals = $conn->query($sqlfestivali);

?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <div style="padding: 10px;position: relative;">
        <a href="program.php" >
            <button1 class="left">Program festivala</button1>
        </a>
    </div>
    <title>Panel Organizatora</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color:rgba(245, 47, 47, 0.96);
            align:center;
            
            background-size: cover;  
            background-position: center center;  
            background-repeat: no-repeat; 
            height: auto;
        }
        .left {
            left: 10px; 
        }

        .right {
            right: 10px; 
        }        button1 {
            background-color: white; 
            color: black; 
            border: 2px solid black; 
            padding: 10px 20px; 
            font-size: 16px; 
            font-weight: bold; 
            text-transform: uppercase; 
            border-radius: 5px; 
            cursor: pointer; 
            transition: all 0.3s ease; 
        }

        
        button1:hover {
            background-color: black; 
            color: white; 
            border-color: white; 
        }

        
        button1:focus {
            outline: none; 
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3); 
        }

        .form-container, .events-container {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
        }
        .form-container h2, .events-container h2 {
            margin-bottom: 15px;
        }
        input, textarea, select, button {
            width: 100%;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color:rgb(110, 175, 244);
            color: white;
            cursor: pointer;
            width: 30%;
        }
        button:hover {
            background-color:rgb(30, 116, 202);
        }
        .comments-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
        }

        .comment-item {
            margin-bottom: 20px;
        }

        .comment-item h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .comment-item p {
            font-size: 16px;
        }
        .ratings-container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
            max-width: 600px;
        }

        .rating-item {
            margin-bottom: 20px;
        }

        .rating-item h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .rating-item p {
            font-size: 16px;
            margin: 5px 0;
        }

        .rating-item h4 {
            font-size: 16px;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .rating-item small {
            font-size: 12px;
            color: gray;
        }

        .rating-item strong {
            color: #333;
        }


    </style>
</head>
<body>

<div class="form-container">
    <h2>Dodaj ili Ažuriraj Događaj</h2>
    <form method="POST">
    <label for="festival_name">Izaberite Festival:</label>
    <select name="festival_id" id="festival_id"  required>
        <option value="">Ime festivala</option> 
        <?php
        
        if ($resultfestivals->num_rows > 0) {
            
            while ($row = $resultfestivals->fetch_assoc()) {
                echo '<option value="' . $row['festival_id'] . '">' . htmlspecialchars($row['name']) . '</option>';
            }
        } else {
            echo '<option value="">Nema festivala</option>'; // Ako nema festivala u bazi
        }
        ?>
    </select>
        
        <input type="hidden" name="event_id" id="event_id">
        <label for="event_name">Naziv Događaja:</label>
        <input type="text" name="event_name" id="event_name" required>

        <label for="description">Opis:</label>
        <textarea name="description" id="description" rows="4"></textarea>

        <label for="event_time">Vreme Događaja:</label>
        <input type="datetime-local" name="event_time" id="event_time" required>

        <label for="artist_names">Imena Izvođača:</label>
        <div id="artists-container">
            <input type="text" name="artist_names[]" placeholder="Unesite ime izvođača">
        </div>
        <button type="button" onclick="addArtist()">Dodaj još izvođača</button>
        <br>
        <label for="scene">Scena:</label>
        <input type="text" name="scene" id="scene">

        <button type="submit">Spremi Događaj</button>
    </form>
</div>

<div class="events-container">
    <h2>Vaši Događaji</h2>
    <?php while ($event = $events->fetch_assoc()): ?>
        <div class="event-item">
            <h3><?php echo htmlspecialchars($event['name']); ?></h3>
            <p><strong>Opis:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
            <p><strong>Vreme:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
            
            <p><strong>Scena:</strong> <?php echo htmlspecialchars($event['scene']); ?></p>
            <br>
            <button onclick="editEvent(<?php echo htmlspecialchars(json_encode($event)); ?>)">Izmeni</button>
            <button onclick="deleteEvent(<?php echo htmlspecialchars($event['event_id']); ?>)">Izbriši</button>
           
        </div>
        
    <?php endwhile; ?>
    <h3><center>Uredi izvodjače</center></h3>
    <?php



$events_query = "SELECT Distinct e.event_id, e.name, e.description, e.event_time, e.scene FROM events e WHERE e.organizator_id = ?";
$stmt = $conn->prepare($events_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$events = $stmt->get_result();


while ($event = $events->fetch_assoc()) {
    
    $performers_query = "SELECT distinct p.id, p.name FROM performers p JOIN event_performers ep ON p.id = ep.performer_id WHERE ep.event_id = ?";
    $stmt = $conn->prepare($performers_query);
    $stmt->bind_param("i", $event['event_id']);
    $stmt->execute();
    $performers_result = $stmt->get_result();
?>
    
    <form method="POST" action="remove_performer.php">
        
        <h3><?php echo htmlspecialchars($event['name']); ?></h3>
        <label for="performer_id">Izvođač:</label>
        <select name="performer_id" id="performer_id">
            <option value="">-- Izaberite izvođača --</option>
            <?php
            while ($performer = $performers_result->fetch_assoc()) {
                echo '<option value="' . $performer['id'] . '">' . htmlspecialchars($performer['name']) . '</option>';
            }
            ?>
        </select>
        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
        <button type="submit">Izbriši izvođača</button>
    </form>
<?php
}
?>
   
</div>

<script>
    function addArtist() {
        
        var newArtistInput = document.createElement("input");
        newArtistInput.setAttribute("type", "text");
        newArtistInput.setAttribute("name", "artist_names[]");
        newArtistInput.setAttribute("placeholder", "Unesite ime izvođača");

        var container = document.getElementById("artists-container");
        container.appendChild(newArtistInput);
    }

    function editEvent(event) {
        document.getElementById('event_id').value = event.event_id;
        document.getElementById('event_name').value = event.name;
        document.getElementById('description').value = event.description;
        document.getElementById('event_time').value = event.event_time.replace(' ', 'T');
        document.getElementById('scene').value = event.scene;
        document.getElementById('festival_id').value = event.festival_id;
        
    }
</script>
<div class="ratings-container">
    <h2>Ocene i Komentari Posetilaca</h2>

    <?php
    
    if ($ratings->num_rows > 0) {
        while ($rating = $ratings->fetch_assoc()) {
            echo "<div class='rating-item'>";
            echo "<h3>" .($rating['name']) . "</h3>";

            
            if ($rating['average_rating'] > 0) {
                echo "<p><strong>Prosečna ocena:</strong> " . htmlspecialchars($rating['average_rating']) . "</p>";
                echo "<p><strong>Broj ocena:</strong> " . ($rating['rating_count']) . "</p>";
            } else {
                echo "<p><strong>Nema ocena za ovaj događaj.</strong></p>";
            }

            
            $comment_stmt->bind_param("i", $rating['event_id']);
            $comment_stmt->execute();
            $comments_result = $comment_stmt->get_result();

            if ($comments_result->num_rows > 0) {
                echo "<h4>Komentari Izvođača:</h4>";
                while ($comment = $comments_result->fetch_assoc()) {
                    echo "<p><strong>" . htmlspecialchars($comment['performer_name']) . ":</strong> " . htmlspecialchars($comment['content']) . "</p>";
                    echo "<p><small>Datum: " . htmlspecialchars($comment['created_at']) . "</small></p>";
                }
            } else {
                echo "<p><strong>Nema komentara izvođača za ovaj događaj.</strong></p>";
            }

            echo "</div>";
        }
    } else {
        echo "<p>Nemate nikakve događaje sa ocenama i komentarima.</p>";
    }
    ?>
   

</div>
<div style="text-align: right; padding: 10px;">
        <a href="logout.php" style="font-size: 24px; color:rgb(252, 247, 247); text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Odjavi se
        </a>
</div>

<script>
    function deleteEvent(eventId) {
        if (confirm("Da li ste sigurni da želite da izbrišete ovaj događaj?")) {
            window.location.href = 'delete_event.php?event_id=' + eventId;
        }
    }
</script>
</body>
</html>
