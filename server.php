<?php
include('config.php');
session_start();


if (!isset($_SESSION['user_id'])) {
    echo "Morate biti prijavljeni da biste kupili ili rezervisali ulaznice.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $eventId = $_POST['event'];  // ID događaja
    $seatCount = $_POST['seat'];  // Broj mesta
    $seatType = $_POST['seatType'];  // Tip mesta
    $userId = $_SESSION['user_id'];  // ID korisnika sa sesije

   
    if (empty($eventId) || empty($seatCount) || empty($seatType)) {
        echo "Molimo vas da popunite sva polja.";
        exit;
    }

    // Definisanje cene na osnovu tipa mesta
    $prices = [
        'VIP' => 5000,
        'srednja' => 3000,
        'niza' => 2000
    ];

    // Provera da li je tip mesta validan
    if (!array_key_exists($seatType, $prices)) {
        echo "Neispravan tip mesta.";
        exit;
    }

    $price = $prices[$seatType] * $seatCount;  

   
    $sqlEvent = "SELECT name FROM events WHERE event_id = :event_id";
    $stmtEvent = $conn->prepare($sqlEvent);
    $stmtEvent->bindParam(':event_id', $eventId);
    $stmtEvent->execute();

    
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        echo "Događaj nije pronađen.";
        exit;
    }
    $eventName = $event['name'];  

    
    $sql = "INSERT INTO tickets (user_id, event_id, event_name, seat_count, seat_type, price) 
            VALUES (:user_id, :event_id, :event_name, :seat_count, :seat_type, :price)";
    $stmt = $conn->prepare($sql);

    
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':event_id', $eventId);
    $stmt->bindParam(':event_name', $eventName);
    $stmt->bindParam(':seat_count', $seatCount);
    $stmt->bindParam(':seat_type', $seatType);
    $stmt->bindParam(':price', $price);

    try {
        $stmt->execute();
        echo "<script>
                    window.location.href = 'ticket.php'; 
                    alert('Uspešno ste kupili ulaznice.');
                  </script>";
    } catch (PDOException $e) {
        echo "Greška pri unosu: " . $e->getMessage();
    }

    $conn = null;  // Zatvaranje konekcije
}
?>
