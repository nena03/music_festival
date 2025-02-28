<?php
include 'config.php';

if (isset($_GET['id'])) {
    $performer_id = $_GET['id'];

    $query = "SELECT * FROM performers WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $performer_id, PDO::PARAM_INT);
    $stmt->execute();

    $performer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$performer) {
        echo "Performer not found.";
        exit;
    }
} else {
    echo "No performer selected.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalji o Izvođaču</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 50px;
            text-align: center;
        }

        .performer-info {
            max-width: 800px;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(255, 255, 255, 0.2);
            animation: fadeIn 1s ease-out;
        }

        .performer-info img {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            animation: rotateIn 1s ease-out;
        }

        .performer-info h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            animation: slideInFromLeft 1s ease-out;
        }

        .performer-info p {
            font-size: 1.2rem;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes rotateIn {
            0% {
                transform: rotate(360deg);
                opacity: 0;
            }
            100% {
                transform: rotate(0deg);
                opacity: 1;
            }
        }

        @keyframes slideInFromLeft {
            0% {
                transform: translateX(-100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            0% {
                transform: translateY(30px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .back-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #8B0000;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #b22222;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="performer-info">
            <img src="<?php echo htmlspecialchars($performer['image_url']); ?>" alt="<?php echo htmlspecialchars($performer['name']); ?>">
            <h1><?php echo htmlspecialchars($performer['name']); ?></h1>
            <p><?php echo htmlspecialchars($performer['bio']); ?></p>
        </div>
        <a href="index.php" class="back-btn">Vrati se na početnu</a>
    </div>
</body>
</html>

