<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>



<?php
// index.php
session_start();
require_once 'db_connect.php';

// --- FONCTIONS UTILITAIRES ---
function formatDateFr($dateStr) {
    setlocale(LC_TIME, 'fr_FR.UTF8', 'fra');
    $date = new DateTime($dateStr);
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    return $formatter->format($date);
}

// --- LOGIQUE BACKEND ---

// 1. Contact
$msg_sent = false;
$msg_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    $nom = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $sujet = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    if (!empty($nom) && !empty($email) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (nom_complet, email, sujet, message) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$nom, $email, $sujet, $message])) {
            $msg_sent = true;
        } else {
            $msg_error = "Erreur lors de l'enregistrement.";
        }
    }
}

// 2. Prochain événement
$stmt = $pdo->prepare("SELECT * FROM camps WHERE supprime = 0 AND date_debut >= CURDATE() ORDER BY date_debut ASC LIMIT 1");
$stmt->execute();
$nextEvent = $stmt->fetch();

// 3. Compteurs
$counters = [1 => 0, 2 => 0, 3 => 0];
$sqlCounters = "SELECT category_id, COUNT(*) as total FROM camp_category cc JOIN camps c ON c.id = cc.camp_id WHERE c.supprime = 0 AND c.date_debut >= CURDATE() GROUP BY category_id";
$stmt = $pdo->query($sqlCounters);
while ($row = $stmt->fetch()) { $counters[$row['category_id']] = $row['total']; }

// 4. Albums
$stmt = $pdo->prepare("SELECT * FROM albums WHERE supprime = 0 ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$albums = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PaJe - Pastorale des Jeunes</title>
    <link rel="icon" type="image/png" href="https://github.com/Cyprdu/PaJe/blob/main/img/favico.png?raw=true">
    
    <link rel="preload" as="image" href="/uploads/cover.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Polices */
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); font-weight: bold; }
        @font-face { font-family: 'RobotoMedium'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Medium.ttf') format('truetype'); font-weight: 500; }
        @font-face { font-family: 'RobotoRegular'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/Roboto-Regular.ttf') format('truetype'); font-weight: 400; }
        
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .font-regular { font-family: 'RobotoRegular', sans-serif; }
        
        html { scroll-behavior: smooth; }
        
        /* Image de fond */
        .hero-gradient {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.5) 0%, rgba(0, 0, 0, 0.6) 100%), url('uploads/cover.png');
            background-attachment: fixed; background-repeat: no-repeat; background-size: cover; background-position: center;
        }
        @media (max-width: 768px) { .hero-gradient { background-attachment: scroll; } }
        
        /* Boutons Uniformes */
        .btn-custom {
            font-family: 'InsatiableDisplay', sans-serif;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            padding: 0.75rem 2rem;
            border-radius: 9999px;
            font-size: 1.125rem;
            transition: all 0.3s;
            display: inline-block;
            text-align: center;
            border-width: 2px;
            cursor: pointer;
        }
        .btn-red { background-color: #dc2626; color: white; border-color: #dc2626; }
        .btn-red:hover { background-color: #b91c1c; border-color: #b91c1c; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .btn-white { background-color: white; color: #dc2626; border-color: white; }
        .btn-white:hover { background-color: #f9fafb; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .btn-outline { background-color: transparent; border-color: white; color: white; }
        .btn-outline:hover { background-color: white; color: #dc2626; transform: translateY(-2px); }

        .btn-outline-red { background-color: transparent; border-color: #dc2626; color: #dc2626; }
        .btn-outline-red:hover { background-color: #dc2626; color: white; transform: translateY(-2px); }

        /* EFFET PHOTO PENCHÉE (Tilt) */
        .tilt-wrapper {
            overflow: hidden;
            transform: rotate(1.5deg) scale(1.05); /* Légèrement zoomé pour éviter les bords blancs */
            transition: transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .group:hover .tilt-wrapper {
            transform: rotate(0deg) scale(1);
        }

        /* BANDEAU INTEGRE (Badges) */
        .integrated-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            padding: 0.5rem 0;
            text-align: center;
            font-family: 'InsatiableDisplay', sans-serif;
            color: #dc2626;
            font-size: 1.25rem;
            letter-spacing: 0.05em;
            transform: translateY(0);
            transition: background 0.3s;
            border-top: 1px solid rgba(220, 38, 38, 0.1);
        }
        .group:hover .integrated-badge {
            background: #dc2626;
            color: white;
        }

        /* Accordéon */
        .value-toggle { cursor: pointer; transition: background-color 0.2s; }
        .value-toggle:hover { background-color: #fef2f2; }
        .value-content { max-height: 0; overflow: hidden; transition: max-height 0.3s ease-out; }
        .value-icon { transition: transform 0.3s; }
        .value-active .value-icon { transform: rotate(180deg); }

        /* Animations */
        .fade-in-up { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .fade-in-up.visible { opacity: 1; transform: translateY(0); }

        /* Modales */
        .video-modal, .registration-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; display: none; }
        .video-modal { background: rgba(0, 0, 0, 0.9); align-items: center; justify-content: center; }
        .registration-overlay { background: white; flex-direction: column; }
        .registration-iframe { flex: 1; border: none; width: 100%; }

        /* Active Header */
        .nav-active { color: #dc2626 !important; font-weight: bold; position: relative; }
        .nav-active::after { content: ''; position: absolute; left: 0; bottom: -5px; width: 100%; height: 2px; background-color: #dc2626; }
    </style>
</head>
<body class="font-regular text-gray-800 bg-white">

    <?php include 'header.php'; ?>

    <section id="accueil" class="hero-gradient min-h-screen flex items-center justify-center text-white pt-16 section-spy">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h1 class="font-display text-6xl md:text-8xl font-bold mb-6 drop-shadow-lg">PaJe</h1>
            <h2 class="font-medium text-2xl md:text-3xl mb-8 drop-shadow-md">Pastorale des Jeunes</h2>
            <p class="font-regular text-xl md:text-2xl mb-12 max-w-3xl mx-auto leading-relaxed drop-shadow-md opacity-90">
                Viens vivre des moments inoubliables de foi, de partage et d'amitié avec d'autres jeunes de ton âge.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#camps" class="btn-custom btn-white shadow-lg">
                    Découvrir nos camps
                </a>
                <a href="#qui-sommes-nous" class="btn-custom btn-outline shadow-lg">
                    En savoir plus
                </a>
            </div>
        </div>
    </section>

    <section id="qui-sommes-nous" class="py-24 bg-white section-spy">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="font-display text-5xl font-bold text-gray-800 mb-6">Qui sommes-nous ?</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto"></div>
            </div>
            
            <div class="grid md:grid-cols-2 gap-12 items-start">
                <div class="fade-in-up">
                    <h3 class="font-display text-3xl text-red-600 mb-6">La Pastorale des Jeunes</h3>
                    <p class="font-regular text-lg text-gray-700 mb-6 leading-relaxed">
                        La PaJe est la Pastorale des Jeunes et des vocations du Diocèse de Belley-Ars. Notre mission est d'accompagner les jeunes de 6 à 35 ans dans leur découverte et leur approfondissement de la foi chrétienne.
                    </p>
                    <p class="font-regular text-lg text-gray-700 mb-8 leading-relaxed">
                        À travers nos camps, week-ends et journées de retraite, nous proposons aux jeunes de vivre des expériences authentiques de rencontre avec le Christ.
                    </p>
                    <a href="qui_sommes_nous.php" class="btn-custom btn-outline-red">
                        En savoir plus
                    </a>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 shadow-md fade-in-up border border-gray-100">
                    <h4 class="font-display text-2xl text-gray-800 mb-6 border-b pb-2">Nos valeurs</h4>
                    
                    <div class="mb-2">
                        <div class="value-toggle flex justify-between items-center p-3 rounded-lg" onclick="toggleValue(this)">
                            <span class="font-bold text-lg text-gray-700">Accueil et bienveillance</span>
                            <span class="value-icon text-red-600 text-xl">+</span>
                        </div>
                        <div class="value-content">
                            <p class="text-gray-600 p-3 pt-0">Chacun est accueilli tel qu'il est, avec son histoire et ses questions, dans un climat de respect absolu.</p>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="value-toggle flex justify-between items-center p-3 rounded-lg" onclick="toggleValue(this)">
                            <span class="font-bold text-lg text-gray-700">Fraternité et partage</span>
                            <span class="value-icon text-red-600 text-xl">+</span>
                        </div>
                        <div class="value-content">
                            <p class="text-gray-600 p-3 pt-0">Vivre ensemble des moments forts, tisser des amitiés sincères et s'entraider au quotidien.</p>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="value-toggle flex justify-between items-center p-3 rounded-lg" onclick="toggleValue(this)">
                            <span class="font-bold text-lg text-gray-700">Découverte de la foi</span>
                            <span class="value-icon text-red-600 text-xl">+</span>
                        </div>
                        <div class="value-content">
                            <p class="text-gray-600 p-3 pt-0">Approfondir sa relation avec Dieu à travers la prière, les enseignements et les sacrements.</p>
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="value-toggle flex justify-between items-center p-3 rounded-lg" onclick="toggleValue(this)">
                            <span class="font-bold text-lg text-gray-700">Joie et épanouissement</span>
                            <span class="value-icon text-red-600 text-xl">+</span>
                        </div>
                        <div class="value-content">
                            <p class="text-gray-600 p-3 pt-0">La foi se vit dans la joie ! Chanter, jouer et rire ensemble fait partie intégrante de notre spiritualité.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-24 fade-in-up">
                <div class="text-center mb-12">
                    <h3 class="font-display text-3xl font-bold text-gray-800 mb-4">Notre prochain événement</h3>
                    <div class="w-16 h-1 bg-red-200 mx-auto"></div>
                </div>
                
                <div class="max-w-5xl mx-auto">
                    <?php if($nextEvent): ?>
                        <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-200">
                            <div class="grid md:grid-cols-2 gap-8 items-center">
                                <div>
                                    <div class="flex items-center gap-3 mb-4">
                                        <span class="bg-red-600 text-white px-4 py-1 rounded-full text-sm font-bold shadow-sm">
                                            <?= $nextEvent['age_min'] . '-' . $nextEvent['age_max'] ?> ans
                                        </span>
                                        <span class="text-red-600 font-bold text-sm bg-red-50 px-4 py-1 rounded-full border border-red-100">
                                            <?= formatDateFr($nextEvent['date_debut']) ?>
                                        </span>
                                    </div>
                                    <h4 class="font-display text-4xl text-gray-800 mb-3"><?= htmlspecialchars($nextEvent['titre']) ?></h4>
                                    <p class="text-gray-500 font-medium mb-4">
                                        <?= htmlspecialchars($nextEvent['adresse']) ?>
                                    </p>
                                    <p class="font-regular text-gray-600 mb-8 leading-relaxed">
                                        <?= substr(htmlspecialchars($nextEvent['description']), 0, 180) ?>...
                                    </p>
                                    
                                    <div class="flex flex-wrap gap-3">
                                        <a href="camp_detail.php?id=<?= $nextEvent['id'] ?>" class="btn-custom btn-outline-red" style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                                            Détail
                                        </a>

                                        <button onclick="openRegistration('<?= htmlspecialchars($nextEvent['lien_inscription']) ?>')" class="btn-custom btn-red shadow-md" style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                                            S'inscrire
                                        </button>
                                        
                                        <?php if(!empty($nextEvent['lien_teaser'])): ?>
                                        <button onclick="openVideoModal('<?= htmlspecialchars($nextEvent['lien_teaser']) ?>')" class="btn-custom btn-outline-red" style="font-size: 1rem; padding: 0.5rem 1.5rem;">
                                            Teaser
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="h-80 bg-gray-200 rounded-xl overflow-hidden shadow-lg group">
                                    <div class="tilt-wrapper w-full h-full">
                                        <?php $img = !empty($nextEvent['image_couverture']) ? htmlspecialchars($nextEvent['image_couverture']) : 'https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true'; ?>
                                        <img src="<?= $img ?>" alt="Next Event" class="w-full h-full object-cover">
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 bg-gray-50 rounded-xl">
                            <h4 class="font-display text-xl text-gray-600 mb-2">Aucun événement prévu</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section id="camps" class="py-24 bg-gray-100 section-spy">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="font-display text-5xl font-bold text-gray-800 mb-6">Nos camps</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto"></div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div onclick="window.location.href='camps/ados.php'" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover fade-in-up cursor-pointer h-full flex flex-col group">
                    <div class="h-56 relative overflow-hidden">
                        <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true" class="w-full h-full object-cover">
                        </div>
                        <div class="integrated-badge">
                            10 - 17 ANS
                        </div>
                    </div>
                    <div class="p-8 flex flex-col flex-grow">
                        <h3 class="font-display text-2xl text-gray-800 mb-2">Ados</h3>
                        <p class="text-gray-600 text-sm mb-6 flex-grow">Tu as envie de mieux te connaître et d'apprendre à connaître Jésus ? Viens vivre des temps forts !</p>
                        <div class="flex items-center justify-between border-t pt-4 mt-auto">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide"><?= $counters[1] ?> événement(s)</span>
                            <span class="text-red-600 font-bold text-sm font-display">Découvrir</span>
                        </div>
                    </div>
                </div>

                <div onclick="window.location.href='camps/jeunes_pros.php'" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover fade-in-up cursor-pointer h-full flex flex-col group" style="transition-delay: 100ms">
                    <div class="h-56 relative overflow-hidden">
                        <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/blob/main/img/pro.png?raw=true" class="w-full h-full object-cover">
                        </div>
                        <div class="integrated-badge">
                            18 - 35 ANS
                        </div>
                    </div>
                    <div class="p-8 flex flex-col flex-grow">
                        <h3 class="font-display text-2xl text-gray-800 mb-2">Jeunes Pros / Étudiants</h3>
                        <p class="text-gray-600 text-sm mb-6 flex-grow">C'est le lieu pour grandir dans ta foi et rencontrer d'autres jeunes adultes.</p>
                        <div class="flex items-center justify-between border-t pt-4 mt-auto">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide"><?= $counters[2] ?> événement(s)</span>
                            <span class="text-red-600 font-bold text-sm font-display">Découvrir</span>
                        </div>
                    </div>
                </div>

                <div onclick="window.location.href='camps/vocations.php'" class="bg-white rounded-2xl shadow-lg overflow-hidden card-hover fade-in-up cursor-pointer h-full flex flex-col group" style="transition-delay: 200ms">
                    <div class="h-56 relative overflow-hidden">
                        <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/blob/main/img/voc.png?raw=true" class="w-full h-full object-cover">
                        </div>
                        <div class="integrated-badge">
                            VOCATIONS
                        </div>
                    </div>
                    <div class="p-8 flex flex-col flex-grow">
                        <h3 class="font-display text-2xl text-gray-800 mb-2">Vocations</h3>
                        <p class="text-gray-600 text-sm mb-6 flex-grow">Et si Dieu t'appelait ? La vocation est une aventure. Viens te poser des questions.</p>
                        <div class="flex items-center justify-between border-t pt-4 mt-auto">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wide"><?= $counters[3] ?> événement(s)</span>
                            <span class="text-red-600 font-bold text-sm font-display">Découvrir</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="activites" class="py-24 bg-white section-spy">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="font-display text-5xl font-bold text-gray-800 mb-6">Nos activités</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto"></div>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-2xl p-6 fade-in-up border border-gray-100 card-hover group">
                    <div class="h-48 rounded-xl mb-6 overflow-hidden">
                        <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/DSC00736.jpeg" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h3 class="font-display text-xl text-red-600 mb-2">Louange</h3>
                    <p class="text-gray-600 text-sm">Chants et prière pour exprimer notre joie.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-6 fade-in-up border border-gray-100 card-hover group" style="transition-delay: 100ms">
                    <div class="h-48 rounded-xl mb-6 overflow-hidden">
                         <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/DSC00667.jpeg" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h3 class="font-display text-xl text-red-600 mb-2">Témoignages</h3>
                    <p class="text-gray-600 text-sm">Partage d'expériences de foi authentiques.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-6 fade-in-up border border-gray-100 card-hover group" style="transition-delay: 200ms">
                    <div class="h-48 rounded-xl mb-6 overflow-hidden">
                         <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/FaiK9Rc4T-GU9M5tXeLtUA.jpeg" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h3 class="font-display text-xl text-red-600 mb-2">Grands jeux</h3>
                    <p class="text-gray-600 text-sm">Activités ludiques pour créer des liens.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-6 fade-in-up border border-gray-100 card-hover group">
                    <div class="h-48 rounded-xl mb-6 overflow-hidden">
                         <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/8MQTKhaJSqC2ea0H9pNPlA.jpeg" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h3 class="font-display text-xl text-red-600 mb-2">Adoration</h3>
                    <p class="text-gray-600 text-sm">Silence et recueillement.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-6 fade-in-up border border-gray-100 card-hover group" style="transition-delay: 100ms">
                    <div class="h-48 rounded-xl mb-6 overflow-hidden">
                         <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/DSC00582.jpeg" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h3 class="font-display text-xl text-red-600 mb-2">Enseignements</h3>
                    <p class="text-gray-600 text-sm">Approfondir la foi et l'Évangile.</p>
                </div>
                <div class="bg-gray-50 rounded-2xl p-6 fade-in-up border border-gray-100 card-hover group" style="transition-delay: 200ms">
                    <div class="h-48 rounded-xl mb-6 overflow-hidden">
                         <div class="tilt-wrapper w-full h-full">
                            <img src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/A4aBSxjvQFqglyO0Vt9hew.jpeg" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <h3 class="font-display text-xl text-red-600 mb-2">Prière personnelle</h3>
                    <p class="text-gray-600 text-sm">Relation personnelle avec Dieu.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="photos" class="py-24 bg-gray-100 section-spy">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="font-display text-5xl font-bold text-gray-800 mb-6">Nos albums photos</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto"></div>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mb-12">
                <?php foreach($albums as $index => $album): 
                    $delay = $index * 100;
                    $bgImg = !empty($album['image_illustration']) ? htmlspecialchars($album['image_illustration']) : 'assets/default.jpg';
                    
                    // --- LOGIQUE REDIRECTION ---
                    $tag = "a";
                    $href = "#";
                    $target = "_self";
                    $onclick = "";
                    $btnLabel = "Voir l'album";

                    if ($album['type_media'] === 'upload') {
                        $href = "photo.php?id=" . $album['id'];
                        $btnLabel = "Voir la galerie";
                    } elseif ($album['type_media'] === 'youtube') {
                        $tag = "div";
                        $onclick = "openVideoModal('" . htmlspecialchars($album['lien_externe']) . "')";
                        $btnLabel = "Voir la vidéo";
                    } elseif ($album['type_media'] === 'drive') {
                        $href = htmlspecialchars($album['lien_externe']);
                        $target = "_blank";
                        $btnLabel = "Voir le Drive";
                    }
                ?>
                
                <<?= $tag ?> 
                    href="<?= $href ?>" 
                    target="<?= $target ?>" 
                    onclick="<?= $onclick ?>"
                    class="block bg-white rounded-2xl shadow-lg overflow-hidden card-hover fade-in-up cursor-pointer h-full group" 
                    style="transition-delay: <?= $delay ?>ms;">
                    
                    <div class="h-64 bg-gray-200 overflow-hidden relative">
                         <div class="tilt-wrapper w-full h-full">
                            <img src="<?= $bgImg ?>" alt="<?= htmlspecialchars($album['titre']) ?>" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="p-8">
                        <h3 class="font-display text-xl text-gray-800 mb-4 truncate group-hover:text-red-600 transition">
                            <?= htmlspecialchars($album['titre']) ?>
                        </h3>
                        <span class="text-red-600 font-bold text-sm border-b-2 border-red-100 pb-1 group-hover:border-red-600 transition font-display">
                            <?= $btnLabel ?>
                        </span>
                    </div>
                </<?= $tag ?>>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="photo.php" class="btn-custom btn-red shadow-lg">
                    Tous les albums
                </a>
            </div>
        </div>
    </section>

    <section id="contact" class="py-24 bg-white section-spy">
        <div class="max-w-4xl mx-auto px-4">
            <div class="text-center mb-16 fade-in-up">
                <h2 class="font-display text-5xl font-bold text-gray-800 mb-6">Contactez-nous</h2>
                <div class="w-24 h-1 bg-red-600 mx-auto mb-8"></div>
                <p class="font-regular text-xl text-gray-600 max-w-3xl mx-auto">
                    Une question ? Envie de nous rejoindre ? N'hésitez pas !
                </p>
            </div>

            <div class="bg-gray-50 rounded-2xl shadow-lg p-8 md:p-12 fade-in-up border border-gray-100">
                <?php if($msg_sent): ?>
                    <div class="text-center py-10">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-3xl text-green-600">✓</span>
                        </div>
                        <h3 class="font-medium text-2xl text-gray-800 mb-2">Message envoyé !</h3>
                        <p class="font-regular text-gray-600">Merci. Nous vous répondrons très rapidement.</p>
                        <button onclick="window.location.href='index.php'" class="mt-6 text-red-600 font-bold hover:underline">Envoyer un autre message</button>
                    </div>
                <?php else: ?>
                    <form method="POST" action="index.php#contact" class="space-y-6">
                        <?php if($msg_error): ?>
                            <div class="bg-red-100 text-red-700 p-3 rounded text-center mb-4"><?= $msg_error ?></div>
                        <?php endif; ?>
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block font-medium text-gray-700 mb-2">Nom complet</label>
                                <input type="text" id="name" name="name" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition bg-white">
                            </div>
                            <div>
                                <label for="email" class="block font-medium text-gray-700 mb-2">Votre email</label>
                                <input type="email" id="email" name="email" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition bg-white">
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="block font-medium text-gray-700 mb-2">Sujet</label>
                            <input type="text" id="subject" name="subject" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition bg-white">
                        </div>
                        <div>
                            <label for="message" class="block font-medium text-gray-700 mb-2">Votre message</label>
                            <textarea id="message" name="message" rows="6" required class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-transparent transition resize-vertical bg-white"></textarea>
                        </div>
                        <div class="text-center pt-4">
                            <button type="submit" name="contact_submit" class="btn-custom btn-red shadow-md">
                                Envoyer le message
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <div id="video-modal" class="video-modal hidden">
        <div class="relative w-[90%] max-w-4xl aspect-video bg-black rounded-xl overflow-hidden shadow-2xl">
            <button class="absolute -top-12 right-0 text-white text-4xl hover:text-red-500 focus:outline-none" onclick="closeVideoModal()">&times;</button>
            <iframe id="video-iframe" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
        </div>
    </div>

    <div id="registration-overlay" class="registration-overlay hidden">
        <div class="bg-red-600 p-4 flex justify-between items-center shadow-md">
            <button onclick="closeRegistration()" class="text-white font-bold flex items-center gap-2 hover:bg-white/10 px-3 py-1 rounded transition">
                Retour
            </button>
            <h2 class="font-display text-xl text-white tracking-wide" id="overlay-title">PaJe</h2>
            <div class="w-20"></div> 
        </div>
        <iframe id="registration-iframe" class="registration-iframe"></iframe>
    </div>

    <script>
        // --- TOGGLE VALEURS (Accordéon) ---
        function toggleValue(element) {
            const parent = element.parentElement;
            const content = parent.querySelector('.value-content');
            
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                parent.classList.remove('value-active');
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                parent.classList.add('value-active');
            }
        }

        // --- ANIMATIONS SCROLL (Fade In) ---
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));


        // --- DYNAMIC HEADER UNDERLINE (Scroll Spy) ---
        // Cette fonction s'active au scroll et souligne le lien du header correspondant à la section visible
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('.section-spy');
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                // On détecte si on est dans le tiers haut de la section
                if (scrollY >= (sectionTop - sectionHeight / 3)) {
                    current = section.getAttribute('id');
                }
            });

            // On suppose que le header contient des liens avec href="#sectionId"
            const navLinks = document.querySelectorAll('header a'); 
            navLinks.forEach(link => {
                link.classList.remove('nav-active');
                if (link.getAttribute('href').includes('#' + current) && current !== '') {
                    link.classList.add('nav-active');
                }
            });
        });

        // --- MODALES ---
        function openVideoModal(url) {
            let embedUrl = url;
            if (url.includes('youtu.be/')) embedUrl = 'https://www.youtube.com/embed/' + url.split('youtu.be/')[1].split('?')[0];
            else if (url.includes('youtube.com/watch?v=')) embedUrl = 'https://www.youtube.com/embed/' + url.split('v=')[1].split('&')[0];
            
            embedUrl += "?autoplay=1";
            document.getElementById('video-modal').style.display = 'flex';
            document.getElementById('video-iframe').src = embedUrl;
        }
        function closeVideoModal() {
            document.getElementById('video-modal').style.display = 'none';
            document.getElementById('video-iframe').src = '';
        }
        function openRegistration(url) {
            document.getElementById('registration-overlay').style.display = 'flex';
            document.getElementById('registration-iframe').src = url;
            document.getElementById('overlay-title').innerText = "Inscription";
            document.body.style.overflow = 'hidden';
        }
        function closeRegistration() {
            document.getElementById('registration-overlay').style.display = 'none';
            document.getElementById('registration-iframe').src = '';
            document.body.style.overflow = 'auto';
        }
        
        document.getElementById('video-modal').addEventListener('click', function(e) {
            if(e.target === this) closeVideoModal();
        });
    </script>
</body>
</html>