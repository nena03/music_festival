<?php
session_start(); 
require 'config.php';

if (!isset($_GET['id'])) {
    die("Nema ID-ja izvođača.");
}

$performer_id = $_GET['id'];

try {
    $stmt3 = $conn->prepare("SELECT * FROM performers WHERE id = :id");
    $stmt3->execute([':id' => $performer_id]);
    $performer = $stmt3->fetch(PDO::FETCH_ASSOC);

    if (!$performer) {
        die("Izvođač nije pronađen.");
    }
} catch (PDOException $e) {
    echo "Greška: " . $e->getMessage();
    exit;
}
$user_id = $_SESSION['user_id'];
if (isset($_GET['festival_id'])) 
{
    $festival_id = intval($_GET['festival_id']); 
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($performer['name']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: white;
        }
        .card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in-out;
        }
        .image-container {
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease-in-out;
        }
        .image-container:hover img {
            transform: scale(1.1);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 600;
        }
        p {
            font-size: 16px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .back-button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            transition: 0.3s;
        }
        .back-button:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="image-container">
        <?php if ($performer['image_url']): ?>
            <img src="<?php echo htmlspecialchars($performer['image_url']); ?>" alt="<?php echo htmlspecialchars($performer['name']); ?>">
        <?php else: ?>
            <img src="default-image.jpg" alt="Default Image">
        <?php endif; ?>
    </div>
    <h1><?php echo htmlspecialchars($performer['name']); ?></h1>
    <p><strong>Žanr:</strong> <?php echo htmlspecialchars($performer['genre']); ?></p>
    <p><strong>Biografija:</strong> <?php echo nl2br(htmlspecialchars($performer['bio'])); ?></p>
    <a href="search.php?festival_id=<?php echo urlencode($festival_id); ?>" class="back-button">Nazad na pretragu</a>
    <a href="festival1.php?festival_id=<?php echo urlencode($festival_id); ?>" class="back-button">Nazad na sajt festivala</a>
</div>

</body>
</html>
