<?php
session_start();
require_once 'db_connect.php';

// Sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$error = '';
$success = '';

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $type_media = $_POST['type_media']; // 'youtube', 'drive', 'upload'
    $lien_externe = isset($_POST['lien_externe']) ? trim($_POST['lien_externe']) : '';
    
    // 1. Gestion de l'image de couverture (Commune à tous les types)
    $cover_path = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'heic'];
        $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $filename = 'cover_' . uniqid() . '.' . $ext;
            // Création du dossier si inexistant
            if (!is_dir('uploads/covers/')) mkdir('uploads/covers/', 0755, true);
            
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], 'uploads/covers/' . $filename)) {
                $cover_path = 'uploads/covers/' . $filename;
            }
        } else {
            $error = "Format de l'image de couverture non supporté.";
        }
    }

    if (empty($error)) {
        try {
            $pdo->beginTransaction();

            // 2. Création de l'album
            $stmt = $pdo->prepare("INSERT INTO albums (titre, description, type_media, lien_externe, image_illustration) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$titre, $description, $type_media, $lien_externe, $cover_path]);
            $album_id = $pdo->lastInsertId();

            // 3. Si Type = Upload, gestion des photos multiples
            if ($type_media === 'upload' && isset($_FILES['photos'])) {
                $total_files = count($_FILES['photos']['name']);
                
                // Création du dossier galerie
                if (!is_dir('uploads/gallery/')) mkdir('uploads/gallery/', 0755, true);

                $sql_photo = "INSERT INTO photos (album_id, nom_fichier, position) VALUES (?, ?, ?)";
                $stmt_photo = $pdo->prepare($sql_photo);

                for ($i = 0; $i < $total_files; $i++) {
                    if ($_FILES['photos']['error'][$i] === 0) {
                        $ext = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            // Nommage : IDALBUM_TIMESTAMP_INDEX.ext
                            $photo_name = $album_id . '_' . time() . '_' . $i . '.' . $ext;
                            $destination = 'uploads/gallery/' . $photo_name;

                            if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $destination)) {
                                // Insertion photo
                                $stmt_photo->execute([$album_id, $destination, $i]);
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            header("Location: gestion_albums_admin.php?success=1");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Album - Admin PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        
        /* Styles pour le drag & drop */
        .drag-active { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 pb-10">

    <nav class="bg-white shadow-sm border-b px-6 py-4 sticky top-0 z-50">
        <div class="max-w-4xl mx-auto flex items-center gap-4">
            <a href="gestion_albums_admin.php" class="text-gray-500 hover:text-red-600 transition">
                <i class="fas fa-arrow-left"></i> Annuler
            </a>
            <h1 class="font-display text-2xl text-red-600 border-l pl-4 border-gray-300">Nouveau Média / Album</h1>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-10">
        
        <?php if($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded"><?= $error ?></div>
        <?php endif; ?>

        <form id="albumForm" action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-lg border border-gray-100 p-8 space-y-8">
            
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">Type de média</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <label class="cursor-pointer">
                        <input type="radio" name="type_media" value="youtube" class="peer sr-only" onchange="toggleFields()" checked>
                        <div class="rounded-lg border-2 border-gray-200 p-4 text-center peer-checked:border-red-600 peer-checked:bg-red-50 transition hover:bg-gray-50">
                            <i class="fab fa-youtube text-3xl text-red-600 mb-2"></i>
                            <div class="font-bold text-gray-700">Vidéo YouTube</div>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type_media" value="drive" class="peer sr-only" onchange="toggleFields()">
                        <div class="rounded-lg border-2 border-gray-200 p-4 text-center peer-checked:border-blue-600 peer-checked:bg-blue-50 transition hover:bg-gray-50">
                            <i class="fab fa-google-drive text-3xl text-blue-600 mb-2"></i>
                            <div class="font-bold text-gray-700">Lien Drive</div>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type_media" value="upload" class="peer sr-only" onchange="toggleFields()">
                        <div class="rounded-lg border-2 border-gray-200 p-4 text-center peer-checked:border-green-600 peer-checked:bg-green-50 transition hover:bg-gray-50">
                            <i class="fas fa-images text-3xl text-green-600 mb-2"></i>
                            <div class="font-bold text-gray-700">Galerie Photos</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre de l'album / vidéo *</label>
                    <input type="text" name="titre" required class="w-full px-4 py-2 border rounded-lg focus:ring-red-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description courte</label>
                    <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:ring-red-500"></textarea>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                
                <div id="field-link">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Lien vers la ressource *</label>
                    <div class="flex">
                        <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-100 text-gray-500">
                            <i class="fas fa-link"></i>
                        </span>
                        <input type="url" name="lien_externe" id="input-link" class="flex-1 px-4 py-2 border rounded-r-lg focus:ring-red-500" placeholder="https://youtube.com/...">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Collez ici le lien de la vidéo ou du dossier Drive.</p>
                </div>

                <div id="field-upload" class="hidden">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Importer des photos (Sélection multiple)</label>
                    
                    <div id="drop-zone" class="border-2 border-dashed border-gray-400 rounded-lg p-8 text-center bg-white cursor-pointer transition relative">
                        <input type="file" name="photos[]" id="file-input" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        
                        <div class="pointer-events-none">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="text-lg font-medium text-gray-700">Glissez vos photos ici</p>
                            <p class="text-sm text-gray-500">ou cliquez pour parcourir vos dossiers</p>
                        </div>
                    </div>
                    
                    <div id="gallery-preview" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-4"></div>
                    <p id="file-count" class="text-sm text-gray-500 mt-2 text-right"></p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture (Vignette) *</label>
                <div class="flex items-center gap-4">
                    <div class="border border-gray-300 rounded-lg p-2 w-full">
                        <input type="file" name="cover_image" accept="image/*" required onchange="previewCover(this)">
                    </div>
                    <img id="cover-preview-img" class="h-16 w-16 object-cover rounded hidden border">
                </div>
                <p class="text-xs text-gray-500 mt-1">Sera affichée sur la page "Photos". Obligatoire même pour YouTube.</p>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" id="submit-btn" class="bg-red-600 text-white font-display text-xl px-8 py-3 rounded-full hover:bg-red-700 transition shadow-lg flex items-center gap-2">
                    <i class="fas fa-save"></i> Créer l'album
                </button>
            </div>
        </form>
    </div>

    <script>
        // 1. Gestion des champs dynamiques
        function toggleFields() {
            const type = document.querySelector('input[name="type_media"]:checked').value;
            const fieldLink = document.getElementById('field-link');
            const fieldUpload = document.getElementById('field-upload');
            const inputLink = document.getElementById('input-link');

            if (type === 'upload') {
                fieldLink.classList.add('hidden');
                fieldUpload.classList.remove('hidden');
                inputLink.required = false;
            } else {
                fieldLink.classList.remove('hidden');
                fieldUpload.classList.add('hidden');
                inputLink.required = true;
            }
        }

        // 2. Prévisualisation Cover
        function previewCover(input) {
            const img = document.getElementById('cover-preview-img');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 3. Gestion Drag & Drop et Prévisualisation Multiple
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');
        const galleryPreview = document.getElementById('gallery-preview');
        const fileCount = document.getElementById('file-count');

        // Effets visuels dragover
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('drag-active');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('drag-active');
            }, false);
        });

        // Gestion du changement de fichiers
        fileInput.addEventListener('change', handleFiles);

        function handleFiles() {
            const files = this.files;
            galleryPreview.innerHTML = ''; // Reset preview
            fileCount.innerText = files.length + " photo(s) sélectionnée(s)";

            if(files.length > 0) {
                Array.from(files).forEach(file => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'relative aspect-square rounded overflow-hidden shadow-sm border';
                            div.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
                            galleryPreview.appendChild(div);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        // Loader au submit (car l'upload peut être long)
        document.getElementById('albumForm').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Init au chargement
        toggleFields();
    </script>
</body>
</html>