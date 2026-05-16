<?php
session_start();
require_once 'db_connect.php';

// --- LOGIQUE DE SÉCURITÉ ---

// 1. Si l'utilisateur n'est pas connecté du tout -> Redirection vers le login
if (!isset($_SESSION['anim_logged_in']) || $_SESSION['anim_logged_in'] !== true) {
    header("Location: login_animateur.php");
    exit;
}

// 2. Si l'utilisateur a déjà changé son mot de passe -> Redirection vers le dashboard
// On vérifie en base de données pour être 100% sûr
$stmtCheck = $pdo->prepare("SELECT must_change_password FROM animateurs WHERE id = ?");
$stmtCheck->execute([$_SESSION['anim_id']]);
$status = $stmtCheck->fetchColumn();

if ($status == 0) {
    $_SESSION['must_change_password'] = 0; // On met à jour la session au cas où
    header("Location: dashboard_animateur.php");
    exit;
}

$erreur = "";

// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['changer_mdp'])) {
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirm_mdp = $_POST['confirm_mdp'];

    if (strlen($nouveau_mdp) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($nouveau_mdp !== $confirm_mdp) {
        $erreur = "Les deux mots de passe ne sont pas identiques.";
    } else {
        $hashed_password = password_hash($nouveau_mdp, PASSWORD_DEFAULT);

        // MISE À JOUR CRITIQUE : on passe must_change_password à 0
        $stmt = $pdo->prepare("UPDATE animateurs SET password = ?, must_change_password = 0 WHERE id = ?");
        
        if ($stmt->execute([$hashed_password, $_SESSION['anim_id']])) {
            // TRÈS IMPORTANT : On met à jour la session pour que la vérification du haut ne bloque plus
            $_SESSION['must_change_password'] = 0;
            
            // Redirection finale
            header("Location: dashboard_animateur.php?first_login=success");
            exit;
        } else {
            $erreur = "Une erreur technique est survenue.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sécuriser mon compte - PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { 
            font-family: 'InsatiableDisplay'; 
            src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); 
            font-weight: bold; 
        }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-gray-100">
        <div class="bg-gradient-to-r from-orange-400 to-red-500 p-8 text-white text-center">
            <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-md">
                <i class="fas fa-shield-alt text-3xl"></i>
            </div>
            <h1 class="font-display text-4xl mb-2">Sécurise ton compte</h1>
            <p class="text-orange-50 text-sm opacity-90">C'est ta première connexion ! Choisis un mot de passe personnel pour accéder à ton espace.</p>
        </div>

        <div class="p-8">
            <?php if ($erreur): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg text-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= $erreur ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="space-y-6">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nouveau mot de passe</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-orange-500 transition">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" name="nouveau_mdp" required minlength="8"
                               class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 focus:bg-white transition"
                               placeholder="8 caractères minimum">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Confirmer le mot de passe</label>
                    <div class="relative group">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 group-focus-within:text-orange-500 transition">
                            <i class="fas fa-check-double"></i>
                        </span>
                        <input type="password" name="confirm_mdp" required minlength="8"
                               class="block w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 focus:bg-white transition"
                               placeholder="Répéter le mot de passe">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" name="changer_mdp" 
                            class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-orange-200 hover:shadow-orange-300 transform hover:-translate-y-0.5 transition duration-200">
                        Activer mon compte <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
            <p class="text-gray-400 text-xs italic">Besoin d'aide ? Contacte l'administrateur.</p>
        </div>
    </div>

</body>
</html>