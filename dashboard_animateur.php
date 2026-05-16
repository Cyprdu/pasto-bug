<?php
session_start();
require_once 'db_connect.php';

// 1. SÉCURITÉ
if (!isset($_SESSION['anim_logged_in']) || $_SESSION['anim_logged_in'] !== true) {
    header("Location: login_animateur.php");
    exit;
}

// Redirection si changement de mot de passe requis
if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password'] == 1) {
    header("Location: force_password.php");
    exit;
}

$anim_id = $_SESSION['anim_id'];
$anim_nom = $_SESSION['anim_nom'];
$msg_success = "";

// 2. TRAITEMENTS POST (Intentions & Favoris)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['ajouter_intention'])) {
        $intention = htmlspecialchars($_POST['intention']);
        $stmt = $pdo->prepare("INSERT INTO intentions_priere (animateur_id, intention) VALUES (?, ?)");
        $stmt->execute([$anim_id, $intention]);
        $msg_success = "Ton intention a été déposée avec succès !";
    }
    
    if (isset($_POST['toggle_fav'])) {
        $res_id = (int)$_POST['res_id'];
        $check = $pdo->prepare("SELECT id FROM favoris_animateurs WHERE animateur_id = ? AND ressource_id = ?");
        $check->execute([$anim_id, $res_id]);
        if ($check->fetch()) {
            $pdo->prepare("DELETE FROM favoris_animateurs WHERE animateur_id = ? AND ressource_id = ?")->execute([$anim_id, $res_id]);
        } else {
            $pdo->prepare("INSERT INTO favoris_animateurs (animateur_id, ressource_id) VALUES (?, ?)")->execute([$anim_id, $res_id]);
        }
        header("Location: dashboard_animateur.php"); exit;
    }
}

// 3. RÉCUPÉRATION DES DONNÉES
// Lectures AELF
$date_jour = date('Y-m-d');
$aelf_url = "https://api.aelf.org/v1/messes/$date_jour/france";
$lectures = [];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $aelf_url); curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); curl_setopt($ch, CURLOPT_USERAGENT, 'PaJe/1.0');
$response = curl_exec($ch); curl_close($ch);
if ($response) {
    $data = json_decode($response, true);
    if (isset($data['messes'][0]['lectures'])) $lectures = $data['messes'][0]['lectures'];
}

// Topos (Catalogue)
$stmt = $pdo->prepare("SELECT r.*, f.id as is_fav FROM ressources_animateurs r 
                       LEFT JOIN favoris_animateurs f ON r.id = f.ressource_id AND f.animateur_id = ? 
                       ORDER BY r.id DESC");
$stmt->execute([$anim_id]);
$ressources = $stmt->fetchAll();

// Camps
$camps = $pdo->query("SELECT * FROM camps WHERE date_debut >= NOW() AND supprime = 0 ORDER BY date_debut ASC")->fetchAll();
$stmt_mes_camps = $pdo->prepare("SELECT camp_id FROM camp_animateur WHERE animateur_id = ?");
$stmt_mes_camps->execute([$anim_id]);
$mes_camps_ids = $stmt_mes_camps->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Animateur - PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 pb-24 lg:pb-10">

    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md border-b border-slate-200 px-4 md:px-6 py-3 md:py-4 flex justify-between items-center shadow-sm">
        <h1 class="font-display text-2xl md:text-3xl text-teal-600 flex items-center gap-2">
            <i class="fas fa-campground"></i> <span class="hidden sm:inline">Espace Animateur</span>
        </h1>
        
        <div class="flex items-center gap-2 md:gap-4">
            <a href="index.php" class="hidden md:flex items-center gap-2 text-sm font-bold text-slate-600 hover:text-teal-600 hover:bg-teal-50 transition px-4 py-2 rounded-full">
                <i class="fas fa-home"></i> Site Public
            </a>
            
            <span class="hidden lg:inline text-sm font-bold text-slate-400 bg-slate-100 px-4 py-2 rounded-full border border-slate-200">
                <?= explode(' ', $anim_nom)[0] ?>
            </span>
            
            <a href="login_animateur.php?logout=true" class="flex items-center gap-2 bg-red-50 text-red-500 border border-red-100 hover:bg-red-500 hover:text-white px-4 py-2 md:py-2 rounded-full transition text-sm font-bold shadow-sm group">
                <i class="fas fa-power-off group-hover:scale-110 transition"></i> 
                <span class="hidden sm:inline">Déconnexion</span>
            </a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-4 md:p-8">

        <?php if($msg_success): ?>
            <div class="bg-teal-100 border-l-4 border-teal-500 text-teal-700 p-4 mb-6 rounded-r-lg shadow-sm flex items-center gap-3">
                <i class="fas fa-check-circle text-xl"></i> <?= $msg_success ?>
            </div>
        <?php endif; ?>

        <section class="flex gap-4 overflow-x-auto hide-scrollbar mb-10 pb-2">
            <a href="#catalogue" class="flex-shrink-0 bg-teal-600 text-white px-6 py-4 rounded-3xl shadow-lg shadow-teal-100 flex items-center gap-3 hover:-translate-y-1 transition transform">
                <i class="fas fa-book-open"></i> <span class="font-bold">Catalogue Formations</span>
            </a>
            <a href="#camps" class="flex-shrink-0 bg-white border border-slate-200 px-6 py-4 rounded-3xl flex items-center gap-3 hover:bg-slate-50 hover:-translate-y-1 transition transform shadow-sm">
                <i class="fas fa-flag text-red-500"></i> <span class="font-bold">Prochains Camps</span>
            </a>
            <a href="#intentions" class="flex-shrink-0 bg-white border border-slate-200 px-6 py-4 rounded-3xl flex items-center gap-3 hover:bg-slate-50 hover:-translate-y-1 transition transform shadow-sm">
                <i class="fas fa-hands-praying text-orange-500"></i> <span class="font-bold">Boîte à Intentions</span>
            </a>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-12">
                
                <div id="catalogue">
                    <div class="flex justify-between items-center mb-6 px-2">
                        <h2 class="font-display text-4xl text-slate-800">Derniers Topos</h2>
                        <span class="text-xs font-bold bg-slate-200 text-slate-600 px-3 py-1 rounded-full"><?= count($ressources) ?> disponibles</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <?php foreach($ressources as $res): ?>
                        <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm group hover:shadow-xl transition-all duration-300 relative">
                            
                            <form method="POST" action="" class="absolute top-4 right-4 z-10">
                                <input type="hidden" name="res_id" value="<?= $res['id'] ?>">
                                <button type="submit" name="toggle_fav" class="w-10 h-10 rounded-full bg-white/90 backdrop-blur shadow-md flex items-center justify-center hover:scale-110 transition <?= $res['is_fav'] ? 'text-red-500' : 'text-slate-300 hover:text-red-400' ?>">
                                    <i class="<?= $res['is_fav'] ? 'fas' : 'far' ?> fa-heart"></i>
                                </button>
                            </form>

                            <div class="h-48 overflow-hidden relative bg-slate-100">
                                <img src="<?= $res['cover'] ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700" alt="">
                                <div class="absolute bottom-3 left-4">
                                    <span class="bg-black/60 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1.5 rounded-full uppercase tracking-wider flex items-center gap-1.5 shadow-lg">
                                        <?php 
                                            if($res['type_ressource'] == 'youtube') echo '<i class="fab fa-youtube text-red-400"></i> Vidéo';
                                            elseif($res['type_ressource'] == 'lien_audio' || $res['type_ressource'] == 'fichier_audio') echo '<i class="fas fa-headphones text-teal-400"></i> Audio';
                                            else echo '<i class="fas fa-file-alt text-blue-400"></i> Texte';
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="p-6 md:p-8 flex flex-col justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-slate-800 mb-2 leading-tight"><?= htmlspecialchars($res['titre']) ?></h3>
                                    <p class="text-xs text-teal-600 font-bold uppercase tracking-wider mb-4"><i class="fas fa-pen-nib mr-1"></i> <?= htmlspecialchars($res['auteur']) ?></p>
                                    <p class="text-slate-500 text-sm line-clamp-2 mb-6"><?= htmlspecialchars($res['description']) ?></p>
                                </div>
                                <a href="voir_topo.php?id=<?= $res['id'] ?>" class="block w-full text-center bg-slate-900 text-white font-bold py-3 md:py-4 rounded-2xl hover:bg-teal-600 transition shadow-md hover:shadow-lg">
                                    Consulter le topo
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($ressources)): ?>
                            <p class="text-slate-400 italic p-4">Aucun topo n'a été publié pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="camps" class="bg-white rounded-[2.5rem] p-6 md:p-8 border border-slate-100 shadow-sm">
                    <h2 class="font-display text-4xl text-slate-800 mb-6 flex items-center gap-3">
                        <i class="fas fa-map-marked-alt text-teal-600"></i> Mes Missions
                    </h2>
                    <div class="space-y-4">
                        <?php foreach($camps as $camp): ?>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-slate-50 rounded-3xl border border-slate-100 gap-4">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-white text-teal-600 rounded-2xl flex items-center justify-center text-2xl shadow-sm border border-slate-100 flex-shrink-0">
                                    <i class="fas fa-fire"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($camp['titre']) ?></h4>
                                    <p class="text-[11px] text-slate-500 font-bold uppercase tracking-wider mt-1"><i class="far fa-calendar-alt mr-1"></i> Du <?= date('d/m/Y', strtotime($camp['date_debut'])) ?> au <?= date('d/m/Y', strtotime($camp['date_fin'])) ?></p>
                                </div>
                            </div>
                            
                            <?php if(in_array($camp['id'], $mes_camps_ids)): ?>
                                <span class="bg-teal-100 text-teal-700 text-xs font-bold px-4 py-2 rounded-xl uppercase flex items-center justify-center gap-2">
                                    <i class="fas fa-check-circle"></i> Je suis animateur
                                </span>
                            <?php else: ?>
                                <a href="camp_detail.php?id=<?= $camp['id'] ?>" class="bg-white border-2 border-slate-200 text-slate-600 font-bold text-xs px-4 py-2 rounded-xl hover:border-teal-500 hover:text-teal-600 transition flex items-center justify-center gap-2 whitespace-nowrap">
                                    Voir les infos <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if(empty($camps)): ?>
                            <p class="text-slate-400 italic">Aucun camp n'est prévu pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                
                <div id="intentions" class="bg-gradient-to-br from-orange-50 to-orange-100/50 rounded-[2.5rem] p-8 border border-orange-200 shadow-sm relative overflow-hidden">
                    <i class="fas fa-dove absolute -top-4 -right-4 text-8xl text-orange-500/10 rotate-12"></i>
                    
                    <h3 class="font-display text-3xl text-orange-800 mb-2 relative z-10">Prier ensemble</h3>
                    <p class="text-sm text-orange-700/80 mb-6 leading-relaxed relative z-10 font-medium">Une épreuve, une joie, un jeune qui t'a marqué ? Dépose ton intention ici.</p>
                    
                    <form method="POST" action="" class="relative z-10">
                        <textarea name="intention" required rows="4" class="w-full bg-white/80 backdrop-blur rounded-2xl border-orange-200 p-4 text-sm focus:ring-orange-500 focus:border-orange-500 transition shadow-inner placeholder-orange-300" placeholder="Seigneur, je te confie..."></textarea>
                        <button type="submit" name="ajouter_intention" class="w-full mt-4 bg-orange-500 text-white font-bold py-4 rounded-2xl shadow-lg shadow-orange-200 hover:bg-orange-600 hover:-translate-y-1 transition transform flex items-center justify-center gap-2">
                            <i class="fas fa-paper-plane"></i> Confier mon intention
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-sm">
                    <div class="bg-slate-900 p-6 text-white flex justify-between items-center">
                        <h3 class="font-display text-2xl flex items-center gap-3">
                            <i class="fas fa-bible text-teal-400"></i> Parole du Jour
                        </h3>
                        <span class="text-xs font-bold text-slate-400"><?= date('d/m') ?></span>
                    </div>
                    <div class="p-6 max-h-[500px] overflow-y-auto custom-scrollbar">
                        <?php if($lectures): ?>
                            <?php foreach($lectures as $l): ?>
                                <div class="mb-4 last:mb-0 bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
                                    <button type="button" onclick="document.getElementById('text-<?= md5($l['type']) ?>').classList.toggle('hidden'); this.querySelector('.fa-chevron-down').classList.toggle('rotate-180');" class="w-full text-left p-4 flex justify-between items-center hover:bg-slate-100 transition focus:outline-none">
                                        <div>
                                            <span class="text-[10px] font-bold text-teal-600 uppercase tracking-widest block mb-1"><?= $l['type'] ?></span>
                                            <h4 class="font-bold text-slate-800 text-sm leading-tight pr-4"><?= $l['titre'] ?></h4>
                                        </div>
                                        <i class="fas fa-chevron-down text-slate-400 transition-transform duration-300"></i>
                                    </button>
                                    
                                    <div id="text-<?= md5($l['type']) ?>" class="hidden p-5 pt-0 text-sm text-slate-600 italic leading-relaxed border-t border-slate-100 bg-white">
                                        <div class="pt-4"><?= $l['contenu'] ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-6">
                                <i class="fas fa-cloud-sun text-3xl text-slate-300 mb-3"></i>
                                <p class="text-sm text-slate-400 italic">Impossible de charger l'AELF aujourd'hui.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-xl border-t border-slate-200 flex justify-around p-2 z-50 shadow-[0_-10px_20px_rgba(0,0,0,0.05)] pb-safe">
        
        <a href="#catalogue" class="flex flex-col items-center justify-center text-teal-600 w-1/4 py-2 hover:bg-slate-50 rounded-2xl transition">
            <i class="fas fa-book-open text-xl mb-1"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Topos</span>
        </a>
        
        <a href="#camps" class="flex flex-col items-center justify-center text-slate-400 w-1/4 py-2 hover:text-teal-600 hover:bg-slate-50 rounded-2xl transition">
            <i class="fas fa-flag text-xl mb-1"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Camps</span>
        </a>
        
        <a href="#intentions" class="flex flex-col items-center justify-center text-slate-400 w-1/4 py-2 hover:text-orange-500 hover:bg-slate-50 rounded-2xl transition">
            <i class="fas fa-hands-praying text-xl mb-1"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Prière</span>
        </a>

        <a href="index.php" class="flex flex-col items-center justify-center text-slate-400 w-1/4 py-2 hover:text-slate-800 hover:bg-slate-50 rounded-2xl transition">
            <i class="fas fa-globe text-xl mb-1"></i>
            <span class="text-[9px] font-bold uppercase tracking-wider">Site</span>
        </a>
    </nav>

    <script>
        // Smooth scroll pour les ancres
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetEl = document.getElementById(targetId);
                if(targetEl) {
                    // On compense la hauteur du header fixe (env 80px)
                    window.scrollTo({
                        top: targetEl.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>