<?php
$dsn = "mysql:host=mysql-anasch.alwaysdata.net;dbname=anasch_film;charset=utf8mb4";
$username = "anasch";
$password = "Charaf123*";
try {
	$pdo = new PDO($dsn, $username, $password);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo ("connection réussi");
} catch (PDOException $e) {
	http_response_code(500);
	echo "Erreur PDO : " . $e->getMessage() . "\n";
	exit();
}