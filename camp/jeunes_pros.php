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

// Récupération des camps "Jeunes Pros / 18-35 ans" (ID = 2)
$sql = "
    SELECT c.* FROM camps c
    JOIN camp_category cc ON c.id = cc.camp_id
    WHERE cc.category_id = 2 
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
    <title>18-35 ans - PaJe</title>
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
            /* MODIFICATION ICI : Assurez-vous d'avoir une image appropriée pour les 18-35 ans */
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.4) 0%, rgba(185, 28, 28, 0.4) 100%), url('https://github.com/Cyprdu/PaJe/blob/main/img/pro.png?raw=true');
            /* Si l'image jeunepro.png n'existe pas, remettez ado.png ou changez le lien */
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
            <h1 class="font-display text-5xl md:text-7xl mb-4 drop-shadow-lg">18-35 ans</h1>
            <p class="text-xl md:text-2xl font-light opacity-100 drop-shadow-md">Jeunes Adultes, Étudiants & Jeunes Pros</p>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-12">
        
        <div class="grid lg:grid-cols-12 gap-12">
            
            <div class="lg:col-span-4 space-y-8">
                
                <div class="bg-white p-8 rounded-2xl shadow-lg border-t-4 border-red-600">
                    <h2 class="font-display text-3xl text-red-600 mb-4">Tu as entre 18 et 35 ans ?</h2>
                    
                    <p class="text-gray-700 mb-4 leading-relaxed font-semibold">
                        Que tu sois étudiant, en début de vie professionnelle, en famille ou simplement en chemin spirituel, cette rubrique est faite pour toi.
                    </p>

                    <p class="text-gray-700 mb-4 leading-relaxed">
                        Tu cherches un lieu où vivre ta foi, te poser, rencontrer d’autres jeunes adultes et grandir dans ta vie spirituelle ?
                    </p>

                    <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-400 mb-6">
                        <h3 class="font-bold text-red-800 mb-2">Notre pédagogie</h3>
                        <p class="text-sm text-gray-700">
                            C’est de t’inviter à te mettre au service des jeunes et entrer dans le corps des animateurs. Nous t’invitons à mettre en service tes talents, tes charismes et tous les dons que Dieu t’a confiés.
                        </p>
                    </div>

                    <h3 class="font-display text-xl text-gray-800 mb-3">Pourquoi venir ?</h3>
                    <ul class="space-y-2 text-gray-600 text-sm mb-6">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-red-500 mt-1"></i>
                            <span>Pour rencontrer d’autres jeunes adultes qui partagent ta foi ou tes questionnements.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-red-500 mt-1"></i>
                            <span>Pour approfondir ta relation avec Dieu et te poser dans un chemin spirituel.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-red-500 mt-1"></i>
                            <span>Pour donner du sens à ta vie, ton travail, tes engagements.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-red-500 mt-1"></i>
                            <span>Pour trouver un équilibre entre foi, vie personnelle et responsabilités.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-red-500 mt-1"></i>
                            <span>Pour t’engager dans l’Église et la société, avec confiance et maturité.</span>
                        </li>
                    </ul>

                    <p class="text-gray-600 text-sm italic border-t pt-4 border-gray-100">
                        Que tu sois déjà ancré dans la foi ou que tu sois curieux de la découvrir, tu es le bienvenu. Ici, chacun avance à son rythme, dans le respect et la richesse des parcours variés.
                    </p>
                </div>

                <div class="bg-red-50 p-6 rounded-xl border border-red-100 text-center">
                    <i class="fas fa-quote-left text-red-300 text-2xl mb-2"></i>
                    <p class="font-display text-xl text-red-800 mb-2">
                        « Exultant de joie, vous puiserez les eaux aux sources du salut. »
                    </p>
                    <p class="text-sm text-red-600 font-bold">(Is 12,3)</p>
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
                            // Image par défaut si vide (adapter le nom de l'image par défaut si besoin)
                            $img = !empty($camp['image_couverture']) ? '../'.$camp['image_couverture'] : 'https://github.com/Cyprdu/PaJe/blob/main/img/jeunepro.png?raw=true';
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
                        <p class="text-gray-500">Reviens vite, de nouvelles dates seront bientôt ajoutées pour les 18-35 ans !</p>
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