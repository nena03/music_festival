<?php
session_start();
require 'config.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
$user_id=$_SESSION['user_id'];
try {
    $stmt = $conn->prepare("INSERT INTO admin (user_id) VALUES (?)");
    $stmt->execute([$user_id]);
} catch (PDOException $e) {
    if ($e->getCode() != 23000) {
        echo "Greška: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_festival'])) {
    $name = $_POST['festival_name'];
    $location = $_POST['festival_location'];
    $start_date = $_POST['festival_start_date'];
    $end_date = $_POST['festival_end_date'];
    $description = $_POST['festival_description'];
    $organizator_id = $_POST['festival_organizator_id'];
    $image_url = $_POST['festival_image_url'];

    $query = "INSERT INTO festivals (name, location, start_date, end_date, description, organizator_id, image_url) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$name, $location, $start_date, $end_date, $description, $organizator_id, $image_url]);

    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_festival'])) {
    $festival_id = $_POST['festival_id'];

    $query = "DELETE FROM festivals WHERE festival_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$festival_id]);

    header("Location: admin.php");
    exit();
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];

    $query = "DELETE FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$event_id]);

    header("Location: admin.php");
    exit();
}

$query = "SELECT * FROM festivals";
$stmt = $conn->prepare($query);
$stmt->execute();
$festivals = $stmt->fetchAll();

$query = "SELECT * FROM events";
$stmt = $conn->prepare($query);
$stmt->execute();
$events = $stmt->fetchAll();


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_comment'])) {
    $comment_id = $_POST['comment_id'];

    $query = "DELETE FROM comments WHERE comment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$comment_id]);

    header("Location: admin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);

    $query = "DELETE FROM comments WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$user_id]);

    header("Location: admin.php");
    exit();
}
$query = "
    SELECT o.organizator_id, u.username 
    FROM organizator o 
    JOIN users u ON o.user_id = u.user_id";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $organizatori = $stmt->fetchAll();


$query = "SELECT comments.comment_id, comments.content, comments.event_id, comments.user_id, 
                 users.username AS user_name, events.name AS event_name, performers.id as performer_id,performers.name as performer_name
          FROM comments
          JOIN events ON comments.event_id = events.event_id
          JOIN users ON comments.user_id = users.user_id
          JOIN performers ON comments.performer_id = performers.id";

$stmt = $conn->prepare($query);
$stmt->execute();
$comments = $stmt->fetchAll();

$query = "SELECT * FROM users";
$stmt = $conn->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll();


// Dodavanje izvođača
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_performer'])) {
    $performer_name = $_POST['performer_name'];
    $performer_genre = $_POST['performer_genre'];
    $performer_bio = $_POST['performer_bio'];
    $performer_image_url = $_POST['performer_image_url'];
    $event_ids = $_POST['event_ids']; // Ovo je niz ID-ova događaja na koje izvođač treba biti dodeljen

    
    $query = "INSERT INTO performers (name, genre, bio, image_url) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$performer_name, $performer_genre, $performer_bio, $performer_image_url]);

    $performer_id = $conn->lastInsertId();

    

    if (!empty($event_ids)) {
        foreach ($event_ids as $event_id) {
            $query = "INSERT INTO event_performers (event_id, performer_id) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$event_id, $performer_id]);
        }
    }

    header("Location: admin.php");
    exit();
}
// Brisanje izvođača
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_performer'])) {
    $performer_id = $_POST['performer_id'];

    $query = "DELETE FROM event_performers WHERE performer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$performer_id]);

    $query = "DELETE FROM user_performer WHERE performer_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$performer_id]);

    $query = "DELETE FROM performers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->execute([$performer_id]);

    header("Location: admin.php");
    exit();
}


try {
    $queryFestivals = "SELECT f.name, fv.visit_count
                       FROM festivals f
                       LEFT JOIN festival_visits fv ON f.festival_id = fv.festival_id
                       ORDER BY f.name"; 
    $stmt = $conn->prepare($queryFestivals);
    $stmt->execute();
    $festivalData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data = [];
    
    foreach ($festivalData as $festival) {
        $labels[] = $festival['name'];
        $data[] = $festival['visit_count'] ?: 0; 
    }

} catch (PDOException $e) {
    echo "Greška u bazi podataka: " . $e->getMessage();
}



try {
    $ratings_niz = "SELECT AVG(rating) as average_rating, events.name as event_name, event_ratings.event_id 
                    FROM event_ratings 
                    JOIN events ON event_ratings.event_id = events.event_id 
                    GROUP BY events.event_id";
    $stmt = $conn->prepare($ratings_niz);
    $stmt->execute();
    $ratings_result1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $event_names1 = [];
    $average_ratings1 = [];
    
    foreach ($ratings_result1 as $rating1) {
        $event_names1[] = $rating1['event_name'];
        $average_ratings1[] = (float)$rating1['average_rating'];
    }


} catch (Exception $e) {
    echo "Greška: " . $e->getMessage();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $naziv_ankete = $_POST['naziv_ankete'];
    $user_id = $_SESSION['user_id'];  

    $stmt = $conn->prepare("SELECT COUNT(*) FROM ankete WHERE naziv = :naziv");
    $stmt->bindParam(':naziv', $naziv_ankete, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "<script>alert('Anketa sa ovim nazivom već postoji!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO ankete (naziv, user_id) VALUES (:naziv, :user_id)");
        $stmt->bindParam(':naziv', $naziv_ankete, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $anketa_id = $conn->lastInsertId();

        // Dodavanje pitanja i opcija
        foreach ($_POST['pitanja'] as $index => $pitanje) {
            if (!empty($pitanje)) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM pitanja WHERE anketa_id = :anketa_id AND pitanje = :pitanje");
                $stmt->bindParam(':anketa_id', $anketa_id, PDO::PARAM_INT);
                $stmt->bindParam(':pitanje', $pitanje, PDO::PARAM_STR);
                $stmt->execute();
                $pitanje_count = $stmt->fetchColumn();

                // Ako pitanje već postoji
                if ($pitanje_count == 0) {
                    $stmt = $conn->prepare("INSERT INTO pitanja (anketa_id, pitanje) VALUES (:anketa_id, :pitanje)");
                    $stmt->bindValue(":anketa_id", $anketa_id);
                    $stmt->bindValue(":pitanje", $pitanje);
                    $stmt->execute();
                    $pitanje_id = $conn->lastInsertId();

                    if (!empty($_POST['opcije'][$index])) {
                        foreach ($_POST['opcije'][$index] as $opcija) {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM opcije WHERE pitanje_id = :pitanje_id AND opcija = :opcija");
                            $stmt->bindParam(':pitanje_id', $pitanje_id, PDO::PARAM_INT);
                            $stmt->bindParam(':opcija', $opcija, PDO::PARAM_STR);
                            $stmt->execute();
                            $opcija_count = $stmt->fetchColumn();

                            if ($opcija_count == 0) {
                                $stmt = $conn->prepare("INSERT INTO opcije (pitanje_id, opcija) VALUES (:pitanje_id, :opcija)");
                                $stmt->bindValue(":pitanje_id", $pitanje_id);
                                $stmt->bindValue(":opcija", $opcija);
                                $stmt->execute();
                            }
                        }
                    }
                }
            }
        }

        header("Location: admin.php"); 
        exit;
    }
}


if (isset($_GET['delete_anketa_id'])) {
    $anketa_id = $_GET['delete_anketa_id'];

    $conn->beginTransaction();

    try {
        $stmt = $conn->prepare("DELETE FROM odgovori WHERE anketa_id = ?");
        $stmt->bindParam(1, $anketa_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM opcije WHERE pitanje_id IN (SELECT id FROM pitanja WHERE anketa_id = ?)");
        $stmt->bindParam(1, $anketa_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM pitanja WHERE anketa_id = ?");
        $stmt->bindParam(1, $anketa_id, PDO::PARAM_INT);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM ankete WHERE id = ?");
        $stmt->bindParam(1, $anketa_id, PDO::PARAM_INT);
        $stmt->execute();

        $conn->commit();

        echo "Anketa je uspešno obrisana!";
        header("Location: admin.php"); 
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        echo "Došlo je do greške prilikom brisanja ankete: " . $e->getMessage();
    }
}

$stmt = $conn->prepare("SELECT * FROM ankete");
$stmt->execute();
$anketenovo = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>

    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            align-items:center;
            margin: 0;
            padding: 0;
            background-color:rgba(216, 247, 253, 0.79);
        }
        .kartice-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .kartica {
            background-color:rgb(252, 88, 203);
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 250px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .kartica:hover {
            transform: translateY(-10px); 
        }

        .kartica-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .kartica-body {
            text-align: center;
        }

        .kartica-body a {
            text-decoration: none;
            color:rgb(10, 11, 11);
            font-weight: bold;
            margin: 0 10px;
            transition: color 0.3s ease;
        }

        .kartica-body a:hover {
            color:rgb(253, 255, 253);
        }

        .btn-edit, .btn-delete {
            background-color:rgb(251, 251, 251);
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-edit:hover, .btn-delete:hover {
            background-color:rgb(16, 186, 56);
        }

        
        .form-container {
            width: 50%; 
            max-width: 900px; 
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(252, 146, 25, 0.96); 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
        }

        
        .form-container input,
        .form-container textarea {
            width: 100%; 
            padding: 10px; 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            border-radius: 5px;
            box-sizing: border-box; 
            font-size: 14px; 
        }

        
        .form-container button {
            padding: 12px 20px; 
            background-color: #4CAF50; 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px; 
        }

        
        .form-container button:hover {
            background-color: #45a049; 
        }


        
        h1 {
            text-align: center; 
            font-family: 'Arial', sans-serif; 
            font-weight: bold; 
            color: black; 
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.39); 
            margin: 20px 0; 
        }

        
        h2 {
            text-align: center; 
            font-family: 'Arial', sans-serif; 
            font-weight: bold; 
            color: white; 
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); 
            margin: 15px 0; 
        }
        h3 {
            text-align: center; 
            font-family: 'Arial', sans-serif; 
            font-weight: bold; 
            color: white; 
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2); 
            margin: 15px 0; 
        }


        .chart-container {
            width: 50%;
            height: 200px;
            align-items:center;
            margin:  0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex; 
            justify-content: center; 
            align-items: center; 
        }

        canvas {
            width: 90%; 
            height: 90%;
        }
        table {
            width: 50%;
            margin: 30px auto;
            border-collapse: collapse;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

        
        select {
            width: 100%; 
            padding: 10px; 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            font-size: 14px; 
            background-color: #fff; 
            cursor: pointer; 
        }

        
        select option {
            padding: 10px; 
            background-color: #fff;
            color: #333;
        }

        
        select option:disabled {
            color: #999; 
            background-color: #f9f9f9;
        }

        
        select option:hover {
            background-color: #f0f0f0;
        }
        button{
           
            margin-bottom: 10px;
        }
       
        button[type="submit"] {
            padding: 12px 24px; 
            background-color:rgb(12, 171, 30);
            color: white; 
            font-size: 16px; 
            font-weight: bold; 
            border: none; 
            border-radius: 5px;
            cursor: pointer; 
            transition: background-color 0.3s ease, transform 0.2s ease; 
            margin-bottom: 10px;
        }

        
        button[type="submit"]:hover {
            background-color:rgb(253, 60, 179); 
            transform: scale(1.05); 
        }

        button[type="submit"]:active {
            background-color:rgb(12, 171, 30); 
            transform: scale(0.98); 
        }
        .anketa-statistika {
            background: rgba(255, 255, 255, 0.3);
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .opcija-statistika {
            margin-bottom: 10px;
        }

        .opcija-statistika span {
            margin-right: 10px;
            font-weight: bold;
        }

        .opcija-statistika .procenat {
            color: #ffcc00;
        }
        .container {
            max-width: 50%;
            margin: auto;
            background: rgba(250, 92, 206, 0.94);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
       
    

    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div style="text-align: right; padding: 10px;">
            <a href="logout.php" style="font-size: 24px; color:rgb(11, 10, 10); text-decoration: none;">
                <i class="fas fa-sign-out-alt"></i> Odjavi se
            </a>
    </div>
    <h1>Admin Panel</h1>

    <div class="form-container">
        <form method="post" action="">
            <h3>Dodaj Novi Festival</h3>

            <label for="festival_name">Naziv Festivala:</label>
            <input type="text" name="festival_name" id="festival_name" required placeholder="Naziv Festivala">

            <label for="festival_location">Lokacija:</label>
            <input type="text" name="festival_location" id="festival_location" required placeholder="Lokacija">

            <label for="festival_start_date">Datum Početka:</label>
            <input type="date" name="festival_start_date" id="festival_start_date" required>

            <label for="festival_end_date">Datum Kraja:</label>
            <input type="date" name="festival_end_date" id="festival_end_date" required>

            <label for="festival_description">Opis:</label>
            <textarea name="festival_description" id="festival_description" required placeholder="Opis Festivala"></textarea>
            <label for="festival_organizator">Organizator:</label>
            <select id="festival_organizator" name="festival_organizator" required>
                        
            
                <option value="" disabled selected>Izaberi organizatora</option>
                <?php foreach ($organizatori as $organizator): ?>
                    <option value="<?= $organizator['organizator_id'] ?>"><?= htmlspecialchars($organizator['username']) ?></option>
                <?php endforeach; ?>
            
            </select>

            <input type="hidden" name="festival_organizator_id" id="festival_organizator_id">
            <script>
                        document.getElementById("festival_organizator").addEventListener("change", function() {
                document.getElementById("festival_organizator_id").value = this.value;
            });
            </script>
            <br>
            <label for="festival_image_url">URL Slike:</label>
            <input type="text" name="festival_image_url" id="festival_image_url" required placeholder="URL Slike">

            <button type="submit" name="add_festival">Dodaj Festival</button>
        </form>
    </div>

    <h1>Postojeći Festivali</h1>
    <table>
        <tr>
            
            <th>Naziv</th>
            <th>Lokacija</th>
            <th>Datum Početka</th>
            <th>Datum Kraja</th>
            <th>Izaberi</th>
        </tr>
        <?php foreach ($festivals as $festival): ?>
            <tr>
                
                <td><?= $festival['name'] ?></td>
                <td><?= $festival['location'] ?></td>
                <td><?= $festival['start_date'] ?></td>
                <td><?= $festival['end_date'] ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="festival_id" value="<?= $festival['festival_id'] ?>">
                        <button type="submit" name="delete_festival" onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj festival?');">Obriši</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <h1>Statistika poseta festivala</h1>

    <!-- Div za prikazivanje grafikona -->
    <div class="chart-container">
        <canvas id="myPieChart1" width="400" height="400"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        
        const labels = <?php echo json_encode($labels); ?>;
        const data = <?php echo json_encode($data); ?>;

        const ctx1 = document.getElementById('myPieChart1').getContext('2d');
        const myPieChart1 = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($data); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#FF9F40'],
                    hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#FF9F40']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' poseta';
                            }
                        }
                    }
                }
            }
        });

         
    


    </script>

    <h1>Statistika ocene eventa</h1>
    <div class="chart-container">
        <canvas id="myPieChart2" width="420" height="420"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Podaci za drugi grafikon
    const labels2 = <?php echo json_encode($event_names1); ?>;
    const data2 = <?php echo json_encode($average_ratings1); ?>;

    const ctx2 = document.getElementById('myPieChart2').getContext('2d');
    const myPieChart2 = new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: labels2,
            datasets: [{
                data: data2,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#FF9F40'],
                hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#FF9F40']
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top',
                    labels: {
                    boxWidth: 40, // Povećava širinu kvadratića u legendi
                    font: { size: 10 },
                    padding: 10, // Dodaje malo prostora između legendi
                    usePointStyle: true, // Omogućava kružne oznake umesto kvadrata
                    }   
                },
                
                tooltip: {
                    padding: 0,
                    callbacks: {
                        label: function(tooltipItem) {
                            return tooltipItem.label + ': ' + tooltipItem.raw + ' ocena';
                        }
                    }
                }
            }
           
        }
    });

        
    </script>
    <h1>Postojeći Eventi</h1>
    <table>
        <tr>
            
            <th>Naziv</th>
            <th>Vreme</th>
            <th>Scena</th>
            <th>Izaberi</th>
        </tr>
        <?php foreach ($events as $event): ?>
            <tr>
                
                <td><?= $event['name'] ?></td>
                <td><?= $event['event_time'] ?></td>
                <td><?= $event['scene'] ?></td>
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                        <button type="submit" name="delete_event" onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj event?');">Obriši</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    
    

    <h1>Svi Komentari</h1>
    <table>
        <tr>
            <th>Ime eventa</th>
            <th>Ime izvodjaca</th>
            <th>Autor</th>
            <th>Komentar</th>
            <th>Izaberi</th>
            
        </tr>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?= $comment['event_name'] ?></td>
                <td><?= $comment['performer_name'] ?></td>
                <td><?= $comment['user_name'] ?></td> 
                <td><?= $comment['content'] ?></td>
                
                <td>
                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                        <button type="submit" name="delete_comment" onclick="return confirm('Da li ste sigurni da želite da obrišete ovaj komentar?');">Obriši Komentar</button>
                    </form>

                    <form method="post" action="" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= $comment['user_id'] ?>">
                        <button type="submit" name="delete_user" onclick="return confirm('Da li ste sigurni da želite da blokirate ovog korisnika?');">Blokiraj Korisnika</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="form-container">
    <h3>Dodaj Novog Izvođača</h3>
    <form method="POST" action="">

        <label for="performer_name">Ime Izvođača:</label>
        <input type="text" name="performer_name" id="performer_name" required>

        <label for="performer_genre">Žanr:</label>
        <input type="text" name="performer_genre" id="performer_genre" required>

        <label for="performer_bio">Biografija:</label>
        <textarea name="performer_bio" id="performer_bio" required></textarea>

        <label for="performer_image_url">Slika Izvođača (URL):</label>
        <input type="text" name="performer_image_url" id="performer_image_url" required>

        <label for="event_ids">Izbor Događaja (ako postoji):</label>
        <select name="event_ids[]" id="event_ids" multiple required>
            <?php
            $query = "SELECT * FROM events";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $events = $stmt->fetchAll();

            foreach ($events as $event) {
                echo "<option value='" . $event['event_id'] . "'>" . $event['name'] . "</option>";
            }
            ?>
        </select>

        <button type="submit" name="add_performer">Dodaj Izvođača</button>
    </form>
    </div>
    


    <div class="form-container">
    <?php

        $user_id = $_SESSION['user_id']; 

        $stmt = $conn->prepare("SELECT * FROM ankete WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT); 
        $stmt->execute();
        $resultAnkete = $stmt->fetchAll(PDO::FETCH_ASSOC); 

        echo '<h2>Vaše ankete:</h2>';

        if (!empty($resultAnkete)) {
            echo '<div class="kartice-container">'; 
            foreach ($resultAnkete as $anketa) { 
                echo '<div class="kartica">';
                echo '<div class="kartica-header">';
                echo '<h3>' . htmlspecialchars($anketa['naziv']) . '</h3>';
                echo '</div>';
                echo '<div class="kartica-body">';
                echo '<a href="edit_anketa.php?anketa_id=' . $anketa['id'] . '" class="btn-edit">Uredi</a>';
                echo ' | ';
                echo '<a href="admin.php?delete_anketa_id=' . $anketa['id'] . '" class="btn-delete" onclick="return confirm(\'Da li ste sigurni da želite da obrišete ovu anketu?\')">Obriši</a>';
                echo '</div>';
                echo '</div>';
            }
            echo '</div>'; 
        } else {
            echo '<p>Nemate kreiranih anketa.</p>';
        }
    ?>
        <h2>Kreiraj novu anketu</h2>
        
        <form method="post">
            <label>Naziv ankete:</label>
            <input type="text" name="naziv_ankete" required><br><br>

            <div id="pitanja-container">
                <div class="pitanje" data-pitanje-id="0">
                    <label>Pitanje:</label>
                    <input type="text" name="pitanja[]" required><br><br>

                    <label>Opcije:</label><br>
                    <div class="opcije">
                        <div class="opcija">
                            <input type="text" name="opcije[0][]" required>
                            <button type="button" class="obrisi-opciju">Obriši opciju</button>
                        </div>
                    </div>

                    <button type="button" onclick="dodajOpciju(this)">+ Dodaj opciju</button><br><br>
                    <button type="button" class="obrisi-pitanje">Obriši pitanje</button>
                </div>
            </div>

            <button type="button" onclick="dodajPitanje()">+ Dodaj još jedno pitanje</button><br><br>
            <button type="submit">Napravi anketu</button>
        </form>

        <script>
            let pitanjeID = 0;  

            function dodajPitanje() {
                pitanjeID++;
                let pitanjaContainer = document.getElementById('pitanja-container');

                let novoPitanje = document.createElement('div');
                novoPitanje.classList.add('pitanje');
                novoPitanje.setAttribute('data-pitanje-id', pitanjeID);

                novoPitanje.innerHTML = `
                    <label>Pitanje:</label>
                    <input type="text" name="pitanja[]" required><br><br>

                    <label>Opcije:</label><br>
                    <div class="opcije">
                        <div class="opcija">
                            <input type="text" name="opcije[${pitanjeID}][]" required>
                            <button type="button" class="obrisi-opciju">Obriši opciju</button>
                        </div>
                    </div>

                    <button type="button" onclick="dodajOpciju(this)">+ Dodaj opciju</button><br><br>
                    <button type="button" class="obrisi-pitanje">Obriši pitanje</button>
                `;

                pitanjaContainer.appendChild(novoPitanje);
            }

            function dodajOpciju(button) {
                let opcijeDiv = button.previousElementSibling; 
                let pitanjeIndex = button.parentElement.getAttribute('data-pitanje-id');

                let novaOpcija = document.createElement('div');
                novaOpcija.classList.add('opcija');
                novaOpcija.innerHTML = `
                    <input type="text" name="opcije[${pitanjeIndex}][]" required>
                    <button type="button" class="obrisi-opciju">Obriši opciju</button>
                `;

                opcijeDiv.appendChild(novaOpcija);
            }

            
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('obrisi-opciju')) {
                    let opcijaDiv = e.target.parentElement;
                    opcijaDiv.remove();
                }
            });

            
            document.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('obrisi-pitanje')) {
                    let pitanjeDiv = e.target.closest('.pitanje');
                    pitanjeDiv.remove();
                }
            });
        </script>



    </div>
    <div class="container">
        <h2>Statistika anketa</h2>

        <?php
        foreach ($anketenovo as $anketa) {
            echo "<div class='anketa-statistika'>";
            echo "<h3>Statistika za anketu: " . htmlspecialchars($anketa['naziv']) . "</h3>";

            $stmt = $conn->prepare("
                SELECT opcije.opcija, COUNT(odgovori.id) AS broj_odgovora
                FROM odgovori
                LEFT JOIN opcije ON odgovori.opcija_id = opcije.id
                WHERE odgovori.anketa_id = :anketa_id
                GROUP BY odgovori.opcija_id
            ");
            $stmt->bindParam(':anketa_id', $anketa['id'], PDO::PARAM_INT);
            $stmt->execute();
            $odgovori_opcije = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("SELECT COUNT(*) AS ukupno_glasova FROM odgovori WHERE anketa_id = :anketa_id");
            $stmt->bindParam(':anketa_id', $anketa['id'], PDO::PARAM_INT);
            $stmt->execute();
            $ukupno_glasova = $stmt->fetch(PDO::FETCH_ASSOC)['ukupno_glasova'];

            echo "<ul>";
            foreach ($odgovori_opcije as $odgovor) {
                $procenat = ($ukupno_glasova > 0) ? round(($odgovor['broj_odgovora'] / $ukupno_glasova) * 100, 2) : 0;
                echo "<li class='opcija-statistika'>";
                echo "<span>" . htmlspecialchars($odgovor['opcija']) . "</span>";
                echo "<span>Broj glasova: " . $odgovor['broj_odgovora'] . "</span>";
                echo "<span class='procenat'>(" . $procenat . "%)</span>";
                echo "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        ?>

    </div>
    <h1>Postojeći Izvođači</h1>
    <table>
        <tr>
            <th>Ime</th>
            <th>Izaberi</th>
            
        </tr>
        <?php
        $query = "SELECT * FROM performers";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $performers = $stmt->fetchAll();

        foreach ($performers as $performer) {
            echo "<tr>";
            echo "<td>" . $performer['name'] . "</td>";
            
            echo "<td>
                <form method='post' action='' style='display:inline;'>
                    <input type='hidden' name='performer_id' value='" . $performer['id'] . "'>
                    <button type='submit' name='delete_performer' onclick='return confirm(\"Da li ste sigurni da želite da obrišete ovog izvođača?\");'>Obriši</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
    </table>
    
        <h1>Postojeći Korisnici</h1>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Izaberi</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['username'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" name="delete_user" onclick="return confirm('Da li ste sigurni da želite da obrišete ovog korisnika?');">Obriši</button>
                        </form>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" name="block_user" onclick="return confirm('Da li ste sigurni da želite da blokirate ovog korisnika?');">Blokiraj</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
 
 
</body>
</html>
