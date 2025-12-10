<!-- Header DormQuest -->
<?php
// Initialiser les variables si elles ne sont pas déjà définies
if (!isset($isLoggedIn)) {
    $isLoggedIn = is_logged_in();
}
if (!isset($userType)) {
    $userType = get_user_role();
}
?>
<header class="main-header">
    <div class="header-container">
        <div class="logo-section">
            <a href="index.php" class="logo">
                <img src="img/logo-dormquest.png" alt="DormQuest Logo" class="logo-img">
                <span class="logo-text">DormQuest</span>
            </a>
        </div>

        <nav class="main-nav" id="mainNav">
            <ul class="nav-links">
                <li><a href="annonces.php" class="nav-link">Annonces</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>

                <?php if ($isLoggedIn): ?>
                <?php if ($userType === 'loueur'): ?>
                <li><a href="dashboard-loueur.php" class="nav-link">Mon Tableau de bord</a></li>
                <li><a href="create-annonce.php" class="nav-link">Créer une annonce</a></li>
                <?php else: ?>
                <li><a href="dashboard-etudiant.php" class="nav-link">Tableau de bord</a></li>
                <li><a href="favoris.php" class="nav-link">Favoris</a></li>
                <li><a href="candidatures.php" class="nav-link">Candidatures</a></li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>

            <div class="nav-actions">
                <?php if ($isLoggedIn): ?>
                <a href="profil.php" class="btn-profile">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="10" cy="6" r="3" stroke="currentColor" stroke-width="2" />
                        <path d="M4 16c0-3.314 2.686-6 6-6s6 2.686 6 6" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" />
                    </svg>
                    Mon profil
                </a>
                <a href="logout.php" class="btn-logout">Déconnexion</a>
                <?php else: ?>
                <a href="login.php" class="btn-login">Connexion</a>
                <a href="register.php" class="btn-register">Inscription</a>
                <?php endif; ?>
            </div>
        </nav>

        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
</header>

<style>
/* ===== Header Styles ===== */
.main-header {
    background-color: var(--color-white);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
    padding: 0;
    transition: transform 0.3s ease-in-out;
}

.main-header.header-hidden {
    transform: translateY(-100%);
}

.header-container {
    max-width: var(--container-width);
    margin: 0 auto;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

/* Logo */
.logo-section {
    flex-shrink: 0;
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--color-primary);
    font-weight: 700;
    font-size: 1.5rem;
    transition: var(--transition);
}

.logo:hover {
    color: var(--color-primary-dark);
    transform: scale(1.02);
}

.logo-img {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

.logo-text {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Navigation */
.main-nav {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex: 1;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
}

.nav-link {
    color: var(--color-text);
    font-weight: 500;
    transition: var(--transition);
    position: relative;
    padding: 0.5rem 0;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background-color: var(--color-primary);
    transition: width 0.3s ease;
}

.nav-link:hover {
    color: var(--color-primary);
}

.nav-link:hover::after {
    width: 100%;
}

/* Navigation Actions */
.nav-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.btn-profile {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    color: var(--color-text);
    font-weight: 500;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-profile:hover {
    background-color: var(--color-gray-light);
    color: var(--color-primary);
}

.btn-login {
    padding: 0.625rem 1.5rem;
    color: var(--color-primary);
    font-weight: 600;
    border: 2px solid var(--color-primary);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.btn-login:hover {
    background-color: var(--color-primary);
    color: var(--color-white);
}

.btn-register,
.btn-logout {
    padding: 0.625rem 1.5rem;
    color: var(--color-white);
    font-weight: 600;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-light));
    border-radius: var(--border-radius);
    transition: var(--transition);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.2);
}

.btn-register:hover,
.btn-logout:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
    display: none;
    flex-direction: column;
    gap: 0.375rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
}

.hamburger-line {
    width: 24px;
    height: 2px;
    background-color: var(--color-text);
    transition: var(--transition);
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .header-container {
        padding: 1rem 1.5rem;
    }

    .nav-links {
        gap: 1rem;
    }
}

@media (max-width: 768px) {
    .mobile-menu-toggle {
        display: flex;
    }

    .main-nav {
        position: fixed;
        top: 70px;
        left: 0;
        right: 0;
        background-color: var(--color-white);
        flex-direction: column;
        padding: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .main-nav.active {
        transform: translateX(0);
    }

    .nav-links {
        flex-direction: column;
        width: 100%;
        gap: 0.5rem;
    }

    .nav-link {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: var(--border-radius);
    }

    .nav-link:hover {
        background-color: var(--color-gray-light);
    }

    .nav-actions {
        width: 100%;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-profile,
    .btn-login,
    .btn-register,
    .btn-logout {
        width: 100%;
        justify-content: center;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .header-container {
        padding: 1rem;
    }

    .logo {
        font-size: 1.25rem;
    }

    .logo-img {
        width: 28px;
        height: 28px;
    }
}
</style>

<script>
// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const mainNav = document.getElementById('mainNav');
    const header = document.querySelector('.main-header');
    let lastScrollTop = 0;
    let scrollThreshold = 100; // Commence à masquer après 100px de scroll

    if (mobileMenuToggle && mainNav) {
        mobileMenuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');

            // Animate hamburger lines
            const lines = this.querySelectorAll('.hamburger-line');
            if (mainNav.classList.contains('active')) {
                lines[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                lines[1].style.opacity = '0';
                lines[2].style.transform = 'rotate(-45deg) translate(7px, -6px)';
            } else {
                lines[0].style.transform = 'none';
                lines[1].style.opacity = '1';
                lines[2].style.transform = 'none';
            }
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(event) {
        if (mainNav && mobileMenuToggle) {
            if (!mainNav.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                mainNav.classList.remove('active');
                const lines = mobileMenuToggle.querySelectorAll('.hamburger-line');
                lines[0].style.transform = 'none';
                lines[1].style.opacity = '1';
                lines[2].style.transform = 'none';
            }
        }
    });

    // Hide/Show header on scroll
    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop > scrollThreshold) {
            if (scrollTop > lastScrollTop) {
                // Scroll vers le bas - masquer le header
                header.classList.add('header-hidden');
            } else {
                // Scroll vers le haut - afficher le header
                header.classList.remove('header-hidden');
            }
        } else {
            // En haut de la page - toujours afficher
            header.classList.remove('header-hidden');
        }

        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, false);
});
</script>