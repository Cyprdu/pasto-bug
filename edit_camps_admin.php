<?php
session_start();
require_once 'db_connect.php';

// Sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// Vérifier l'ID dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gestion_camps_admin.php");
    exit;
}

$id = intval($_GET['id']);
$error = '';
$success = '';

// --- RÉCUPÉRATION DES DONNÉES ACTUELLES ---

// 1. Infos du camp
$stmt = $pdo->prepare("SELECT * FROM camps WHERE id = ? AND supprime = 0");
$stmt->execute([$id]);
$camp = $stmt->fetch();

if (!$camp) {
    die("Camp introuvable.");
}

// 2. Catégories assignées (pour cocher les cases)
$stmt_cats = $pdo->prepare("SELECT category_id FROM camp_category WHERE camp_id = ?");
$stmt_cats->execute([$id]);
$current_categories = $stmt_cats->fetchAll(PDO::FETCH_COLUMN); // Retourne un tableau simple ex: [1, 3]

// 3. Toutes les catégories disponibles (pour afficher la liste)
$all_cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC")->fetchAll();

// 4. Paiements (JSON -> Array)
$current_paiements = json_decode($camp['moyens_paiement'], true);
if (!is_array($current_paiements)) $current_paiements = [];


// --- TRAITEMENT DE LA MISE À JOUR ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupération des champs
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $lien_inscription = trim($_POST['lien_inscription']);
    $lien_teaser = trim($_POST['lien_teaser']);
    $age_min = intval($_POST['age_min']);
    $age_max = intval($_POST['age_max']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $adresse = trim($_POST['adresse']);
    $pays = trim($_POST['pays']);
    $itinerant = isset($_POST['itinerant']) ? 1 : 0;
    $moyens_paiement = isset($_POST['paiements']) ? json_encode($_POST['paiements']) : json_encode([]);

    // Gestion de l'image
    $image_path = $camp['image_couverture']; // Par défaut, on garde l'ancienne
    
    // Si une nouvelle image est envoyée
    if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'heic'];
        $filename = $_FILES['image_couverture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_dir = 'uploads/';
            
            if (move_uploaded_file($_FILES['image_couverture']['tmp_name'], $upload_dir . $new_filename)) {
                $image_path = $upload_dir . $new_filename;
                // Optionnel : Supprimer l'ancienne image du serveur pour gagner de la place
                // if(file_exists($camp['image_couverture'])) unlink($camp['image_couverture']);
            }
        } else {
            $error = "Format d'image non valide.";
        }
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            // 1. Update Camp
            $sql = "UPDATE camps SET 
                    titre=?, description=?, prix=?, lien_inscription=?, lien_teaser=?, 
                    age_min=?, age_max=?, date_debut=?, date_fin=?, itinerant=?, 
                    adresse=?, pays=?, moyens_paiement=?, image_couverture=? 
                    WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $titre, $description, $prix, $lien_inscription, $lien_teaser, 
                $age_min, $age_max, $date_debut, $date_fin, $itinerant, 
                $adresse, $pays, $moyens_paiement, $image_path, $id
            ]);

            // 2. Update Catégories (Méthode radicale : On supprime tout et on recrée)
            $pdo->prepare("DELETE FROM camp_category WHERE camp_id = ?")->execute([$id]);

            if (isset($_POST['categories']) && is_array($_POST['categories'])) {
                $sql_cat = "INSERT INTO camp_category (camp_id, category_id) VALUES (?, ?)";
                $stmt_cat = $pdo->prepare($sql_cat);
                foreach ($_POST['categories'] as $cat_id) {
                    $stmt_cat->execute([$id, $cat_id]);
                }
            }

            $pdo->commit();
            // Rafraîchir les données pour l'affichage
            header("Location: gestion_camps_admin.php?success_edit=1");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur de mise à jour : " . $e->getMessage();
        }
    }
}

// Fonction helper pour formater la date pour l'input datetime-local (YYYY-MM-DDTHH:MM)
function formatForInput($dateStr) {
    return date('Y-m-d\TH:i', strtotime($dateStr));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Camp - Admin PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 pb-10">

    <nav class="bg-white shadow-sm border-b px-6 py-4 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center gap-4">
            <a href="gestion_camps_admin.php" class="text-gray-500 hover:text-red-600 transition">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
            <h1 class="font-display text-2xl text-red-600 border-l pl-4 border-gray-300">Modifier : <?= htmlspecialchars($camp['titre']) ?></h1>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-10">
        
        <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><?= $error ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 space-y-8">
            
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Informations Principales</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Titre de l'événement *</label>
                        <input type="text" name="titre" value="<?= htmlspecialchars($camp['titre']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="4" required class="w-full px-4 py-2 border rounded-lg focus:ring-red-500 focus:border-red-500"><?= htmlspecialchars($camp['description']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de début *</label>
                        <input type="datetime-local" name="date_debut" value="<?= formatForInput($camp['date_debut']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-red-500 focus:border-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date de fin *</label>
                        <input type="datetime-local" name="date_fin" value="<?= formatForInput($camp['date_fin']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-red-500 focus:border-red-500">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Public & Catégories</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex gap-4">
                        <div class="w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Âge Min</label>
                            <input type="number" name="age_min" value="<?= $camp['age_min'] ?>" min="6" max="99" required class="w-full px-4 py-2 border rounded-lg">
                        </div>
                        <div class="w-1/2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Âge Max</label>
                            <input type="number" name="age_max" value="<?= $camp['age_max'] ?>" min="6" max="99" required class="w-full px-4 py-2 border rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catégories concernées</label>
                        <div class="space-y-2 bg-gray-50 p-3 rounded-lg border">
                            <?php foreach($all_cats as $cat): ?>
                            <label class="inline-flex items-center mr-4 cursor-pointer">
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" 
                                       <?php if(in_array($cat['id'], $current_categories)) echo 'checked'; ?>
                                       class="form-checkbox text-red-600 h-5 w-5 rounded">
                                <span class="ml-2 text-gray-700"><?= htmlspecialchars($cat['nom']) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Logistique</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Adresse / Lieu *</label>
                        <input type="text" name="adresse" value="<?= htmlspecialchars($camp['adresse']) ?>" required class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays</label>
                        <input type="text" name="pays" value="<?= htmlspecialchars($camp['pays']) ?>" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div class="flex items-center mt-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="itinerant" <?php if($camp['itinerant']) echo 'checked'; ?> class="form-checkbox text-red-600 h-5 w-5 rounded">
                            <span class="ml-2 text-gray-700 font-medium">Camp Itinérant</span>
                        </label>
                    </div>
                    
                    <div class="border-t col-span-2 my-2"></div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                        <input type="number" step="0.01" name="prix" value="<?= $camp['prix'] ?>" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Moyens de paiement acceptés</label>
                        <div class="flex flex-wrap gap-4">
                            <?php 
                            $options = ["CB", "Chèques", "Espèces", "Chèques Vacances"];
                            foreach($options as $opt): 
                            ?>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="paiements[]" value="<?= $opt ?>" 
                                       <?php if(in_array($opt, $current_paiements)) echo 'checked'; ?>
                                       class="text-red-600 rounded mr-2"> <?= $opt ?>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Liens & Images</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lien Inscription</label>
                        <input type="url" name="lien_inscription" value="<?= htmlspecialchars($camp['lien_inscription']) ?>" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lien Teaser</label>
                        <input type="url" name="lien_teaser" value="<?= htmlspecialchars($camp['lien_teaser']) ?>" class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture</label>
                        
                        <?php if(!empty($camp['image_couverture'])): ?>
                            <div class="mb-3">
                                <p class="text-xs text-gray-500">Image actuelle :</p>
                                <img src="<?= htmlspecialchars($camp['image_couverture']) ?>" class="h-32 rounded shadow object-cover border">
                            </div>
                        <?php endif; ?>

                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition cursor-pointer relative">
                            <input type="file" name="image_couverture" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(this)">
                            <div id="preview-container">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">Cliquez pour modifier l'image</p>
                            </div>
                            <img id="preview-img" class="hidden mt-4 mx-auto max-h-48 rounded shadow" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-6 border-t">
                <button type="submit" class="bg-red-600 text-white font-display text-xl px-8 py-3 rounded-full hover:bg-red-700 transition shadow-lg flex items-center gap-2">
                    <i class="fas fa-save"></i> Mettre à jour
                </button>
            </div>

        </form>
    </div>

    <script>
        function previewImage(input) {
            const container = document.getElementById('preview-container');
            const img = document.getElementById('preview-img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    // container.classList.add('hidden'); // On laisse le texte pour dire qu'on peut encore changer
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

</body>
</html>