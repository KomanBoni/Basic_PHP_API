<?php
$dsn = "pgsql:host=127.0.0.1;port=5432;dbname=anasch_film";
$username = "postgres";
$password = "KomanKali12";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie\n";
} catch (PDOException $e) {
    echo "Erreur PDO : " . $e->getMessage() . "\n";
}
