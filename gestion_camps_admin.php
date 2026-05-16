<?php
session_start();
require_once 'db_connect.php';

// Vérification de sécurité
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit;
}

// --- LOGIQUE DE SUPPRESSION (Soft Delete) ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("UPDATE camps SET supprime = 1 WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success_msg = "Le camp a été supprimé avec succès.";
    } else {
        $error_msg = "Erreur lors de la suppression.";
    }
}

// --- RÉCUPÉRATION DES CAMPS ---
// On utilise GROUP_CONCAT pour récupérer les noms des catégories sur une seule ligne par camp
$sql = "
    SELECT c.*, GROUP_CONCAT(cat.nom SEPARATOR ', ') as categories_list 
    FROM camps c
    LEFT JOIN camp_category cc ON c.id = cc.camp_id
    LEFT JOIN categories cat ON cc.category_id = cat.id
    WHERE c.supprime = 0 
    GROUP BY c.id 
    ORDER BY c.date_debut DESC
";
$stmt = $pdo->query($sql);
$camps = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Camps - Admin PaJe</title>
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
                <h1 class="font-display text-2xl text-red-600 border-l pl-4 border-gray-300">Gestion des Camps</h1>
            </div>
            <a href="add_camps_admin.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition shadow-md flex items-center gap-2">
                <i class="fas fa-plus"></i> Ajouter un camp
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

        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <?php if(count($camps) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre / Info</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Public</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($camps as $camp): 
                                $dateObj = new DateTime($camp['date_debut']);
                                $isPast = $dateObj < new DateTime();
                                $rowClass = $isPast ? 'bg-gray-50 opacity-75' : ''; // Griser les événements passés
                            ?>
                            <tr class="hover:bg-red-50 transition <?= $rowClass ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">
                                        <?= $dateObj->format('d/m/Y') ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php if($isPast): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Passé</span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">À venir</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <?php $img = !empty($camp['image_couverture']) ? htmlspecialchars($camp['image_couverture']) : 'https://placehold.co/100x100?text=No+Img'; ?>
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-200" src="<?= $img ?>" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($camp['titre']) ?></div>
                                            <div class="text-sm text-gray-500"><i class="fas fa-map-marker-alt text-xs mr-1"></i> <?= htmlspecialchars($camp['adresse']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">
                                        <?= !empty($camp['categories_list']) ? htmlspecialchars($camp['categories_list']) : '<em class="text-gray-400">Aucune catégorie</em>' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?= htmlspecialchars($camp['prix']) ?> €
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="edit_camps_admin.php?id=<?= $camp['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4" title="Modifier">
                                        <i class="fas fa-edit text-lg"></i>
                                    </a>
                                    <a href="gestion_camps_admin.php?delete_id=<?= $camp['id'] ?>" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce camp ? Cette action est irréversible (soft delete).');" 
                                       class="text-red-600 hover:text-red-900" title="Supprimer">
                                        <i class="fas fa-trash-alt text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="text-gray-300 mb-4">
                        <i class="fas fa-campground text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Aucun camp trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500">Commencez par ajouter un nouvel événement pour la pastorale.</p>
                    <div class="mt-6">
                        <a href="add_camps_admin.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                            <i class="fas fa-plus mr-2"></i> Créer un camp
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>