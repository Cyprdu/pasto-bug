<?php
// header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Détection de la page courante
$currentPage = basename($_SERVER['PHP_SELF']);
// On considère qu'on est sur l'index si le fichier est index.php ou si l'URI est juste "/"
$isIndex = ($currentPage == 'index.php' || $_SERVER['REQUEST_URI'] == '/');

// Préfixe pour les liens d'ancres
$anchorPrefix = $isIndex ? '' : 'index.php';
?>

<style>
    /* Effet de soulignement animé */
    .nav-item {
        position: relative;
        text-decoration: none;
        padding-bottom: 4px;
    }
    
    .nav-item::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: 0;
        left: 0;
        background-color: #dc2626; /* Rouge */
        transition: width 0.3s ease-in-out;
    }

    /* État au survol : la barre grandit */
    .nav-item:hover::after {
        width: 100%;
    }

    /* État actif (géré par JS) : la barre reste fixe et le texte est rouge */
    .nav-active {
        color: #dc2626 !important;
        font-weight: 600;
    }
    .nav-active::after {
        width: 100%;
    }
</style>

<nav class="bg-white/95 backdrop-blur-md shadow-md fixed w-full top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <a href="index.php" class="flex items-center cursor-pointer group">
                <h1 class="font-display text-3xl font-bold text-red-600 group-hover:scale-105 transition-transform">PaJe</h1>
            </a>

            <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-8" id="desktop-menu">
                    <a href="<?= $anchorPrefix ?>#accueil" class="nav-item text-gray-700 font-display transition-colors">Accueil</a>
                    <a href="<?= $anchorPrefix ?>#qui-sommes-nous" class="nav-item text-gray-700 font-display transition-colors">Qui sommes-nous</a>
                    <a href="<?= $anchorPrefix ?>#camps" class="nav-item text-gray-700 font-display transition-colors">Nos camps</a>
                    <a href="<?= $anchorPrefix ?>#activites" class="nav-item text-gray-700 font-display transition-colors">Nos activités</a>
                    <a href="<?= $anchorPrefix ?>#photos" class="nav-item text-gray-700 font-display transition-colors">Photos</a>
                    <a href="<?= $anchorPrefix ?>#contact" class="nav-item text-gray-700 font-display transition-colors">Contact</a>

                    <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                        <a href="dashboard_admin.php" class="bg-gray-800 text-white px-3 py-1 rounded hover:bg-black transition text-sm font-bold ml-4 font-display">
                            Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="md:hidden">
                <button id="mobile-menu-btn" class="text-gray-700 hover:text-red-600 focus:outline-none p-2">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div id="mobile-menu" class="md:hidden hidden bg-white border-t absolute w-full shadow-xl">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="<?= $anchorPrefix ?>#accueil" class="mobile-link block px-3 py-2 font-display text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-md">Accueil</a>
            <a href="<?= $anchorPrefix ?>#qui-sommes-nous" class="mobile-link block px-3 py-2 font-display text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-md">Qui sommes-nous</a>
            <a href="<?= $anchorPrefix ?>#camps" class="mobile-link block px-3 py-2 font-display text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-md">Nos camps</a>
            <a href="<?= $anchorPrefix ?>#activites" class="mobile-link block px-3 py-2 font-display text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-md">Nos activités</a>
            <a href="<?= $anchorPrefix ?>#photos" class="mobile-link block px-3 py-2 font-display text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-md">Photos</a>
            <a href="<?= $anchorPrefix ?>#contact" class="mobile-link block px-3 py-2 font-display text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-md">Contact</a>
            
            <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                <a href="dashboard_admin.php" class="block px-3 py-2 font-display text-white bg-gray-800 rounded-md mt-2">Dashboard Admin</a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- GESTION MENU MOBILE ---
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const mobileLinks = document.querySelectorAll('.mobile-link');
            
            if(btn && menu) {
                btn.addEventListener('click', () => menu.classList.toggle('hidden'));
                mobileLinks.forEach(link => {
                    link.addEventListener('click', () => menu.classList.add('hidden'));
                });
            }

            // --- SCROLL SPY (Soulignement Dynamique) ---
            // Ce script ne s'exécute que si nous sommes sur la page d'index et que les sections existent
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('#desktop-menu a');

            // Si on n'est pas sur la page d'accueil ou qu'il n'y a pas de sections, on arrête le script ici
            if (sections.length === 0) return;

            window.addEventListener('scroll', () => {
                let current = '';
                
                // On détecte quelle section est visible
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    // On ajoute un décalage (100px) pour compenser la hauteur du header fixe
                    if (scrollY >= (sectionTop - 150)) {
                        current = section.getAttribute('id');
                    }
                });

                // Si on est tout en haut de la page, active 'accueil' par défaut
                if (scrollY < 100) {
                    current = 'accueil';
                }

                // On applique la classe active au bon lien
                navLinks.forEach(link => {
                    link.classList.remove('nav-active');
                    // On vérifie si le href du lien contient l'ID de la section actuelle
                    if (link.getAttribute('href').includes('#' + current)) {
                        link.classList.add('nav-active');
                    }
                });
            });
        });
    </script>
</nav>