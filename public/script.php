<?php
$dsn = "mysql:host=127.0.0.1;dbname=anasch_film;charset=utf8mb4";
$username = "root";
$password = "root";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie\n";
} catch (PDOException $e) {
    echo "Erreur PDO : " . $e->getMessage() . "\n";
}
