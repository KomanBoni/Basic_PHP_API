<?php
header("Content-Type: application/json; charset=utf-8");

// Connexion à MySQL (Docker local)
$dsn = "mysql:host=127.0.0.1;dbname=anasch_film;charset=utf8mb4";
$username = "root";
$password = "root";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB error: " . $e->getMessage()]);
    exit();
}

// Récupérer l’URL (chemin)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// ------ ROUTE GET /films ------
if ($uri === '/films' && $method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM film");
        $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($films);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Query error: " . $e->getMessage()]);
    }
    exit();
}

// Route par défaut si rien ne matche
http_response_code(404);
echo json_encode(["error" => "Route not found", "path" => $uri]);
