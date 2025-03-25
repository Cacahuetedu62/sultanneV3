document.addEventListener('DOMContentLoaded', function() {
    // Gestion des FAQs
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        
        question.addEventListener('click', () => {
            // Fermer tous les autres items
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Toggle l'état actif de l'item cliqué
            item.classList.toggle('active');
        });
    });
    
    // Gestion du formulaire de contact
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Animation de soumission
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            submitButton.innerHTML = '<span>Envoi en cours...</span>';
            submitButton.disabled = true;
            
            // Simuler un délai d'envoi (à remplacer par votre logique d'envoi réelle)
            setTimeout(() => {
                // Récupérer les données du formulaire
                const formData = new FormData(this);
                let formValues = {};
                
                for (let [key, value] of formData.entries()) {
                    formValues[key] = value;
                }
                
                console.log('Formulaire soumis:', formValues);
                
                // Réinitialiser le formulaire
                contactForm.reset();
                
                // Afficher un message de confirmation
                const confirmationMessage = document.createElement('div');
                confirmationMessage.className = 'form-confirmation';
                confirmationMessage.innerHTML = `
                    <div class="confirmation-icon">✓</div>
                    <h3>Message envoyé avec succès!</h3>
                    <p>Merci pour votre message. Je vous répondrai dans les plus brefs délais.</p>
                `;
                
                // Remplacer le formulaire par le message de confirmation
                contactForm.style.opacity = '0';
                setTimeout(() => {
                    contactForm.parentNode.replaceChild(confirmationMessage, contactForm);
                    confirmationMessage.style.opacity = '0';
                    setTimeout(() => {
                        confirmationMessage.style.opacity = '1';
                    }, 100);
                }, 300);
                
            }, 1500);
        });
    }
    
    // Animation des éléments de contact au scroll
    const contactMethods = document.querySelectorAll('.contact-method');
    const socialIcons = document.querySelectorAll('.social-icon');
    
    function animateOnScroll() {
        const windowHeight = window.innerHeight;
        
        contactMethods.forEach((method, index) => {
            const methodTop = method.getBoundingClientRect().top;
            if (methodTop < windowHeight - 100) {
                setTimeout(() => {
                    method.style.opacity = '1';
                    method.style.transform = 'translateX(0)';
                }, index * 200);
            }
        });
        
        socialIcons.forEach((icon, index) => {
            const iconTop = icon.getBoundingClientRect().top;
            if (iconTop < windowHeight - 100) {
                setTimeout(() => {
                    icon.style.opacity = '1';
                    icon.style.transform = 'translateY(0)';
                }, index * 150);
            }
        });
    }
    
    // Initialiser les animations
    function initContactAnimations() {
        // Préparer les éléments pour l'animation
        contactMethods.forEach(method => {
            method.style.opacity = '0';
            method.style.transform = 'translateX(-30px)';
            method.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
        
        socialIcons.forEach(icon => {
            icon.style.opacity = '0';
            icon.style.transform = 'translateY(20px)';
            icon.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });
        
        // Animer au chargement
        setTimeout(animateOnScroll, 300);
        
        // Animer au scroll
        window.addEventListener('scroll', animateOnScroll);
    }
    
    // Initialiser les fonctionnalités
    initContactAnimations();
});

//capcha + ajax
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Vérifier si le reCAPTCHA est complété
            const recaptchaResponse = grecaptcha.getResponse();
            if (recaptchaResponse.length === 0) {
                // reCAPTCHA non validé
                const messageElement = document.createElement('div');
                messageElement.className = 'error-message';
                messageElement.textContent = 'Veuillez confirmer que vous n\'êtes pas un robot.';
                
                // Supprimer les messages précédents
                const previousMessages = contactForm.closest('.contact-form-container').querySelectorAll('.error-message, .success-message');
                previousMessages.forEach(msg => msg.remove());
                
                const formContainer = contactForm.closest('.contact-form-container');
                formContainer.appendChild(messageElement);
                
                return;
            }
            
            // Création d'un objet FormData pour récupérer les données du formulaire
            const formData = new FormData(this);
            
            // Désactivation du bouton d'envoi pour éviter les soumissions multiples
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<span>Envoi en cours...</span>';
            
            // Supprimer les messages précédents
            const previousMessages = contactForm.closest('.contact-form-container').querySelectorAll('.error-message, .success-message');
            previousMessages.forEach(msg => msg.remove());
            
            // Envoi des données en AJAX
            fetch('process-contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Réactivation du bouton
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                // Création d'un élément pour afficher le message de retour
                const messageElement = document.createElement('div');
                messageElement.className = data.success ? 'success-message' : 'error-message';
                messageElement.textContent = data.message;
                
                // Ajout du message sous le formulaire
                const formContainer = contactForm.closest('.contact-form-container');
                formContainer.appendChild(messageElement);
                
                // Si succès, on réinitialise le formulaire et le reCAPTCHA
                if (data.success) {
                    contactForm.reset();
                    grecaptcha.reset();
                    
                    // Suppression du message après 5 secondes
                    setTimeout(() => {
                        messageElement.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                // Réactivation du bouton en cas d'erreur
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
                
                // Affichage d'un message d'erreur
                const messageElement = document.createElement('div');
                messageElement.className = 'error-message';
                messageElement.textContent = 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.';
                
                const formContainer = contactForm.closest('.contact-form-container');
                formContainer.appendChild(messageElement);
            });
        });
    }
    

});