<?php
// Fichier: lib/newsletter_unsubscribe.php
require_once 'config.php';

// Validation du token
$token = filter_input(INPUT_GET, 'token', FILTER_SANITIZE_STRING);
if (!$token || strlen($token) !== 64) {
    die('Token invalide');
}

try {
    // Vérification et suppression de l'abonnement
    $query = "DELETE FROM newsletter_subscribers WHERE unsubscribe_token = :token";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Désinscription réussie
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
                .unsubscribe-icon {
                    color: var(--color-accent);
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="particles-overlay"></div>
            <div class="unsubscribe-container">
                <div class="unsubscribe-icon">
                    <svg viewBox="0 0 24 24" width="64" height="64">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                    </svg>
                </div>
                <h1>Désinscription confirmée</h1>
                <p>Vous avez été désinscrit avec succès de notre newsletter.</p>
                <p>Nous espérons vous revoir bientôt sur <a href="../index.html">Sultanne Design</a>.</p>
            </div>
        </body>
        </html>';
    } else {
        // Abonnement non trouvé
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
                .unsubscribe-icon {
                    color: #f44336;
                    margin-bottom: 20px;
                }
            </style>
        </head>
        <body>
            <div class="particles-overlay"></div>
            <div class="unsubscribe-container">
                <div class="unsubscribe-icon">
                    <svg viewBox="0 0 24 24" width="64" height="64">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                    </svg>
                </div>
                <h1>Lien expiré ou invalide</h1>
                <p>Ce lien de désinscription n\'est plus valide ou a déjà été utilisé.</p>
                <p>Si vous souhaitez vous désinscrire, veuillez utiliser le lien dans le dernier email que vous avez reçu.</p>
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