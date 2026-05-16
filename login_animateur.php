<?php
/**
 * CONFIGURATION SESSION
 * Identique à l'espace Admin pour permettre d'être connecté aux deux en même temps
 * sans que le navigateur ne bloque les cookies.
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

// Si l'animateur est déjà connecté, on le redirige
if (isset($_SESSION['anim_logged_in']) && $_SESSION['anim_logged_in'] === true) {
    if (isset($_SESSION['must_change_password']) && $_SESSION['must_change_password'] == 1) {
        header("Location: force_password.php");
        exit;
    }
    header("Location: dashboard_animateur.php");
    exit;
}

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_anim'])) {
    // On nettoie les espaces avant/après (très important pour les copier/coller depuis un PDF)
    $email = trim(htmlspecialchars($_POST['email']));
    $password = trim($_POST['password']); 

    if (!empty($email) && !empty($password)) {
        // On cherche l'animateur dans la base
        $stmt = $pdo->prepare("SELECT * FROM animateurs WHERE email = ?");
        $stmt->execute([$email]);
        $animateur = $stmt->fetch();

        // On vérifie le mot de passe
        if ($animateur && password_verify($password, $animateur['password'])) {
            
            // Création de la session Animateur (différente de celle de l'admin)
            $_SESSION['anim_logged_in'] = true;
            $_SESSION['anim_id'] = $animateur['id'];
            $_SESSION['anim_nom'] = $animateur['prenom'] . ' ' . $animateur['nom'];
            $_SESSION['must_change_password'] = $animateur['must_change_password'];

            // Redirection selon le statut du mot de passe
            if ($animateur['must_change_password'] == 1) {
                header("Location: force_password.php");
                exit;
            } else {
                header("Location: dashboard_animateur.php");
                exit;
            }
        } else {
            $erreur = "Email ou mot de passe incorrect.";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Animateur - PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">

    <div class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md border-t-4 border-teal-500">
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 text-3xl mx-auto mb-4">
                <i class="fas fa-campground"></i>
            </div>
            <h1 class="font-display text-4xl text-gray-800">Espace Animateur</h1>
            <p class="text-gray-500 mt-2">Connecte-toi pour accéder à tes ressources et missions.</p>
        </div>

        <?php if ($erreur): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> <?= $erreur ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse Email</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-teal-500 transition">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" name="email" id="email" required 
                           class="pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 border p-3 bg-gray-50 focus:bg-white transition" 
                           placeholder="ton.email@exemple.com">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-teal-500 transition">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password" id="password" required 
                           class="pl-10 w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 border p-3 bg-gray-50 focus:bg-white transition" 
                           placeholder="••••••••">
                </div>
            </div>

            <button type="submit" name="login_anim" class="w-full bg-teal-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-teal-700 transition duration-200 flex justify-center items-center gap-2 shadow-lg shadow-teal-200">
                Se connecter <i class="fas fa-arrow-right"></i>
            </button>
        </form>

        <div class="mt-8 text-center border-t border-gray-100 pt-6">
            <a href="index.php" class="text-sm text-gray-400 hover:text-teal-600 transition flex items-center justify-center gap-2 font-medium">
                <i class="fas fa-arrow-left"></i> Retour au site principal
            </a>
        </div>
    </div>

</body>
</html>