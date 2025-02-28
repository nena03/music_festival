<?php
require_once 'db_connection.php'; 

if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    $check_event_query = "SELECT * FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($check_event_query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_event_performers = "DELETE FROM event_performers WHERE event_id = ?";
        $stmt = $conn->prepare($delete_event_performers);
        $stmt->bind_param("i", $event_id);
        $stmt->execute();

        $delete_event_query = "DELETE FROM events WHERE event_id = ?";
        $stmt = $conn->prepare($delete_event_query);
        $stmt->bind_param("i", $event_id);
        if ($stmt->execute()) {
            echo "Događaj je uspešno obrisan.";
        } else {
            echo "Greška pri brisanju događaja: " . $conn->error;
        }
    } else {
        echo "Događaj nije pronađen.";
    }
} else {
    echo "Nema ID događaja za brisanje.";
}
?>
