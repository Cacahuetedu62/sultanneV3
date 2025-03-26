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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    header('Content-Type: application/json');
    
    // Vérification des champs requis
    if (!isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['subject']) || !isset($_POST['message']) || !isset($_POST['privacy']) || !isset($_POST['g-recaptcha-response'])) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs obligatoires doivent être présents.']);
        exit;
    }
    
    // Vérification de la case de confidentialité
    if ($_POST['privacy'] !== 'on' && $_POST['privacy'] !== '1') {
        echo json_encode(['success' => false, 'message' => 'Vous devez accepter que vos données soient utilisées pour être recontacté.']);
        exit;
    }
    
    // Vérification du reCAPTCHA
    $recaptcha_response = $_POST['g-recaptcha-response'];
    if (!verifyRecaptcha($recaptcha_response)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez confirmer que vous n\'êtes pas un robot.']);
        exit;
    }
    
    // Récupération des données du formulaire
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Validation du nom (lettres, espaces, tirets, apostrophes et caractères accentués)
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Le nom est obligatoire.']);
        exit;
    }
    
    if (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\s\-\'.]+$/', $name)) {
        echo json_encode(['success' => false, 'message' => 'Le nom contient des caractères non autorisés.']);
        exit;
    }
    
    // Validation de l'email
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'L\'email est obligatoire.']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Veuillez entrer une adresse email valide.']);
        exit;
    }
    
    // Validation du téléphone (optionnel, mais doit contenir uniquement des chiffres si fourni)
    if (!empty($phone) && !preg_match('/^[0-9]{10,15}$/', $phone)) {
        echo json_encode(['success' => false, 'message' => 'Le numéro de téléphone doit contenir entre 10 et 15 chiffres.']);
        exit;
    }
    
    // Validation du sujet
    if (empty($subject)) {
        echo json_encode(['success' => false, 'message' => 'Le sujet est obligatoire.']);
        exit;
    }
    
    // Validation plus permissive pour le sujet, mais toujours sécurisée
    if (!preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\-\'.,:!?()]+$/', $subject)) {
        echo json_encode(['success' => false, 'message' => 'Le sujet contient des caractères non autorisés.']);
        exit;
    }
    
    // Validation du message
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Le message est obligatoire.']);
        exit;
    }
    
    // Nettoyage sécurisé des entrées pour éviter les injections
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
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
                body {
                    margin: 0;
                    padding: 0;
                    line-height: 1.6;
                }
                .email-wrapper {
                    width: 100%;
                    text-align: center;
                    padding: 20px 0;
                }
                .container { 
                    max-width: 600px; 
                    margin: 0 auto; 
                    padding: 20px; 
                    background-color: white;
                    border-radius: 15px;
                    border: 3px dotted #ff66b3;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
                    text-align: left;
                    display: inline-block;
                }
                h2 { 
                    color: #ff4d94; 
                    text-align: center;
                    font-size: 28px;
                    letter-spacing: 1px;
                    margin-top: 10px;
                    text-shadow: 1px 1px 2px #ffcceb;
                }
                .info { 
                    margin-bottom: 20px; 
                    background-color: #ffedf5;
                    padding: 15px;
                    border-radius: 10px;
                }
                .label { 
                    font-weight: bold; 
                    color: #cc3399;
                }
                .message { 
                    background-color: #f0f8ff; 
                    padding: 15px; 
                    border-left: 5px solid #66b3ff; 
                    border-radius: 10px;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    margin-top: 20px;
                    color: #ff80bf;
                }
                .emoji {
                    font-size: 24px;
                    vertical-align: middle;
                    margin: 0 5px;
                }
            </style>
        </head>
        <body>
            <div class='email-wrapper'>
                <div class='container'>
                    <h2><span class='emoji'>✨</span> Nouveau message sur ton site <span class='emoji'>✨</span></h2>
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
                    <div class='footer'>
                        <p>💖 Message envoyé depuis le formulaire de contact de Sultanne 💖</p>
                        <p>Reçu le: " . date('d/m/Y à H:i') . "</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
        
        // Version texte alternative
        $mail->AltBody = "Nouveau message de $name ($email)\n\nTéléphone: $phone\n\nSujet: $subject\n\nMessage:\n$message";
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.']);
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
    
    if ($result === false) {
        return false;
    }
    
    $result_json = json_decode($result, true);
    return isset($result_json['success']) && $result_json['success'] === true;
}
?>