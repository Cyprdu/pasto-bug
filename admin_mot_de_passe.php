<?php
/**
 * CONFIGURATION SESSION POUR IFRAME
 */
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

// Sécurité : Redirection si non connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

$message = '';
$msg_type = ''; // 'success' ou 'error'

// --- 1. TRAITEMENT : AJOUT ADMIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add') {
    $new_username = trim($_POST['username']);
    $new_password = $_POST['password'];

    if (!empty($new_username) && !empty($new_password)) {
        // Vérif doublon
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$new_username]);
        if ($stmt->fetch()) {
            $message = "Cet identifiant existe déjà.";
            $msg_type = 'error';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, created_at) VALUES (?, ?, NOW())");
            if ($stmt->execute([$new_username, $hashed_password])) {
                $message = "Administrateur ajouté avec succès.";
                $msg_type = 'success';
            } else {
                $message = "Erreur technique lors de l'ajout.";
                $msg_type = 'error';
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $msg_type = 'error';
    }
}

// --- 2. TRAITEMENT : SUPPRESSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id_to_delete = intval($_POST['admin_id']);

    // PROTECTION SUPREME ID 2
    if ($id_to_delete == 2) {
        $message = "ACTION INTERDITE : Impossible de supprimer l'Administrateur Suprême (ID 2).";
        $msg_type = 'error';
    } 
    // Protection anti-suicide (se supprimer soi-même)
    elseif ($id_to_delete == $_SESSION['admin_id']) {
        $message = "Action refusée : Vous ne pouvez pas supprimer votre propre compte.";
        $msg_type = 'error';
    } else {
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        if ($stmt->execute([$id_to_delete])) {
            $message = "Compte supprimé avec succès.";
            $msg_type = 'success';
        } else {
            $message = "Erreur lors de la suppression.";
            $msg_type = 'error';
        }
    }
}

// --- 3. TRAITEMENT : CHANGEMENT DE MOT DE PASSE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_password') {
    $target_id = intval($_POST['admin_id']);
    $new_pass = $_POST['new_password'];

    if (!empty($new_pass)) {
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $target_id])) {
            $message = "Mot de passe modifié avec succès.";
            $msg_type = 'success';
        } else {
            $message = "Erreur lors de la modification.";
            $msg_type = 'error';
        }
    } else {
        $message = "Le mot de passe ne peut pas être vide.";
        $msg_type = 'error';
    }
}

// --- RECUPERATION LISTE ---
$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Admins - PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

    <nav class="bg-white shadow-md px-6 py-4 flex justify-between items-center sticky top-0 z-40">
        <div class="flex items-center gap-4">
            <h1 class="font-display text-3xl text-orange-600">Sécurité Admin</h1>
        </div>
        <a href="dashboard_admin.php" class="text-gray-500 hover:text-orange-600 flex items-center gap-2 transition font-medium">
            <i class="fas fa-arrow-left"></i> Retour au Dashboard
        </a>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-10">
        
        <?php if ($message): ?>
            <div class="<?= $msg_type === 'success' ? 'bg-green-100 border-green-500 text-green-700' : 'bg-red-100 border-red-500 text-red-700' ?> border-l-4 p-4 mb-8 rounded shadow-sm flex justify-between items-center" role="alert">
                <div>
                    <p class="font-bold"><?= $msg_type === 'success' ? 'Succès' : 'Erreur' ?></p>
                    <p><?= htmlspecialchars($message) ?></p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-2xl font-bold leading-none">&times;</button>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 sticky top-24">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-orange-600">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2 class="font-display text-2xl text-gray-800">Ajouter un admin</h2>
                    </div>

                    <form action="" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Identifiant</label>
                            <input type="text" name="username" required placeholder="Ex: JeanDupont"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                            <div class="relative">
                                <input type="password" id="add_password" name="password" required placeholder="********"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-colors pr-10">
                                <button type="button" onclick="togglePassword('add_password', 'eye-add')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-orange-600">
                                    <i class="fas fa-eye" id="eye-add"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-orange-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-orange-700 transition duration-300 shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> Créer le compte
                        </button>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                        <h2 class="font-display text-2xl text-gray-800">Comptes existants</h2>
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?= count($admins) ?> compte(s)</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 text-sm uppercase tracking-wider">
                                    <th class="px-6 py-4 font-medium">Identifiant</th>
                                    <th class="px-6 py-4 font-medium">Créé le</th>
                                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($admins as $admin): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                    <?php if($admin['id'] == 2): ?>
                                                        <i class="fas fa-crown text-yellow-500"></i> <?php else: ?>
                                                        <i class="fas fa-user"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-800">
                                                        <?= htmlspecialchars($admin['username']) ?>
                                                        <?php if($admin['id'] == 2): ?>
                                                            <span class="text-xs text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full ml-2">Suprême</span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if($admin['id'] == $_SESSION['admin_id']): ?>
                                                        <span class="text-xs text-green-600"> (C'est vous)</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 text-sm">
                                            <?= date("d/m/Y", strtotime($admin['created_at'])) ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end items-center gap-2">
                                                
                                                <button onclick="openModal(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>')" 
                                                        class="text-yellow-500 hover:text-yellow-600 hover:bg-yellow-50 p-2 rounded-full transition" 
                                                        title="Changer le mot de passe">
                                                    <i class="fas fa-key"></i>
                                                </button>

                                                <?php if($admin['id'] != 2 && $admin['id'] != $_SESSION['admin_id']): ?>
                                                    <form action="" method="POST" onsubmit="return confirm('Supprimer définitivement <?= $admin['username'] ?> ?');" class="inline">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                        <button type="submit" class="text-red-400 hover:text-red-600 hover:bg-red-50 p-2 rounded-full transition" title="Supprimer">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-gray-300 p-2 cursor-not-allowed" title="Suppression interdite">
                                                        <i class="fas fa-ban"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="passwordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 transform transition-all scale-95 opacity-0" id="modalContent">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Modifier mot de passe</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <p class="text-gray-600 mb-4">Modification pour l'utilisateur : <strong id="modalUsername" class="text-orange-600">...</strong></p>

            <form action="" method="POST">
                <input type="hidden" name="action" value="update_password">
                <input type="hidden" name="admin_id" id="modalAdminId">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <div class="relative">
                        <input type="password" id="modal_new_password" name="new_password" required 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <button type="button" onclick="togglePassword('modal_new_password', 'eye-modal')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-orange-600">
                            <i class="fas fa-eye" id="eye-modal"></i>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg transition">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white font-bold rounded-lg hover:bg-orange-700 transition shadow-md">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Gestion Mot de passe visible/masqué
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Gestion Modale
        const modal = document.getElementById('passwordModal');
        const modalContent = document.getElementById('modalContent');
        const modalUsername = document.getElementById('modalUsername');
        const modalAdminId = document.getElementById('modalAdminId');

        function openModal(id, username) {
            modalUsername.textContent = username;
            modalAdminId.value = id;
            modal.classList.remove('hidden');
            // Animation simple
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Fermer si on clique en dehors
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    </script>

</body>
</html>