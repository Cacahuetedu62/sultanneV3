<?php
// Fonction pour charger les variables d'environnement
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Enlever les guillemets si présents
        if (strpos($value, '"') === 0 || strpos($value, "'") === 0) {
            $value = substr($value, 1, -1);
        }
        
        $_ENV[$name] = $value;
    }
    
    return true;
}

// Charger les variables d'environnement
loadEnv(__DIR__ . '/.env');

// Charger l'autoloader de Composer
require 'vendor/autoload.php';

// Si vous n'avez pas utilisé Composer, importez manuellement les fichiers PHPMailer
// require 'PHPMailer/src/Exception.php';
// require 'PHPMailer/src/PHPMailer.php';
// require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérification du reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'];
    
    if (!verifyRecaptcha($recaptcha_response)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez confirmer que vous n\'êtes pas un robot.']);
        exit;
    }
    
    // Récupération et nettoyage des données du formulaire
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $phone = filter_var($_POST['phone'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message = filter_var($_POST['message'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
    // Validation des champs obligatoires
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires.']);
        exit;
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez entrer une adresse email valide.']);
        exit;
    }
    
    // Préparation et envoi de l'email avec PHPMailer
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
        
        // Pour déboguer (à commenter en production)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        
        // Destinataires
        $mail->setFrom($_ENV['SMTP_USERNAME'], 'Formulaire Sultanne');
        $mail->addAddress($_ENV['SMTP_USERNAME'], 'Sultanne');
        $mail->addReplyTo($email, $name);
        
        // Contenu
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Contact Sultanne: $subject";
        
        // Corps du message en HTML
        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                h2 { color: #333366; }
                .info { margin-bottom: 20px; }
                .label { font-weight: bold; }
                .message { background-color: #f9f9f9; padding: 15px; border-left: 5px solid #333366; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h2>Nouveau message depuis le site Sultanne</h2>
                <div class='info'>
                    <p><span class='label'>Nom:</span> $name</p>
                    <p><span class='label'>Email:</span> $email</p>
                    <p><span class='label'>Téléphone:</span> $phone</p>
                    <p><span class='label'>Sujet:</span> $subject</p>
                </div>
                <div class='message'>
                    <p><span class='label'>Message:</span></p>
                    <p>" . nl2br($message) . "</p>
                </div>
            </div>
        </body>
        </html>";
        
        // Version texte alternative
        $mail->AltBody = "Nouveau message de $name ($email)\n\nTéléphone: $phone\n\nSujet: $subject\n\nMessage:\n$message";
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de l\'envoi du message: ' . $mail->ErrorInfo]);
    }
} else {
    // Si quelqu'un essaie d'accéder directement à cette page
    header("Location: contact.html");
    exit;
}

// Fonction pour vérifier le reCAPTCHA
function verifyRecaptcha($recaptcha_response) {
    if (empty($recaptcha_response)) {
        return false;
    }
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
        'response' => $recaptcha_response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $result_json = json_decode($result, true);
    
    return $result_json['success'] ?? false;
}
?>