<?php
include 'config.php';

if (!isset($_GET['anketa_id'])) {
    echo "Anketa nije odabrana!";
    exit;
}

$anketa_id = $_GET['anketa_id'];

$stmt = $conn->prepare("SELECT * FROM ankete WHERE id = :anketa_id");
$stmt->bindParam(':anketa_id', $anketa_id, PDO::PARAM_INT);
$stmt->execute();
$anketa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anketa) {
    echo "Anketa nije pronađena!";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM pitanja WHERE anketa_id = :anketa_id");
$stmt->bindParam(':anketa_id', $anketa_id, PDO::PARAM_INT);
$stmt->execute();
$pitanja = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conn->beginTransaction();

        $naziv_ankete = $_POST['naziv_ankete'];
        $stmt = $conn->prepare("UPDATE ankete SET naziv = :naziv WHERE id = :anketa_id");
        $stmt->bindParam(':naziv', $naziv_ankete, PDO::PARAM_STR);
        $stmt->bindParam(':anketa_id', $anketa_id, PDO::PARAM_INT);
        $stmt->execute();

        foreach ($_POST['pitanja'] as $pitanje_id => $pitanje_tekst) {
            $stmt = $conn->prepare("UPDATE pitanja SET pitanje = :pitanje WHERE id = :pitanje_id");
            $stmt->bindParam(':pitanje', $pitanje_tekst, PDO::PARAM_STR);
            $stmt->bindParam(':pitanje_id', $pitanje_id, PDO::PARAM_INT);
            $stmt->execute();
        }

        if (isset($_POST['opcije'])) {
            foreach ($_POST['opcije'] as $pitanje_id => $opcije) {
                if (isset($opcije['novi']) && is_array($opcije['novi'])) {
                    foreach ($opcije['novi'] as $nova_opcija) {
                        if (!empty($nova_opcija)) {
                            $stmt = $conn->prepare("INSERT INTO opcije (pitanje_id, opcija) VALUES (:pitanje_id, :opcija)");
                            $stmt->bindParam(':pitanje_id', $pitanje_id, PDO::PARAM_INT);
                            $stmt->bindParam(':opcija', $nova_opcija, PDO::PARAM_STR);
                            $stmt->execute();
                        }
                    }
                }

                if (isset($opcije['brisanje']) && is_array($opcije['brisanje'])) {
                    foreach ($opcije['brisanje'] as $opcija_id) {
                        $stmt = $conn->prepare("DELETE FROM opcije WHERE id = :opcija_id");
                        $stmt->bindParam(':opcija_id', $opcija_id, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }

                if (isset($opcije['postojeci']) && is_array($opcije['postojeci'])) {
                    foreach ($opcije['postojeci'] as $opcija_id => $opcija_tekst) {
                        $stmt = $conn->prepare("UPDATE opcije SET opcija = :opcija WHERE id = :opcija_id");
                        $stmt->bindParam(':opcija', $opcija_tekst, PDO::PARAM_STR);
                        $stmt->bindParam(':opcija_id', $opcija_id, PDO::PARAM_INT);
                        $stmt->execute();
                    }
                }
            }
        }

        $conn->commit();
        echo "<div class='success-message'>Anketa je uspešno ažurirana!</div>";
        header("Location: edit_anketa.php?anketa_id=" . $anketa_id);
        exit;
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<div class='error-message'>Greška: " . $e->getMessage() . "</div>";
    }
}
?>


<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uredi Anketu</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #fdf2f8;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h2, h3, h4 {
            color: #d13581;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
        }

        .button {
            display: inline-block;
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-size: 16px;
            margin-top:10px;
        }

        .button:hover {
            background-color: #218838;
        }

        .success-message, .error-message {
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
        }

        .question-container {
            padding: 15px;
            border-bottom: 1px solid #f1c6d9;
        }

        label {
            font-size: 16px;
            color: #333;
            margin-bottom: 8px;
            display: block;
        }

        input[type="text"]:focus {
            border-color: #d13581;
            outline: none;
        }

        .button-container {
            text-align: center;
        }

        .opcija-container {
            margin-bottom: 10px;
        }
        .opcija-container button {
            padding: 10px 20px;
            background-color: #d13581; 
            color: white; 
            border: none; 
            border-radius: 8px; 
            font-size: 16px; 
            cursor: pointer; 
            transition: background-color 0.3s ease; 
        }

        .opcija-container button:hover {
            background-color: #b1286c; 
        }

        .remove-option {
            color: red;
            cursor: pointer;
            font-size: 14px;
        }

        .add-option {
            background-color: #f3a3d1;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .add-option:hover {
            background-color: #d13581;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Uredi Anketu: <?= htmlspecialchars($anketa['naziv']) ?></h2>

    <form method="POST">
        <label for="naziv_ankete">Naziv ankete:</label>
        <input type="text" id="naziv_ankete" name="naziv_ankete" value="<?= htmlspecialchars($anketa['naziv']) ?>" required>

        <h3>Pitanja:</h3>
        <?php foreach ($pitanja as $pitanje): ?>
            <div class="question-container">
                <label for="pitanje_<?= $pitanje['id'] ?>">Pitanje:</label>
                <input type="text" id="pitanje_<?= $pitanje['id'] ?>" name="pitanja[<?= $pitanje['id'] ?>]" value="<?= htmlspecialchars($pitanje['pitanje']) ?>" required>

                <h4>Opcije:</h4>
                <?php
                $stmt = $conn->prepare("SELECT * FROM opcije WHERE pitanje_id = :pitanje_id");
                $stmt->bindParam(':pitanje_id', $pitanje['id'], PDO::PARAM_INT);
                $stmt->execute();
                $opcije = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <div class="opcija-container">
                    <?php foreach ($opcije as $opcija): ?>
                        <div>
                            <label for="opcija_<?= $opcija['id'] ?>">Opcija:</label>
                            <input type="text" id="opcija_<?= $opcija['id'] ?>" name="opcije[<?= $pitanje['id'] ?>][postojeci][<?= $opcija['id'] ?>]" value="<?= htmlspecialchars($opcija['opcija']) ?>" required>
                            <button class="remove-option" onclick="removeOption(<?= $pitanje['id'] ?>, <?= $opcija['id'] ?>)">Obriši</button>

                        </div>
                    <?php endforeach; ?>
                    <input type="text" name="opcije[<?= $pitanje['id'] ?>][novi][]" placeholder="Nova opcija">
                    <button type="button" class="add-option" onclick="addNewOption(<?= $pitanje['id'] ?>)">Dodaj opciju</button>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="button-container">
            <button type="submit" class="button">Izmeni anketu</button>
        </div>
    </form>
</div>

<script>
   function removeOption(pitanje_id, opcija_id) {
    let confirmation = confirm("Da li ste sigurni da želite da obrišete ovu opciju?");
    if (confirmation) {
        // Kreiraj skriveni input za brisanje
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'opcije[' + pitanje_id + '][brisanje][]';
        input.value = opcija_id;

        // Dodaj input u formu
        document.querySelector('form').appendChild(input);

        // Ponovno pošaljite formu (ne reload stranice)
        document.querySelector('form').submit();
    }
}

    function addNewOption(pitanje_id) {
        let newOptionInput = document.createElement('input');
        newOptionInput.type = 'text';
        newOptionInput.name = 'opcije[' + pitanje_id + '][novi][]';
        newOptionInput.placeholder = 'Nova opcija';
        document.querySelector(`#pitanje_${pitanje_id} .opcija-container`).appendChild(newOptionInput);
    }
</script>

</body>
</html>
