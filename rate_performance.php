<?php
session_start();
include 'config.php';



$data = json_decode(file_get_contents('php://input'), true);
$performance_id = $data['performance_id'];
$rating = $data['rating'];
$user_id = $_SESSION['user_id'];

if ($rating < 1 || $rating > 5) {
    echo json_encode(['message' => 'Ocena mora biti između 1 i 5.']);
    exit();
}

$query = "INSERT INTO performance_ratings (performance_id, user_id, rating) VALUES (?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('iii', $performance_id, $user_id, $rating);
$stmt->execute();

echo json_encode(['message' => 'Hvala na oceni!']);
?>
