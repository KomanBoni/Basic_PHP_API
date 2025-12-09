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
    exit();
}

// Récupérer l’URL (chemin)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];


// ------ ROUTE GET /films/:genre ------
if (preg_match('/^\/films\/([a-zA-Z0-9_-]+)$/', $uri, $matches) && $method === 'GET') {
    $genre = $matches[1];

    $sql = "SELECT * FROM films WHERE genre = :genre";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':genre', $genre, PDO::PARAM_STR);
        $stmt->execute();
        
        $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $nombreFilms = count($films);

        echo json_encode([
            "genre_request" => $genre,
            "count" => $nombreFilms,
            "films" => $films
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
    }
    exit();
}

// ------ ROUTE GET /films ------
if ($uri === '/films' && $method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM film");
        $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($films);
    } catch (PDOException $e) {
        http_response_code(500);
    }
    exit();
}

// ========== ROUTES USERS ==========

// ------ ROUTE GET /users/:id ------
if (preg_match('/^\/users\/(\d+)$/', $uri, $matches) && $method === 'GET') {
    $userId = (int)$matches[1];

    try {
        $sql = "SELECT id, name, email FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
        }

    } catch (PDOException $e) {
        http_response_code(500);
    }
    exit();
}

// ------ ROUTE GET /users ------
if ($uri === '/users' && $method === 'GET') {
    try {
        $sql = "SELECT id, name, email FROM users";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
    }
    exit();
}

// ------ ROUTE POST /users ------
if ($uri === '/users' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        exit();
    }

    $name = trim($data['name']);
    $email = trim($data['email']);
    $password = $data['password'];

    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        exit();
    }

    try {
        $sql = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();

        $userId = $pdo->lastInsertId();
        
        http_response_code(201);
        echo json_encode([
            "id" => (int)$userId,
            "name" => $name,
            "email" => $email
        ]);

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            http_response_code(409);
        } else {
            http_response_code(500);
        }
    }
    exit();
}

// Route par défaut si rien ne matche
http_response_code(404);
