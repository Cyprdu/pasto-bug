<?php
session_start();
// Pas besoin de db_connect ici sauf si on veut afficher des stats dynamiques, 
// mais pour du texte statique c'est inutile.
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qui sommes-nous ? - PaJe</title>
    <link rel="icon" type="image/png" href="https://github.com/Cyprdu/PaJe/blob/main/img/favico.png?raw=true">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Polices identiques au reste du site */
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'RobotoMedium'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Medium.ttf') format('truetype'); font-weight: 500; }
        @font-face { font-family: 'RobotoRegular'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Regular.ttf') format('truetype'); font-weight: 400; }
        
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .font-regular { font-family: 'RobotoRegular', sans-serif; }
        
        /* Animation d'apparition */
        .fade-in-up { opacity: 0; transform: translateY(30px); transition: opacity 0.8s ease, transform 0.8s ease; }
        .fade-in-up.visible { opacity: 1; transform: translateY(0); }

        /* Hero spécifique */
        .hero-about {
            height: 50vh;
            background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('uploads/logo/byYLbEhM6ou9ZqJUQh5lp2fBzZl2gBVuC-d1H7ldAEkeJxFPc.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Effet sur les cartes valeurs */
        .value-card:hover { transform: translateY(-5px); }
        .value-card:hover .icon-box { background-color: #dc2626; color: white; border-color: #dc2626; }
    </style>
</head>
<body class="font-regular text-gray-800 bg-gray-50 flex flex-col min-h-screen">

    <?php include 'header.php'; ?>

    <header class="hero-about flex items-center justify-center text-white relative">
        <div class="text-center px-4 fade-in-up visible">
            <h1 class="font-display text-6xl md:text-7xl drop-shadow-lg mb-4">Notre Mission</h1>
            <div class="w-24 h-1 bg-red-600 mx-auto rounded-full"></div>
            <p class="mt-6 text-xl md:text-2xl font-light max-w-2xl mx-auto drop-shadow-md">
                Au service de la jeunesse et des vocations
            </p>
        </div>
    </header>

    <section class="py-20 max-w-6xl mx-auto px-4">
        <div class="grid md:grid-cols-12 gap-12 items-center">
            
            <div class="md:col-span-4 flex justify-center fade-in-up">
                <div class="bg-white p-6 rounded-2xl shadow-xl transform rotate-2 hover:rotate-0 transition duration-500">
                    <img src="uploads/logo/unnamed.jpg" alt="Logo PaJe Belley-Ars" class="w-full max-w-[250px] object-contain">
                </div>
            </div>

            <div class="md:col-span-8 fade-in-up">
                <h2 class="font-display text-4xl text-gray-800 mb-6">Qui sommes-nous ?</h2>
                <div class="prose prose-lg text-gray-600 leading-relaxed">
                    <p class="mb-4">
                        La <strong class="text-red-600">PaJe</strong> est la Pastorale des Jeunes et des Vocations du diocèse de <strong class="text-gray-800">Belley-Ars</strong>. Notre mission est d'accompagner les jeunes de <strong class="text-gray-800">6 à 35 ans</strong> dans leur découverte et leur approfondissement de la foi chrétienne.
                    </p>
                    <p class="mb-4">
                        Nous œuvrons à l’évangélisation des jeunes et à la formation des disciples missionnaires marchant sur les pas de Jésus.
                    </p>
                    <p>
                        À travers nos camps, week-ends et journées de retraite, nous proposons aux jeunes de vivre des expériences authentiques de rencontre avec le Christ, dans un climat de joie, de fraternité et de partage. Nos activités mêlent temps spirituels (louange, adoration, enseignements) et moments de détente (grands jeux, activités créatives, temps de partages) pour offrir une expérience vivante et enrichissante.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-red-50 rounded-full blur-3xl opacity-50"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-blue-50 rounded-full blur-3xl opacity-50"></div>

        <div class="max-w-6xl mx-auto px-4 relative z-10">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="font-display text-5xl font-bold text-gray-800 mb-6">Nos valeurs</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto"></div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <div class="value-card bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm transition duration-300 fade-in-up">
                    <div class="icon-box w-14 h-14 rounded-full border-2 border-red-200 text-red-600 flex items-center justify-center text-2xl mb-6 transition duration-300">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="font-display text-xl text-gray-800 mb-2">Accueil et bienveillance</h3>
                    <p class="text-gray-600 text-sm">Chacun est accueilli tel qu'il est, avec écoute et respect, pour trouver sa place au sein du groupe.</p>
                </div>

                <div class="value-card bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm transition duration-300 fade-in-up" style="transition-delay: 100ms">
                    <div class="icon-box w-14 h-14 rounded-full border-2 border-red-200 text-red-600 flex items-center justify-center text-2xl mb-6 transition duration-300">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="font-display text-xl text-gray-800 mb-2">Fraternité et partage</h3>
                    <p class="text-gray-600 text-sm">Vivre ensemble des moments forts, tisser des liens d'amitié solides et partager nos expériences de vie.</p>
                </div>

                <div class="value-card bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm transition duration-300 fade-in-up" style="transition-delay: 200ms">
                    <div class="icon-box w-14 h-14 rounded-full border-2 border-red-200 text-red-600 flex items-center justify-center text-2xl mb-6 transition duration-300">
                        <i class="fas fa-cross"></i>
                    </div>
                    <h3 class="font-display text-xl text-gray-800 mb-2">Découverte de la foi</h3>
                    <p class="text-gray-600 text-sm">Une rencontre personnelle et authentique avec le Christ à travers la prière et les sacrements.</p>
                </div>

                <div class="value-card bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm transition duration-300 fade-in-up" style="transition-delay: 300ms">
                    <div class="icon-box w-14 h-14 rounded-full border-2 border-red-200 text-red-600 flex items-center justify-center text-2xl mb-6 transition duration-300">
                        <i class="fas fa-laugh-beam"></i>
                    </div>
                    <h3 class="font-display text-xl text-gray-800 mb-2">Joie et épanouissement</h3>
                    <p class="text-gray-600 text-sm">Parce que croire rend heureux ! Nous vivons notre foi dans une joie communicative et dynamique.</p>
                </div>

                <div class="value-card bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm transition duration-300 fade-in-up" style="transition-delay: 400ms">
                    <div class="icon-box w-14 h-14 rounded-full border-2 border-red-200 text-red-600 flex items-center justify-center text-2xl mb-6 transition duration-300">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="font-display text-xl text-gray-800 mb-2">Accompagnement</h3>
                    <p class="text-gray-600 text-sm">Un soutien pour aider chaque jeune à grandir, à discerner et à s'engager dans sa vie.</p>
                </div>

                <div class="value-card bg-gray-50 rounded-xl p-8 border border-gray-100 shadow-sm transition duration-300 fade-in-up" style="transition-delay: 500ms">
                    <div class="icon-box w-14 h-14 rounded-full border-2 border-red-200 text-red-600 flex items-center justify-center text-2xl mb-6 transition duration-300">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h3 class="font-display text-xl text-gray-800 mb-2">Mission et feu de l'Esprit</h3>
                    <p class="text-gray-600 text-sm">Oser témoigner de sa foi et devenir des disciples missionnaires enflammés par l'Esprit Saint.</p>
                </div>

            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50 text-center">
        <div class="max-w-4xl mx-auto px-4 fade-in-up">
            <h2 class="font-display text-4xl text-gray-800 mb-6">Envie d'en savoir plus ou de nous rejoindre ?</h2>
            <p class="text-gray-600 text-lg mb-8">
                Que tu sois jeune, parent, ou animateur, n'hésite pas à nous contacter pour toute question.
            </p>
            <a href="index.php#contact" class="inline-flex items-center gap-3 bg-red-600 text-white font-display text-xl px-10 py-4 rounded-full hover:bg-red-700 transition shadow-lg transform hover:-translate-y-1">
                <span>Nous contacter</span>
                <i class="fas fa-paper-plane"></i>
            </a>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
        // Animation au scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
    </script>
</body>
</html>