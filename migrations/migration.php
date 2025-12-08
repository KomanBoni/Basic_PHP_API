<?php
/**
 * Script de migration pour créer la table `films`
 * Adapter les valeurs ci-dessous selon votre environnement.
 */

$host = "localhost";
$dbname = "ma_base";      // Nom de la base à créer ou déjà existante
$user = "root";
$pass = "";
$charset = "utf8mb4";

try {
    // Connexion au serveur MySQL (sans base)
    $pdo = new PDO("mysql:host=$host;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création de la base si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Connexion à la base créée
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création de la table
    $sql = "
        CREATE TABLE IF NOT EXISTS films (
            id_film INT AUTO_INCREMENT PRIMARY KEY,
            titre VARCHAR(100) NOT NULL,
            realisateur VARCHAR(100) NOT NULL,
            annee_sortie YEAR NOT NULL,
            duree_min INT NOT NULL,
            genre VARCHAR(50) NULL
        ) ENGINE=InnoDB;
    ";

    $pdo->exec($sql);

    echo "Migration effectuée avec succès : table 'films' créée.\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
