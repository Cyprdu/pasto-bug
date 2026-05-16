<?php
session_start();
require_once 'db_connect.php';

// Sécurité : Vérification de la connexion admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// --- LOGIQUE DE SUPPRESSION (Soft Delete) ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    // On marque l'album comme supprimé
    $stmt = $pdo->prepare("UPDATE albums SET supprime = 1 WHERE id = ?");
    if ($stmt->execute([$id])) {
        // Optionnel : Si c'était un upload physique, on pourrait supprimer les fichiers ici, 
        // mais pour un soft delete, on garde généralement les fichiers au cas où.
        $success_msg = "L'album a été supprimé avec succès.";
    } else {
        $error_msg = "Erreur lors de la suppression.";
    }
}

// --- RÉCUPÉRATION DES ALBUMS ---
$stmt = $pdo->query("SELECT * FROM albums WHERE supprime = 0 ORDER BY created_at DESC");
$albums = $stmt->fetchAll();

// Fonction helper pour les badges de type
function getBadgeColor($type) {
    switch($type) {
        case 'youtube': return 'bg-red-100 text-red-800 border-red-200';
        case 'drive': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'upload': return 'bg-green-100 text-green-800 border-green-200';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getTypeLabel($type) {
    switch($type) {
        case 'youtube': return '<i class="fab fa-youtube mr-1"></i> Vidéo YouTube';
        case 'drive': return '<i class="fab fa-google-drive mr-1"></i> Google Drive';
        case 'upload': return '<i class="fas fa-images mr-1"></i> Galerie Photos';
        default: return $type;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Albums - Admin PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <nav class="bg-white shadow-sm border-b px-6 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="dashboard_admin.php" class="text-gray-500 hover:text-red-600 transition">
                    <i class="fas fa-arrow-left"></i> Retour Dashboard
                </a>
                <h1 class="font-display text-2xl text-red-600 border-l pl-4 border-gray-300">Gestion des Albums</h1>
            </div>
            <a href="add_album_admin.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition shadow-md flex items-center gap-2">
                <i class="fas fa-plus"></i> Ajouter un album
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <?php if(isset($success_msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex justify-between items-center">
                <p><i class="fas fa-check-circle mr-2"></i><?= $success_msg ?></p>
                <button onclick="this.parentElement.style.display='none'" class="text-green-700 hover:text-green-900">&times;</button>
            </div>
        <?php endif; ?>

        <?php if(count($albums) > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($albums as $album): ?>
                    <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-100 hover:shadow-lg transition group">
                        
                        <div class="h-48 bg-gray-200 relative overflow-hidden">
                            <?php 
                                $imgSrc = !empty($album['image_illustration']) ? htmlspecialchars($album['image_illustration']) : 'https://placehold.co/600x400?text=Pas+d+image';
                            ?>
                            <img src="<?= $imgSrc ?>" alt="Cover" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            
                            <div class="absolute top-3 right-3">
                                <span class="<?= getBadgeColor($album['type_media']) ?> px-3 py-1 rounded-full text-xs font-bold border shadow-sm">
                                    <?= getTypeLabel($album['type_media']) ?>
                                </span>
                            </div>
                        </div>

                        <div class="p-5">
                            <h3 class="font-display text-xl text-gray-800 mb-2 truncate"><?= htmlspecialchars($album['titre']) ?></h3>
                            <p class="text-gray-500 text-sm line-clamp-2 h-10 mb-4">
                                <?= !empty($album['description']) ? htmlspecialchars($album['description']) : 'Pas de description.' ?>
                            </p>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <span class="text-xs text-gray-400">
                                    Ajouté le <?= date('d/m/Y', strtotime($album['created_at'])) ?>
                                </span>
                                
                                <div class="flex gap-3">
                                    <a href="edit_albums_admin.php?id=<?= $album['id'] ?>" class="text-gray-400 hover:text-indigo-600 transition" title="Modifier">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <a href="gestion_albums_admin.php?delete_id=<?= $album['id'] ?>" 
                                       onclick="return confirm('Supprimer cet album ?');" 
                                       class="text-gray-400 hover:text-red-600 transition" title="Supprimer">
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="text-gray-200 mb-4">
                    <i class="fas fa-images text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Aucun album photo</h3>
                <p class="mt-1 text-sm text-gray-500">Partagez les souvenirs des camps et événements.</p>
                <div class="mt-6">
                    <a href="add_album_admin.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-full text-white bg-red-600 hover:bg-red-700 focus:outline-none shadow-lg transition">
                        <i class="fas fa-plus mr-2"></i> Ajouter un premier album
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>