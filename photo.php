<?php
session_start();
require_once 'db_connect.php';

// --- LOGIQUE DE NAVIGATION ---
$album_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$view_mode = 'list';
$current_album = null;
$album_photos = [];

if ($album_id > 0) {
    // Récupération de l'album demandé
    $stmt = $pdo->prepare("SELECT * FROM albums WHERE id = ? AND supprime = 0");
    $stmt->execute([$album_id]);
    $current_album = $stmt->fetch();

    if ($current_album && $current_album['type_media'] === 'upload') {
        $view_mode = 'detail';
        // Récupération de TOUTES les photos
        $stmt_p = $pdo->prepare("SELECT * FROM photos WHERE album_id = ? ORDER BY position ASC, id DESC");
        $stmt_p->execute([$album_id]);
        $album_photos = $stmt_p->fetchAll(PDO::FETCH_ASSOC);
    } else {
        header("Location: photo.php");
        exit;
    }
} else {
    // Mode Liste : Récupère tous les albums
    $stmt = $pdo->query("SELECT * FROM albums WHERE supprime = 0 ORDER BY created_at DESC");
    $albums = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($view_mode === 'detail') ? htmlspecialchars($current_album['titre']) : 'Photos & Vidéos' ?> - PaJe</title>
    <link rel="icon" type="image/png" href="https://github.com/Cyprdu/PaJe/blob/main/img/favico.png?raw=true">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Polices */
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'RobotoMedium'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Medium.ttf') format('truetype'); font-weight: 500; }
        @font-face { font-family: 'RobotoRegular'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Regular.ttf') format('truetype'); font-weight: 400; }
        
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .font-regular { font-family: 'RobotoRegular', sans-serif; }
        
        /* Image de fond demandée */
        .hero-photos {
            background: linear-gradient(135deg, rgba(31, 41, 55, 0.6) 0%, rgba(0, 0, 0, 0.8) 100%), url('uploads/covers/cover_69528fa2efc82.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        /* Effets de survol */
        .album-card:hover .overlay-icon { opacity: 1; transform: scale(1); }
        
        /* Lightbox Custom */
        #lightbox { transition: opacity 0.3s ease; }
        .lightbox-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 1rem;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s;
            z-index: 2010;
        }
        .lightbox-nav-btn:hover { background: rgba(255, 255, 255, 0.3); }
        .prev-btn { left: 20px; }
        .next-btn { right: 20px; }
    </style>
</head>
<body class="font-regular text-gray-800 bg-gray-50 flex flex-col min-h-screen">

    <?php include 'header.php'; ?>

    <header class="hero-photos py-24 text-white text-center relative">
        <div class="max-w-4xl mx-auto px-4 animate-fade-in-up">
            <?php if($view_mode === 'detail'): ?>
                <a href="photo.php" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 px-4 py-2 rounded-full mb-6 transition backdrop-blur-sm">
                    <i class="fas fa-arrow-left"></i> Retour aux albums
                </a>
                <h1 class="font-display text-4xl md:text-6xl mb-4 drop-shadow-lg"><?= htmlspecialchars($current_album['titre']) ?></h1>
                <p class="text-xl font-light opacity-90"><?= htmlspecialchars($current_album['description']) ?></p>
            <?php else: ?>
                <h1 class="font-display text-5xl md:text-7xl mb-4 drop-shadow-lg">Nos Souvenirs</h1>
                <p class="text-xl md:text-2xl font-light opacity-90">Revivez les moments forts de la PaJe</p>
            <?php endif; ?>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-12 flex-grow">

        <?php if($view_mode === 'list'): ?>
            <?php if(count($albums) > 0): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach($albums as $album): 
                        $cover = !empty($album['image_illustration']) ? $album['image_illustration'] : 'assets/default.jpg';
                        
                        // Logique de clic
                        $onclick = "";
                        $href = "#";
                        $target = "";
                        
                        if ($album['type_media'] === 'upload') {
                            // Galerie interne -> page détail
                            $href = "photo.php?id=" . $album['id'];
                        } elseif ($album['type_media'] === 'youtube') {
                            // Youtube -> Modale (pas de redirection)
                            $onclick = "openVideoModal('" . htmlspecialchars($album['lien_externe']) . "'); return false;";
                        } elseif ($album['type_media'] === 'drive') {
                            // Drive -> Nouvel onglet
                            $href = htmlspecialchars($album['lien_externe']);
                            $target = "_blank";
                        }
                    ?>
                    
                    <a href="<?= $href ?>" target="<?= $target ?>" onclick="<?= $onclick ?>" 
                       class="album-card group bg-white rounded-2xl shadow-lg overflow-hidden block transition hover:-translate-y-1 hover:shadow-xl relative">
                        
                        <div class="h-64 relative overflow-hidden bg-gray-100">
                            <img loading="lazy" src="<?= htmlspecialchars($cover) ?>" alt="Cover" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                            
                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 transition duration-300 overlay-icon backdrop-blur-[2px]">
                                <?php if($album['type_media'] === 'youtube'): ?>
                                    <i class="fas fa-play-circle text-white text-6xl drop-shadow-lg"></i>
                                <?php elseif($album['type_media'] === 'drive'): ?>
                                    <i class="fab fa-google-drive text-white text-6xl drop-shadow-lg"></i>
                                <?php else: ?>
                                    <i class="fas fa-images text-white text-6xl drop-shadow-lg"></i>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="font-display text-2xl text-gray-800 mb-2 truncate group-hover:text-red-600 transition"><?= htmlspecialchars($album['titre']) ?></h3>
                            <p class="text-gray-500 text-sm line-clamp-2 h-10 mb-4"><?= htmlspecialchars($album['description']) ?></p>
                            
                            <div class="border-t pt-4 flex justify-between items-center">
                                <span class="text-xs text-gray-400 uppercase tracking-wide">
                                    <?= date('d M Y', strtotime($album['created_at'])) ?>
                                </span>
                                <span class="text-sm font-bold text-red-600 flex items-center gap-1">
                                    Voir <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-xl border-2 border-dashed border-gray-200">
                    <i class="far fa-images text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-xl">Aucun album disponible pour le moment.</p>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <?php if(count($album_photos) > 0): ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach($album_photos as $index => $photo): ?>
                        <div class="aspect-square rounded-xl overflow-hidden shadow-md hover:shadow-xl transition cursor-zoom-in group bg-gray-100"
                             onclick="openLightbox(<?= $index ?>)">
                            <img loading="lazy" 
                                 src="<?= htmlspecialchars($photo['nom_fichier']) ?>" 
                                 class="w-full h-full object-cover transform group-hover:scale-105 transition duration-500">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-20">
                    <p class="text-gray-500 text-xl">Cet album est vide pour le moment.</p>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <?php include 'footer.php'; ?>

    <div id="video-modal" class="video-modal fixed inset-0 bg-black/90 z-[3000] hidden items-center justify-center">
        <div class="relative w-[90%] max-w-4xl aspect-video bg-black rounded-xl overflow-hidden shadow-2xl">
            <button class="absolute -top-12 right-0 text-white text-4xl hover:text-red-500 transition focus:outline-none" onclick="closeVideoModal()">&times;</button>
            <iframe id="video-iframe" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

    <?php if($view_mode === 'detail'): ?>
    <div id="lightbox" class="fixed inset-0 bg-black/95 z-[2000] hidden flex items-center justify-center select-none">
        
        <button class="absolute top-4 right-6 text-white text-5xl hover:text-gray-300 transition z-[2020]" onclick="closeLightbox()">&times;</button>
        
        <button class="lightbox-nav-btn prev-btn" onclick="changeImage(-1)"><i class="fas fa-chevron-left text-2xl"></i></button>
        <button class="lightbox-nav-btn next-btn" onclick="changeImage(1)"><i class="fas fa-chevron-right text-2xl"></i></button>

        <img id="lightbox-img" src="" class="rounded shadow-2xl max-h-[90vh] max-w-[90vw] object-contain transition-transform duration-300">

        <a id="download-btn" href="" download class="absolute bottom-6 right-6 bg-white/10 hover:bg-white/30 text-white px-4 py-2 rounded-full backdrop-blur-md transition flex items-center gap-2 border border-white/20">
            <i class="fas fa-download"></i> <span class="text-sm font-bold">Télécharger</span>
        </a>
    </div>

    <script>
        // Passage des données PHP vers JS pour la navigation
        const photos = <?= json_encode($album_photos) ?>;
        let currentIndex = 0;

        function openLightbox(index) {
            currentIndex = index;
            updateLightbox();
            document.getElementById('lightbox').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            currentIndex += direction;
            // Boucle infini
            if (currentIndex >= photos.length) currentIndex = 0;
            if (currentIndex < 0) currentIndex = photos.length - 1;
            updateLightbox();
        }

        function updateLightbox() {
            const photo = photos[currentIndex];
            const img = document.getElementById('lightbox-img');
            const dlBtn = document.getElementById('download-btn');
            
            // Petit effet de fade
            img.style.opacity = '0.5';
            
            setTimeout(() => {
                img.src = photo.nom_fichier;
                dlBtn.href = photo.nom_fichier;
                img.style.opacity = '1';
            }, 150);
        }

        // Navigation Clavier
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').classList.contains('hidden')) return;
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
    <?php endif; ?>

    <script>
        // --- LOGIQUE VIDEO YOUTUBE ---
        function openVideoModal(url) {
            let embedUrl = url;
            // Conversion URL Youtube standard -> Embed
            if (url.includes('youtu.be/')) embedUrl = 'https://www.youtube.com/embed/' + url.split('youtu.be/')[1].split('?')[0];
            else if (url.includes('youtube.com/watch?v=')) embedUrl = 'https://www.youtube.com/embed/' + url.split('v=')[1].split('&')[0];
            
            embedUrl += "?autoplay=1";
            
            const modal = document.getElementById('video-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('video-iframe').src = embedUrl;
        }

        function closeVideoModal() {
            const modal = document.getElementById('video-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('video-iframe').src = '';
        }

        // Fermeture au clic dehors
        document.getElementById('video-modal').addEventListener('click', function(e) {
            if(e.target === this) closeVideoModal();
        });
    </script>
</body>
</html>