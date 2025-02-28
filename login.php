<?php
require_once 'config.php'; 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id']; 
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'admin':
                    header("Location: admin.php");
                    break;
                case 'visitor':
                    header("Location: festival.php");
                    break;
                case 'artist':
                    header("Location: izvodjac.php");
                    break;
                case 'organizer':
                    header("Location: organizator.php");
                    break;
                default:
                    header("Location: register.php?error=unknown_role");
                    break;
            }
            exit();
        } else {
            $poruka= "Neispravno korisničko ime ili lozinka.";
        }
    } catch (PDOException $e) {
        $poruka= "Greška prilikom prijave: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff0000, #b30000);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            width: 320px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: #333;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 400;
        }

        .form-group input {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: 0.3s;
            text-align: center;
        }

        .form-group input:focus {
            border-color: #ff0000;
            outline: none;
        }

        .form-group button {
            width: calc(100% - 20px);
            padding: 10px;
            background: linear-gradient(135deg, #ff0000, #b30000);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .form-group button:hover {
            background: linear-gradient(135deg, #b30000, #ff0000);
        }

        .register-btn {
            display: block;
            margin-top: 10px;
            color: #ff0000;
            text-decoration: none;
            transition: 0.3s;
            text-align: center;
        }

        .register-btn:hover {
            color: #800000;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if (isset($poruka)): ?>
            <p class="error-message"> <?php echo $poruka; ?> </p>
        <?php endif; ?>
        <h2>Prijava</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Korisničko ime:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Lozinka:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit">Prijavi se</button>
            </div>
            <div class="form-group">
                <a href="register.php" class="register-btn">Registruj se ako nisi prijavljen korisnik</a>
            </div>
        </form>
    </div>
</body>
</html>
