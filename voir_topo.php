<?php
session_start();
require_once 'db_connect.php';

// --- CONFIGURATION CHIFFREMENT (Doit être identique partout) ---
define('ENCRYPTION_KEY', 'MaCleSecretePaje2024!#'); 
define('ENCRYPTION_METHOD', 'aes-256-cbc');

function encrypt_note($data) {
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt_note($data) {
    if (!$data || $data == "") return "";
    $parts = explode('::', base64_decode($data), 2);
    if (count($parts) !== 2) return "";
    return openssl_decrypt($parts[0], ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $parts[1]);
}

// 1. Sécurité
if (!isset($_SESSION['anim_logged_in'])) { header("Location: login_animateur.php"); exit; }
$anim_id = $_SESSION['anim_id'];
$id = (int)$_GET['id'];

// --- 2. TRAITEMENT AJAX (Sauvegarde sans recharger la page) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_ajax'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action_ajax'] === 'save_note') {
        $note_chiffree = encrypt_note($_POST['note_text']);
        $stmt = $pdo->prepare("INSERT INTO notes_animateurs (animateur_id, ressource_id, contenu) 
                               VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE contenu = ?, date_maj = NOW()");
        $stmt->execute([$anim_id, $id, $note_chiffree, $note_chiffree]);
        echo json_encode(['status' => 'success']);
        exit;
    }

    if ($_POST['action_ajax'] === 'save_progress') {
        $progress = (int)$_POST['seconds'];
        $stmt = $pdo->prepare("INSERT INTO notes_animateurs (animateur_id, ressource_id, progression) 
                               VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE progression = ?");
        $stmt->execute([$anim_id, $id, $progress, $progress]);
        echo json_encode(['status' => 'success']);
        exit;
    }
}

// 3. Récupération Topo
$stmt = $pdo->prepare("SELECT * FROM ressources_animateurs WHERE id = ?");
$stmt->execute([$id]);
$res = $stmt->fetch();
if (!$res) exit("Contenu introuvable.");

// 4. Récupération Note et Progression
$stmtData = $pdo->prepare("SELECT contenu, progression FROM notes_animateurs WHERE animateur_id = ? AND ressource_id = ?");
$stmtData->execute([$anim_id, $id]);
$userData = $stmtData->fetch();

$current_note = decrypt_note($userData['contenu'] ?? "");
$start_at = $userData['progression'] ?? 0;

$is_audio = ($res['type_ressource'] == 'lien_audio' || $res['type_ressource'] == 'fichier_audio');
$audio_src = ($res['type_ressource'] == 'fichier_audio') ? $res['fichier_joint'] : $res['lien_externe'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($res['titre']) ?> - PaJe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'InsatiableDisplay'; src: url('https://raw.githubusercontent.com/Cyprdu/PaJe/main/police/InsatiableDisplay-BoldCondensed.ttf') format('truetype'); }
        .font-display { font-family: 'InsatiableDisplay', sans-serif; }
        .custom-range { height: 4px; border-radius: 2px; appearance: none; background: #334155; cursor: pointer; width: 100%; }
        .custom-range::-webkit-slider-thumb { appearance: none; width: 12px; height: 12px; border-radius: 50%; background: #2dd4bf; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen pb-32">

    <nav class="p-6 border-b border-white/10 flex justify-between items-center sticky top-0 bg-slate-900/80 backdrop-blur-md z-40">
        <a href="dashboard_animateur.php" class="text-slate-400 hover:text-teal-400 transition flex items-center gap-2 font-bold uppercase text-xs">
            <i class="fas fa-arrow-left"></i> Quitter le topo
        </a>
        <h1 class="font-display text-2xl text-white"><?= htmlspecialchars($res['titre']) ?></h1>
        <div id="global-save-status" class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
            <i class="fas fa-check-circle text-teal-500"></i> Synchronisé
        </div>
    </nav>

    <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-10 p-6 lg:p-10">
        
        <div class="flex-grow">
            <div class="bg-slate-800/50 rounded-[2.5rem] overflow-hidden border border-white/5 shadow-2xl">
                
                <?php if($res['type_ressource'] == 'youtube'): 
                    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $res['lien_externe'], $match);
                ?>
                    <div class="aspect-video" id="yt-player-container">
                        <div id="player"></div> </div>

                <?php elseif($is_audio): ?>
                    <div class="p-12 text-center">
                        <img src="<?= $res['cover'] ?>" class="w-72 h-72 mx-auto rounded-[3rem] shadow-2xl mb-8 object-cover border-4 border-teal-500/20" alt="">
                        <h2 class="text-4xl font-bold mb-2"><?= $res['titre'] ?></h2>
                        <p class="text-teal-400 font-medium mb-10">Par <?= $res['auteur'] ?></p>
                        <div class="flex justify-center gap-6 text-3xl">
                            <i class="fas fa-undo-alt text-slate-500 cursor-pointer hover:text-white" onclick="seek(-10)"></i>
                            <i class="fas fa-play-circle text-teal-500 hover:scale-110 transition cursor-pointer" id="btn-play-center" onclick="togglePlay()"></i>
                            <i class="fas fa-redo-alt text-slate-500 cursor-pointer hover:text-white" onclick="seek(10)"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="lg:w-96 flex-shrink-0">
            <div class="bg-white/5 rounded-[2rem] p-8 border border-white/10 sticky top-28">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fas fa-lock text-teal-400"></i>
                    <h3 class="font-display text-2xl">Notes Personnelles</h3>
                </div>
                <p class="text-[10px] text-slate-500 mb-6 uppercase font-bold tracking-tighter">Chiffrement AES-256 actif</p>
                
                <textarea id="note_area" rows="12" class="w-full bg-black/20 border border-white/5 rounded-2xl p-4 text-sm focus:ring-teal-500 focus:border-teal-500 transition mb-4 text-slate-300" placeholder="Tes notes ici..."><?= htmlspecialchars($current_note) ?></textarea>
                
                <button onclick="saveNote()" id="btn-save-note" class="w-full bg-teal-600 text-white font-bold py-3 rounded-xl hover:bg-teal-700 transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer les notes
                </button>
            </div>
        </div>
    </div>

    <?php if($is_audio): ?>
    <div class="fixed bottom-0 left-0 right-0 bg-slate-800/95 backdrop-blur-xl border-t border-white/10 p-4 z-50 flex flex-col md:flex-row items-center gap-4 px-8 shadow-2xl">
        <div class="flex items-center gap-4 w-full md:w-1/4">
            <img src="<?= $res['cover'] ?>" class="w-14 h-14 rounded-xl object-cover" alt="">
            <div class="overflow-hidden">
                <h4 class="font-bold text-sm truncate"><?= $res['titre'] ?></h4>
                <p class="text-xs text-slate-400 truncate"><?= $res['auteur'] ?></p>
            </div>
        </div>
        <div class="flex flex-col items-center gap-1 w-full md:w-2/4">
            <div class="flex items-center gap-6 text-xl mb-1">
                <i class="fas fa-step-backward text-slate-500 hover:text-white cursor-pointer text-sm" onclick="seek(-10)"></i>
                <i class="fas fa-play-circle text-3xl text-white cursor-pointer" id="btn-play-sticky" onclick="togglePlay()"></i>
                <i class="fas fa-step-forward text-slate-500 hover:text-white cursor-pointer text-sm" onclick="seek(10)"></i>
            </div>
            <div class="flex items-center gap-3 w-full max-w-xl">
                <span id="time-current" class="text-[10px] font-bold text-slate-500">0:00</span>
                <input type="range" id="progress-bar" value="0" class="custom-range">
                <span id="time-total" class="text-[10px] font-bold text-slate-500">0:00</span>
            </div>
        </div>
        <div class="hidden md:flex items-center justify-end gap-3 w-1/4 text-slate-500">
            <i class="fas fa-volume-up"></i>
            <input type="range" class="w-20 custom-range" oninput="audio.volume = this.value/100" value="100">
        </div>
        <audio id="main-audio" src="<?= $audio_src ?>"></audio>
    </div>
    <?php endif; ?>

    <script>
        // --- LOGIQUE COMMUNE & AJAX ---
        const startAt = <?= $start_at ?>;
        const statusEl = document.getElementById('global-save-status');

        function updateStatus(isSaving) {
            statusEl.innerHTML = isSaving ? '<i class="fas fa-sync fa-spin text-orange-400"></i> Synchronisation...' : '<i class="fas fa-check-circle text-teal-500"></i> Synchronisé';
        }

        async function saveNote() {
            const noteText = document.getElementById('note_area').value;
            const btn = document.getElementById('btn-save-note');
            
            updateStatus(true);
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chiffrement...';

            let formData = new FormData();
            formData.append('action_ajax', 'save_note');
            formData.append('note_text', noteText);

            await fetch(window.location.href, { method: 'POST', body: formData });
            
            updateStatus(false);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Enregistrer les notes';
        }

        async function saveProgress(seconds) {
            let formData = new FormData();
            formData.append('action_ajax', 'save_progress');
            formData.append('seconds', Math.floor(seconds));
            fetch(window.location.href, { method: 'POST', body: formData });
        }

        // --- LOGIQUE AUDIO ---
        <?php if($is_audio): ?>
        const audio = document.getElementById('main-audio');
        const playBtnS = document.getElementById('btn-play-sticky');
        const playBtnC = document.getElementById('btn-play-center');
        const progBar = document.getElementById('progress-bar');

        audio.addEventListener('loadedmetadata', () => {
            audio.currentTime = startAt;
            document.getElementById('time-total').innerText = formatTime(audio.duration);
        });

        function togglePlay() {
            if (audio.paused) {
                audio.play();
                playBtnS.classList.replace('fa-play-circle', 'fa-pause-circle');
                if(playBtnC) playBtnC.classList.replace('fa-play-circle', 'fa-pause-circle');
            } else {
                audio.pause();
                playBtnS.classList.replace('fa-pause-circle', 'fa-play-circle');
                if(playBtnC) playBtnC.classList.replace('fa-pause-circle', 'fa-play-circle');
                saveProgress(audio.currentTime); // Sauvegarde quand on met pause
            }
        }

        function seek(val) { audio.currentTime += val; }

        audio.addEventListener('timeupdate', () => {
            progBar.value = (audio.currentTime / audio.duration) * 100 || 0;
            document.getElementById('time-current').innerText = formatTime(audio.currentTime);
            // Sauvegarde auto toutes les 10 secondes
            if (Math.floor(audio.currentTime) % 10 === 0) saveProgress(audio.currentTime);
        });

        progBar.addEventListener('input', () => { audio.currentTime = (progBar.value / 100) * audio.duration; });

        function formatTime(s) {
            const m = Math.floor(s / 60);
            const sec = Math.floor(s % 60);
            return m + ":" + (sec < 10 ? "0" + sec : sec);
        }
        <?php endif; ?>

        // --- LOGIQUE YOUTUBE (API) ---
        <?php if($res['type_ressource'] == 'youtube'): ?>
        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('player', {
                height: '100%', width: '100%',
                videoId: '<?= $match[1] ?>',
                playerVars: { 'start': startAt, 'rel': 0 },
                events: { 'onStateChange': onPlayerStateChange }
            });
        }

        function onPlayerStateChange(event) {
            if (event.data == YT.PlayerState.PAUSED) {
                saveProgress(player.getCurrentTime());
            }
        }

        setInterval(() => {
            if (player && player.getPlayerState() == YT.PlayerState.PLAYING) {
                saveProgress(player.getCurrentTime());
            }
        }, 10000);
        <?php endif; ?>
    </script>
</body>
</html>