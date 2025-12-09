<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json; charset=utf-8");

// Connexion à MySQL (Docker local)
$dsn = "mysql:host=127.0.0.1:3306;dbname=anasch_film;charset=utf8mb4";
$username = "root";
$password = "root";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion BDD']);
    exit();
}

// ======================
//      CONFIG JWT
// ======================
$jwt_secret     = 'CHANGE_CETTE_CHAINE_PAR_UNE_GROSSE_CLE_TRES_SECRETE';
$jwt_issuer     = 'http://localhost:8000';
$jwt_expiration = 3600; // 1h

// Récupérer l’URL (chemin)
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Vérifie le JWT dans le header Authorization
 * Retourne l'id utilisateur (sub) si OK, sinon 401.
 */
function getAuthenticatedUserId($jwt_secret) {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Header Authorization manquant']);
        exit;
    }

    $authHeader = $headers['Authorization'];

    if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Format du header Authorization invalide']);
        exit;
    }

    $jwt = $matches[1];

    try {
        $decoded = JWT::decode($jwt, new Key($jwt_secret, 'HS256'));
        // on retourne l’id utilisateur contenu dans le token
        return $decoded->sub;
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(['error' => 'Token invalide ou expiré']);
        exit;
    }
}

// ======================
//        /login
// ======================
// POST /login  { "email": "...", "password": "..." }
if ($uri === '/login' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email et password sont obligatoires']);
        exit;
    }

    $email    = trim($data['email']);
    $password = $data['password'];

    try {
        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        

        // Vérifier utilisateur + mot de passe
        // ⚠️ NE FONCTIONNE que si le password en BDD est hashé avec password_hash
        if (!$user || !password_verify($password, $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants invalides']);
            exit;
        }

        $now = time();
        $exp = $now + $jwt_expiration;

        $payload = [
            'iss'   => $jwt_issuer,
            'iat'   => $now,
            'exp'   => $exp,
            'sub'   => $user['id'],      // id de l'utilisateur
            'email' => $user['email'],
            'name'  => $user['name'],
        ];

        $token = JWT::encode($payload, $jwt_secret, 'HS256');

        echo json_encode([
            'message' => 'Login réussi',
            'token'   => $token,
            'user'    => [
                'id'    => (int)$user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
            ],
        ]);
        exit;

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur']);
        exit;
    }
}

// ======================
//    ROUTES FILMS
// ======================

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
            "count"         => $nombreFilms,
            "films"         => $films
        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur']);
    }
    exit();
}

// ------ ROUTE GET /films ------
if ($uri === '/films' && $method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM film"); // vérifie le nom de ta table: film ou films ?
        $films = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($films);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur']);
    }
    exit();
}

// ======================
//    ROUTES USERS
// ======================

// ------ ROUTE GET /users/:id ------
if (preg_match('/^\/user\/(\d+)$/', $uri, $matches) && $method === 'GET') {
    // 🔐 route protégée par JWT
    $authUserId = getAuthenticatedUserId($jwt_secret);

    $userId = (int)$matches[1];

    try {
        $sql = "SELECT id, name, email FROM user WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        echo ("ok");

        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User non trouvé']);
        }

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur']);
    }
    exit();
}

// ------ ROUTE GET /users ------
if ($uri === '/users' && $method === 'GET') {
    // 🔐 route protégée par JWT
    $authUserId = getAuthenticatedUserId($jwt_secret);

    try {
        $sql = "SELECT id, name, email FROM user";
        $stmt = $pdo->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur serveur']);
    }
    exit();
}

// ------ ROUTE POST /users ------
// inscription: création d'un compte utilisateur
if ($uri === '/users' && $method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Champs name, email, password obligatoires']);
        exit();
    }

    $name     = trim($data['name']);
    $email    = trim($data['email']);
    $password = $data['password'];


    if (empty($name) || empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Champs vides']);
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email invalide']);
        exit();
    }

    // Hash du mot de passe pour qu'il soit compatible avec password_verify
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO user (name, email, password) VALUES (:name, :email, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);
        $stmt->execute();

        $userId = $pdo->lastInsertId();
        echo ("ok");
        
        http_response_code(201);
        echo json_encode([
            "id"    => (int)$userId,
            "name"  => $name,
            "email" => $email
        ]);

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // contrainte unique (email déjà utilisé ?)
            http_response_code(409);
            echo json_encode(['error' => 'Email déjà utilisé']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }
    exit();
}

// Route par défaut si rien ne matche
http_response_code(404);
echo json_encode(['error' => 'Route non trouvée']);
