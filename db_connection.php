<?php

$servername = "localhost";  // Server na kojem se baza nalazi (lokalna mašina)
$username = "root";         // Korisničko ime za MySQL
$password = "";             // Lozinka za MySQL 
$dbname = "music_festival"; // Ime baze podataka

// Kreira se konekcija
$conn = new mysqli($servername, $username, $password, $dbname);

// Provera konekcije
if ($conn->connect_error) {
    die("Konekcija nije uspela: " . $conn->connect_error);
} 
?>
