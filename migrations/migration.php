<?php

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '5432';
$dbname = $_ENV['DB_DATABASE'] ?? 'anasch_film';
$user = $_ENV['DB_USERNAME'] ?? 'postgres';
$pass = $_ENV['DB_PASSWORD'] ?? 'KomanKali12';

try {
    // Connexion à PostgreSQL (sans spécifier la base de données)
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=postgres", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Créer la base de données si elle n'existe pas
    $result = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$dbname'");
    if ($result->rowCount() == 0) {
        $pdo->exec("CREATE DATABASE $dbname");
        echo "Base de données '$dbname' créée.\n";
    }

    // Connexion à la base de données créée
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        CREATE TABLE IF NOT EXISTS films (
            id_film SERIAL PRIMARY KEY,
            titre VARCHAR(100) NOT NULL,
            realisateur VARCHAR(100) NOT NULL,
            annee_sortie INTEGER NOT NULL,
            duree_min INTEGER NOT NULL,
            genre VARCHAR(50) NULL
        );
    ";

    $pdo->exec($sql);

    echo "Migration effectuée avec succès : table 'films' créée.\n";

    $sqlUsers = "
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            token VARCHAR(255) NULL
        );
        
        CREATE INDEX IF NOT EXISTS idx_email ON users(email);
    ";

    $pdo->exec($sqlUsers);

    echo "Migration effectuée avec succès : table 'users' créée.\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
