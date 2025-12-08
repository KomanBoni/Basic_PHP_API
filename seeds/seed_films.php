<?php
/**
 * Script de seed pour ajouter des données de test dans la table `films`
 * Adapter les valeurs ci-dessous selon votre environnement.
 */

$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$charset = $_ENV['DB_CHARSET'];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'films'");
    if ($stmt->rowCount() == 0) {
        echo "Erreur : La table 'films' n'existe pas. Veuillez d'abord exécuter la migration.\n";
        exit(1);
    }

    // Vérifier si des données existent déjà
    $stmt = $pdo->query("SELECT COUNT(*) FROM films");
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo "La table contient déjà $count film(s). Voulez-vous continuer ? (o/n) : ";
        // Pour l'automatisation, on peut vider la table ou ajouter quand même
        // Ici, on vide la table pour réinitialiser
        $pdo->exec("TRUNCATE TABLE films");
        echo "Table vidée. Insertion des nouvelles données...\n";
    }

    // Préparer la requête d'insertion
    $sql = "INSERT INTO films (titre, realisateur, annee_sortie, duree_min, genre) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    // Données de test
    $films = [
        ["Le Seigneur des Anneaux : La Communauté de l'anneau", "Peter Jackson", 2001, 178, "Fantasy"],
        ["Inception", "Christopher Nolan", 2010, 148, "Science-Fiction"],
        ["Pulp Fiction", "Quentin Tarantino", 1994, 154, "Crime"],
        ["Le Parrain", "Francis Ford Coppola", 1972, 175, "Drame"],
        ["Forrest Gump", "Robert Zemeckis", 1994, 142, "Drame"],
        ["Matrix", "Lana Wachowski, Lilly Wachowski", 1999, 136, "Science-Fiction"],
        ["Fight Club", "David Fincher", 1999, 139, "Drame"],
        ["Interstellar", "Christopher Nolan", 2014, 169, "Science-Fiction"],
        ["Gladiator", "Ridley Scott", 2000, 155, "Action"],
        ["The Dark Knight", "Christopher Nolan", 2008, 152, "Action"]
    ];

    // Insérer les films
    $inserted = 0;
    foreach ($films as $film) {
        $stmt->execute($film);
        $inserted++;
    }

    echo "Seed effectué avec succès : $inserted film(s) ajouté(s) à la table 'films'.\n";

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
    exit(1);
}

