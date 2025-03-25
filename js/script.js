document.addEventListener('DOMContentLoaded', function() {
    // Variables
    const header = document.querySelector('header');
    const scrollIndicator = document.querySelector('.scroll-indicator');
    const workItems = document.querySelectorAll('.work-item');
    const serviceCards = document.querySelectorAll('.service-card');
    
    // Fonction pour animer les particules de fond
    function createParticles() {
        const particlesContainer = document.querySelector('.particles-overlay');
        const numParticles = 50;
        
        for (let i = 0; i < numParticles; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            // Position aléatoire
            const posX = Math.random() * 100;
            const posY = Math.random() * 100;
            
            // Taille aléatoire
            const size = Math.random() * 2 + 1;
            
            // Style de la particule
            particle.style.cssText = `
                position: absolute;
                top: ${posY}%;
                left: ${posX}%;
                width: ${size}px;
                height: ${size}px;
                background-color: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                pointer-events: none;
                opacity: ${Math.random() * 0.5 + 0.2};
                animation: float ${Math.random() * 10 + 10}s linear infinite;
            `;
            
            particlesContainer.appendChild(particle);
        }
    }
    
    // Défilement vers la section suivante
    if (scrollIndicator) {
        scrollIndicator.addEventListener('click', function() {
            const nextSection = document.querySelector('.services-preview');
            if (nextSection) {
                window.scrollTo({
                    top: nextSection.offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    }
    
    // Header fixe lors du défilement
    window.addEventListener('scroll', function() {
        if (window.scrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
    
    // Animation au scroll
    function revealOnScroll() {
        const windowHeight = window.innerHeight;
        
        serviceCards.forEach((card, index) => {
            const cardTop = card.getBoundingClientRect().top;
            if (cardTop < windowHeight - 100) {
                setTimeout(() => {
                    card.style.opacity = 1;
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
        
        workItems.forEach((item, index) => {
            const itemTop = item.getBoundingClientRect().top;
            if (itemTop < windowHeight - 100) {
                setTimeout(() => {
                    item.style.opacity = 1;
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }
    
    // Initialisation des animations
    function initAnimations() {
        // Préparer les éléments pour l'animation
        serviceCards.forEach(card => {
            card.style.opacity = 0;
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
        
        workItems.forEach(item => {
            item.style.opacity = 0;
            item.style.transform = 'translateY(30px)';
            item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
        
        // Lancer l'animation au chargement
        setTimeout(revealOnScroll, 300);
        
        // Lancer l'animation au défilement
        window.addEventListener('scroll', revealOnScroll);
    }
    
    // Initialiser les fonctionnalités
    createParticles();
    initAnimations();
});

