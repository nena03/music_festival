<?php


include('config.php');
session_start();
if (!isset($_SESSION['user_id'])) {
  
    header("Location: register.php");
    exit(); 
}



$sql = "SELECT event_id, name FROM events"; 
$stmt = $conn->prepare($sql);  
$stmt->execute();  
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);  

if (!$events) {
    echo "Nema dostupnih događaja.";
    exit;
}
$conn = null;  
$user_id = $_SESSION['user_id'];
?>


<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kupovina ulaznica</title>
    <style>
       body {
        background: linear-gradient(135deg, #8B0000, #FF4500);
        font-family: Arial, sans-serif;
        color: white;
        text-align: center;
        margin: 0;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        max-width: 600px;
        width: 100%;
        padding: 20px;
        background: rgba(255, 69, 0, 0.9);
        border-radius: 15px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        text-align: left;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h1 {
        margin-bottom: 20px;
        text-align: center;
    }

    .ticket-button {
        display: inline-block;
        background: #DC143C;
        color: white;
        padding: 15px 30px;
        margin: 10px;
        font-size: 18px;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        transition: 0.3s;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.5);
        text-align: center;
    }

    .ticket-button:hover {
        background: #FF0000;
        box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.6);
    }

    .form-group {
        margin: 15px 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    input, select {
        width: 100%;  
        padding: 10px;
        margin-top: 5px;
        border-radius: 10px;
        border: none;
        text-align: center;
    }
    .logout-icon {
            position: absolute; 
            top: 20px; 
            right: 20px; 
            width: 50px; 
            height: 50px; 
            display: block; 
            cursor: pointer; 
        }

        .logout-icon img {
            width: 100%; 
            height: auto; 
            border-radius: 50%; 
            transition: transform 0.2s ease; 
        }

        .logout-icon img:hover {
            transform: scale(1.1); 
        }


    </style>
    
</head>
<body>
    <a href="logout.php" class="logout-icon" title="Logout">
            <img src="https://thumbs.dreamstime.com/b/exit-logout-log-off-icon-isolated-white-red-thin-right-rounded-arrow-bracket-sign-out-profile-user-box-quit-export-128526354.jpg" alt="Logout" />
    </a>

    <div class="container">
        <h1>Kupovina ili rezervacija ulaznica</h1>
        <form id="ticketForm" method="POST">
    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>"> <!-- Skriveni unos za user_id -->

    <div class="form-group">
        <label for="event">Izaberi događaj:</label>
        <select id="event" name="event">
            <?php foreach ($events as $event): ?>
                <option value="<?php echo $event['event_id']; ?>"><?php echo htmlspecialchars($event['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="seat">Broj mesta:</label>
        <input type="number" id="seat" name="seat" min="1" max="10">
    </div>
    <div class="form-group">
        <label for="seatType">Izaberite vrstu mesta:</label>
        <select id="seatType" name="seatType">
            <option value="VIP">VIP 5000 din</option>
            <option value="srednja">Srednja klasa 3000 din</option>
            <option value="niza">Niža klasa 2000 din</option>
        </select>
    </div>
    
    <button class="ticket-button" type="submit" name="action" value="buy" onclick="changeAction('buy')">Kupi ulaznicu</button>
    
    <button class="ticket-button" type="submit" name="action" value="reserve" onclick="changeAction('reserve')">Rezerviši mesto</button>
</form>

<script>
    function changeAction(action) {
        
        var form = document.getElementById('ticketForm');
        if (action === 'reserve') {
            form.action = 'server1.php';  
        } else {
            form.action = 'server.php';  
        }
    }
</script>
    </div>
</body>
</html>
