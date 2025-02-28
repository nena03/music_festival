<?php
session_start(); 
require_once 'config.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}
if (isset($_GET['festival_id'])) {
    $festival_id = intval($_GET['festival_id']); 
    $_SESSION['festival_id']=$festival_id;

     
} else {
    die("Festival ID nije prosleđen!");
}
$user_id = $_SESSION['user_id'];
try {
    $query = "SELECT visit_count FROM festival_visits WHERE $festival_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$festival_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $query = "UPDATE festival_visits SET visit_count = visit_count + 1, last_visit = NOW() WHERE festival_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$festival_id]);
        
    } else {
        $query = "INSERT INTO festival_visits (festival_id, visit_count, last_visit) VALUES (?, 1, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->execute([$festival_id]);
        
    }
} catch (PDOException $e) {
    echo "Greška u bazi podataka: " . $e->getMessage();
}



try {
    $stmt = $conn->prepare(
        "SELECT DISTINCT events.event_id, events.name AS event_name, events.description, events.event_time, events.scene,
                performers.id AS performer_id, performers.name AS performer_name, performers.image_url AS image_url,
                IFNULL(er.rating_count, 0) AS rating_count, IFNULL(er.average_rating, 0) AS average_rating
         FROM events
         LEFT JOIN event_ratings er ON events.event_id = er.event_id
         LEFT JOIN event_performers ON events.event_id = event_performers.event_id
         LEFT JOIN performers ON event_performers.performer_id = performers.id
         WHERE events.festival_id = :festival_id
         ORDER BY events.event_time"
    );
   
    
    $stmt->bindValue(':festival_id', $festival_id, PDO::PARAM_INT);
    $stmt->execute();

    $events = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $event_id = $row['event_id'];
        if (!isset($events[$event_id])) {
            $events[$event_id] = [
                'name' => $row['event_name'],
                'description' => $row['description'],
                'event_time' => $row['event_time'],
                'scene' => $row['scene'],
                'average_rating' => $row['average_rating'],
                'rating_count' => $row['rating_count'],
                'performers' => []
            ];
        }
        if (!empty($row['performer_id']) && !empty($row['performer_name'])) {
            $performer = [
                'id' => $row['performer_id'],
                'name' => $row['performer_name']
            ];
        
            if (!empty($row['image_url'])) {
                $performer['image_url'] = $row['image_url'];
            }
        
            $events[$event_id]['performers'][] = $performer;
        }
        
        
    }
} catch (PDOException $e) {
    die("Greška prilikom učitavanja podataka: " . $e->getMessage());
}
$favorites_stmt = $conn->prepare(
    "SELECT p.id, p.name 
     FROM favourite_performers fp
     JOIN performers p ON fp.performer_id = p.id
     WHERE fp.user_id = ?"
);
$favorites_stmt->execute([$user_id]);
$favorites = $favorites_stmt->fetchAll(PDO::FETCH_ASSOC);

$events_sql = "SELECT DISTINCT e.*, er.rating_count, er.average_rating FROM events e
               LEFT JOIN event_ratings er ON e.event_id = er.event_id
               ORDER BY e.event_time ASC";
$events_result = $conn->query($events_sql);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type'])) {
    if ($_POST['type'] === 'event_rating') {
        $event_id = intval($_POST['event_id']);
        $rating = intval($_POST['rating']);

        try {
            // Provera da li već postoji ocena za događaj
            $stmt = $conn->prepare("SELECT rating_count, average_rating FROM event_ratings WHERE event_id = ?");
            $stmt->execute([$event_id]);
            $existing_rating = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_rating) {
                // Ažuriranje postojeće ocene
                $new_rating_count = $existing_rating['rating_count'] + 1;
                $new_average_rating = (($existing_rating['average_rating'] * $existing_rating['rating_count']) + $rating) / $new_rating_count;

                $update_stmt = $conn->prepare("UPDATE event_ratings SET rating_count = ?, average_rating = ? WHERE event_id = ?");
                $update_stmt->execute([$new_rating_count, $new_average_rating, $event_id]);
            } else {
                // Dodavanje nove ocene
                $new_rating_count = 1;
                $new_average_rating = $rating;

                $insert_stmt = $conn->prepare("INSERT INTO event_ratings (event_id, rating_count, average_rating) VALUES (?, ?, ?)");
                $insert_stmt->execute([$event_id, $new_rating_count, $new_average_rating]);
            }
        } catch (PDOException $e) {
            die("Greška prilikom ažuriranja ocena: " . $e->getMessage());
        }
    }
}
$stmtImage = $conn->prepare("SELECT image_url FROM festivals WHERE festival_id = :festival_id");
        $stmtImage->bindParam(':festival_id', $festival_id, PDO::PARAM_INT);

        $stmtImage->execute();

        $resultimage = $stmtImage->fetch(PDO::FETCH_ASSOC);

        if ($resultimage && !empty($resultimage['image_url'])) {
            $imageUrl = $resultimage['image_url'];
        } 
        $stmtName = $conn->prepare("SELECT name FROM festivals WHERE festival_id = :festival_id");
        $stmtName->bindParam(':festival_id', $festival_id, PDO::PARAM_INT);

        $stmtName->execute();

        $resultName=  $stmtName->fetch(PDO::FETCH_ASSOC);

        
        if ($resultName && !empty($resultName['name'])) {
            
            $festivalname = $resultName['name'];
        } 
$stmtankete = $conn->query("SELECT * FROM ankete");
$ankete = $stmtankete->fetchAll(PDO::FETCH_ASSOC);

$genres = [];
$query = "SELECT DISTINCT genre FROM performers";
$result = $conn->query($query);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $genres[] = $row['genre'];
}

$times = [];
$query = "SELECT DISTINCT event_time FROM events";
$result = $conn->query($query);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $times[] = $row['event_time'];
}

$scenes = [];
$query = "SELECT DISTINCT scene FROM events";
$result = $conn->query($query);
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $scenes[] = $row['scene'];
}


        
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            align-items: center;
            background-color:#15144E;
            color:white;
            display: grid;
            place-items: center;
            
        }
         
        .card-container {
        perspective: 1500px;
        }

        .card {
        width: 700px;
        height: 300px;
        position: center;
        transform-style: preserve-3d;
        animation: rotateCard 5s cubic-bezier(0.17, 0.67, 0.83, 0.67) infinite;
        margin-top: 140px;
        margin-bottom: 30px;
         
        
        }

        .card-front, .card-back {
        position: absolute;
        width: 100%;
        height: 100%;
        backface-visibility: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
        color: white;
         
        }

        .card-front {
        background-image: url('<?php echo htmlspecialchars($imageUrl); ?>');
        background-size: cover;
        background-position: center;
        border-radius: 40px; 
        }

        .card-back {
        
        background-image: url('<?php echo htmlspecialchars($imageUrl); ?>');
        background-size: cover;
        background-position: center;
        transform: rotateY(180deg);
        border-radius: 40px; 
        }

        @keyframes rotateCard {
        0% {
            transform: rotateY(0deg);
        }
        25% {
            transform: rotateY(90deg);
        }
        50% {
            transform: rotateY(180deg);
        }
        75% {
            transform: rotateY(270deg);
        }
        100% {
            transform: rotateY(360deg);
        }
        }
        
        h1 {
            font-family: 'Georgia', serif;
            font-size: 3rem;
            color: white;
            text-align: center;
            text-transform: capitalize;
            margin: 0;
            padding: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
            
        }
        h2 {
            font-family: 'Georgia', serif;
            font-size: 2rem;
            color: white;
            text-align: center;
            text-transform: capitalize;
            margin: 0;
            padding: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
        }
       
        .full-width-image {
            width: 100%; 
            height: 100 px; 
            display: block; 
            margin: 0; 
            background-repeat: no-repeat; 
        }
        .logout-icon {
            position: absolute; 
            top: 20px; 
            right: 20px; 
            width: 50px; 
            height: 50px; 
            display: block; 
            cursor: pointer; 
        }

        .logout-icon img {
            width: 100%; 
            height: auto; 
            border-radius: 50%; 
            transition: transform 0.2s ease; 
        }

        .logout-icon img:hover {
            transform: scale(1.1); 
        }
        .performer-image {
            width: 250px; 
            height: auto; 
            border-radius: 10px; 
            margin-top: 10px; 
        }

        .no-image {
            font-style: italic; 
            color: gray; 
        }



        .event {
            background-color:rgba(126, 120, 120, 0.97);
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            box:center;
            margin-bottom: 20px;
            width: 80%;
            box-sizing: border-box;
            justify-content: center; 
            align-items: center;
            
        }
        .event h2 {
            margin-bottom: 10px;
            font-family: 'Anton', sans-serif;
            text-shadow: 4px 4px 0px black, 
                -4px -4px 0px black, 
                4px -4px 0px black, 
                -4px 4px 0px black, 
                2px 2px 5px rgba(0, 0, 0, 0.5);
            text-transform: uppercase;
            letter-spacing: 10px;
        }
        .event p, label {  
        font-family: 'Poppins', sans-serif;
        font-size: 16px; 
        font-weight: 400; 
        color: white; 
        line-height: 1.6; 
        margin-bottom: 15px; 

        }
        .performer {
            
            margin-left: 20px;
            margin-top: 10px;
        }
        .performer form {
            
            margin-top: 10px;
        }
        .performer k {
           
            margin: 10px;
        }
        .performer form textarea 
        {   place-items: center;
            width: 50%;
            height: 20px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
        }
        button {
            background: linear-gradient(90deg,rgb(255, 0, 174),rgb(253, 235, 34));
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background 0.3s, transform 0.2s;
        }
        


        button:hover {
            background: linear-gradient(90deg, #0056b3, #003e8b);
            transform: scale(1.05);
        }

        button:active {
            transform: scale(0.95);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .tekst-klasican {
            font-family: 'Georgia', serif;
            font-size: 2rem;
            color: white; 
            text-shadow: 2px 2px 4px black; 
            font-weight: bold; 
            border: 2px solid black; 
            text-align: center;
            font-weight: bold;
            text-transform: capitalize;
            margin: 0;
            padding: 20px;
            
            animation: bounceText 0.5s infinite alternate ease-in-out;  
        }
        @keyframes bounceText {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .performer form button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .performer form button:hover {
            background-color:rgb(252, 198, 20);
        }
        .share-buttons a, .comment-share button {
            margin-right: 10px;
            color: white;
            background-color: #007BFF;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .share-buttons a:hover, .comment-share button:hover {
            background-color: #0056b3;
        }
        .full-width-image {
            width: 100%; 
            height: 100 px; 
            display: block; 
            margin: 0; 
            background-repeat: no-repeat; 
        }
        #performers-list {
            display: none; 
        }
        #performers-toggle {
            cursor: pointer; 
            font-family: 'Poppins', sans-serif;
            background-color:rgb(244, 250, 244); 
            color: black; 
            padding: 10px 5px; 
            border-radius: 5px; 
            text-align: center;
            width: 20%;;
        }
        #performers-toggle:hover {
            background-color:rgba(11, 11, 11, 0.96); 
            color:white;
        }
        .comment-box {
            background: white;
            color:rgba(252, 93, 7, 0.97);
            border-radius: 15px;
            padding: 15px;
            width: 300px;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
        }
       
        .hidden {
            
            display: none;
        }
        
        .visible {
            display: block;
        }
        .container {
            max-width: 600px;
            margin-top:40px;
            margin: flex;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin-bottom:10px;
        }

       
        .anketa-lista {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 0;
        }

        .anketa-item {
            background: rgba(255, 255, 255, 0.3);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            transition: 0.3s ease-in-out;
            list-style: none;
            font-family: 'Poppins', sans-serif;
        }

        .anketa-item:hover {
            transform: scale(1.05);
        }

        .anketa-link {
            display: inline-block;
            text-decoration: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
            padding: 10px 15px;
            background:#E30613;
            border-radius: 5px;
            transition: 0.3s;
        }

        .anketa-link:hover {
            background:#E30613;
            transform: scale(1.1);
        }

        .navbar {
            background: #E30613;
            padding: 0px;
            display: flex;
            position: fixed;
            color:white;
            align-items: center; 
            justify-content: flex-start; 
            padding-left: 20px; 
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
            position: absolute; 
            top: 0;
            left: 0;
            width: 100%;
        } 
        .dropdown {
            position: relative;
            display: inline-block;
        }
        .dropdown-button {
            background:rgb(248, 78, 31);
            color: white;
            padding: 15px 25px;
            font-size: 18px;
            border: none;
            border-radius: 25px;
            margin-left:15px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        }
        .dropdown-button:hover {
            background: #FF0000;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.6);
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background: rgba(255, 69, 0, 0.95);
            min-width: 200px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        .dropdown-content a {
            color: white;
            padding: 12px 16px;
            display: block;
            text-decoration: none;
            text-align: left;
        }
        .dropdown-content a:hover {
            background:rgb(10, 10, 10);
            color:white;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .modal {
            display: none; 
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(220, 12, 12, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            color:white;
            font-size:12px;
        }

        .modal-content {
            background:rgba(220, 12, 12, 0.95); ;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            position: relative;
            width: 300px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            font-size:12px;
        }
        

        .close {
            position: absolute;
            top: 20px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
            color:black;
        }

        .close:hover {
            color: black;
        }
    
        .close-modal {
                position: absolute;
                top: 20px;
                right: 15px;
                font-size: 20px;
                cursor: pointer;
                color:black;
        }

        .close-modal:hover {
            color: black; 
        }

        .notification-modal{
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(220, 12, 12, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            color:white;
            font-size:12px;
        }
        .izvodjac {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(255, 255, 255, 0.2);
        transition: transform 0.3s;
        cursor: pointer;
        animation: slideIn 0.5s ease-out;
        text-align: center;
        width:60%;
        }

        .izvodjac:hover {
            transform: scale(1.1);
            background:white;
            color:black;
            width:70%;
            
        }

        .izvodjac img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.5s ease-in-out;
            animation: slideIn 0.5s ease-out;
        }
        .izvodjac a {
            text-decoration: none;
        }
        .izvodjac:hover a {
            text-decoration: none;
        }


        .izvodjac p {
            font-size: 16px;
            color: white;
            font-weight: bold;
            animation: namePopUp 0.8s ease-out;
            text-decoration: none;
        }
        .izvodjac:hover p {
            font-size: 16px;
            color: black;
            font-weight: bold;
            animation: namePopUp 0.8s ease-out;
            text-decoration: none;
        }

        .izvodjac:hover img {
            transform: rotateY(360deg);
            color:black;
            
        }
        .event select {
        font-family: 'Poppins', sans-serif;
        font-size: 12px;
        padding: 7px 10px;
        border: 2px solid #ccc;
        border-radius: 6px;
        background-color: #fff;
        color: #333;
        cursor: pointer;
        outline: none;
        transition: border-color 0.3s ease-in-out;
    }

    .event select:focus {
        border-color: #007bff;
    }

    .event select option {
        font-size: 12px;
        background-color: #fff;
        color: #333;
    }
    .navbar-title {
        
    font-family: 'Anton', sans-serif;
    font-size: 50px;
    font-weight: 900;
    color: white;
    text-transform: uppercase;
    letter-spacing: 10px;
    text-shadow: 4px 4px 0px black, 
                -4px -4px 0px black, 
                4px -4px 0px black, 
                -4px 4px 0px black, 
                2px 2px 5px rgba(0, 0, 0, 0.5);


    margin-left: 25%;
    text-align: center;
    }
    .performers-toggle {
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        color: #fff;
        padding: 12px 20px;
        background: #d90429;
        border: 2px solid #333;
        border-radius: 50px;
        display: inline-block;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease-in-out;
    }

    .performers-toggle:hover {
        background: #fff;
        color: #333;
        border-color: #333;
    }



    </style>
   
</head>
<body>

   

    <div class="navbar">
            <div class="dropdown">
                <button class="dropdown-button">Meni</button>
                <div class="dropdown-content">
                    <a href="ticket.php">Kupovina ulaznica</a>
                    <a href="search.php?festival_id=<?php echo urlencode($festival_id); ?>">Pretraži izvođače</a>
                    
                    <a href="#" id="openModal">Vaši omiljeni izvođači</a>
                    <a href="#" id="open-modal">Obaveštenja</a>
                </div>
            </div>
            <h1 class="navbar-title">WELCOME</h1>
    </div>
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            
            <h2>Vaši omiljeni izvođači</h2>
            
                <?php if (empty($favorites)): ?>
                    <p>Nemate omiljenih izvođača.</p>
                <?php else: ?>
                    <?php foreach ($favorites as $favorite): ?>
                       <h3><?= htmlspecialchars($favorite['name']) ?></h3>
                    <?php endforeach; ?>
                <?php endif; ?>
            
        </div>
    </div>
    <script>
        document.getElementById("openModal").addEventListener("click", function(event) {
            event.preventDefault(); // Sprečava preusmeravanje
            document.getElementById("modal").style.display = "flex";
        });

        document.querySelector(".close").addEventListener("click", function() {
            document.getElementById("modal").style.display = "none";
        });

        // Zatvaranje kada se klikne van modala
        window.addEventListener("click", function(event) {
            let modal = document.getElementById("modal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    </script>

    <div id="notification-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Obaveštenja o nastupima</h2>

            <?php
            $notification_stmt = $conn->prepare('
                SELECT DISTINCT pn.id, pn.user_id, pn.performer_id, pn.event_id, p.name AS performer_name, e.name AS event_name
                FROM performer_notifications pn
                LEFT JOIN performers p ON pn.performer_id = p.id
                LEFT JOIN events e ON pn.event_id = e.event_id  
                WHERE pn.user_id = ? AND pn.notified = FALSE
            ');

            $notification_stmt->execute([$user_id]);
            $notifications = $notification_stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($notifications) {
                foreach ($notifications as $notification) {
                    echo "<p>Obaveštenje: Izvođač <strong>" . htmlspecialchars($notification['performer_name']) . "</strong> nastupa na događaju <strong>" . htmlspecialchars($notification['event_name']) . "</strong></p>";
            ?>
                    <form method="POST" action="mark_as_read.php?festival_id=<?= $_GET['festival_id'] ?>" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $notification['id'] ?>">
                        <button type="submit">Označi kao pročitano</button>
                    </form>
            <?php
                }
            } else {
                echo "<p>Nema novih obaveštenja.</p>";
            }
            ?>
        </div>
    </div>
    <script>
    document.getElementById("open-modal").addEventListener("click", function(event) {
        event.preventDefault(); 
        document.getElementById("notification-modal").style.display = "flex";
    });

    document.querySelector(".close-modal").addEventListener("click", function() {
        document.getElementById("notification-modal").style.display = "none";
    });

    window.addEventListener("click", function(event) {
        let modal = document.getElementById("notification-modal");
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
    </script>


    
    
    <div class="card-container">
        <div class="card">
            <div class="card-front">
            <?php
            if($festival_id!=2){
            if ($festivalname) {
                echo "<h1>" . htmlspecialchars($festivalname) . "</h1>";} }
            ?>
            </div>
            <div class="card-back">
            <?php
             if($festival_id!=2){
            if ($festivalname) {
                echo "<h1>" . htmlspecialchars($festivalname) . "</h1>";} }
            ?>
            </div>
        </div>
    </div>
    
   
    
    <div class="container">
        <h2>Lista dostupnih anketa</h2>
        
        <?php if (!empty($ankete)): ?>
            <ul class="anketa-lista">
                <?php foreach ($ankete as $anketa): ?>
                    <li class="anketa-item">
                        <a class="anketa-link" href="vote.php?anketa_id=<?= $anketa['id'] ?>&user_id=<?= $user_id ?>&festival_id=<?= $festival_id ?>"><?= htmlspecialchars($anketa['naziv']) ?></a>
                            
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Trenutno nema dostupnih anketa.</p>
        <?php endif; ?>
    </div>
        

    <?php foreach ($events as $event_id => $event): ?>
    <div class="event">
        <h2><?= htmlspecialchars($event['name']) ?></h2>
        <p>Opis: <?= htmlspecialchars($event['description']) ?></p>
        <p>Datum i vreme: <?= htmlspecialchars($event['event_time']) ?></p>
        <p>Scena: <?= htmlspecialchars($event['scene']) ?></p>

        <p><strong>Prosečna ocena:</strong> <?= number_format($event['average_rating'] ?: 0, 2) ?></p>
        <p><strong>Broj ocena:</strong> <?= $event['rating_count'] ?: 0 ?></p>
        
            <form method="POST" action="">
                <input type="hidden" name="type" value="event_rating" />
                <input type="hidden" name="event_id" value="<?= $event_id ?>" />
                <label for="rating">Oceni događaj:</label>
                <select name="rating" id="rating">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                </select>
                <button type="submit">Pošaljite ocenu</button>
            </form>
        

        <div class="share-buttons">
            <p>Podeli ovu stranicu:</p>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://localhost/music_festival/festival1.php') ?>" target="_blank">Podeli na Facebook</a>
            <a href="https://twitter.com/share?url=<?= urlencode('http://localhost/music_festival/festival1.php') ?>&text=<?= urlencode('Pogledajte ovaj događaj!') ?>" target="_blank">Podeli na Twitter</a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('http://localhost/music_festival/festival1.php') ?>" target="_blank">Podeli na LinkedIn</a>
        </div>
        <h3 class="performers-toggle" data-event-id="<?= $event_id ?>">Izvođači</h3>

        
        <div id="performers-list-<?= $event_id ?>" style="display: none;">

            
            <?php if (!empty($event['performers'])): ?>
            <?php foreach ($event['performers'] as $performer): ?>
                
                <div class="performer">
                    
                    <div class="izvodjac">
                    <?php if (!empty($performer['image_url'])): ?>
                        <a href="performer2.php?id=<?php echo $performer['id']; ?>">
                            <img src="<?= htmlspecialchars($performer['image_url']) ?>" alt="<?= htmlspecialchars($performer['name']) ?>" class="performer-image">
                        </a>
                        <a href="performer2.php?id=<?php echo $performer['id']; ?>">
                            <p><?php echo htmlspecialchars($performer['name']); ?></p>
                        </a>
                    <?php endif; ?>
                    </div>
                    
                    
                    <?php
                    $is_favorite = false;
                    $check_stmt = $conn->prepare("SELECT 1 FROM favourite_performers WHERE user_id = ? AND performer_id = ?");
                    $check_stmt->execute([$user_id, $performer['id']]);
                    if ($check_stmt->fetch()) {
                        $is_favorite = true;
                    }
                    ?>
                    

                    <form method="POST" action="favourite_toggle.php?festival_id=<?= $_GET['festival_id'] ?>">
                        <input type="hidden" name="performer_id" value="<?= $performer['id'] ?>">
                        <input type="hidden" name="event_id" value="<?= $event_id ?>">
                        <button type="submit" name="action" value="<?= $is_favorite ? 'remove' : 'add' ?>">
                            <?= $is_favorite ? 'Ukloni iz omiljenih' : 'Dodaj u omiljene' ?>
                        </button>
                    </form>
                    

                    <form method="POST" action="add_comment.php?festival_id=<?= $_GET['festival_id'] ?>">
                        <textarea name="comment" placeholder="Unesite komentar..."></textarea>
                        <input type="hidden" name="performer_id" value="<?= $performer['id'] ?>">
                        <input type="hidden" name="event_id" value="<?= $event_id ?>">
                        <button type="submit">Dodaj komentar</button>
                    </form>
                    
                    
                    <?php
                    $performer_id = $performer['id'];
                    try {
                        $fetchComments = $conn->prepare("
                        SELECT comments.content, users.username, events.name AS event_name
                        FROM comments
                        JOIN users ON comments.user_id = users.user_id
                        JOIN events ON comments.event_id = events.event_id
                        WHERE comments.performer_id = :performer_id
                        ");
                        $fetchComments->bindParam(':performer_id', $performer_id);
                        $fetchComments->execute();
                        $content = $fetchComments->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $error) {
                        die("Greška prilikom učitavanja komentara: " . $error->getMessage());
                    }
                    ?>

                
                    <button type="button" id="toggleComments-<?= $performer_id ?>" onclick="toggleComments(<?= $performer_id ?>)">Pogledaj komentare</button>

                    <div id="commentsContainer-<?= $performer_id ?>" class="hidden">
                        <?php if (!empty($content)): ?>
                            <?php foreach ($content as $comment): ?>
                                <div class="comment-box">
                                    <ul>
                                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?>
                                    </ul>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Nema komentara za ovog izvođača.</p>
                        <?php endif; ?>
                    </div>

                    <script>
                        function toggleComments(performerId) {
                            let commentsDiv = document.getElementById("commentsContainer-" + performerId);
                            let button = document.getElementById("toggleComments-" + performerId);

                            if (commentsDiv.classList.contains("hidden")) {
                                commentsDiv.classList.remove("hidden");  // Prikazujemo komentare
                                button.textContent = "Sakrij komentare";
                            } else {
                                commentsDiv.classList.add("hidden");  // Sakrivamo komentare
                                button.textContent = "Pogledaj komentare";
                            }
                        }
                    </script>


                    <div class="comment-share">
                        <p>Podeli komentare izvođača:</p>
                        <button onclick="sharePerformerComments(<?= $performer['id'] ?>)">Kopiraj link</button>
                        <button onclick="shareToFacebook(<?= $performer['id'] ?>)">Podeli na Facebook</button>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p>Nema izvođača za ovaj događaj.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
   

    <script>
    
    function sharePerformerComments(performerId) {
        const url = `https://localhost/festival1.php/performer_comments.php?performer_id=${performerId}`;
        navigator.clipboard.writeText(url).then(() => {
            alert("Link ka komentarima je kopiran!");
        }).catch(err => {
            console.error('Kopiranje linka nije uspelo:', err);
        });
    }

    function shareToFacebook(performerId) {
        const url = `https://localhost/performer_comments.php?performer_id=${performerId}`;
        const facebookShareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
        window.open(facebookShareUrl, '_blank');
    }
   
    
   

        
      
    document.addEventListener("DOMContentLoaded", function() 
    {
        const card = document.querySelector(".card");
        card.addEventListener("animationend", function() 
        {
        setTimeout(function() 
        {
            card.style.animation = 'none';
        }, 5000);
        });
        var toggles = document.querySelectorAll('.performers-toggle');

    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            var eventId = this.getAttribute('data-event-id');
            var performersList = document.getElementById('performers-list-' + eventId);
            
            if (performersList) {
                // Ako je sakriven, prikaži ga; ako je vidljiv, sakrij ga
                performersList.style.display = (performersList.style.display === 'none' || performersList.style.display === '') ? 'block' : 'none';
            }
        });
    });


    });
    </script>

    <a href="logout.php" class="logout-icon" title="Logout">
        <img src="https://thumbs.dreamstime.com/b/exit-logout-log-off-icon-isolated-white-red-thin-right-rounded-arrow-bracket-sign-out-profile-user-box-quit-export-128526354.jpg" alt="Logout" />
    </a>
</body>
</html>
