<?php
session_start();
require_once 'db_connect.php';

// Vérification de sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$msg_action = "";
$msg_error = "";

// ==========================================
// TRAITEMENT DES FORMULAIRES ADMIN (POST)
// ==========================================

// 1. Ajouter une ressource / formation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_ressource'])) {
    $titre = htmlspecialchars($_POST['titre']);
    $auteur = htmlspecialchars($_POST['auteur']);
    $description = htmlspecialchars($_POST['description']);
    $type_ressource = $_POST['type_ressource'];
    
    $contenu_texte = isset($_POST['contenu_texte']) ? $_POST['contenu_texte'] : null;
    $lien_externe = isset($_POST['lien_externe']) ? htmlspecialchars($_POST['lien_externe']) : null;
    
    $cover_path = null;
    $fichier_joint = null;

    // A. Gestion de l'upload de la Cover
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $upload_dir_cover = 'uploads/covers/';
        if (!is_dir($upload_dir_cover)) mkdir($upload_dir_cover, 0777, true);
        
        // Sécuriser l'extension de l'image
        $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
        $nom_cover = 'cover_' . uniqid() . '.' . $ext;
        
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_dir_cover . $nom_cover)) {
            $cover_path = $upload_dir_cover . $nom_cover;
        }
    }

    // B. Gestion de l'upload d'un fichier (PDF, Word, MP3)
    if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] == 0) {
        $upload_dir_fichier = 'uploads/topos/';
        if (!is_dir($upload_dir_fichier)) mkdir($upload_dir_fichier, 0777, true);
        
        $nom_fichier = uniqid() . '_' . basename($_FILES['fichier']['name']);
        if (move_uploaded_file($_FILES['fichier']['tmp_name'], $upload_dir_fichier . $nom_fichier)) {
            $fichier_joint = $upload_dir_fichier . $nom_fichier;
        }
    }

    // C. Insertion en base de données
    $stmt = $pdo->prepare("INSERT INTO ressources_animateurs (titre, auteur, cover, description, type_ressource, contenu_texte, fichier_joint, lien_externe) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if($stmt->execute([$titre, $auteur, $cover_path, $description, $type_ressource, $contenu_texte, $fichier_joint, $lien_externe])) {
        $msg_action = "Le contenu a bien été publié sur l'espace animateur.";
    } else {
        $msg_error = "Erreur lors de l'ajout du contenu.";
    }
}

// 2. Création d'un compte Animateur & Génération PDF
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['creer_animateur'])) {
    
    if(!file_exists('fpdf.php')) {
        $msg_error = "Erreur critique : Le fichier 'fpdf.php' est introuvable. Veuillez le télécharger sur fpdf.org et le mettre dans votre dossier racine.";
    } else {
        require 'fpdf.php'; 

        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = htmlspecialchars($_POST['email']);
        
        $random_password = bin2hex(random_bytes(4)); 
        $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO animateurs (nom, prenom, email, password, must_change_password) VALUES (?, ?, ?, ?, 1)");
        
        try {
            $stmt->execute([$nom, $prenom, $email, $hashed_password]);
            
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, utf8_decode('Bienvenue dans la Pastorale des Jeunes !'), 0, 1, 'C');
            $pdf->Ln(10);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, utf8_decode("Bonjour $prenom $nom,"), 0, 1);
            $pdf->MultiCell(0, 10, utf8_decode("Voici tes identifiants pour te connecter à l'Espace Animateurs. Lors de ta première connexion, il te sera demandé de créer ton propre mot de passe personnalisé."));
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_decode("Lien de connexion : https://votre-site.fr/login_animateur.php"), 0, 1);
            $pdf->Cell(0, 10, utf8_decode("Email (Identifiant) : " . $email), 0, 1);
            $pdf->Cell(0, 10, utf8_decode("Mot de passe provisoire : " . $random_password), 0, 1);
            
            ob_end_clean(); 
            $pdf->Output('D', "Identifiants_Animateur_$prenom.pdf");
            exit; 

        } catch(PDOException $e) {
            $msg_error = "Erreur : Cet email est peut-être déjà utilisé.";
        }
    }
}

// ==========================================
// RÉCUPÉRATION DES DONNÉES ET STATS
// ==========================================
$nb_animateurs = $pdo->query("SELECT COUNT(*) FROM animateurs")->fetchColumn();
$nb_intentions = $pdo->query("SELECT COUNT(*) FROM intentions_priere")->fetchColumn();
$nb_ressources = $pdo->query("SELECT COUNT(*) FROM ressources_animateurs")->fetchColumn();

$intentions = $pdo->query("SELECT ip.intention, ip.date_creation, a.nom, a.prenom FROM intentions_priere ip JOIN animateurs a ON ip.animateur_id = a.id ORDER BY ip.date_creation DESC LIMIT 20")->fetchAll();
$manifestations = $pdo->query("SELECT c.titre as camp_titre, a.nom, a.prenom, ca.message_organisateur, ca.date_manifestation FROM camp_animateur ca JOIN camps c ON ca.camp_id = c.id JOIN animateurs a ON ca.animateur_id = a.id ORDER BY ca.date_manifestation DESC LIMIT 20")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Animateurs - PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-teal-600 shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-teal-600 text-xl">
                <i class="fas fa-users-cog"></i>
            </div>
            <h1 class="font-display text-3xl text-white">Dashboard Animateurs</h1>
        </div>
        <div class="flex gap-4">
            <a href="dashboard_admin.php" class="bg-white text-teal-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition font-medium flex items-center gap-2 shadow-sm">
                <i class="fas fa-arrow-left"></i> <span class="hidden sm:inline">Retour Admin</span>
            </a>
            <a href="?logout=true" class="bg-teal-800 text-white px-4 py-2 rounded-lg hover:bg-teal-900 transition font-medium flex items-center gap-2 shadow-sm">
                <i class="fas fa-sign-out-alt"></i><span class="hidden sm:inline">Déconnexion</span>
            </a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10">
        
        <?php if($msg_action): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg shadow-sm" role="alert">
                <p><i class="fas fa-check-circle mr-2"></i> <?= $msg_action ?></p>
            </div>
        <?php endif; ?>
        <?php if($msg_error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg shadow-sm" role="alert">
                <p><i class="fas fa-exclamation-triangle mr-2"></i> <?= $msg_error ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Comptes Animateurs</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?= $nb_animateurs ?></h3>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xl"><i class="fas fa-users"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Ressources publiées</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?= $nb_ressources ?></h3>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 text-xl"><i class="fas fa-book-open"></i></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-gray-500 font-medium mb-1">Intentions confiées</p>
                    <h3 class="text-3xl font-bold text-gray-800"><?= $nb_intentions ?></h3>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 text-xl"><i class="fas fa-pray"></i></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="space-y-8">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <i class="fas fa-user-plus text-teal-600 text-2xl"></i>
                        <h2 class="font-display text-3xl text-gray-800">Créer un compte Animateur</h2>
                    </div>
                    <form method="POST" action="" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                                <input type="text" name="prenom" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 border p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                                <input type="text" name="nom" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 border p-2 text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse Email</label>
                            <input type="email" name="email" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 border p-2 text-sm">
                        </div>
                        <button type="submit" name="creer_animateur" class="w-full bg-teal-600 text-white font-medium py-2 px-4 rounded-lg hover:bg-teal-700 transition flex items-center justify-center gap-2">
                            <i class="fas fa-file-pdf"></i> Créer et télécharger les identifiants (PDF)
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <i class="fas fa-hands-praying text-orange-500 text-2xl"></i>
                        <h2 class="font-display text-3xl text-gray-800">Intentions confiées</h2>
                    </div>
                    <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                        <?php foreach($intentions as $ip): ?>
                            <div class="bg-orange-50 rounded-lg p-4 border border-orange-100">
                                <p class="text-sm text-gray-800 italic mb-2">« <?= nl2br(htmlspecialchars($ip['intention'])) ?> »</p>
                                <div class="flex justify-between items-center text-xs text-gray-500 mt-2">
                                    <span class="font-semibold text-orange-800"><?= htmlspecialchars($ip['prenom'] . ' ' . $ip['nom']) ?></span>
                                    <span><?= date('d/m/Y à H:i', strtotime($ip['date_creation'])) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($intentions)): ?>
                            <p class="text-center text-gray-500 py-4">Aucune intention de prière déposée.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <div class="space-y-8">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6 border-b pb-4">
                        <i class="fas fa-plus-circle text-purple-600 text-2xl"></i>
                        <h2 class="font-display text-3xl text-gray-800">Publier un contenu</h2>
                    </div>
                    <form method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                                <input type="text" name="titre" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 border p-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Auteur</label>
                                <input type="text" name="auteur" required placeholder="Ex: Père Jean" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 border p-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture (Cover)</label>
                            <input type="file" name="cover" accept="image/png, image/jpeg, image/webp" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 border p-1 rounded-md">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Courte description (pour la carte)</label>
                            <textarea name="description" rows="2" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 border p-2 text-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de média principal</label>
                            <select name="type_ressource" id="select_type" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 border p-2 text-sm bg-white">
                                <option value="">-- Choisissez le type --</option>
                                <option value="texte">Document Texte (Rédiger sur le site)</option>
                                <option value="pdf">Fichier à télécharger (PDF / Word)</option>
                                <option value="youtube">Vidéo YouTube</option>
                                <option value="lien_audio">Lien Audio (Ex: fsound.lol)</option>
                                <option value="fichier_audio">Fichier Audio (Upload MP3)</option>
                            </select>
                        </div>

                        <div id="div_texte" style="display:none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Votre Texte / Article</label>
                            <textarea name="contenu_texte" rows="6" class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 border p-2 text-sm"></textarea>
                        </div>

                        <div id="div_lien" style="display:none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1" id="label_lien">URL du lien (YouTube ou Audio)</label>
                            <input type="url" name="lien_externe" placeholder="https://..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 border p-2 text-sm">
                        </div>

                        <div id="div_fichier" style="display:none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1" id="label_fichier">Fichier à uploader</label>
                            <input type="file" name="fichier" id="input_fichier" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border p-1 rounded-md">
                        </div>

                        <button type="submit" name="ajouter_ressource" class="w-full bg-purple-600 text-white font-medium py-3 px-4 rounded-lg hover:bg-purple-700 transition mt-4">
                            Publier sur l'Espace Animateurs
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('select_type').addEventListener('change', function() {
            var val = this.value;
            
            // On cache tout par défaut
            document.getElementById('div_texte').style.display = 'none';
            document.getElementById('div_lien').style.display = 'none';
            document.getElementById('div_fichier').style.display = 'none';

            if (val === 'texte') {
                document.getElementById('div_texte').style.display = 'block';
            } 
            else if (val === 'youtube' || val === 'lien_audio') {
                document.getElementById('div_lien').style.display = 'block';
                document.getElementById('label_lien').innerHTML = val === 'youtube' ? 'URL de la vidéo YouTube' : 'Lien du streaming Audio (Ex: fsound.lol)';
            } 
            else if (val === 'pdf' || val === 'fichier_audio') {
                document.getElementById('div_fichier').style.display = 'block';
                document.getElementById('label_fichier').innerHTML = val === 'pdf' ? 'Sélectionner le PDF / DOCX' : 'Sélectionner le fichier MP3';
                document.getElementById('input_fichier').accept = val === 'pdf' ? '.pdf,.doc,.docx' : 'audio/mp3,audio/mpeg';
            }
        });
    </script>
</body>
</html>