<?php
require_once 'lib/config.php';

// Pagination
$articles_par_page = 6;
$page_actuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page_actuelle - 1) * $articles_par_page;

// Requête pour compter le nombre total d'articles
$query_count = "SELECT COUNT(*) FROM articles";
$stmt_count = $pdo->prepare($query_count);
$stmt_count->execute();
$total_articles = $stmt_count->fetchColumn();
$total_pages = ceil($total_articles / $articles_par_page);

// Requête pour récupérer les articles
$query = "SELECT id, titre, contenu, image, date_creation 
          FROM articles
          ORDER BY date_creation DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':limit', $articles_par_page, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        
        <!-- Titre optimisé avec mots-clés géolocalisés -->
        <title>Sultanne | Logos, Flyers, Sites Web | Le blog</title>
        
        <!-- Meta description optimisée -->
        <meta name="description" content="Création de supports numériques personnalisés à Bapaume : logos, flyers, affiches, sites web à prix abordables pour TPE et particuliers. Services de communication numérique de qualité par un designer junior local.">
        
        <!-- Mots-clés (moins importants aujourd'hui mais toujours utiles) -->
        <meta name="keywords" content="création web Bapaume, logo pas cher, flyer Bapaume, site internet TPE, montage photo, design graphique 62450, communication numérique, affiche personnalisée">
        
        <!-- Contrôle du robot d'indexation -->
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow">
        
        <!-- Auteur et copyright -->
        <meta name="author" content="Sultanne">
        
        <!-- Open Graph pour partage sur réseaux sociaux -->
        <meta property="og:title" content="Sultanne | Création Web et Design à Bapaume">
        <meta property="og:description" content="Création de supports numériques personnalisés à prix abordables : logos, flyers, affiches, sites web pour TPE et particuliers à Bapaume.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="https://sultanne.fr/">
        <meta property="og:image" content="https://sultanne.fr/images/logo-sultanne.jpg">
        <meta property="og:locale" content="fr_FR">
        <meta property="og:site_name" content="Sultanne">
        
        <!-- Localisation géographique -->
        <meta name="geo.region" content="FR-62">
        <meta name="geo.placename" content="Bapaume">
        <meta name="geo.position" content="50.1015;2.8513">
        <meta name="ICBM" content="50.1015, 2.8513">
        
        <!-- Favicon -->
        <link rel="icon" href="images/favicon.ico" type="image/x-icon">
        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
        <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
        
        <!-- Stylesheets -->
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/actualites.css">
        
        <!-- Polices -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        
        <!-- Canonical URL -->
        <link rel="canonical" href="https://sultanne.fr/actualites.php">
    </head>
<body>
    <!-- Overlay d'arrière-plan avec effet de particules subtil -->
    <div class="particles-overlay"></div>
    
    <!-- Logo et Navigation principale -->
    <header>
        <div class="logo-container">
            <img src="images/LOGO BLANC - SULTANNE - SVG - FOND TRANSPARENT.svg" alt="Sultanne" class="logo">
        </div>
        
        <nav class="main-nav">
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="portfolio.html">Portfolio</a></li>
                <li><a href="about.html">Qui suis-je</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li class="active"><a href="actualites.php">Le blog</a></li>
                <li><a href="informationsLegales.html">Informations légales</a></li>
            </ul>
        </nav>
    </header>
<main>
    <!-- Section Compteur Facebook -->
    <section class="social-counter">
        <div class="counter-container">
            <div class="facebook-counter">
                <div class="counter-icon">
                    <svg viewBox="0 0 24 24" class="facebook-svg">
                        <path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z"/>
                    </svg>
                </div>
                <div class="counter-text">
                    <h3>Rejoignez la communauté <span class="highlight">Sultanne</span> !</h3>
                    <p class="counter-number">Vous êtes déjà <span id="follower-count">plus de 700</span> à me suivre sur Facebook</p>
                    <p class="counter-tagline">Rejoignez l'aventure pour découvrir mon travail avec ma mascotte Super Cacahuète ! <br><span class="small-text">(Aucune obligation d'achat, juste du fun et de la créativité)</span></p>
                    <a href="https://www.facebook.com/profile.php?id=61571421057061" target="_blank" class="btn-social">Rejoindre Super Cacahuète</a>
                </div>
            </div>
        </div>
    </section>

    <section class="actualites-header">
        <div class="container">
            <h1>Actualités</h1>
            <p>Restez informé des dernières nouvelles de Sultanne</p>
        </div>
    </section>

    <div class="articles-container">
    <?php if(count($articles) > 0): ?>
        <?php foreach($articles as $article): ?>
            <article class="article-card">
                <?php if($article['image']): ?>
                <div class="article-image">
                    <img src="uploads/actualites/<?php echo htmlspecialchars($article['image']); ?>" 
                         alt="<?php echo htmlspecialchars($article['titre']); ?>">
                </div>
                <?php endif; ?>
                
                <div class="article-content">
                    <div class="article-meta">
                        <time datetime="<?php echo date('Y-m-d', strtotime($article['date_creation'])); ?>">
                            <?php echo date('d/m/Y', strtotime($article['date_creation'])); ?>
                        </time>
                    </div>
                    
                    <h2><?php echo htmlspecialchars($article['titre']); ?></h2>
                    
                    <div class="article-text">
                        <?php echo $article['contenu']; ?>
                    </div>

<!-- L'ensemble du processus se déroule dans l'interface de Facebook -->
                    <div class="article-share">
                        <p>Partager :</p>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>" 
                               target="_blank" class="share-facebook" aria-label="Partager sur Facebook">
                                <svg viewBox="0 0 24 24" width="18" height="18">
                                    <path d="M12 2C6.477 2 2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.879V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.989C18.343 21.129 22 16.99 22 12c0-5.523-4.477-10-10-10z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-articles">
            <p>Aucun article disponible pour le moment.</p>
        </div>
    <?php endif; ?>
</div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php if($page_actuelle > 1): ?>
                <a href="?page=<?php echo $page_actuelle - 1; ?>" class="pagination-prev">&laquo; Précédent</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="pagination-number <?php echo $i === $page_actuelle ? 'active' : ''; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
                
                <?php if($page_actuelle < $total_pages): ?>
                <a href="?page=<?php echo $page_actuelle + 1; ?>" class="pagination-next">Suivant &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>


    <section class="newsletter-section" id="newsletter">
    <div class="container">
        <div class="newsletter-container">
            <div class="newsletter-content">
                <h2>Restez <span class="highlight">informé</span></h2>
                <p>Inscrivez-vous à la newsletter pour recevoir les dernières actualités et conseils en création web directement dans votre boîte mail.</p>
                
                <?php if(isset($_GET['newsletter']) && $_GET['newsletter'] == 'success'): ?>
                    <div class="newsletter-success">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
                        </svg>
                        <p>Merci pour votre inscription! Vous recevrez bientôt mes actualités.</p>
                    </div>
                <?php elseif(isset($_GET['newsletter']) && $_GET['newsletter'] == 'exists'): ?>
                    <div class="newsletter-warning">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                        </svg>
                        <p>Cette adresse email est déjà inscrite à la newsletter.</p>
                    </div>
                <?php elseif(isset($_GET['newsletter']) && $_GET['newsletter'] == 'error'): ?>
                    <div class="newsletter-error">
                        <svg viewBox="0 0 24 24" width="24" height="24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/>
                        </svg>
                        <p>Une erreur s'est produite. Veuillez réessayer.</p>
                    </div>
                <?php endif; ?>
                
                <form action="lib/newsletter_subscribe.php" method="post" class="newsletter-form">
                    <!-- Protection CSRF -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(bin2hex(random_bytes(32))); ?>">
                    
                    <!-- Honeypot anti-spam -->
                    <div class="honeypot">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Votre adresse email" required>
                        <button type="submit" class="btn-newsletter">
                            <span>S'inscrire</span>
                            <svg viewBox="0 0 24 24" width="20" height="20">
                                <path d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z" fill="currentColor"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="form-consent">
                        <input type="checkbox" id="consent" name="consent" required>
                        <label for="consent">J'accepte de recevoir la newsletter et comprends que je peux me désinscrire à tout moment</label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>





</main>
<footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/LOGO BLANC - SULTANNE - SVG - FOND TRANSPARENT.svg" alt="Sultanne">
            </div>
            
            <div class="footer-links">
            <ul class="nav-links">
                <li><a href="index.html">Accueil</a></li>
                <li><a href="portfolio.html">Portfolio</a></li>
                <li><a href="about.html">Qui suis-je</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="actualites.php">Le blog</a></li>
                <li><a href="informationsLegales.html">Informations légales</a></li>
            </ul>
            </div>
            
            <div class="footer-contact">
                <a href="mailto:contact@sultanne.fr">contact@sultanne.fr</a>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Sultanne. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Script JS minimal et efficace -->
    <script src="js/script.js"></script>
</body>
</html>