<?php
session_start();
require_once '../db_connect.php';

// Fonction de formatage date
function formatDateShort($dateStr) {
    setlocale(LC_TIME, 'fr_FR.UTF8', 'fra');
    $date = new DateTime($dateStr);
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    return $formatter->format($date);
}

// Récupération des camps "Ados" (ID = 1)
$sql = "
    SELECT c.* FROM camps c
    JOIN camp_category cc ON c.id = cc.camp_id
    WHERE cc.category_id = 1 
    AND c.supprime = 0 
    AND c.date_debut >= CURDATE()
    ORDER BY c.date_debut ASC
";
$stmt = $pdo->query($sql);
$camps = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>10-17 ans - PaJe</title>
    <link rel="icon" type="image/png" href="https://github.com/Cyprdu/PaJe/blob/main/img/favico.png?raw=true">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Polices */
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'RobotoMedium'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Medium.ttf') format('truetype'); font-weight: 500; }
        @font-face { font-family: 'RobotoRegular'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Regular.ttf') format('truetype'); font-weight: 400; }
        
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .font-regular { font-family: 'RobotoRegular', sans-serif; }
        
        .hero-category {
            /* MODIFICATION ICI : Opacité passée de 0.9 à 0.4 pour mieux voir l'image */
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.4) 0%, rgba(185, 28, 28, 0.4) 100%), url('https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true');
            background-size: cover;
            background-position: center;
        }

        .camp-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="font-regular text-gray-800 bg-gray-50 flex flex-col min-h-screen">

    <?php include '../header.php'; ?>

    <header class="hero-category py-20 text-white text-center relative">
        
        <button onclick="goBack()" 
                class="absolute top-28 left-4 md:left-8 z-30 bg-white/20 hover:bg-white text-white hover:text-red-600 px-5 py-2 rounded-full font-bold flex items-center gap-2 transition backdrop-blur-sm border border-white/30 shadow-lg">
            <i class="fas fa-arrow-left"></i> Retour
        </button>

        <div class="max-w-4xl mx-auto px-4 mt-8">
            <h1 class="font-display text-5xl md:text-7xl mb-4 drop-shadow-lg">10-17 ans</h1>
            <p class="text-xl md:text-2xl font-light opacity-100 drop-shadow-md">Collégiens & Lycéens</p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-12">
        
        <div class="grid lg:grid-cols-12 gap-12">
            
            <div class="lg:col-span-4 space-y-8">
                
                <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-red-600">
                    <h2 class="font-display text-3xl text-red-600 mb-4">Tu as entre 10 et 17 ans ?</h2>
                    <p class="text-gray-700 mb-4 leading-relaxed">
                        Tu as envie de mieux te connaître et d’apprendre à connaître Jésus ?
                        Tu veux vivre quelque chose de vrai, de profond, de joyeux ?
                    </p>
                    <p class="text-gray-700 mb-6 leading-relaxed">
                        Viens vivre des temps forts, rencontrer d'autres jeunes comme toi, découvrir et rencontrer Jésus, et te construire dans la foi, la joie et l'amitié sous le regard de l’Esprit Saint !
                    </p>
                    <p class="text-gray-600 text-sm italic border-l-4 border-red-200 pl-4">
                        Que tu sois déjà croyant ou que tu te poses encore plein de questions, tu es le bienvenu. Nos propositions sont faites pour t’aider à grandir, à te découvrir et à te rapprocher de Dieu, à ton rythme.
                    </p>
                </div>

                <div class="bg-red-50 p-6 rounded-xl border border-red-100 text-center">
                    <i class="fas fa-quote-left text-red-300 text-2xl mb-2"></i>
                    <p class="font-display text-xl text-red-800 mb-2">
                        « Ne crains pas, dit le Seigneur, je t’ai appelé par ton nom, parce que tu as du prix à mes yeux, que tu as de la valeur et moi je t’aime »
                    </p>
                    <p class="text-sm text-red-600 font-bold">(Is 43,1;4)</p>
                </div>
            </div>

            <div class="lg:col-span-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-display text-4xl text-gray-800">
                        Ce que nous te proposons
                    </h2>
                    <span class="bg-red-100 text-red-800 px-4 py-1 rounded-full text-sm font-bold border border-red-200">
                        <?= count($camps) ?> événement(s)
                    </span>
                </div>

                <?php if(count($camps) > 0): ?>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php foreach($camps as $camp): 
                            $img = !empty($camp['image_couverture']) ? '../'.$camp['image_couverture'] : 'https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true';
                        ?>
                        <article class="bg-white rounded-xl shadow-lg overflow-hidden camp-card transition duration-300 flex flex-col h-full border border-gray-100 group">
                            <div class="h-48 overflow-hidden relative">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($camp['titre']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-red-600 shadow-sm">
                                    <?= formatDateShort($camp['date_debut']) ?>
                                </div>
                            </div>

                            <div class="p-6 flex-1 flex flex-col">
                                <h3 class="font-display text-2xl text-gray-800 mb-2 leading-tight"><?= htmlspecialchars($camp['titre']) ?></h3>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                                    <i class="fas fa-map-marker-alt text-red-500"></i>
                                    <?= htmlspecialchars($camp['adresse']) ?>
                                </div>

                                <p class="text-gray-600 text-sm mb-6 line-clamp-3 flex-1">
                                    <?= htmlspecialchars($camp['description']) ?>
                                </p>

                                <div class="mt-auto pt-4 border-t flex justify-between items-center">
                                    <span class="text-lg font-bold text-red-600">
                                        <?= ($camp['prix'] > 0) ? number_format($camp['prix'], 0).' €' : 'Gratuit' ?>
                                    </span>
                                    <a href="../camp_detail.php?id=<?= $camp['id'] ?>" class="bg-red-600 text-white px-5 py-2 rounded-full font-bold hover:bg-red-700 transition shadow-md text-sm">
                                        Voir le détail
                                    </a>
                                </div>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-xl shadow-sm p-12 text-center border-2 border-dashed border-gray-200">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400 text-3xl">
                            <i class="far fa-calendar-times"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Aucun événement pour le moment</h3>
                        <p class="text-gray-500">Reviens vite, de nouvelles dates seront bientôt ajoutées pour les 10-17 ans !</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script>
        function goBack() {
            if (document.referrer !== "" && document.referrer.includes(window.location.hostname)) {
                window.history.back();
            } else {
                window.location.href = '../index.php#camps';
            }
        }
    </script>
</body>
</html>
