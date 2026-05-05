<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'projet_mouna');

function getDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Erreur de connexion : " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    return $conn;
}
?>
