<?php
/**
 * CONFIGURATION SESSION MOBILE & IFRAME
 * Doit être IDENTIQUE à admin_login.php pour que la session suive
 */
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

session_start();
require_once 'db_connect.php';

// Vérification de sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_login.php");
    exit;
}

// --- STATISTIQUES ---
// 1. Messages non lus
$stmt = $pdo->query("SELECT COUNT(*) FROM messages WHERE est_lu = 0");
$nb_msg_non_lus = $stmt->fetchColumn();

// 2. Camps futurs
$stmt = $pdo->query("SELECT COUNT(*) FROM camps WHERE date_debut >= CURDATE() AND supprime = 0");
$nb_camps_futurs = $stmt->fetchColumn();

// 3. Albums
$stmt = $pdo->query("SELECT COUNT(*) FROM albums WHERE supprime = 0");
$nb_albums = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PaJe Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="font-display text-3xl text-red-600">PaJe Dashboard</h1>
            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm hidden sm:inline-block">
                <?= htmlspecialchars($_SESSION['admin_username']) ?>
            </span>
        </div>
        <div class="flex gap-4">
            <a href="index.php" class="text-gray-500 hover:text-red-600 flex items-center gap-2 transition">
                <i class="fas fa-external-link-alt"></i> <span class="hidden sm:inline">Voir le site</span>
            </a>
            <a href="?logout=true" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-600 hover:text-white transition font-medium">
                <i class="fas fa-sign-out-alt sm:mr-2"></i><span class="hidden sm:inline">Déconnexion</span>
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Messages non lus</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?= $nb_msg_non_lus ?></h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xl">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Camps à venir</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?= $nb_camps_futurs ?></h3>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-xl">
                    <i class="fas fa-campground"></i>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Albums photos</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?= $nb_albums ?></h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 text-xl">
                    <i class="fas fa-images"></i>
                </div>
            </div>
        </div>

        <h2 class="font-display text-4xl text-gray-800 mb-8">Gestion du site</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <a href="gestion_camps_admin.php" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="h-2 bg-green-500 w-full"></div>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center text-green-600 text-2xl mx-auto mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-campground"></i>
                    </div>
                    <h3 class="font-display text-2xl text-gray-800 mb-2">Camps</h3>
                    <p class="text-gray-500 text-sm">Gérer les camps et retraites.</p>
                </div>
            </a>

            <a href="gestion_albums_admin.php" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="h-2 bg-purple-500 w-full"></div>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-purple-50 rounded-full flex items-center justify-center text-purple-600 text-2xl mx-auto mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 class="font-display text-2xl text-gray-800 mb-2">Albums</h3>
                    <p class="text-gray-500 text-sm">Gérer les galeries photos.</p>
                </div>
            </a>

            <a href="gestion_message_admin.php" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="h-2 bg-blue-500 w-full"></div>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center text-blue-600 text-2xl mx-auto mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h3 class="font-display text-2xl text-gray-800 mb-2">Messagerie</h3>
                    <?php if($nb_msg_non_lus > 0): ?>
                        <span class="inline-block bg-red-100 text-red-600 text-xs font-bold px-2 py-1 rounded-full mb-2">
                            <?= $nb_msg_non_lus ?> nouveau(x)
                        </span>
                    <?php endif; ?>
                    <p class="text-gray-500 text-sm">Lire les messages reçus.</p>
                </div>
            </a>

            <a href="gestion_espace_animateurs.php" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="h-2 bg-teal-500 w-full"></div>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-teal-50 rounded-full flex items-center justify-center text-teal-600 text-2xl mx-auto mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3 class="font-display text-2xl text-gray-800 mb-2">Animateurs</h3>
                    <p class="text-gray-500 text-sm">Gérer les comptes et ressources.</p>
                </div>
            </a>

            <a href="admin_mot_de_passe.php" class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer border border-gray-100">
                <div class="h-2 bg-orange-500 w-full"></div>
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center text-orange-600 text-2xl mx-auto mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="font-display text-2xl text-gray-800 mb-2">Sécurité</h3>
                    <p class="text-gray-500 text-sm">Gérer les administrateurs.</p>
                </div>
            </a>

        </div>
    </div>
</body>
</html>