<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Méthode HTTP + chemin de l'URL
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si ton projet est dans /public, on enlève ce préfixe
$uri = str_replace('/public', '', $uri);

// 2. Routing

// >>>>>>>>>>>>>  NOUVELLE ROUTE /films/noms  <<<<<<<<<<<<<<
if ($uri === '/films/noms' && $method === 'GET') {
    getFilmTitles();
    exit;
}

// Route pour /films (liste complète ou création)
if ($uri === '/films') {
    if ($method === 'GET') {
        getAllFilms();
    } elseif ($method === 'POST') {
        createFilm();
    } else {
        http_response_code(405);
        console.log('error')
        echo json_encode(['error' => 'Méthode non autorisée']);
    }
    exit;
}

// Route pour /films/{id}
if (preg_match('#^/films/(\d+)$#', $uri, $matches)) {
    $id = (int) $matches[1];

    if ($method === 'GET') {
        getFilmById($id);
    } elseif ($method === 'PUT') {
        updateFilm($id);
    } elseif ($method === 'DELETE') {
        deleteFilm($id);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée']);
    }
    exit;
}

// Si aucune route ne match :
http_response_code(404);
echo json_encode(['error' => 'Route inconnue']);

// 3. FONCTIONS

function getAllFilms() {
    $pdo = getPDOConnection();
    $stmt = $pdo->query('SELECT * FROM film');
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($films);
}

function getFilmById(int $id) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('SELECT * FROM film WHERE id_film = :id');
    $stmt->execute(['id' => $id]);
    $film = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($film) {
        echo json_encode($film);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Film non trouvé']);
    }
}

function createFilm() {
    $pdo = getPDOConnection();
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        !$data ||
        !isset($data['titre'], $data['realisateur'], $data['annee_sortie'], $data['duree_min'])
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Données invalides']);
        return;
    }

    $stmt = $pdo->prepare('
        INSERT INTO film (titre, realisateur, annee_sortie, duree_min, genre)
        VALUES (:titre, :realisateur, :annee_sortie, :duree_min, :genre)
    ');

    $stmt->execute([
        'titre'        => $data['titre'],
        'realisateur'  => $data['realisateur'],
        'annee_sortie' => $data['annee_sortie'],
        'duree_min'    => $data['duree_min'],
        'genre'        => $data['genre'] ?? null,
    ]);

    http_response_code(201);
    echo json_encode([
        'message' => 'Film créé',
        'id'      => $pdo->lastInsertId(),
    ]);
}

function updateFilm(int $id) {
    $pdo = getPDOConnection();
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Données invalides']);
        return;
    }

    $stmt = $pdo->prepare('
        UPDATE film
        SET titre = :titre,
            realisateur = :realisateur,
            annee_sortie = :annee_sortie,
            duree_min = :duree_min,
            genre = :genre
        WHERE id_film = :id
    ');

    $stmt->execute([
        'id'           => $id,
        'titre'        => $data['titre'],
        'realisateur'  => $data['realisateur'],
        'annee_sortie' => $data['annee_sortie'],
        'duree_min'    => $data['duree_min'],
        'genre'        => $data['genre'] ?? null,
    ]);

    echo json_encode(['message' => 'Film mis à jour']);
}

function deleteFilm(int $id) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare('DELETE FROM film WHERE id_film = :id');
    $stmt->execute(['id' => $id]);

    echo json_encode(['message' => 'Film supprimé']);
}

// >>>>>>>>>>>>  ICI LA FONCTION POUR /films/noms  <<<<<<<<<<<<<<

function getFilmTitles() {
    $pdo = getPDOConnection();
    $stmt = $pdo->query('SELECT titre FROM film');

    // Retourne ["Inception", "Gladiator", ...]
    $titres = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($titres);
}
