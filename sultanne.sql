-- Création de la base de données
CREATE DATABASE IF NOT EXISTS sultanne CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sultanne;

-- Création de la table articles
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    image VARCHAR(255),
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO articles (titre, contenu, image)
VALUES ('Titre de test', 'Ceci est le contenu de l\'article de test.', 'test.jpg');
