<?php
require 'config.php'; 
if (isset($_GET['festival_id'])) {
    $festival_id = intval($_GET['festival_id']); 
}

try {
    
    $conditions = [];
    $params = [];

    if (!empty($_GET['genre'])) {
        $conditions[] = "p.genre LIKE :genre";
        $params[':genre'] = '%' . $_GET['genre'] . '%';
    }

    if (!empty($_GET['event_time'])) {
        $conditions[] = "e.event_time = :event_time";
        $params[':event_time'] = $_GET['event_time'];
    }

    if (!empty($_GET['scene'])) {
        $conditions[] = "e.scene LIKE :scene";
        $params[':scene'] = '%' . $_GET['scene'] . '%';
    }

    
    $sql = "SELECT p.id, p.name, p.genre, e.event_time, e.scene 
            FROM performers p
            JOIN event_performers ep ON p.id = ep.performer_id
            JOIN events e ON ep.event_id = e.event_id";

    
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    
    $stmt2 = $conn->prepare($sql); 
    $stmt2->execute($params);
    $results = $stmt2->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Greška: " . $e->getMessage();
}

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pretraga izvođača</title>
    <style>
    
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #ff416c, #ff4b2b); /* Gradijent */
        display: block;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: white;
    }

    
    ul {
        list-style-type: none;
        padding: 0;
        margin: 0;
        text-align: center;
    }

    ul li {
        background-color: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
        animation: fadeIn 1s ease-in-out;
    }

    ul li:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: scale(1.05);
    }

    
    ul li a {
        font-size: 18px;
        color: white;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    ul li a:hover {
        color: #f1c40f;
    }

    
    button[type="submit"] {
        background-color:rgb(12, 11, 11);
        color: white;
        padding: 15px 25px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none;
        display: inline-block;
        width: 30%;
        margin-bottom:10px;
    }

    button[type="submit"]:hover {
        background-color:rgb(90, 71, 71);
        color:white;
        transform: scale(1.05);
    }
    button{
        background-color:rgb(12, 11, 11);
        color: white;
        padding: 15px 25px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none;
        display: inline-block;
        width: 30%;
        margin-bottom:10px;
    }

    button:hover {
        background-color:rgb(90, 71, 71);
        color:white;
        transform: scale(1.05);
    }

    
    input[type="text"], select {
        padding: 15px 25px;
        font-size: 16px;
        width: 100%;
        border-radius: 8px;
        border: 2px solid #ddd;
        margin-bottom: 20px;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus, select:focus {
        border-color: #e74c3c;
        box-shadow: 0 0 8px rgba(231, 76, 60, 0.4);
    }

    
    .search-container {
        max-width: 600px;
        margin: 40px auto;
        padding: 25px;
        background-color: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        transition: box-shadow 0.3s ease, transform 0.2s ease;
        text-align: center;
        
        align-items: center; /* Centriranje po širini */
        justify-content: center;
    }
    .search-container button {
        background-color:rgb(12, 11, 11);
        color: white;
        padding: 15px 25px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none;
        display: inline-block;
        width: 30%;
        margin-bottom:10px;
        text-align: center;
        
        align-items: center; /* Centriranje po širini */
        justify-content: center;
    }
   

    .search-container:hover {
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        transform: translateY(-5px);
    }

    
    p {
        text-align: center;
        font-size: 18px;
        color: #888;
    }

    n {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    

    /* Animacija za dugme (početni efekat prilikom učitavanja stranice) */
    @keyframes buttonFadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .back-button {
        background-color:rgb(15, 14, 14);
        color: white;
        padding: 15px 25px;
        font-size: 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        border: none;
        display:block;
        width: 30%;
        margin-bottom:10px;
    }
    .back-button:hover {
        background-color:rgb(86, 71, 69);
        transform: scale(1.05);
    }

</style>
</head>
<body>

<div class="search-container">
    <form action="search.php" method="GET">
        <input type="hidden" name="festival_id" value="<?php echo htmlspecialchars($festival_id); ?>">

        
        <select name="genre">
            <option value="">Izaberite žanr</option>
            <?php
            
            foreach ($genres as $genre) {
                echo "<option value='$genre'>$genre</option>";
            }
            ?>
        </select>

        
        <select name="event_time">
            <option value="">Izaberite vreme nastupa</option>
            <?php
            
            foreach ($times as $time) {
                echo "<option value='$time'>$time</option>";
            }
            ?>
        </select>

        
        <select name="scene">
            <option value="">Izaberite scenu</option>
            <?php
           
            foreach ($scenes as $scene) {
                echo "<option value='$scene'>$scene</option>";
            }
            ?>
        </select>
        <div style="text-align: center;">
        <button type="submit">Pretraži izvodjača</button>
        </div>
        <div style="text-align: center;">
        <button type="button" onclick="location.href='festival1.php?festival_id=<?php echo urlencode($festival_id); ?>'" class="back-button">Nazad na sajt festivala</button>
        </div>

    </form>
</div>





<?php
if ($results) {
    echo "<ul>";
    foreach ($results as $row) {
        echo "<li><a href='performer.php?festival_id={$festival_id}&id={$row['id']}'>
            {$row['name']}
        </a> - Žanr: {$row['genre']}, Vreme: {$row['event_time']}, Scena: {$row['scene']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Nema rezultata.</p>";
}
?>

</body>
</html>
