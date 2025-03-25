document.addEventListener('DOMContentLoaded', function() {
    // Filtre de portfolio
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');
    
    // Afficher tous les items au chargement
    portfolioItems.forEach(item => {
        item.classList.add('show');
    });
    
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Retirer la classe active de tous les boutons
            filterButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Ajouter la classe active au bouton cliqué
            button.classList.add('active');
            
            // Obtenir la catégorie à filtrer
            const filterValue = button.getAttribute('data-category');
            
            // Filtrer les éléments du portfolio
            portfolioItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                
                // Cacher d'abord tous les items
                item.classList.remove('show');
                
                // Afficher après un court délai pour l'animation
                setTimeout(() => {
                    if (filterValue === 'all' || filterValue === itemCategory) {
                        item.style.display = 'flex';
                        // Délai pour l'animation d'apparition
                        setTimeout(() => {
                            item.classList.add('show');
                        }, 50);
                    } else {
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                }, 300);
            });
        });
    });
    
    // Effet de parallaxe subtil sur les images
    const portfolioImages = document.querySelectorAll('.portfolio-image');
    
    window.addEventListener('mousemove', e => {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        portfolioImages.forEach(image => {
            const rect = image.getBoundingClientRect();
            const imageX = rect.left + rect.width / 2;
            const imageY = rect.top + rect.height / 2;
            
            // Ne pas appliquer l'effet si l'image n'est pas visible
            if (
                rect.bottom < 0 || 
                rect.top > window.innerHeight || 
                rect.right < 0 || 
                rect.left > window.innerWidth
            ) {
                return;
            }
            
            // Calculer le mouvement en fonction de la position de la souris
            const moveX = (mouseX - 0.5) * 10;
            const moveY = (mouseY - 0.5) * 10;
            
            // Appliquer la transformation avec une transition douce
            image.style.transform = `translate(${moveX}px, ${moveY}px)`;
        });
    });
    
    // Animation fluide au défilement pour les projets
    function animatePortfolioItems() {
        const windowHeight = window.innerHeight;
        
        portfolioItems.forEach((item, index) => {
            const itemTop = item.getBoundingClientRect().top;
            if (itemTop < windowHeight - 100) {
                setTimeout(() => {
                    item.classList.add('show');
                }, index * 200);
            }
        });
    }
    
    // Initialiser les animations
    animatePortfolioItems();
    window.addEventListener('scroll', animatePortfolioItems);
});


    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
        const images = carousel.querySelectorAll('.carousel-images img');
        const prevBtn = carousel.querySelector('.prev');
        const nextBtn = carousel.querySelector('.next');
        let currentIndex = 0;

        function showImage(index) {
            images.forEach((img, i) => {
                img.style.display = i === index ? 'block' : 'none';
            });
        }

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(currentIndex);
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
        });

        showImage(currentIndex);
    });
