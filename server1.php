<?php

include('config.php');
session_start();


if (!isset($_SESSION['user_id'])) {
    echo "Morate biti prijavljeni da biste napravili rezervaciju.";
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $userId = $_SESSION['user_id'];  
    $eventId = $_POST['event'];  
    $seatCount = $_POST['seat'];  
    $seatType = $_POST['seatType'];  

    
    if (empty($eventId) || empty($seatCount) || empty($seatType)) {
        echo "Molimo vas da popunite sva polja.";
        exit;
    }

    
    $sql_event_name = "SELECT name FROM events WHERE event_id = :event_id";
    $stmt_event_name = $conn->prepare($sql_event_name);
    $stmt_event_name->bindParam(':event_id', $eventId);
    $stmt_event_name->execute();
    $eventName = $stmt_event_name->fetchColumn(); 

    if (!$eventName) {
        echo "Događaj nije pronađen.";
        exit;
    }

    
    $sql = "INSERT INTO reservations (user_id, event_id, event_name, seat_count, seat_type) 
            VALUES (:user_id, :event_id, :event_name, :seat_count, :seat_type)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':event_id', $eventId);
    $stmt->bindParam(':event_name', $eventName);
    $stmt->bindParam(':seat_count', $seatCount);
    $stmt->bindParam(':seat_type', $seatType);

    if ($stmt->execute()) {
        echo "<script>
                    window.location.href = 'ticket.php'; 
                    alert('Uspešno ste rezervisali ulaznice.');
                  </script>";
    } else {
        echo "Došlo je do greške prilikom rezervacije.";
    }
}

// Zatvaranje konekcije
$conn = null;
?>
