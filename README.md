# Basic_PHP_API
#  API REST – Gestion des Films  
Projet PHP / MySQL / Docker

Cette API permet de récupérer la liste des films stockés dans une base MySQL.  
Elle a été développée en PHP (sans framework).

---

##  Structure du projet

Basic_PHP_API/
└── public/
└── index.php # Point d’entrée unique de l’API

sql
Copy code

---

##  Base de données

### Création de la table

```sql
CREATE TABLE film (
    id_film INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    realisateur VARCHAR(100) NOT NULL,
    annee_sortie YEAR NOT NULL,
    duree_min INT NOT NULL,
    genre VARCHAR(50)
);
Données d’exemple (seed)
sql
Copy code
INSERT INTO film (titre, realisateur, annee_sortie, duree_min, genre)
VALUES
('Inception', 'Christopher Nolan', 2010, 148, 'Science-fiction'),
('Titanic', 'James Cameron', 1997, 195, 'Romance'),
('The Dark Knight', 'Christopher Nolan', 2008, 152, 'Action');
 Docker – Lancer MySQL
bash
Copy code
docker run --name api_mysql \
  -e MYSQL_ROOT_PASSWORD=root \
  -e MYSQL_DATABASE=anasch_film \
  -p 3306:3306 \
  -d mysql:8
🚀 Lancer l’API
Dans le dossier public :

bash
Copy code
php -S localhost:8000 index.php
📌 Endpoints
GET /films
Retourne tous les films au format JSON.

Exemple de requête
bash
Copy code
GET http://localhost:8000/films
Exemple de réponse
json
Copy code
[
  {
    "id_film": 1,
    "titre": "Inception",
    "realisateur": "Christopher Nolan",
    "annee_sortie": 2010,
    "duree_min": 148,
    "genre": "Science-fiction"
  }
]
 Gestion des erreurs
Code	Signification
500	Erreur PDO / MySQL
404	Route inconnue

 Technologies
PHP 8

PDO

MySQL 8

Docker

Serveur PHP intégré
