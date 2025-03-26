<?php

session_start();
require_once 'config.php';


require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Charger les variables d'environnement
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
            $value = substr($value, 1, -1);
        }
        
        $_ENV[$name] = $value;
    }
    
    return true;
}

loadEnv(__DIR__ . '/../.env');

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../actualites.php#newsletter');
    exit;
}

// Protection anti-spam (honeypot)
if (!empty($_POST['website'])) {
    // C'est probablement un bot, rediriger silencieusement
    header('Location: ../actualites.php?newsletter=success#newsletter');
    exit;
}

// Validation de l'email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('Location: ../actualites.php?newsletter=error#newsletter');
    exit;
}

// Validation du consentement
if (!isset($_POST['consent'])) {
    header('Location: ../actualites.php?newsletter=error#newsletter');
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
        header('Location: ../actualites.php?newsletter=exists#newsletter');
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
    
    // Stockage anonymisé de l'IP pour des raisons de sécurité
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $hashed_ip = password_hash($ip, PASSWORD_DEFAULT);
    $insert_stmt->bindParam(':ip_address', $hashed_ip);
    
    $insert_stmt->execute();
    
    // Préparation des données pour l'email
    $site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $unsubscribe_url = $site_url . "/lib/newsletter_unsubscribe.php?token=" . $unsubscribe_token;
    
    // Utilisation de PHPMailer pour l'envoi d'email (comme dans le formulaire de contact)
    try {
        $mail = new PHPMailer(true);
        
        // Configuration du serveur SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->Port       = $_ENV['SMTP_PORT'];
        $mail->SMTPSecure = ($_ENV['SMTP_ENCRYPTION'] == 'tls') ? 
                            PHPMailer::ENCRYPTION_STARTTLS : 
                            PHPMailer::ENCRYPTION_SMTPS;
        
        // Destinataires
        $mail->setFrom($_ENV['SMTP_USERNAME'], 'Sultanne Design');
        $mail->addAddress($email);
        
        // Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Bienvenue à la newsletter de Sultanne Design";
        
        // Corps du mail simple et convivial
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h2 { color: #ff66b3; }
                .btn { 
                    display: inline-block; 
                    padding: 10px 15px; 
                    background-color: #ff66b3; 
                    color: white !important; 
                    text-decoration: none; 
                    border-radius: 5px; 
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Bienvenue à la newsletter de Sultanne Design !</h2>
                <p>Bonjour,</p>
                <p>Merci pour votre inscription à notre newsletter !</p>
                <p>Vous recevrez désormais les nouveautés et offres de Sultanne. Pas d'inquiétude, nous n'allons pas vous harceler - les envois seront occasionnels et toujours pertinents.</p>
                <p>Si vous souhaitez vous désinscrire à tout moment, cliquez simplement sur le bouton ci-dessous :</p>
                <p><a href='$unsubscribe_url' class='btn'>Me désinscrire</a></p>
                <p>À bientôt !<br>L'équipe Sultanne Design</p>
            </div>
        </body>
        </html>";
        
        // Version texte alternative
        $mail->AltBody = "Bienvenue à la newsletter de Sultanne Design !\n\nMerci pour votre inscription à notre newsletter !\n\nVous recevrez désormais les nouveautés et offres de Sultanne. Pas d'inquiétude, nous n'allons pas vous harceler - les envois seront occasionnels et toujours pertinents.\n\nSi vous souhaitez vous désinscrire à tout moment, utilisez ce lien :\n$unsubscribe_url\n\nÀ bientôt !\nL'équipe Sultanne Design";
        
        $mail->send();
        
        // Redirection avec message de succès et ancre pour position
        header('Location: ../actualites.php?newsletter=success#newsletter');
        exit;
    } catch (Exception $e) {
        // Journalisation de l'erreur d'envoi d'email
        error_log('Erreur envoi email newsletter: ' . $mail->ErrorInfo);
        // Continuer sans bloquer l'inscription même si l'email n'a pas été envoyé
        header('Location: ../actualites.php?newsletter=success#newsletter');
        exit;
    }
    
} catch (PDOException $e) {
    // Journalisation de l'erreur
    error_log('Erreur newsletter: ' . $e->getMessage());
    header('Location: ../actualites.php?newsletter=error#newsletter');
    exit;
}