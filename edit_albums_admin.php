<?php
session_start();
require_once 'db_connect.php';

// Sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gestion_albums_admin.php");
    exit;
}

$id = intval($_GET['id']);
$error = '';
$success = '';

// --- 1. SUPPRESSION D'UNE PHOTO UNIQUE (Action via lien) ---
if (isset($_GET['delete_photo_id'])) {
    $photo_id = intval($_GET['delete_photo_id']);
    
    // Récupérer le chemin pour supprimer le fichier
    $stmt = $pdo->prepare("SELECT nom_fichier FROM photos WHERE id = ? AND album_id = ?");
    $stmt->execute([$photo_id, $id]);
    $photoToDelete = $stmt->fetch();

    if ($photoToDelete) {
        // Supprimer le fichier physique
        if (file_exists($photoToDelete['nom_fichier'])) {
            unlink($photoToDelete['nom_fichier']);
        }
        // Supprimer de la BDD
        $pdo->prepare("DELETE FROM photos WHERE id = ?")->execute([$photo_id]);
        $success = "Photo supprimée avec succès.";
    }
}

// --- 2. TRAITEMENT DU FORMULAIRE DE MISE À JOUR ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $lien_externe = isset($_POST['lien_externe']) ? trim($_POST['lien_externe']) : '';
    
    // Mise à jour image couverture
    $stmt_curr = $pdo->prepare("SELECT image_illustration FROM albums WHERE id = ?");
    $stmt_curr->execute([$id]);
    $current_album = $stmt_curr->fetch();
    $cover_path = $current_album['image_illustration'];

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'cover_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], 'uploads/covers/' . $filename)) {
                $cover_path = 'uploads/covers/' . $filename;
            }
        }
    }

    // Mise à jour infos générales
    $sql = "UPDATE albums SET titre = ?, description = ?, lien_externe = ?, image_illustration = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titre, $description, $lien_externe, $cover_path, $id]);

    // Ajout de NOUVELLES photos (Si type upload)
    if (isset($_FILES['new_photos'])) {
        $total_files = count($_FILES['new_photos']['name']);
        
        // On récupère la dernière position pour ajouter à la suite
        $stmt_pos = $pdo->prepare("SELECT MAX(position) FROM photos WHERE album_id = ?");
        $stmt_pos->execute([$id]);
        $max_pos = $stmt_pos->fetchColumn() ?: 0;

        $sql_photo = "INSERT INTO photos (album_id, nom_fichier, position) VALUES (?, ?, ?)";
        $stmt_photo = $pdo->prepare($sql_photo);

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['new_photos']['error'][$i] === 0) {
                $ext = strtolower(pathinfo($_FILES['new_photos']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $photo_name = $id . '_' . time() . '_' . $i . '.' . $ext;
                    $destination = 'uploads/gallery/' . $photo_name;

                    if (move_uploaded_file($_FILES['new_photos']['tmp_name'][$i], $destination)) {
                        $max_pos++;
                        $stmt_photo->execute([$id, $destination, $max_pos]);
                    }
                }
            }
        }
    }

    $success = "Album mis à jour avec succès.";
}

// --- 3. RÉCUPÉRATION DES DONNÉES ---
$stmt = $pdo->prepare("SELECT * FROM albums WHERE id = ?");
$stmt->execute([$id]);
$album = $stmt->fetch();

if (!$album) die("Album introuvable");

// Si c'est une galerie, on récupère les photos
$gallery_photos = [];
if ($album['type_media'] === 'upload') {
    $stmt_p = $pdo->prepare("SELECT * FROM photos WHERE album_id = ? ORDER BY position ASC");
    $stmt_p->execute([$id]);
    $gallery_photos = $stmt_p->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Album - Admin PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        
        .photo-overlay { background: rgba(0,0,0,0.5); opacity: 0; transition: opacity 0.2s; }
        .photo-container:hover .photo-overlay { opacity: 1; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 pb-10">

    <nav class="bg-white shadow-sm border-b px-6 py-4 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center gap-4">
            <a href="gestion_albums_admin.php" class="text-gray-500 hover:text-red-600 transition">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <h1 class="font-display text-2xl text-red-600 border-l pl-4 border-gray-300">Modifier : <?= htmlspecialchars($album['titre']) ?></h1>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-10">
        
        <?php if($success): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded"><?= $success ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 space-y-8">
            
            <div class="bg-gray-50 p-4 rounded-lg border flex items-center gap-3">
                <div class="font-bold text-gray-700">Type d'album :</div>
                <span class="px-3 py-1 rounded-full bg-gray-200 text-gray-700 font-bold text-sm uppercase">
                    <?= $album['type_media'] ?>
                </span>
                <span class="text-xs text-gray-500">(Non modifiable)</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($album['titre']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-red-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-red-500"><?= htmlspecialchars($album['description']) ?></textarea>
                </div>
            </div>

            <?php if($album['type_media'] !== 'upload'): ?>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Lien externe</label>
                <input type="url" name="lien_externe" value="<?= htmlspecialchars($album['lien_externe']) ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-red-500">
            </div>
            <?php endif; ?>

            <?php if($album['type_media'] === 'upload'): ?>
            <div class="border-t pt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Gestion des photos</h3>
                
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4 mb-6">
                    <?php foreach($gallery_photos as $photo): ?>
                    <div class="relative aspect-square rounded-lg overflow-hidden border photo-container">
                        <img src="<?= $photo['nom_fichier'] ?>" class="w-full h-full object-cover">
                        <div class="photo-overlay absolute inset-0 flex items-center justify-center">
                            <a href="edit_albums_admin.php?id=<?= $id ?>&delete_photo_id=<?= $photo['id'] ?>" 
                               onclick="return confirm('Supprimer cette photo ?')"
                               class="bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition" title="Supprimer">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <label class="block text-sm font-bold text-gray-700 mb-2">Ajouter de nouvelles photos</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition relative">
                    <input type="file" name="new_photos[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    <i class="fas fa-plus-circle text-3xl text-gray-400 mb-2"></i>
                    <p class="text-sm text-gray-500">Cliquez ou glissez pour ajouter</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="border-t pt-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture</label>
                <div class="flex items-start gap-6">
                    <div class="w-32 h-32 rounded-lg border overflow-hidden flex-shrink-0 bg-gray-100">
                        <?php if(!empty($album['image_illustration'])): ?>
                            <img src="<?= $album['image_illustration'] ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="flex items-center justify-center h-full text-gray-400 text-xs">Aucune</div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <input type="file" name="cover_image" accept="image/*" class="w-full px-4 py-2 border rounded-lg">
                        <p class="text-xs text-gray-500 mt-1">Laissez vide pour conserver l'image actuelle.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-red-600 text-white font-display text-xl px-8 py-3 rounded-full hover:bg-red-700 transition shadow-lg flex items-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

</body>
</html>