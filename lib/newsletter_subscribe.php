<?php
// Fichier: lib/newsletter_subscribe.php
session_start();
require_once 'config.php';

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../actualites.php');
    exit;
}

// Protection anti-spam (honeypot)
if (!empty($_POST['website'])) {
    // C'est probablement un bot, rediriger silencieusement
    header('Location: ../actualites.php?newsletter=success');
    exit;
}

// Validation de l'email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('Location: ../actualites.php?newsletter=error');
    exit;
}

// Validation du consentement
if (!isset($_POST['consent'])) {
    header('Location: ../actualites.php?newsletter=error');
    exit;
}

try {
    // Vérification si l'email existe déjà
    $check_query = "SELECT id FROM newsletter_subscribers WHERE email = :email";
    $check_stmt = $pdo->prepare($check_query);
    $check_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() > 0) {
        // L'email existe déjà
        header('Location: ../actualites.php?newsletter=exists');
        exit;
    }
    
    // Génération d'un token de désabonnement
    $unsubscribe_token = bin2hex(random_bytes(32));
    $subscription_date = date('Y-m-d H:i:s');
    
    // Insertion dans la base de données
    $insert_query = "INSERT INTO newsletter_subscribers (email, subscription_date, unsubscribe_token, ip_address) 
                    VALUES (:email, :subscription_date, :unsubscribe_token, :ip_address)";
    
    $insert_stmt = $pdo->prepare($insert_query);
    $insert_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $insert_stmt->bindParam(':subscription_date', $subscription_date);
    $insert_stmt->bindParam(':unsubscribe_token', $unsubscribe_token);
    
    // Stockage anonymisé de l'IP pour des raisons de sécurité (détection d'abus)
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $hashed_ip = password_hash($ip, PASSWORD_DEFAULT);
    $insert_stmt->bindParam(':ip_address', $hashed_ip);
    
    $insert_stmt->execute();
    
    // Envoi d'un email de confirmation (facultatif)
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $unsubscribe_url = $site_url . "/lib/newsletter_unsubscribe.php?token=" . $unsubscribe_token;
    
    $subject = "Confirmation d'inscription à la newsletter de Sultanne Design";
    $message = "Bonjour,\n\n";
    $message .= "Merci de vous être inscrit(e) à la newsletter de Sultanne Design.\n\n";
    $message .= "Vous recevrez désormais nos dernières actualités et conseils en création web.\n\n";
    $message .= "Si vous souhaitez vous désinscrire, cliquez sur ce lien:\n";
    $message .= $unsubscribe_url . "\n\n";
    $message .= "Cordialement,\nL'équipe Sultanne Design";
    
    $headers = "From: contact@sultanne.fr";
    
    mail($email, $subject, $message, $headers);
    
    // Redirection avec un message de succès
    header('Location: ../actualites.php?newsletter=success');
    exit;
    
} catch (PDOException $e) {
    // Journalisation de l'erreur (en production, ne pas afficher l'erreur à l'utilisateur)
    error_log('Erreur newsletter: ' . $e->getMessage());
    header('Location: ../actualites.php?newsletter=error');
    exit;
}