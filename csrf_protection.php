<?php
session_start();

// Generisanje CSRF tokena
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
}

// CSRF token u formama
echo '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '" />';
?>
