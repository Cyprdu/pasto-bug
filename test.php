<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
// index.php
session_start();
require_once 'db_connect.php';

function formatDateFr($dateStr) {
    setlocale(LC_TIME, 'fr_FR.UTF8', 'fra');
    $date = new DateTime($dateStr);
    $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
    return $formatter->format($date);
}

$msg_sent = false;
$msg_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
    $nom = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $sujet = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);
    if (!empty($nom) && !empty($email) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (nom_complet, email, sujet, message) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$nom, $email, $sujet, $message])) { $msg_sent = true; }
        else { $msg_error = "Erreur lors de l'enregistrement."; }
    }
}

$stmt = $pdo->prepare("SELECT * FROM camps WHERE supprime = 0 AND date_debut >= CURDATE() ORDER BY date_debut ASC LIMIT 1");
$stmt->execute();
$nextEvent = $stmt->fetch();

$counters = [1 => 0, 2 => 0, 3 => 0];
$stmt = $pdo->query("SELECT category_id, COUNT(*) as total FROM camp_category cc JOIN camps c ON c.id = cc.camp_id WHERE c.supprime = 0 AND c.date_debut >= CURDATE() GROUP BY category_id");
while ($row = $stmt->fetch()) { $counters[$row['category_id']] = $row['total']; }

$stmt = $pdo->prepare("SELECT * FROM albums WHERE supprime = 0 ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$albums = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PaJe — Pastorale des Jeunes</title>
    <link rel="icon" type="image/png" href="https://github.com/Cyprdu/PaJe/blob/main/img/favico.png?raw=true">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300;1,400;1,600&family=Bebas+Neue&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        /* =============================================
           DESIGN SYSTEM — PaJe Editorial 2025
        ============================================= */
        :root {
            --cream:       #F5F0E8;
            --cream-dark:  #EDE7D9;
            --scarlet:     #C0201E;
            --scarlet-dark:#9A1614;
            --gold:        #B8962E;
            --gold-light:  #D4AF50;
            --ink:         #1A1209;
            --ink-soft:    #3D3020;
            --mist:        #8A7F72;
            --white:       #FFFDF9;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--cream);
            color: var(--ink);
            overflow-x: hidden;
        }

        /* === TYPOGRAPHY === */
        .f-display {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.04em;
        }
        .f-serif {
            font-family: 'Cormorant Garamond', serif;
        }
        .f-body {
            font-family: 'DM Sans', sans-serif;
        }

        /* === CURSOR CUSTOM === */
        body { cursor: none; }
        #cursor {
            width: 10px; height: 10px;
            background: var(--scarlet);
            border-radius: 50%;
            position: fixed;
            top: 0; left: 0;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease;
            mix-blend-mode: multiply;
        }
        #cursor-ring {
            width: 38px; height: 38px;
            border: 1.5px solid var(--scarlet);
            border-radius: 50%;
            position: fixed;
            top: 0; left: 0;
            pointer-events: none;
            z-index: 9998;
            transform: translate(-50%, -50%);
            transition: left 0.15s ease, top 0.15s ease, width 0.3s ease, height 0.3s ease, border-color 0.3s;
        }
        body:has(a:hover) #cursor-ring,
        body:has(button:hover) #cursor-ring { width: 60px; height: 60px; border-color: var(--gold); }

        /* === NAVIGATION === */
        nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 3rem;
            height: 72px;
            transition: background 0.4s, box-shadow 0.4s;
        }
        nav.scrolled {
            background: rgba(245, 240, 232, 0.97);
            backdrop-filter: blur(12px);
            box-shadow: 0 1px 0 rgba(26,18,9,0.08);
        }
        .nav-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 2.2rem;
            color: var(--scarlet);
            text-decoration: none;
            letter-spacing: 0.06em;
            line-height: 1;
        }
        .nav-logo span {
            display: block;
            font-family: 'Cormorant Garamond', serif;
            font-size: 0.5rem;
            letter-spacing: 0.25em;
            color: var(--mist);
            text-transform: uppercase;
            font-weight: 400;
            margin-top: 2px;
        }
        .nav-links {
            display: flex;
            gap: 2.5rem;
            list-style: none;
        }
        .nav-links a {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.05rem;
            font-style: italic;
            color: var(--ink-soft);
            text-decoration: none;
            position: relative;
            transition: color 0.3s;
        }
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -3px; left: 0;
            width: 0; height: 1px;
            background: var(--scarlet);
            transition: width 0.35s ease;
        }
        .nav-links a:hover { color: var(--scarlet); }
        .nav-links a:hover::after,
        .nav-links a.active::after { width: 100%; }
        .nav-links a.active { color: var(--scarlet); }

        .nav-cta {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.08em;
            font-size: 0.95rem;
            padding: 0.55rem 1.6rem;
            background: var(--scarlet);
            color: var(--white);
            text-decoration: none;
            border: 1.5px solid var(--scarlet);
            transition: background 0.3s, color 0.3s;
        }
        .nav-cta:hover { background: transparent; color: var(--scarlet); }

        .burger { display: none; background: none; border: none; cursor: pointer; flex-direction: column; gap: 5px; padding: 4px; }
        .burger span { display: block; width: 24px; height: 1.5px; background: var(--ink); transition: all 0.3s; }

        /* === HERO === */
        #accueil {
            position: relative;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            overflow: hidden;
        }
        .hero-left {
            background: var(--ink);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 6rem 4rem 5rem 6rem;
            position: relative;
            z-index: 2;
        }
        .hero-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(160deg, transparent 40%, rgba(192,32,30,0.15) 100%);
        }
        .hero-eyebrow {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 1rem;
            letter-spacing: 0.2em;
            color: var(--gold-light);
            text-transform: uppercase;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .hero-eyebrow::before {
            content: '';
            display: block;
            width: 40px;
            height: 1px;
            background: var(--gold);
        }
        .hero-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(6rem, 12vw, 11rem);
            line-height: 0.9;
            color: var(--white);
            letter-spacing: 0.02em;
            position: relative;
            z-index: 1;
        }
        .hero-title-outline {
            -webkit-text-stroke: 1.5px var(--scarlet);
            color: transparent;
        }
        .hero-subtitle {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            font-style: italic;
            color: rgba(255,253,249,0.65);
            margin-top: 1.5rem;
            margin-bottom: 3rem;
            line-height: 1.5;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }
        .hero-btns {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        .btn-primary {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 1rem;
            padding: 0.9rem 2.4rem;
            background: var(--scarlet);
            color: var(--white);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            transition: background 0.3s, transform 0.3s;
            border: 1.5px solid var(--scarlet);
        }
        .btn-primary:hover { background: var(--scarlet-dark); transform: translateY(-2px); }
        .btn-primary .arrow { transition: transform 0.3s; }
        .btn-primary:hover .arrow { transform: translateX(4px); }
        .btn-ghost {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 1rem;
            padding: 0.9rem 2.4rem;
            background: transparent;
            color: var(--white);
            text-decoration: none;
            border: 1.5px solid rgba(255,253,249,0.3);
            transition: border-color 0.3s, color 0.3s;
        }
        .btn-ghost:hover { border-color: var(--white); }

        .hero-right {
            position: relative;
            overflow: hidden;
        }
        .hero-right img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: saturate(0.8) contrast(1.05);
            transition: transform 8s ease;
        }
        .hero-right:hover img { transform: scale(1.04); }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(26,18,9,0.45) 0%, transparent 40%),
                        linear-gradient(to top, rgba(26,18,9,0.3) 0%, transparent 50%);
        }
        .hero-scroll-hint {
            position: absolute;
            bottom: 3rem;
            left: 6rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 0.85rem;
            letter-spacing: 0.12em;
            color: var(--mist);
            text-transform: uppercase;
            z-index: 10;
            animation: fadeup 1.2s ease 1.5s both;
        }
        .scroll-line {
            width: 1px;
            height: 50px;
            background: linear-gradient(to bottom, var(--gold), transparent);
            animation: scrolldown 2s ease-in-out infinite;
        }
        @keyframes scrolldown {
            0%, 100% { transform: scaleY(1); opacity: 1; }
            50% { transform: scaleY(0.5); opacity: 0.5; }
        }

        /* === MARQUEE BAND === */
        .marquee-band {
            background: var(--scarlet);
            overflow: hidden;
            white-space: nowrap;
            padding: 0.7rem 0;
            border-top: 1px solid var(--scarlet-dark);
            border-bottom: 1px solid var(--scarlet-dark);
        }
        .marquee-inner {
            display: inline-flex;
            animation: marquee 22s linear infinite;
        }
        .marquee-inner span {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1rem;
            letter-spacing: 0.15em;
            color: var(--white);
            padding: 0 2.5rem;
        }
        .marquee-inner .dot {
            color: var(--gold-light);
            font-size: 1.4rem;
            line-height: 1;
        }
        @keyframes marquee {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        /* === SECTION BASE === */
        section { position: relative; }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 3rem;
        }
        .section-pad { padding: 7rem 0; }

        /* === SECTION LABEL === */
        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 0.9rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 1.2rem;
        }
        .section-label::before {
            content: '';
            display: block;
            width: 32px;
            height: 1px;
            background: var(--gold);
        }
        .section-heading {
            font-family: 'Bebas Neue', sans-serif;
            font-size: clamp(3rem, 6vw, 5.5rem);
            line-height: 0.95;
            letter-spacing: 0.03em;
            color: var(--ink);
        }
        .section-heading em {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-weight: 300;
            color: var(--scarlet);
        }

        /* === QUI SOMMES NOUS === */
        #qui-sommes-nous { background: var(--cream); }
        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6rem;
            align-items: start;
            margin-top: 5rem;
        }
        .about-text p {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.25rem;
            line-height: 1.8;
            color: var(--ink-soft);
            margin-bottom: 1.5rem;
        }
        .about-text .intro-big {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        .btn-secondary {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 0.95rem;
            padding: 0.8rem 2rem;
            background: transparent;
            color: var(--scarlet);
            text-decoration: none;
            border: 1.5px solid var(--scarlet);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.3s, color 0.3s;
            margin-top: 1.5rem;
        }
        .btn-secondary:hover { background: var(--scarlet); color: var(--white); }

        /* Accordion valeurs */
        .valeurs-panel {
            border-top: 1px solid rgba(26,18,9,0.1);
        }
        .valeur-item {
            border-bottom: 1px solid rgba(26,18,9,0.1);
        }
        .valeur-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.2rem 0;
            cursor: pointer;
            transition: color 0.2s;
        }
        .valeur-header:hover { color: var(--scarlet); }
        .valeur-header h4 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem;
            font-weight: 600;
        }
        .valeur-icon {
            width: 28px;
            height: 28px;
            border: 1px solid currentColor;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.3s, background 0.3s, color 0.3s;
            font-size: 1rem;
            color: var(--mist);
        }
        .valeur-item.open .valeur-icon {
            transform: rotate(45deg);
            background: var(--scarlet);
            color: white;
            border-color: var(--scarlet);
        }
        .valeur-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }
        .valeur-body p {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            color: var(--mist);
            padding-bottom: 1.2rem;
            line-height: 1.7;
        }

        /* === PROCHAIN EVENEMENT === */
        .next-event-wrap {
            margin-top: 6rem;
            background: var(--ink);
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            overflow: hidden;
            min-height: 420px;
        }
        .next-event-img {
            position: relative;
            overflow: hidden;
        }
        .next-event-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: saturate(0.7);
            transition: transform 0.6s ease;
        }
        .next-event-wrap:hover .next-event-img img { transform: scale(1.04); }
        .next-event-img::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, transparent 60%, var(--ink) 100%);
        }
        .next-event-content {
            padding: 3.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .event-tag {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--scarlet);
            color: var(--white);
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 0.85rem;
            padding: 0.3rem 0.9rem;
            margin-bottom: 1.2rem;
            width: fit-content;
        }
        .next-event-content .event-date {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            color: var(--gold-light);
            font-size: 1rem;
            letter-spacing: 0.08em;
            margin-bottom: 0.8rem;
        }
        .next-event-content h3 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3rem;
            letter-spacing: 0.03em;
            color: var(--white);
            line-height: 1;
            margin-bottom: 0.6rem;
        }
        .next-event-content .event-location {
            font-size: 0.9rem;
            color: var(--mist);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .next-event-content .event-desc {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            color: rgba(255,253,249,0.6);
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .event-actions { display: flex; gap: 0.8rem; flex-wrap: wrap; }
        .btn-gold {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 0.95rem;
            padding: 0.75rem 1.8rem;
            background: var(--gold);
            color: var(--ink);
            text-decoration: none;
            border: 1.5px solid var(--gold);
            cursor: pointer;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }
        .btn-gold:hover { background: transparent; color: var(--gold-light); border-color: var(--gold-light); }
        .btn-ghost-sm {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 0.95rem;
            padding: 0.75rem 1.8rem;
            background: transparent;
            color: rgba(255,253,249,0.7);
            text-decoration: none;
            border: 1.5px solid rgba(255,253,249,0.25);
            cursor: pointer;
            transition: border-color 0.3s, color 0.3s;
        }
        .btn-ghost-sm:hover { border-color: var(--white); color: var(--white); }

        /* === CAMPS === */
        #camps { background: var(--cream-dark); }
        .camps-intro {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 2rem;
            margin-bottom: 4rem;
        }
        .camps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5px;
            background: var(--mist);
        }
        .camp-card {
            background: var(--cream);
            overflow: hidden;
            position: relative;
            cursor: pointer;
            group: true;
            display: flex;
            flex-direction: column;
        }
        .camp-img {
            height: 280px;
            overflow: hidden;
            position: relative;
        }
        .camp-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.7s cubic-bezier(0.25,0.46,0.45,0.94);
            filter: saturate(0.85);
        }
        .camp-card:hover .camp-img img { transform: scale(1.06); }
        .camp-img-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(26,18,9,0.65) 0%, transparent 60%);
        }
        .camp-age {
            position: absolute;
            top: 1.2rem;
            right: 1.2rem;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 0.12em;
            background: var(--scarlet);
            color: var(--white);
            padding: 0.3rem 0.8rem;
        }
        .camp-body {
            padding: 2rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            border: 1px solid transparent;
            border-top: none;
            transition: border-color 0.3s;
        }
        .camp-card:hover .camp-body { border-color: rgba(192,32,30,0.2); }
        .camp-body h3 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.8rem;
            letter-spacing: 0.04em;
            color: var(--ink);
            margin-bottom: 0.6rem;
        }
        .camp-body p {
            font-size: 0.9rem;
            color: var(--mist);
            line-height: 1.6;
            flex-grow: 1;
            margin-bottom: 1.5rem;
        }
        .camp-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(26,18,9,0.08);
        }
        .camp-count {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--mist);
        }
        .camp-link {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 0.08em;
            color: var(--scarlet);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: gap 0.3s;
        }
        .camp-card:hover .camp-link { gap: 0.8rem; }

        /* === ACTIVITIES === */
        #activites { background: var(--ink); }
        #activites .section-heading { color: var(--white); }
        #activites .section-label { color: var(--gold-light); }
        #activites .section-label::before { background: var(--gold-light); }

        .activities-layout {
            margin-top: 4rem;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 4rem;
            align-items: center;
        }
        .activities-intro-text {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.3rem;
            line-height: 1.75;
            color: rgba(255,253,249,0.55);
            font-style: italic;
        }
        .activities-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1px;
            background: rgba(255,253,249,0.06);
        }
        .activity-item {
            background: rgba(255,253,249,0.03);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: background 0.3s;
        }
        .activity-item:hover { background: rgba(255,253,249,0.07); }
        .activity-img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            display: block;
            margin-bottom: 1.2rem;
            filter: saturate(0.6) brightness(0.85);
            transition: filter 0.4s, transform 0.5s;
        }
        .activity-item:hover .activity-img { filter: saturate(0.9) brightness(1); transform: scale(1.02); }
        .activity-num {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3rem;
            letter-spacing: -0.02em;
            color: rgba(255,253,249,0.06);
            position: absolute;
            top: 0.5rem;
            right: 1rem;
            line-height: 1;
            pointer-events: none;
        }
        .activity-item h4 {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.4rem;
            letter-spacing: 0.05em;
            color: var(--white);
            margin-bottom: 0.4rem;
        }
        .activity-item p { font-size: 0.88rem; color: var(--mist); line-height: 1.6; }

        /* === PHOTOS === */
        #photos { background: var(--cream); }
        .photos-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr;
            gap: 1.2rem;
            margin-top: 4rem;
            margin-bottom: 3rem;
        }
        .photo-card {
            position: relative;
            overflow: hidden;
            display: block;
            text-decoration: none;
            cursor: pointer;
        }
        .photo-card:first-child { grid-row: span 2; }
        .photo-card img {
            width: 100%;
            height: 100%;
            min-height: 200px;
            object-fit: cover;
            display: block;
            transition: transform 0.65s cubic-bezier(0.25,0.46,0.45,0.94), filter 0.4s;
            filter: saturate(0.8);
        }
        .photo-card:hover img { transform: scale(1.07); filter: saturate(1); }
        .photo-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(26,18,9,0.8) 0%, transparent 55%);
            opacity: 0;
            transition: opacity 0.35s;
            display: flex;
            align-items: flex-end;
            padding: 1.5rem;
        }
        .photo-card:hover .photo-card-overlay { opacity: 1; }
        .photo-card-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.3rem;
            letter-spacing: 0.05em;
            color: var(--white);
        }
        .photo-card-sub {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 0.85rem;
            color: var(--gold-light);
            display: block;
            margin-top: 0.2rem;
        }
        .photos-cta { text-align: center; }

        /* === CONTACT === */
        #contact { background: var(--cream-dark); }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 5rem;
            margin-top: 4rem;
        }
        .contact-info h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.7rem;
            font-style: italic;
            color: var(--ink);
            margin-bottom: 2rem;
            line-height: 1.4;
        }
        .contact-detail {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .contact-detail .icon {
            width: 36px;
            height: 36px;
            background: var(--scarlet);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .contact-detail .icon svg { color: var(--white); }
        .contact-detail-text strong {
            display: block;
            font-size: 0.75rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--mist);
            margin-bottom: 0.2rem;
        }
        .contact-detail-text span, .contact-detail-text a {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            color: var(--ink-soft);
            text-decoration: none;
            transition: color 0.2s;
        }
        .contact-detail-text a:hover { color: var(--scarlet); }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2.5rem;
        }
        .social-link {
            width: 44px;
            height: 44px;
            border: 1px solid rgba(26,18,9,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--mist);
            text-decoration: none;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }
        .social-link:hover { background: var(--scarlet); border-color: var(--scarlet); color: var(--white); }

        /* Form */
        .contact-form-wrap { }
        .form-group {
            margin-bottom: 1.4rem;
        }
        .form-group label {
            display: block;
            font-size: 0.75rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--mist);
            margin-bottom: 0.5rem;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.9rem 1.1rem;
            border: 1px solid rgba(26,18,9,0.15);
            background: var(--white);
            color: var(--ink);
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            outline: none;
            transition: border-color 0.3s;
            resize: none;
        }
        .form-group input:focus,
        .form-group textarea:focus { border-color: var(--scarlet); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn-submit {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 1rem;
            padding: 1rem 2.8rem;
            background: var(--scarlet);
            color: var(--white);
            border: 1.5px solid var(--scarlet);
            cursor: pointer;
            transition: background 0.3s, color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            margin-top: 0.5rem;
        }
        .btn-submit:hover { background: var(--scarlet-dark); }
        .success-msg {
            text-align: center;
            padding: 3rem;
            background: var(--white);
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: var(--scarlet);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .success-msg h3 { font-family: 'Bebas Neue', sans-serif; font-size: 2rem; margin-bottom: 0.5rem; }
        .success-msg p { font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; color: var(--mist); font-style: italic; }

        /* === FOOTER === */
        footer {
            background: var(--ink);
            padding: 5rem 0 2rem;
        }
        .footer-top {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr;
            gap: 4rem;
            padding-bottom: 4rem;
            border-bottom: 1px solid rgba(255,253,249,0.08);
        }
        .footer-brand .f-logo {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 3rem;
            color: var(--scarlet);
            letter-spacing: 0.06em;
            line-height: 1;
            display: block;
            margin-bottom: 0.3rem;
        }
        .footer-brand .f-tagline {
            font-family: 'Cormorant Garamond', serif;
            font-style: italic;
            font-size: 1rem;
            color: rgba(255,253,249,0.4);
            display: block;
            margin-bottom: 1.5rem;
        }
        .footer-brand p {
            font-size: 0.9rem;
            color: rgba(255,253,249,0.35);
            line-height: 1.7;
            max-width: 280px;
        }
        footer h4 {
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            font-size: 1rem;
            color: var(--white);
            margin-bottom: 1.5rem;
        }
        footer ul { list-style: none; }
        footer ul li { margin-bottom: 0.7rem; }
        footer ul a {
            font-size: 0.9rem;
            color: rgba(255,253,249,0.45);
            text-decoration: none;
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.05rem;
            transition: color 0.2s;
        }
        footer ul a:hover { color: var(--gold-light); }
        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            gap: 0.8rem;
            margin-bottom: 0.9rem;
        }
        .footer-contact-item svg { color: var(--scarlet); flex-shrink: 0; margin-top: 2px; }
        .footer-contact-item span, .footer-contact-item a {
            font-size: 0.88rem;
            color: rgba(255,253,249,0.45);
            text-decoration: none;
            transition: color 0.2s;
            line-height: 1.5;
        }
        .footer-contact-item a:hover { color: var(--white); }
        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 2rem;
            gap: 1rem;
        }
        .footer-bottom p { font-size: 0.8rem; color: rgba(255,253,249,0.25); }
        .footer-admin {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,253,249,0.2);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-admin:hover { color: var(--gold-light); }

        /* === MODALS === */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(26,18,9,0.92);
            backdrop-filter: blur(6px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-backdrop.open { display: flex; }
        .modal-video-inner {
            width: 90%;
            max-width: 900px;
            aspect-ratio: 16/9;
            background: #000;
            position: relative;
        }
        .modal-close {
            position: absolute;
            top: -2.5rem;
            right: 0;
            background: none;
            border: none;
            color: var(--white);
            font-size: 1.5rem;
            cursor: pointer;
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.1em;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        .modal-close:hover { opacity: 1; }
        .modal-video-inner iframe { width: 100%; height: 100%; border: none; }

        .regist-overlay {
            position: fixed;
            inset: 0;
            z-index: 2000;
            display: none;
            flex-direction: column;
            background: var(--white);
        }
        .regist-overlay.open { display: flex; }
        .regist-bar {
            background: var(--scarlet);
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 1rem;
            justify-content: space-between;
        }
        .regist-bar button {
            background: none;
            border: none;
            color: var(--white);
            font-family: 'Bebas Neue', sans-serif;
            letter-spacing: 0.08em;
            font-size: 0.95rem;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .regist-bar button:hover { opacity: 1; }
        .regist-bar span {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 1.2rem;
            color: var(--white);
            letter-spacing: 0.08em;
        }
        .regist-iframe { flex: 1; border: none; }

        /* === ANIMATIONS === */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        @keyframes fadeup {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* === MOBILE === */
        @media (max-width: 900px) {
            nav { padding: 0 1.5rem; }
            .nav-links, .nav-cta { display: none; }
            .burger { display: flex; }
            .mobile-nav {
                position: fixed;
                inset: 0;
                background: var(--ink);
                z-index: 999;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2rem;
                transform: translateX(100%);
                transition: transform 0.4s ease;
            }
            .mobile-nav.open { transform: translateX(0); }
            .mobile-nav a {
                font-family: 'Bebas Neue', sans-serif;
                font-size: 3rem;
                letter-spacing: 0.04em;
                color: var(--white);
                text-decoration: none;
                transition: color 0.2s;
            }
            .mobile-nav a:hover { color: var(--scarlet); }
            #accueil { grid-template-columns: 1fr; }
            .hero-right { height: 55vw; }
            .hero-left { padding: 8rem 2rem 4rem; }
            .hero-title { font-size: 5rem; }
            .hero-scroll-hint { display: none; }
            .about-grid, .contact-grid, .activities-layout { grid-template-columns: 1fr; gap: 3rem; }
            .next-event-wrap { grid-template-columns: 1fr; }
            .next-event-img { height: 260px; }
            .camps-grid { grid-template-columns: 1fr; gap: 0; }
            .activities-grid { grid-template-columns: 1fr; }
            .photos-grid { grid-template-columns: 1fr; }
            .photo-card:first-child { grid-row: span 1; }
            .footer-top { grid-template-columns: 1fr; gap: 2rem; }
            .footer-bottom { flex-direction: column; align-items: flex-start; }
            .container { padding: 0 1.5rem; }
            .camps-intro { flex-direction: column; align-items: flex-start; }
            .form-row { grid-template-columns: 1fr; }
            body { cursor: auto; }
            #cursor, #cursor-ring { display: none; }
        }
    </style>
</head>
<body>

<!-- Custom cursor -->
<div id="cursor"></div>
<div id="cursor-ring"></div>

<!-- ===================== NAVIGATION ===================== -->
<nav id="navbar">
    <a href="index.php" class="nav-logo">
        PaJe
        <span>Pastorale des Jeunes</span>
    </a>

    <ul class="nav-links">
        <li><a href="#accueil" class="active">Accueil</a></li>
        <li><a href="#qui-sommes-nous">Qui sommes-nous</a></li>
        <li><a href="#camps">Nos camps</a></li>
        <li><a href="#activites">Activités</a></li>
        <li><a href="#photos">Photos</a></li>
        <li><a href="#contact">Contact</a></li>
        <?php if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
        <li><a href="dashboard_admin.php" style="color:var(--gold)">Admin</a></li>
        <?php endif; ?>
    </ul>

    <a href="#camps" class="nav-cta">Nos camps →</a>
    <button class="burger" id="burger" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>
</nav>

<!-- Mobile Nav -->
<div class="mobile-nav" id="mobileNav">
    <a href="#accueil" onclick="closeMobile()">Accueil</a>
    <a href="#qui-sommes-nous" onclick="closeMobile()">Qui sommes-nous</a>
    <a href="#camps" onclick="closeMobile()">Nos camps</a>
    <a href="#activites" onclick="closeMobile()">Activités</a>
    <a href="#photos" onclick="closeMobile()">Photos</a>
    <a href="#contact" onclick="closeMobile()">Contact</a>
</div>

<!-- ===================== HERO ===================== -->
<section id="accueil">
    <div class="hero-left">
        <p class="hero-eyebrow">Diocèse de Belley-Ars</p>
        <h1 class="hero-title">
            PA<span class="hero-title-outline">JE</span>
        </h1>
        <p class="hero-subtitle">
            Pastorale des Jeunes — Viens vivre des moments inoubliables de foi, de partage et d'amitié.
        </p>
        <div class="hero-btns">
            <a href="#camps" class="btn-primary">Découvrir nos camps <span class="arrow">→</span></a>
            <a href="#qui-sommes-nous" class="btn-ghost">En savoir plus</a>
        </div>
    </div>
    <div class="hero-right">
        <img src="uploads/cover.png" alt="PaJe - Pastorale des Jeunes" loading="eager">
        <div class="hero-overlay"></div>
    </div>
    <div class="hero-scroll-hint">
        <div class="scroll-line"></div>
        Défiler
    </div>
</section>

<!-- Marquee -->
<div class="marquee-band" aria-hidden="true">
    <div class="marquee-inner">
        <?php for($i=0;$i<2;$i++): ?>
        <span>Foi</span><span class="dot">·</span>
        <span>Partage</span><span class="dot">·</span>
        <span>Fraternité</span><span class="dot">·</span>
        <span>Louange</span><span class="dot">·</span>
        <span>Joie</span><span class="dot">·</span>
        <span>Rencontre</span><span class="dot">·</span>
        <span>Prière</span><span class="dot">·</span>
        <span>Vocation</span><span class="dot">·</span>
        <span>Aventure</span><span class="dot">·</span>
        <?php endfor; ?>
    </div>
</div>

<!-- ===================== QUI SOMMES-NOUS ===================== -->
<section id="qui-sommes-nous" class="section-pad">
    <div class="container">
        <div class="reveal">
            <p class="section-label">Notre identité</p>
            <h2 class="section-heading">Qui<br><em>sommes-nous ?</em></h2>
        </div>

        <div class="about-grid">
            <div class="about-text reveal">
                <p class="intro-big">La PaJe est la Pastorale des Jeunes et des vocations du Diocèse de Belley-Ars.</p>
                <p>Notre mission est d'accompagner les jeunes de 6 à 35 ans dans leur découverte et leur approfondissement de la foi chrétienne.</p>
                <p>À travers nos camps, week-ends et journées de retraite, nous proposons aux jeunes de vivre des expériences authentiques de rencontre avec le Christ.</p>
                <a href="qui_sommes_nous.php" class="btn-secondary">En savoir plus →</a>
            </div>

            <div class="reveal">
                <div class="valeurs-panel">
                    <div class="valeur-item open">
                        <div class="valeur-header" onclick="toggleValeur(this)">
                            <h4>Accueil et bienveillance</h4>
                            <div class="valeur-icon">+</div>
                        </div>
                        <div class="valeur-body" style="max-height: 200px;">
                            <p>Chacun est accueilli tel qu'il est, avec son histoire et ses questions, dans un climat de respect absolu.</p>
                        </div>
                    </div>
                    <div class="valeur-item">
                        <div class="valeur-header" onclick="toggleValeur(this)">
                            <h4>Fraternité et partage</h4>
                            <div class="valeur-icon">+</div>
                        </div>
                        <div class="valeur-body">
                            <p>Vivre ensemble des moments forts, tisser des amitiés sincères et s'entraider au quotidien.</p>
                        </div>
                    </div>
                    <div class="valeur-item">
                        <div class="valeur-header" onclick="toggleValeur(this)">
                            <h4>Découverte de la foi</h4>
                            <div class="valeur-icon">+</div>
                        </div>
                        <div class="valeur-body">
                            <p>Approfondir sa relation avec Dieu à travers la prière, les enseignements et les sacrements.</p>
                        </div>
                    </div>
                    <div class="valeur-item">
                        <div class="valeur-header" onclick="toggleValeur(this)">
                            <h4>Joie et épanouissement</h4>
                            <div class="valeur-icon">+</div>
                        </div>
                        <div class="valeur-body">
                            <p>La foi se vit dans la joie ! Chanter, jouer et rire ensemble fait partie intégrante de notre spiritualité.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prochain événement -->
        <?php if($nextEvent): ?>
        <div class="next-event-wrap reveal">
            <div class="next-event-img">
                <?php $img = !empty($nextEvent['image_couverture']) ? htmlspecialchars($nextEvent['image_couverture']) : 'https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true'; ?>
                <img src="<?= $img ?>" alt="<?= htmlspecialchars($nextEvent['titre']) ?>">
            </div>
            <div class="next-event-content">
                <div class="event-tag">
                    <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor"><circle cx="5" cy="5" r="5"/></svg>
                    Prochain événement
                </div>
                <p class="event-date"><?= formatDateFr($nextEvent['date_debut']) ?></p>
                <h3><?= htmlspecialchars($nextEvent['titre']) ?></h3>
                <p class="event-location">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?= htmlspecialchars($nextEvent['adresse']) ?>
                </p>
                <p class="event-desc"><?= substr(htmlspecialchars($nextEvent['description']), 0, 180) ?>…</p>
                <div class="event-actions">
                    <a href="camp_detail.php?id=<?= $nextEvent['id'] ?>" class="btn-gold">Voir le détail</a>
                    <button onclick="openRegistration('<?= htmlspecialchars($nextEvent['lien_inscription']) ?>')" class="btn-ghost-sm">S'inscrire</button>
                    <?php if(!empty($nextEvent['lien_teaser'])): ?>
                    <button onclick="openVideoModal('<?= htmlspecialchars($nextEvent['lien_teaser']) ?>')" class="btn-ghost-sm">▶ Teaser</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===================== CAMPS ===================== -->
<section id="camps" class="section-pad">
    <div class="container">
        <div class="camps-intro reveal">
            <div>
                <p class="section-label">Pour tous les âges</p>
                <h2 class="section-heading">Nos<br><em>camps</em></h2>
            </div>
            <p style="font-family:'Cormorant Garamond',serif; font-size:1.15rem; color:var(--mist); font-style:italic; max-width:340px; align-self:flex-end;">
                Des expériences pensées pour chaque tranche d'âge, pour grandir dans la foi.
            </p>
        </div>

        <div class="camps-grid reveal">
            <!-- Ados -->
            <div class="camp-card" onclick="window.location.href='camps/ados.php'">
                <div class="camp-img">
                    <img src="https://github.com/Cyprdu/PaJe/blob/main/img/ado.png?raw=true" alt="Ados">
                    <div class="camp-img-overlay"></div>
                    <div class="camp-age">10 — 17 ans</div>
                </div>
                <div class="camp-body">
                    <h3>Ados</h3>
                    <p>Tu as envie de mieux te connaître et d'apprendre à connaître Jésus ? Viens vivre des temps forts inoubliables !</p>
                    <div class="camp-footer">
                        <span class="camp-count"><?= $counters[1] ?> événement(s)</span>
                        <span class="camp-link">Découvrir →</span>
                    </div>
                </div>
            </div>

            <!-- Jeunes Pros -->
            <div class="camp-card" onclick="window.location.href='camps/jeunes_pros.php'">
                <div class="camp-img">
                    <img src="https://github.com/Cyprdu/PaJe/blob/main/img/pro.png?raw=true" alt="Jeunes Pros">
                    <div class="camp-img-overlay"></div>
                    <div class="camp-age">18 — 35 ans</div>
                </div>
                <div class="camp-body">
                    <h3>Jeunes Pros / Étudiants</h3>
                    <p>C'est le lieu pour grandir dans ta foi et rencontrer d'autres jeunes adultes engagés.</p>
                    <div class="camp-footer">
                        <span class="camp-count"><?= $counters[2] ?> événement(s)</span>
                        <span class="camp-link">Découvrir →</span>
                    </div>
                </div>
            </div>

            <!-- Vocations -->
            <div class="camp-card" onclick="window.location.href='camps/vocations.php'">
                <div class="camp-img">
                    <img src="https://github.com/Cyprdu/PaJe/blob/main/img/voc.png?raw=true" alt="Vocations">
                    <div class="camp-img-overlay"></div>
                    <div class="camp-age">Vocations</div>
                </div>
                <div class="camp-body">
                    <h3>Vocations</h3>
                    <p>Et si Dieu t'appelait ? La vocation est une aventure. Viens te poser les bonnes questions.</p>
                    <div class="camp-footer">
                        <span class="camp-count"><?= $counters[3] ?> événement(s)</span>
                        <span class="camp-link">Découvrir →</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== ACTIVITÉS ===================== -->
<section id="activites" class="section-pad">
    <div class="container">
        <div class="reveal">
            <p class="section-label">Ce qu'on vit ensemble</p>
            <h2 class="section-heading">Nos<br><em>activités</em></h2>
        </div>

        <div class="activities-layout">
            <p class="activities-intro-text reveal">
                Des temps spirituels et humains profondément vécus — chaque camp est une aventure unique.
            </p>

            <div class="activities-grid reveal">
                <div class="activity-item">
                    <span class="activity-num">01</span>
                    <img class="activity-img" src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/DSC00736.jpeg" alt="Louange">
                    <h4>Louange</h4>
                    <p>Chants et prière pour exprimer notre joie et notre amour de Dieu.</p>
                </div>
                <div class="activity-item">
                    <span class="activity-num">02</span>
                    <img class="activity-img" src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/DSC00667.jpeg" alt="Témoignages">
                    <h4>Témoignages</h4>
                    <p>Partage d'expériences de foi authentiques et transformantes.</p>
                </div>
                <div class="activity-item">
                    <span class="activity-num">03</span>
                    <img class="activity-img" src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/FaiK9Rc4T-GU9M5tXeLtUA.jpeg" alt="Grands jeux">
                    <h4>Grands jeux</h4>
                    <p>Activités ludiques pour créer des liens et vivre la fraternité.</p>
                </div>
                <div class="activity-item">
                    <span class="activity-num">04</span>
                    <img class="activity-img" src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/8MQTKhaJSqC2ea0H9pNPlA.jpeg" alt="Adoration">
                    <h4>Adoration</h4>
                    <p>Silence, recueillement et présence à Dieu.</p>
                </div>
                <div class="activity-item">
                    <span class="activity-num">05</span>
                    <img class="activity-img" src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/DSC00582.jpeg" alt="Enseignements">
                    <h4>Enseignements</h4>
                    <p>Approfondir la foi, l'Évangile et la tradition de l'Église.</p>
                </div>
                <div class="activity-item">
                    <span class="activity-num">06</span>
                    <img class="activity-img" src="https://github.com/Cyprdu/PaJe/raw/refs/heads/main/img/A4aBSxjvQFqglyO0Vt9hew.jpeg" alt="Prière">
                    <h4>Prière personnelle</h4>
                    <p>Cultiver une relation personnelle et intime avec Dieu.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== PHOTOS ===================== -->
<section id="photos" class="section-pad">
    <div class="container">
        <div class="reveal">
            <p class="section-label">Nos souvenirs</p>
            <h2 class="section-heading">Albums<br><em>photos</em></h2>
        </div>

        <?php if(!empty($albums)): ?>
        <div class="photos-grid reveal">
            <?php foreach($albums as $index => $album):
                $bgImg = !empty($album['image_illustration']) ? htmlspecialchars($album['image_illustration']) : 'uploads/cover.png';
                if ($album['type_media'] === 'upload') { $href = "photo.php?id=" . $album['id']; $btnLabel = "Voir la galerie"; $target="_self"; $onclick=""; }
                elseif ($album['type_media'] === 'youtube') { $href = "#"; $btnLabel = "Voir la vidéo"; $target="_self"; $onclick = "openVideoModal('".htmlspecialchars($album['lien_externe'])."')"; }
                else { $href = htmlspecialchars($album['lien_externe']); $btnLabel = "Voir le Drive"; $target="_blank"; $onclick=""; }
            ?>
            <a href="<?= $href ?>" target="<?= $target ?>" onclick="<?= $onclick ?>" class="photo-card">
                <img src="<?= $bgImg ?>" alt="<?= htmlspecialchars($album['titre']) ?>">
                <div class="photo-card-overlay">
                    <div>
                        <div class="photo-card-title"><?= htmlspecialchars($album['titre']) ?></div>
                        <span class="photo-card-sub"><?= $btnLabel ?> →</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="photos-cta reveal">
            <a href="photo.php" class="btn-primary">Tous les albums <span class="arrow">→</span></a>
        </div>
    </div>
</section>

<!-- ===================== CONTACT ===================== -->
<section id="contact" class="section-pad">
    <div class="container">
        <div class="reveal">
            <p class="section-label">Nous écrire</p>
            <h2 class="section-heading">Contactez-<br><em>nous</em></h2>
        </div>

        <div class="contact-grid">
            <div class="contact-info reveal">
                <h3>Une question, une envie de nous rejoindre ? Nous sommes là.</h3>

                <div class="contact-detail">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <div class="contact-detail-text">
                        <strong>Adresse</strong>
                        <span>31, rue Dr Nodet — CS 60154<br>01004 Bourg Cedex</span>
                    </div>
                </div>

                <div class="contact-detail">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.21 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.12 4.14h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <div class="contact-detail-text">
                        <strong>Téléphone</strong>
                        <span>06 23 25 60 49</span>
                    </div>
                </div>

                <div class="contact-detail">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <div class="contact-detail-text">
                        <strong>Email</strong>
                        <a href="mailto:paje.ain@gmail.com">paje.ain@gmail.com</a>
                    </div>
                </div>

                <div class="contact-detail">
                    <div class="icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div class="contact-detail-text">
                        <strong>Responsable diocésain</strong>
                        <span>Gaëlle-Marie CIEREN</span>
                    </div>
                </div>

                <div class="social-links">
                    <a href="https://www.instagram.com/pastodesjeunesbelleyars" target="_blank" class="social-link" title="Instagram">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="m16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    </a>
                    <a href="https://www.facebook.com/ainjeuneetcatho" target="_blank" class="social-link" title="Facebook">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="https://www.youtube.com/@pastoraledesjeunes-diocese2123" target="_blank" class="social-link" title="YouTube">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
                    </a>
                </div>
            </div>

            <div class="contact-form-wrap reveal">
                <?php if($msg_sent): ?>
                <div class="success-msg">
                    <div class="success-icon">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <h3>Message envoyé !</h3>
                    <p>Merci. Nous vous répondrons très rapidement.</p>
                    <a href="index.php#contact" class="btn-secondary" style="margin:1.5rem auto 0; display:inline-flex;">Envoyer un autre message</a>
                </div>
                <?php else: ?>
                <?php if($msg_error): ?>
                <div style="background:#fef2f2; border:1px solid var(--scarlet); color:var(--scarlet); padding:0.8rem 1rem; margin-bottom:1rem; font-size:0.9rem;"><?= $msg_error ?></div>
                <?php endif; ?>
                <form method="POST" action="index.php#contact">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" required placeholder="Jean Dupont">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required placeholder="jean@exemple.fr">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">Sujet</label>
                        <input type="text" id="subject" name="subject" required placeholder="Mon sujet…">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Votre message…"></textarea>
                    </div>
                    <button type="submit" name="contact_submit" class="btn-submit">
                        Envoyer le message
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer>
    <div class="container">
        <div class="footer-top">
            <div class="footer-brand">
                <span class="f-logo">PaJe</span>
                <span class="f-tagline">Pastorale des Jeunes — Diocèse de Belley-Ars</span>
                <p>Accompagner les jeunes de 6 à 35 ans dans leur découverte et leur approfondissement de la foi chrétienne.</p>
            </div>

            <div>
                <h4>Navigation</h4>
                <ul>
                    <li><a href="#qui-sommes-nous">Qui sommes-nous</a></li>
                    <li><a href="#camps">Nos camps</a></li>
                    <li><a href="#activites">Nos activités</a></li>
                    <li><a href="photo.php">Photos</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4>Contact</h4>
                <div class="footer-contact-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>31, rue Dr Nodet · CS 60154 · 01004 Bourg Cedex</span>
                </div>
                <div class="footer-contact-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.21 15a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.12 4.14h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span>06 23 25 60 49</span>
                </div>
                <div class="footer-contact-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <a href="mailto:paje.ain@gmail.com">paje.ain@gmail.com</a>
                </div>
                <div class="footer-contact-item" style="margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(255,253,249,0.06);">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>Gaëlle-Marie CIEREN · Lydia DADDIZA</span>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p>© <?= date('Y') ?> PaJe — Pastorale des Jeunes. Tous droits réservés.</p>
            <a href="admin_login.php" class="footer-admin">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Espace Admin
            </a>
        </div>
    </div>
</footer>

<!-- ===================== MODALS ===================== -->
<div class="modal-backdrop" id="videoModal">
    <div class="modal-video-inner">
        <button class="modal-close" onclick="closeVideoModal()">✕ Fermer</button>
        <iframe id="videoIframe" allowfullscreen></iframe>
    </div>
</div>

<div class="regist-overlay" id="registOverlay">
    <div class="regist-bar">
        <button onclick="closeRegistration()">← Retour</button>
        <span>Inscription</span>
        <div style="width:80px"></div>
    </div>
    <iframe id="registIframe" class="regist-iframe"></iframe>
</div>

<!-- ===================== SCRIPTS ===================== -->
<script>
    /* === Cursor === */
    const cursor = document.getElementById('cursor');
    const ring = document.getElementById('cursor-ring');
    document.addEventListener('mousemove', e => {
        cursor.style.left = e.clientX + 'px';
        cursor.style.top = e.clientY + 'px';
        ring.style.left = e.clientX + 'px';
        ring.style.top = e.clientY + 'px';
    });

    /* === Navbar scroll === */
    const navbar = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 30);
    });

    /* === Mobile nav === */
    const burger = document.getElementById('burger');
    const mobileNav = document.getElementById('mobileNav');
    burger.addEventListener('click', () => mobileNav.classList.toggle('open'));
    function closeMobile() { mobileNav.classList.remove('open'); }

    /* === Scroll Spy === */
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-links a');
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(s => {
            if (window.scrollY >= s.offsetTop - 150) current = s.id;
        });
        navLinks.forEach(a => {
            a.classList.toggle('active', a.getAttribute('href') === '#' + current);
        });
    });

    /* === Reveal on scroll === */
    const reveals = document.querySelectorAll('.reveal');
    const revealObserver = new IntersectionObserver(entries => {
        entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
    }, { threshold: 0.1 });
    reveals.forEach(el => revealObserver.observe(el));

    /* === Accordion Valeurs === */
    function toggleValeur(header) {
        const item = header.closest('.valeur-item');
        const body = item.querySelector('.valeur-body');
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.valeur-item.open').forEach(i => {
            i.classList.remove('open');
            i.querySelector('.valeur-body').style.maxHeight = null;
        });
        if (!isOpen) {
            item.classList.add('open');
            body.style.maxHeight = body.scrollHeight + 'px';
        }
    }

    /* === Modals === */
    function openVideoModal(url) {
        let embed = url;
        if (url.includes('youtu.be/')) embed = 'https://www.youtube.com/embed/' + url.split('youtu.be/')[1].split('?')[0];
        else if (url.includes('youtube.com/watch?v=')) embed = 'https://www.youtube.com/embed/' + url.split('v=')[1].split('&')[0];
        embed += '?autoplay=1';
        document.getElementById('videoIframe').src = embed;
        document.getElementById('videoModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeVideoModal() {
        document.getElementById('videoModal').classList.remove('open');
        document.getElementById('videoIframe').src = '';
        document.body.style.overflow = '';
    }
    document.getElementById('videoModal').addEventListener('click', e => {
        if (e.target === document.getElementById('videoModal')) closeVideoModal();
    });

    function openRegistration(url) {
        document.getElementById('registIframe').src = url;
        document.getElementById('registOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeRegistration() {
        document.getElementById('registOverlay').classList.remove('open');
        document.getElementById('registIframe').src = '';
        document.body.style.overflow = '';
    }
</script>
</body>
</html>