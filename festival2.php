<?php
require_once 'config.php'; 

if (isset($_GET['festival_id'])) {
    $festival_id = $_GET['festival_id'];

    try {
        $festivalStmt = $conn->prepare("SELECT * FROM festivals WHERE festival_id = :festival_id");
        $festivalStmt->bindParam(':festival_id', $festival_id);
        $festivalStmt->execute();
        $festival = $festivalStmt->fetch(PDO::FETCH_ASSOC);

        if ($festival) {
            $eventsStmt = $conn->prepare("
                SELECT e.event_id, e.name AS event_name, e.description, e.event_time, e.scene,
                    p.name AS performer_name, p.genre AS performer_genre, p.bio AS performer_bio, p.image_url AS performer_image, p.id AS performer_id
                FROM events e
                LEFT JOIN event_performers ep ON e.event_id = ep.event_id
                LEFT JOIN performers p ON ep.performer_id = p.id
                WHERE e.festival_id = :festival_id
                ORDER BY e.event_time ASC
            ");

            $eventsStmt->bindParam(':festival_id', $festival_id);
            $eventsStmt->execute();
            $events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            echo "Festival nije pronađen.";
            exit();
        }
    } catch (PDOException $e) {
        echo "Greška prilikom preuzimanja podataka: " . $e->getMessage();
    }
} else {
    echo "ID festivala nije prosleđen.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Festivala</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #333; 
            color: #f4f4f4; 
            padding: 20px;
        }
        .festival-header {
            text-align: center;
            margin-bottom: 30px;
            color: #e53935; 
            font-size: 2em; 
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .event {
            background-color: #e53935; 
            border-radius: 8px;
            margin: 20px 0;
            padding: 25px; 
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #b71c1c; 
            transition: transform 0.3s ease;
        }
        .event:hover {
            transform: scale(1.03); 
        }
        .event-time {
            font-weight: bold;
            font-size: 1.4em; 
            margin-bottom: 15px;
            color: #fff;
        }
        .event img {
            max-width: 100px; 
            margin-right: 20px; 
        }
        .event-details {
            display: flex;
            align-items: center;
        }
        .event-details p {
            margin: 0;
            color: #fff;
            font-size: 1.1em; 
        }
        .performer-icons {
            display: flex;
            justify-content: start;
            margin-top: 20px;
        }
        .performer-icons img {
            width: 70px; 
            height: 70px;
            border-radius: 50%;
            margin-right: 15px;
            transition: transform 0.3s ease, filter 0.3s ease;
        }
        .performer-icons img:hover {
            transform: scale(1.3); 
            cursor: pointer;
            filter: brightness(0.8);
        }
        .performer-info {
            margin-top: 20px;
        }
        .performer-info p {
            margin: 5px 0;
            color: #fff;
            font-size: 1.1em; 
        }
        h2.event-time {
            font-size: 1.6em; 
            margin-top: 30px;
            color: #e53935; 
        }
    </style>
</head>
<body>

    <div class="festival-header">
        <h1><?php echo htmlspecialchars($festival['name']); ?> - Program</h1>
        <p><?php echo htmlspecialchars($festival['description']); ?></p>
    </div>

    <?php if (!empty($events)): ?>
        <div class="events">
            <?php 
            $current_event_time = '';
            $event_ids = []; 
            foreach ($events as $event): 
                $event_time = date('Y-m-d H:i', strtotime($event['event_time'])); 
                if ($event_time != $current_event_time): 
                    $current_event_time = $event_time;
            ?>
                <h2 class="event-time"><?php echo htmlspecialchars($current_event_time); ?></h2>
                <div class="event">
                    <div class="event-details">
                        <?php if (!empty($event['performer_image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['performer_image']); ?>" alt="<?php echo htmlspecialchars($event['performer_name']); ?>">
                        <?php endif; ?>
                        <div>
                            <h3><?php echo htmlspecialchars($event['event_name']); ?></h3>
                            <p><strong>Vreme:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
                            <p><strong>Scene:</strong> <?php echo htmlspecialchars($event['scene']); ?></p>
                            <p><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>
                    </div>

                    <div class="performer-icons">
                        <?php
                        $performersForEvent = array_filter($events, function ($e) use ($event) {
                            return $e['event_id'] == $event['event_id'] && !empty($e['performer_name']);
                        });
                        
                        foreach ($performersForEvent as $performer):
                        ?>
                            <a href="performer1.php?id=<?php echo $performer['performer_id']; ?>">
                                <img src="<?php echo htmlspecialchars($performer['performer_image']); ?>" alt="<?php echo htmlspecialchars($performer['performer_name']); ?>" title="<?php echo htmlspecialchars($performer['performer_name']); ?>">
                            </a>
                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Nema događaja za ovaj festival.</p>
    <?php endif; ?>

</body>
</html>


