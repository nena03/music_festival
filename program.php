<?php
session_start();
include 'config.php';
include 'db_connection.php';

// Pripremanje SQL upita za prikaz programa festivala
$query = "SELECT name, event_time, scene, description FROM events ORDER BY event_time";
$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$program = [];
while ($event = $result->fetch_assoc()) {
    $program[] = $event;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Festivala</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff; 
            color: #333; 
            padding: 20px;
        }
        .container {
            margin-top: 50px;
            background-color: #f9f9f9; /
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5em;
            color: #e53935; 
            text-align: center;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .table {
            border: 1px solid #e53935; 
            border-radius: 8px;
            background-color: #fff; 
        }
        .table th, .table td {
            color: #333; 
            padding: 15px;
            text-align: center;
            font-size: 1.1em;
        }
        .table th {
            background-color: #e53935; 
            color: #fff; 
        }
        .table tbody tr:hover {
            background-color: #f2f2f2; 
            cursor: pointer;
            transform: scale(1.03); 
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .table td {
            background-color: #fff; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Program Festivala</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ime događaja</th>
                    <th>Vreme nastupa</th>
                    <th>Scena</th>
                    <th>Opis</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($program as $event): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($event['name']); ?></td>
                        <td><?php echo htmlspecialchars($event['event_time']); ?></td>
                        <td><?php echo htmlspecialchars($event['scene']); ?></td>
                        <td><?php echo htmlspecialchars($event['description']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
