<?php
include 'config.php';

$query = "SELECT name, image_url, festival_id FROM festivals";
$stmt = $conn->query($query);


$festivals = $stmt->fetchAll(PDO::FETCH_ASSOC);


$performerQuery = "SELECT p.name,p.id, p.image_url, COUNT(fp.performer_id) AS popularity 
                   FROM favourite_performers fp
                   JOIN performers p ON fp.performer_id = p.id
                   GROUP BY fp.performer_id
                   ORDER BY popularity DESC
                   LIMIT 6";
$performerStmt = $conn->query($performerQuery);
$performers = $performerStmt->fetchAll(PDO::FETCH_ASSOC);

$performerQuery1 = "SELECT p.id, p.name, p.image_url 
                   FROM performers p 
                   ORDER BY p.name";  
$performerStmt1 = $conn->query($performerQuery1);
$performers1 = $performerStmt1->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muzički Festivali</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color:rgba(18, 18, 18, 0.9); 
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        header {
            background: linear-gradient(90deg,rgb(187, 30, 30), #8B0000);
            padding: 20px 0;
            border-bottom: 3px solid #ffffff;
            text-transform: uppercase;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            transition: 0.3s;
            position: relative;
        }

        nav ul li a::after {
            content: '';
            display: block;
            width: 0;
            height: 2px;
            background: yellow;
            transition: width 0.3s;
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
        }

        nav ul li a:hover::after {
            width: 100%;
        }

        .festivali-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
            color:white;
        }
        

        .festival {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease-in-out;
            cursor: pointer;
        }

        .festival:hover {
            transform: scale(1.1);
            background:white;
            color:black;
        }

        .festival img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.5s ease-in-out;
        }

        .festival:hover img {
            transform: rotateY(360deg);
            
        }
        .festival p {
            color:white;
            
        }
        .festival:hover p {
            color:black;
            
        }

        footer {
            margin-top: 50px;
            background: linear-gradient(90deg, #8B0000, #b22222);
            padding: 20px;
            color: white;
            border-top: 3px solid #ffffff;
        }

        .sponzori {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .sponzori img {
            width: 100px;
            transition: transform 0.3s ease-in-out;
            filter: grayscale(100%);
        }

        .sponzori img:hover {
            transform: scale(1.2);
            filter: grayscale(0%);
        }
        .festivali-container, .izvodjaci-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }

        .festival, .izvodjac {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.2);
            transition: transform 0.3s;
            cursor: pointer;
        }

        .festival:hover, .izvodjac:hover {
            transform: scale(1.1);
        }

        .festival img, .izvodjac img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .festival:hover img, .izvodjac:hover img {
            transform: rotateY(360deg);
            background:white;
            color:black;
        }

        footer {
            margin-top: 50px;
            background: linear-gradient(90deg, #8B0000, #b22222);
            padding: 20px;
            color: white;
            border-top: 3px solid #ffffff;
        }
                
        @keyframes slideIn {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes namePopUp {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            60% {
                transform: scale(1.1);
                opacity: 1;
            }
            100% {
                transform: scale(1);
            }
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
    }

    .izvodjac:hover {
        transform: scale(1.1);
        background:white;
        color:black;
    }

    .izvodjac img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        transition: transform 0.5s ease-in-out;
        animation: slideIn 0.5s ease-out;
    }

    .izvodjac p {
        font-size: 16px;
        color: white;
        font-weight: bold;
        animation: namePopUp 0.8s ease-out;
    }
    .izvodjac:hover p {
        font-size: 16px;
        color: black;
        font-weight: bold;
        animation: namePopUp 0.8s ease-out;
    }

    .izvodjac:hover img {
        transform: rotateY(360deg);
        color:black;
        
    }
    .ticket-button {
    display: inline-block;
    padding: 15px 30px;
    font-size: 20px;
    font-weight: bold;
    text-decoration: none;
    background-color: #ff0000; 
    color: white; 
    border-radius: 25px;
    border: 2px solid #ff0000; 
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    transition: all 0.3s ease;
    box-shadow: 0 0 8px 2px rgba(255, 255, 255, 0.6);  
    margin-top:80px;
    margin-bottom:60px;
}

.ticket-button:hover {
    background-color: #e60000; 
    transform: scale(1.1); 
}

.ticket-button::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 300%;
    height: 300%;
    background-color: rgba(255, 255, 255, 0.3); 
    border-radius: 50%;
    animation: glow-animation 1.5s infinite;
    transition: all 0.3s ease;
    transform: translate(-50%, -50%);
}

.ticket-button:hover::before {
    animation: none; 
    width: 350%;
    height: 350%;
    background-color: rgba(255, 255, 255, 0.5); 
}

@keyframes glow-animation {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.8;
    }
    50% {
        transform: translate(-50%, -50%) scale(1.2);
        opacity: 1;
    }
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 0.8;
    }
}
.welcome-message {
    background-color:rgb(199, 36, 34); 
    color: #fff; 
    text-align: center;
    padding: 30px 20px; 
}

.welcome-message h1 {
    font-size: 3em; 
    font-weight: bold;
    text-transform: uppercase; 
    text-shadow: 3px 3px 5px rgba(0, 0, 0, 0.7); 
    margin: 0;
}

</style>
</head>
<body>
<div class="welcome-message">
        <h1>Welcome to Music Festivals Application</h1>
    </div>
    <header>
        <nav>
            <ul>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Registracija</a></li>
                <li><a href="program.php">Program Festivala</a></li>
            </ul>
        </nav>
       
    </header>
    
   
    <section class="festivali">
    <h1>Popularni Festivali</h1>
    <div class="festivali-container">
        <?php foreach ($festivals as $row) { ?>
            <div class="festival">
                <a href="festival2.php?festival_id=<?php echo $row['festival_id']; ?>">
                    <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <p><?php echo htmlspecialchars($row['name']); ?></p>
                </a>
            </div>
        <?php } ?>
    </div>
    </section>


    <a href="ticket.php" class="ticket-button">Ticket</a>

    <section class="izvodjaci">
        <h1>Izvođači</h1>
            <div class="izvodjaci-container">
                <?php 
                foreach ($performers1 as $row1) { ?>
                    <div class="izvodjac">
                        <a href="performer1.php?id=<?php echo $row1['id']; ?>">
                            <img src="<?php echo htmlspecialchars($row1['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </a>
                       
                            <p><?php echo htmlspecialchars($row1['name']); ?></p>
                        
                    </div>
                <?php } ?>
            </div>
    </section>


    <footer>
    <section class="izvodjaci">
        <h1>Najpopularniji Izvođači</h1>
        <div class="izvodjaci-container">
            <?php 
            foreach ($performers as $row) { ?>
                <div class="izvodjac">
                        <a href="performer1.php?id=<?php echo $row['id']; ?>">
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </a>
                        <a href="performer1.php?id=<?php echo $row['id']; ?>">
                            <p><?php echo htmlspecialchars($row['name']); ?></p>
                        </a>
                </div>
            <?php } ?>
        </div>
        <br>
        <br>
        <h1>Uživo stream</h1>
    <iframe width="560" height="315" style="margin-top: 20px;" src="https://www.youtube.com/embed/dQL2lw4llRs" frameborder="0" allowfullscreen ></iframe>
    </section>
   
    </footer>

    <footer>
        <h2>Sponzori</h2>
        <div class="sponzori">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9y682maxiDAzIT7uftcCD-VuHjPgbyiXPew&s" alt="EXIT">
            <img src="https://ih1.redbubble.net/image.1447422943.5063/st,small,507x507-pad,600x600,f8f8f8.webp" alt="Guarana">
            <img src="https://static.wixstatic.com/media/8cb387_faccb7224ee74c57a14f945ab14039ee~mv2.gif/v1/fill/w_320,h_180,q_90/8cb387_faccb7224ee74c57a14f945ab14039ee~mv2.gif" alt="Jelen Pivo">
            <img src="https://seeklogo.com/images/T/tuborg-logo-889B8A3EDB-seeklogo.com.png" alt="Tuborg">
            <img src="https://www.tramatm.cz/_next/image?url=https%3A%2F%2Ftrama-static.s3.eu-central-1.amazonaws.com%2Fimages%2Fhall-of-fame%2Flogos%2F7-logo.png&w=3840&q=75" alt="Coca-Cola">
            <img src="https://stunodracing.net/index.php?attachments/redbull-racing-team-png.128674/" alt="Red Bull">
            <img src="https://thumbs.dreamstime.com/b/heineken-logo-editorial-illustrative-white-background-eps-download-vector-jpeg-banner-heineken-logo-editorial-illustrative-208329404.jpg" alt="Heineken">
            <img src="https://www.serbianlogo.com/thumbnails/niksicko_pivo1.gif" alt="Nikšićko Pivo">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0f/Pepsi_logo_2014.svg/1509px-Pepsi_logo_2014.svg.png" alt="Pepsi">
            <img src="https://brandlogos.net/wp-content/uploads/2015/11/jack-daniels-logo-vector-download.jpg" alt="Jack Daniels">
            <img src="https://brandgenetics.com/wp-content/uploads/2019/11/jagermeister-logo-2-e1573914387874.png" alt="Jägermeister">
        </div>
        
    </footer>
    
    <footer>
    <p>&copy; 2025 Music Festivals Platform - All rights reserved</p>
</footer>
</body>
</html>
