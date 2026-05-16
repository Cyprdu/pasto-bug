<?php
session_start();
require_once 'db_connect.php';

// Vérification de l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Récupération des infos du camp
$stmt = $pdo->prepare("SELECT * FROM camps WHERE id = ? AND supprime = 0");
$stmt->execute([$id]);
$camp = $stmt->fetch();

// Si le camp n'existe pas ou est supprimé
if (!$camp) {
    header("Location: index.php");
    exit;
}

// Fonction de formatage de date (Français)
function formatDateLong($dateStr) {
    setlocale(LC_TIME, 'fr_FR.UTF8', 'fra');
    $date = new DateTime($dateStr);
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
    return ucfirst($formatter->format($date));
}

// Récupération des paiements
$paiements = json_decode($camp['moyens_paiement'], true);
if (!is_array($paiements)) $paiements = [];

?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($camp['titre']) ?> - PaJe</title>
    <link rel="icon" type="image/png" href="https://github.com/Cyprdu/PaJe/blob/main/img/favico.png?raw=true">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'RobotoMedium'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Medium.ttf') format('truetype'); font-weight: 500; }
        @font-face { font-family: 'RobotoRegular'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Regular.ttf') format('truetype'); font-weight: 400; }
        
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .font-regular { font-family: 'RobotoRegular', sans-serif; }

        .hero-detail {
            height: 60vh;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.8));
        }
        
        /* Modales */
        .video-modal, .registration-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; display: none; }
        .video-modal { background: rgba(0, 0, 0, 0.9); align-items: center; justify-content: center; }
        .registration-overlay { background: white; flex-direction: column; }
        .registration-iframe { flex: 1; border: none; width: 100%; }
    </style>
</head>
<body class="font-regular text-gray-800 bg-gray-50 flex flex-col min-h-screen">

    <?php include 'header.php'; ?>

    <?php $imgCover = !empty($camp['image_couverture']) ? htmlspecialchars($camp['image_couverture']) : 'https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true'; ?>
    
    <header class="hero-detail flex items-end pb-12" style="background-image: url('<?= $imgCover ?>');">
        <div class="hero-overlay absolute inset-0"></div>
        
        <button onclick="goBack()" 
                class="absolute top-24 left-4 md:left-8 z-30 bg-white/90 hover:bg-white text-red-600 px-5 py-2 rounded-full shadow-lg font-bold flex items-center gap-2 transition transform hover:-translate-x-1 backdrop-blur-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </button>

        <div class="max-w-7xl mx-auto px-4 relative z-10 w-full">
            <div class="mb-4 animate-fade-in-up">
                <span class="bg-red-600 text-white px-4 py-1 rounded-full text-sm font-bold uppercase tracking-wider shadow-lg">
                    <?= $camp['age_min'] ?> - <?= $camp['age_max'] ?> ans
                </span>
                <?php if($camp['itinerant']): ?>
                    <span class="bg-orange-500 text-white px-4 py-1 rounded-full text-sm font-bold uppercase tracking-wider shadow-lg ml-2">
                        <i class="fas fa-hiking"></i> Itinérant
                    </span>
                <?php endif; ?>
            </div>
            <h1 class="font-display text-5xl md:text-7xl text-white drop-shadow-xl mb-2"><?= htmlspecialchars($camp['titre']) ?></h1>
            <p class="text-gray-200 text-xl flex items-center gap-2 drop-shadow-md">
                <i class="far fa-calendar-alt"></i> 
                <?= formatDateLong($camp['date_debut']) ?>
            </p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-12 -mt-10 relative z-20 flex-grow">
        <div class="grid lg:grid-cols-3 gap-12">
            
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-8 md:p-10 mb-8">
                    <h2 class="font-display text-3xl text-gray-800 mb-6 pb-4 border-b">À propos de cet événement</h2>
                    <div class="prose max-w-none text-gray-700 text-lg leading-relaxed whitespace-pre-line">
                        <?= htmlspecialchars($camp['description']) ?>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl p-8 sticky top-24 border-t-4 border-red-600">
                    <h3 class="font-display text-2xl text-gray-800 mb-6">Détails pratiques</h3>
                    
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                <i class="far fa-clock text-lg"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-700">Début</p>
                                <p class="text-gray-600"><?= formatDateLong($camp['date_debut']) ?></p>
                                <p class="font-bold text-gray-700 mt-2">Fin</p>
                                <p class="text-gray-600"><?= formatDateLong($camp['date_fin']) ?></p>
                            </div>
                        </li>

                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                <i class="fas fa-map-marker-alt text-lg"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-700">Lieu</p>
                                <p class="text-gray-600"><?= htmlspecialchars($camp['adresse']) ?></p>
                                <?php if(!empty($camp['pays'])): ?>
                                    <p class="text-gray-500 text-sm uppercase"><?= htmlspecialchars($camp['pays']) ?></p>
                                <?php endif; ?>
                            </div>
                        </li>

                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                                <i class="fas fa-tag text-lg"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-700">Prix</p>
                                <p class="text-2xl font-display text-red-600"><?= number_format($camp['prix'], 2, ',', ' ') ?> €</p>
                            </div>
                        </li>

                        <?php if(!empty($paiements)): ?>
                        <li class="pt-4 border-t">
                            <p class="text-sm text-gray-500 mb-2">Moyens de paiement acceptés :</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach($paiements as $p): ?>
                                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded text-xs font-bold border">
                                        <?= htmlspecialchars($p) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <div class="mt-8 space-y-3">
                        <?php if(!empty($camp['lien_inscription'])): ?>
                            <button onclick="openRegistration('<?= htmlspecialchars($camp['lien_inscription']) ?>')" class="w-full bg-red-600 text-white font-display text-xl py-4 rounded-full hover:bg-red-700 transition shadow-lg transform hover:-translate-y-1 flex items-center justify-center gap-2">
                                <span>S'inscrire maintenant</span>
                                <i class="fas fa-external-link-alt text-sm"></i>
                            </button>
                        <?php else: ?>
                            <button disabled class="w-full bg-gray-300 text-gray-500 font-display text-xl py-4 rounded-full cursor-not-allowed">
                                Inscriptions bientôt
                            </button>
                        <?php endif; ?>

                        <?php if(!empty($camp['lien_teaser'])): ?>
                            <button onclick="openVideoModal('<?= htmlspecialchars($camp['lien_teaser']) ?>')" class="w-full bg-white text-red-600 border-2 border-red-600 font-display text-xl py-3 rounded-full hover:bg-red-50 transition flex items-center justify-center gap-2">
                                <i class="fas fa-play"></i> Voir le teaser
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>


    <div id="video-modal" class="video-modal hidden">
        <div class="relative w-[90%] max-w-4xl">
            <button class="absolute -top-12 -right-2 text-white text-5xl hover:text-red-600 transition z-50 focus:outline-none" onclick="closeVideoModal()">
                &times;
            </button>
            
            <div class="aspect-video bg-black rounded-xl overflow-hidden shadow-2xl relative">
                <iframe id="video-iframe" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <div id="registration-overlay" class="registration-overlay hidden">
        <div class="bg-red-600 p-4 flex justify-between items-center shadow-md">
            <button onclick="closeRegistration()" class="text-white font-bold flex items-center gap-2 hover:bg-white/10 px-3 py-1 rounded transition">
                <i class="fas fa-arrow-left"></i> Retour
            </button>
            <h2 class="font-display text-xl text-white tracking-wide">Inscription</h2>
            <div class="w-20"></div>
        </div>
        <iframe id="registration-iframe" class="registration-iframe"></iframe>
    </div>

    <script>
        // Logique Bouton Retour
        function goBack() {
            // Vérifie s'il y a un historique et qu'on est sur le même domaine
            if (document.referrer !== "" && document.referrer.includes(window.location.hostname)) {
                window.history.back();
            } else {
                // Sinon fallback sur l'accueil
                window.location.href = 'index.php';
            }
        }

        // Logique Modales
        function openVideoModal(url) {
            let embedUrl = url;
            if (url.includes('youtu.be/')) embedUrl = 'https://www.youtube.com/embed/' + url.split('youtu.be/')[1].split('?')[0];
            else if (url.includes('youtube.com/watch?v=')) embedUrl = 'https://www.youtube.com/embed/' + url.split('v=')[1].split('&')[0];
            embedUrl += "?autoplay=1";
            document.getElementById('video-modal').style.display = 'flex';
            document.getElementById('video-iframe').src = embedUrl;
        }
        function closeVideoModal() {
            document.getElementById('video-modal').style.display = 'none';
            document.getElementById('video-iframe').src = '';
        }
        function openRegistration(url) {
            document.getElementById('registration-overlay').style.display = 'flex';
            document.getElementById('registration-iframe').src = url;
            document.body.style.overflow = 'hidden';
        }
        function closeRegistration() {
            document.getElementById('registration-overlay').style.display = 'none';
            document.getElementById('registration-iframe').src = '';
            document.body.style.overflow = 'auto';
        }
    </script>

</body>
</html>