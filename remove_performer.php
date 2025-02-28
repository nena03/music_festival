<?php
require_once 'db_connection.php'; 


if (isset($_POST['performer_id']) && isset($_POST['event_id'])) {
    $performer_id = $_POST['performer_id'];
    $event_id = $_POST['event_id'];

    $delete_query = "DELETE FROM event_performers WHERE event_id = ? AND performer_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $event_id, $performer_id);

    if ($stmt->execute()) {
        echo "Izvođač je uspešno obrisan sa događaja.";
    } else {
        echo "Greška pri brisanju izvođača: " . $conn->error;
    }
} else {
    echo "Nedostaju potrebni parametri.";
}
?>
