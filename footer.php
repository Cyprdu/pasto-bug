<?php
// Logique pour les liens d'ancres (comme dans le header)
$isIndex = (basename($_SERVER['PHP_SELF']) == 'index.php');
$prefix = $isIndex ? '' : 'index.php';
?>

<footer class="bg-gray-800 text-white py-12 mt-auto border-t border-gray-700">
    <div class="max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-3 gap-8">
            
            <div>
                <h3 class="font-display text-2xl font-bold text-red-400 mb-4">PaJe</h3>
                <p class="font-regular text-gray-300 mb-4">
                    Pastorale des Jeunes du Diocèse de Belley-Ars
                </p>
                <p class="font-regular text-gray-300">
                    Accompagner les jeunes dans leur découverte de la foi chrétienne
                </p>
            </div>

            <div>
                <h4 class="font-display text-lg mb-4">Liens utiles</h4>
                <ul class="space-y-2 mb-6">
                    <li><a href="<?= $prefix ?>#qui-sommes-nous" class="font-regular text-gray-300 hover:text-red-400 transition-colors">Qui sommes-nous</a></li>
                    <li><a href="<?= $prefix ?>#camps" class="font-regular text-gray-300 hover:text-red-400 transition-colors">Nos camps</a></li>
                    <li><a href="<?= $prefix ?>#activites" class="font-regular text-gray-300 hover:text-red-400 transition-colors">Nos activités</a></li>
                    <li><a href="photo.php" class="font-regular text-gray-300 hover:text-red-400 transition-colors">Photos</a></li>
                    <li><a href="<?= $prefix ?>#contact" class="font-regular text-gray-300 hover:text-red-400 transition-colors">Contact</a></li>
                </ul>

                <h4 class="font-display text-lg mb-4">Suivez-nous</h4>
                <div class="flex space-x-4">
                    <a href="https://www.instagram.com/pastodesjeunesbelleyars" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-red-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"/>
                            <path d="m16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>
                        </svg>
                    </a>
                    <a href="https://www.facebook.com/ainjeuneetcatho" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-red-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                    </a>
                    <a href="https://www.youtube.com/@pastoraledesjeunes-diocese2123" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-red-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/>
                            <path d="m10 15 5-3-5-3z"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-display text-lg mb-4">Contact</h4>
                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                         <div class="mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                         </div>
                        <div>
                            <p class="font-regular text-gray-300 text-sm">31, rue Dr Nodet</p>
                            <p class="font-regular text-gray-300 text-sm">CS 60154</p>
                            <p class="font-regular text-gray-300 text-sm">01004 Bourg Cedex</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <p class="font-regular text-gray-300 text-sm">06 23 25 60 49</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                        <a href="mailto:paje.ain@gmail.com" class="font-regular text-gray-300 text-sm hover:text-white transition">paje.ain@gmail.com</a>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="font-display text-red-400 text-sm mb-2">Responsable diocésain</p>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <p class="font-regular text-gray-300 text-sm">Gaëlle-Marie CIEREN</p>
                        </div>
                    </div>

                    <div class="mt-2">
                        <p class="font-display text-red-400 text-sm mb-2">Secrétaire assistante</p>
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-400">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <p class="font-regular text-gray-300 text-sm">Lydia DADDIZA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="font-regular text-gray-400 text-sm">
                © <?= date('Y') ?> PaJe - Pastorale des Jeunes. Tous droits réservés.
            </p>
            
            <div class="flex gap-4">
                <a href="login_animateur.php" class="flex items-center gap-2 px-3 py-1 rounded-full bg-gray-700 hover:bg-blue-600 text-gray-400 hover:text-white transition-all text-xs font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Espace Animateurs</span>
                </a>

                <a href="admin_login.php" class="flex items-center gap-2 px-3 py-1 rounded-full bg-gray-700 hover:bg-red-600 text-gray-400 hover:text-white transition-all text-xs font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>Espace Admin</span>
                </a>
            </div>
        </div>
    </div>
</footer>