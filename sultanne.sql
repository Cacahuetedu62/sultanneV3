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

CREATE TABLE newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    subscription_date DATETIME NOT NULL,
    unsubscribe_token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_email_sent DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Index pour optimiser les recherches
CREATE INDEX idx_email ON newsletter_subscribers(email);
CREATE INDEX idx_unsubscribe_token ON newsletter_subscribers(unsubscribe_token);