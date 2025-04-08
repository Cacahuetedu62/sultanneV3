<?php
// Fichier: lib/newsletter_unsubscribe.php
require_once 'config.php';

// Validation du token
$token = filter_input(INPUT_GET, 'token', FILTER_VALIDATE_REGEXP, [
    'options' => [
        'regexp' => '/^[a-f0-9]{64}$/' // Token hexadécimal de 64 caractères
    ]
]);

if (!$token) {
    die('Token invalide');
}

try {
    // Vérification et suppression de l'abonnement
    $query = "DELETE FROM newsletter_subscribers WHERE unsubscribe_token = :token";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Désinscription réussie - page de confirmation simple
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Désinscription - Sultanne Design</title>
            <link rel="stylesheet" href="../css/style.css">
            <style>
                .unsubscribe-container {
                    max-width: 600px;
                    margin: 100px auto;
                    padding: 30px;
                    text-align: center;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 12px;
                }
                .success-icon {
                    color: #4CAF50;
                    font-size: 48px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="particles-overlay"></div>
            <div class="unsubscribe-container">
                <div class="success-icon">✓</div>
                <h1>Désinscription confirmée</h1>
                <p>Vous avez été désinscrit avec succès de notre newsletter.</p>
                <p>Nous espérons vous revoir bientôt sur <a href="../index.html">Sultanne Design</a>.</p>
            </div>
        </body>
        </html>';
    } else {
        // Lien non valide ou déjà utilisé
        echo '<!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Désinscription - Sultanne Design</title>
            <link rel="stylesheet" href="../css/style.css">
            <style>
                .unsubscribe-container {
                    max-width: 600px;
                    margin: 100px auto;
                    padding: 30px;
                    text-align: center;
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 12px;
                }
                .error-icon {
                    color: #f44336;
                    font-size: 48px;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="particles-overlay"></div>
            <div class="unsubscribe-container">
                <div class="error-icon">!</div>
                <h1>Lien expiré ou invalide</h1>
                <p>Ce lien de désinscription n\'est plus valide ou a déjà été utilisé.</p>
                <p><a href="../index.html">Retour à l\'accueil</a></p>
            </div>
        </body>
        </html>';
    }
    
} catch (PDOException $e) {
    // Journalisation de l'erreur
    error_log('Erreur désinscription newsletter: ' . $e->getMessage());
    die('Une erreur est survenue. Veuillez réessayer plus tard.');
}