<?php
session_start();
include 'db_connection.php'; 

$query = "SELECT festival_id, name, image_url FROM festivals";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festivali</title>
    
    <style>
        body {
            background-color: #8B0000; /* Trula višnja */
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url('https://img.pikbest.com/back_our/20210329/bg/772cfa388cfdd.png!sw800');
            background-size: cover;  
            background-repeat: no-repeat;  
            background-position: center;  
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            width: 80%;
        }
        .festival-card {
            background-color: black;
            color: black;
            width: 250px;
            height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            overflow: hidden;
            position: relative;
        }
        .festival-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            border-radius: 15px;
            opacity: 0.8;
        }
        .festival-card span {
            position: relative;
            z-index: 1;
            color:white;
            font-size:40px;
        }
        .festival-card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <?php while ($festival = $result->fetch_assoc()): ?>
            <a href="festival1.php?festival_id=<?php echo $festival['festival_id']; ?>" class="festival-card">
                <img src="<?php echo htmlspecialchars($festival['image_url']); ?>" alt="<?php echo htmlspecialchars($festival['name']); ?>">
                <span><?php echo htmlspecialchars($festival['name']); ?></span>
            </a>
        <?php endwhile; ?>
    </div>
    
    <div style="text-align: left; padding: 10px;">
        <a href="logout.php" style="font-size: 24px; color:rgb(252, 247, 247); text-decoration: none;">
            <i class="fas fa-sign-out-alt"></i> Odjavi se
        </a>
    </div>
</body>
</html>
