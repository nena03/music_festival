<?php
require 'config.php';

if (!isset($_GET['anketa_id'])) {
    die("Anketa nije pronađena!");
}

$anketa_id = $_GET['anketa_id'];
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$festival_id = isset($_GET['festival_id']) ? $_GET['festival_id'] : null;
if ($user_id && $anketa_id) {
    
    $stmt = $conn->prepare("SELECT * FROM odgovori WHERE user_id = :user_id AND anketa_id = :anketa_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':anketa_id', $anketa_id, PDO::PARAM_INT);
    $stmt->execute();
    $existing_answer = $stmt->fetch(PDO::FETCH_ASSOC);

    
    if ($existing_answer) {
        echo "<script>
        alert('Vec ste glasali!');
        window.location.href='festival1.php?festival_id=$festival_id';
        </script>";
    }
}


$stmt = $conn->prepare("SELECT * FROM ankete WHERE id = ?");
$stmt->execute([$anketa_id]);
$anketa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anketa) {
    die("Anketa ne postoji!");
}


$stmt = $conn->prepare("
    SELECT pitanja.id AS pitanje_id, pitanja.pitanje, opcije.id AS opcija_id, opcije.opcija
    FROM pitanja 
    LEFT JOIN opcije ON pitanja.id = opcije.pitanje_id
    WHERE pitanja.anketa_id = ?
");
$stmt->execute([$anketa_id]);
$pitanja_opcije = $stmt->fetchAll(PDO::FETCH_ASSOC);


$pitanja = [];
foreach ($pitanja_opcije as $row) {
    $pitanja[$row['pitanje_id']]['pitanje'] = $row['pitanje'];
    $pitanja[$row['pitanje_id']]['opcije'][] = [
        'id' => $row['opcija_id'],
        'tekst' => $row['opcija']
    ];
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['odgovori'] as $pitanje_id => $opcija_id) {
        
        $stmt = $conn->prepare("INSERT INTO odgovori (anketa_id, pitanje_id, opcija_id, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$anketa_id, $pitanje_id, $opcija_id, $user_id]);
    }
    echo "<script>
    alert('Uspešno ste glasali!');
    window.location.href='festival1.php?festival_id=$festival_id';
    </script>";


    exit;
}

?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($anketa['naziv']) ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-align: center;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: rgba(255, 255, 255, 0.2);
            padding: 20px;
            border-radius: 15px;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .pitanje {
            background: rgba(255, 255, 255, 0.3);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            text-align: left;
            transition: 0.3s ease-in-out;
        }

        .pitanje:hover {
            transform: scale(1.05);
        }

        input[type="radio"] {
            accent-color: #ffcc00;
            margin-right: 10px;
        }

        label {
            font-size: 18px;
        }

        button {
            background: #ffcc00;
            color: black;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: #ffdb4d;
            transform: scale(1.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <h2><?= htmlspecialchars($anketa['naziv']) ?></h2>

        <form method="post">
            <?php foreach ($pitanja as $pitanje_id => $podaci): ?>
                <div class="pitanje">
                    <p><strong><?= htmlspecialchars($podaci['pitanje']) ?></strong></p>
                    <?php foreach ($podaci['opcije'] as $opcija): ?>
                        <label>
                            <input type="radio" name="odgovori[<?= $pitanje_id ?>]" value="<?= $opcija['id'] ?>" required>
                            <?= htmlspecialchars($opcija['tekst']) ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit">Pošalji glas</button>
        </form>
    </div>

</body>
</html>
