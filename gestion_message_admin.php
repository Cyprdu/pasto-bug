<?php
session_start();
require_once 'db_connect.php';

// Sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// --- ACTIONS (Supprimer / Marquer comme lu) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($_GET['action'] === 'delete') {
        $pdo->prepare("DELETE FROM messages WHERE id = ?")->execute([$id]);
        $msg_success = "Message supprimé.";
    }
    
    if ($_GET['action'] === 'mark_read') {
        $pdo->prepare("UPDATE messages SET est_lu = 1 WHERE id = ?")->execute([$id]);
        // Pas de message de succès, c'est une action fluide
        header("Location: gestion_message_admin.php"); 
        exit;
    }
}

// --- RÉCUPÉRATION DES MESSAGES ---
// Triés par : Non lus d'abord, puis par date décroissante
$sql = "SELECT * FROM messages ORDER BY est_lu ASC, created_at DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll();

// Compteur non lus
$unread_count = 0;
foreach($messages as $m) { if($m['est_lu'] == 0) $unread_count++; }

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie - Admin PaJe</title>
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
                <h1 class="font-display text-2xl text-red-600 border-l pl-4 border-gray-300">Messagerie</h1>
            </div>
            
            <div class="flex items-center gap-2">
                <?php if($unread_count > 0): ?>
                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-sm font-bold border border-red-200">
                        <?= $unread_count ?> non lu(s)
                    </span>
                <?php else: ?>
                    <span class="text-gray-400 text-sm">Tous les messages sont lus</span>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-10">

        <?php if(isset($msg_success)): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $msg_success ?></div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <?php if(count($messages) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">État</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">De</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach($messages as $msg): 
                                $is_unread = ($msg['est_lu'] == 0);
                                $row_class = $is_unread ? 'bg-red-50' : 'bg-white hover:bg-gray-50';
                                $font_class = $is_unread ? 'font-bold text-gray-900' : 'font-normal text-gray-600';
                            ?>
                            <tr class="<?= $row_class ?> transition cursor-pointer" onclick="openMessageModal(<?= htmlspecialchars(json_encode($msg)) ?>)">
                                <td class="px-6 py-4 whitespace-nowrap w-10">
                                    <?php if($is_unread): ?>
                                        <span class="w-3 h-3 bg-red-500 rounded-full inline-block" title="Non lu"></span>
                                    <?php else: ?>
                                        <span class="w-3 h-3 bg-gray-300 rounded-full inline-block" title="Lu"></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="<?= $font_class ?>"><?= htmlspecialchars($msg['nom_complet']) ?></div>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($msg['email']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="<?= $font_class ?>"><?= htmlspecialchars($msg['sujet']) ?></div>
                                    <div class="text-sm text-gray-500 truncate w-64"><?= htmlspecialchars($msg['message']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end gap-3" onclick="event.stopPropagation()">
                                        <?php if($is_unread): ?>
                                            <a href="?action=mark_read&id=<?= $msg['id'] ?>" class="text-blue-600 hover:text-blue-900" title="Marquer comme lu">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?action=delete&id=<?= $msg['id'] ?>" onclick="return confirm('Supprimer ce message ?')" class="text-gray-400 hover:text-red-600" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-envelope-open text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500">Aucun message pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="msgModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto transform transition-all scale-95" id="modalContent">
            
            <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center sticky top-0">
                <h3 class="font-display text-xl text-red-600">Détail du message</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 id="modal-subject" class="text-2xl font-bold text-gray-800 mb-2"></h2>
                        <div class="flex items-center gap-2 text-gray-600">
                            <i class="fas fa-user-circle"></i>
                            <span id="modal-name" class="font-medium"></span>
                        </div>
                        <div class="flex items-center gap-2 text-gray-500 text-sm mt-1">
                            <i class="fas fa-envelope"></i>
                            <span id="modal-email"></span>
                        </div>
                    </div>
                    <span id="modal-date" class="text-sm text-gray-400 bg-gray-100 px-3 py-1 rounded-full"></span>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border border-gray-100 text-gray-700 leading-relaxed whitespace-pre-wrap" id="modal-body"></div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t flex justify-end gap-3">
                <a id="btn-reply" href="#" class="bg-red-600 text-white px-6 py-2 rounded-full hover:bg-red-700 transition flex items-center gap-2">
                    <i class="fas fa-reply"></i> Répondre
                </a>
                <a id="btn-mark-read" href="#" class="bg-white border border-gray-300 text-gray-700 px-6 py-2 rounded-full hover:bg-gray-100 transition">
                    Marquer comme lu
                </a>
            </div>
        </div>
    </div>

    <script>
        function openMessageModal(msg) {
            // Remplissage des champs
            document.getElementById('modal-subject').textContent = msg.sujet;
            document.getElementById('modal-name').textContent = msg.nom_complet;
            document.getElementById('modal-email').textContent = msg.email;
            document.getElementById('modal-body').textContent = msg.message;
            document.getElementById('modal-date').textContent = new Date(msg.created_at).toLocaleDateString('fr-FR');

            // Bouton Répondre (mailto)
            document.getElementById('btn-reply').href = `mailto:${msg.email}?subject=RE: ${msg.sujet}`;
            
            // Bouton Marquer comme lu
            const readBtn = document.getElementById('btn-mark-read');
            if (msg.est_lu == 0) {
                readBtn.style.display = 'inline-block';
                readBtn.href = `?action=mark_read&id=${msg.id}`;
            } else {
                readBtn.style.display = 'none';
            }

            // Affichage
            const modal = document.getElementById('msgModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('modalContent').classList.remove('scale-95');
                document.getElementById('modalContent').classList.add('scale-100');
            }, 10);
        }

        function closeModal() {
            const modal = document.getElementById('msgModal');
            document.getElementById('modalContent').classList.remove('scale-100');
            document.getElementById('modalContent').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Fermer en cliquant en dehors
        document.getElementById('msgModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>