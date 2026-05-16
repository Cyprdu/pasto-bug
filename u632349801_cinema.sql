-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 05 mars 2026 à 17:30
-- Version du serveur : 11.8.3-MariaDB-log
-- Version de PHP : 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `u632349801_cinema`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(2, 'admin', '$2y$10$Lb2Bc8fc.HGoZRVFMtJcf.MAahmCLkQJ1t046lp2sqQK1TCmbr2qq', '2025-12-29 13:56:11');

-- --------------------------------------------------------

--
-- Structure de la table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type_media` enum('youtube','drive','upload') NOT NULL,
  `lien_externe` varchar(255) DEFAULT NULL,
  `image_illustration` varchar(255) DEFAULT NULL,
  `supprime` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `albums`
--

INSERT INTO `albums` (`id`, `titre`, `description`, `type_media`, `lien_externe`, `image_illustration`, `supprime`, `created_at`) VALUES
(1, 'Retour sur le jubilé des jeunes à Rome 2025', 'Revivez les moments extraordinaires du Jubilé des jeunes à Rome avec notre délégation diocésaine.', 'youtube', 'https://youtu.be/UVVAgAYJWNk', 'uploads/covers/cover_69528bf325f84.png', 0, '2025-12-29 14:10:59'),
(2, 'We DEL Belley 2024', 'Découvrez les moments forts de notre week-end \"Dieu est là\" à Belley en 2024', 'drive', 'https://www.amazon.fr/photos/share/8XfLaQKoixQhEl1JS6WZYIsqlhMtpXR5mOGLpOwbNPx', 'uploads/covers/cover_69528fa2efc82.jpg', 0, '2025-12-29 14:26:42'),
(3, 'WE Dieu est là Bourg-en-Bresse 2025', 'Retour sur le weekend Dieu est là à de novembre 2025 à Bourg-en-Bresse', 'upload', '', 'uploads/covers/cover_6952b13656c1f.jpg', 0, '2025-12-29 16:40:32');

-- --------------------------------------------------------

--
-- Structure de la table `billets`
--

CREATE TABLE `billets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seance_id` int(11) NOT NULL,
  `token_qr` varchar(255) NOT NULL,
  `date_reservation` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `camps`
--

CREATE TABLE `camps` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `lien_inscription` varchar(255) DEFAULT NULL,
  `lien_teaser` varchar(255) DEFAULT NULL,
  `age_min` int(11) DEFAULT NULL,
  `age_max` int(11) DEFAULT NULL,
  `date_debut` datetime NOT NULL,
  `date_fin` datetime NOT NULL,
  `itinerant` tinyint(1) DEFAULT 0,
  `adresse` varchar(255) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `moyens_paiement` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`moyens_paiement`)),
  `image_couverture` varchar(255) DEFAULT NULL,
  `supprime` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `camps`
--

INSERT INTO `camps` (`id`, `titre`, `description`, `prix`, `lien_inscription`, `lien_teaser`, `age_min`, `age_max`, `date_debut`, `date_fin`, `itinerant`, `adresse`, `pays`, `moyens_paiement`, `image_couverture`, `supprime`, `created_at`) VALUES
(1, 'Week-end \"Dieu est Là\"', '« Offrez vos corps comme un sacrifice vivant, saint, agréable à Dieu » Rm 12,1 \r\nPrendre un temps avec d\'autres jeunes, pour rencontrer Jésus, apprendre à vivre de Lui afin de pouvoir témoigner des merveilles qu\'Il fait dans nos vies, par la louange, l\'adoration, la confession. Avec des grands jeux et des temps de détente, ces week-ends sont encadrés par les animateurs de la Pastorale des Jeunes du Diocèse de Belley-Ars.\r\n\r\nEn pratique:\r\n\r\nlogement et restauration,\r\nchaque jeune doit prévoir ses draps/ duvet/ oreiller, vêtements pour le week-end et affaires de toilette,\r\napporter un pique-nique pour le repas du samedi midi,\r\nl\'ordonnance du médecin si traitement en cours.\r\nNe pas apporter de téléphone portable. Le téléphone d\'un responsable vous sera transmis pour les cas d\'urgence. Les portables empêchent les jeunes d\'être vraiment présents et disponibles pour le week-end, merci d\'y être vigilants!', 45.00, 'https://weekenddieuestla2026-belley.venio.fr/fr', 'https://youtu.be/j_PNDu0tmZ8', 12, 17, '2026-01-31 09:00:00', '2026-02-01 16:00:00', 0, 'Belley', 'France', '[\"CB\",\"Ch\\u00e8ques\",\"Esp\\u00e8ces\",\"Ch\\u00e8ques Vacances\"]', 'uploads/695293eaa38bc.jpeg', 0, '2025-12-29 14:02:52'),
(2, 'Lourdes 2026', 'Cette année aura lieu un camp des jeunes à Lourdes pour participer au pèlerinage diocésain\r\nLes collégiens vivront un camp sous tente\r\nLes lycéens logeront en dortoir\r\nPour les lycéens, un week-end de préparation a eu lieu à Bourg en Bresse, à la maison Jean- Marie Vianney, les 25 et 26 mai. A reconfirmer', 385.00, 'https://www.venio.fr/fr/organisation/belleyars', '', 12, 18, '2026-07-25 08:00:00', '2026-07-31 16:30:00', 0, 'Lourdes', 'France', '[\"CB\",\"Ch\\u00e8ques\",\"Esp\\u00e8ces\",\"Ch\\u00e8ques Vacances\"]', 'uploads/6952a204541d4.jpg', 0, '2025-12-29 15:45:08'),
(3, 'Ecole de Prière Jeunes', 'Pour qui ?\r\nTout jeune de 7 à 17 ans (dans l’année de l’EPJ), qui désire vivre une semaine à l’école du Seigneur, dans la joie, la prière et la fraternité.\r\n\r\nQu’est-ce qu’une EPJ ?\r\nLes Ecoles de Prière Jeunes (EPJ) proposent à des jeunes de venir se mettre à l’écoute de la Parole de Dieu pour grandir dans leur relation d’intimité avec Dieu tout en vivant des temps de détente et de fraternité.\r\nLes EPJ sont nées au Puy-enVelay en 1983. Depuis elles n’ont cessé de grandir et de se répandre en France et à l’étranger. Les écoles de prière sont regroupées au sein de l’Association « LE CEP ». \r\nA l’appel de notre évêque, une EPJ a été créée dans notre diocèse en 2015, il s’agira donc de la neuvième édition.\r\n\r\nQui l’organise ?\r\nL’EPJ est organisée par la Pastorale des Jeunes. L’équipe d’encadrement, composée de bénévoles, est très heureuse de se mettre au service de vos enfants. Elle comprend des prêtres, des religieuses, des laïcs (parents, jeunes professionnels, étudiants...).\r\n\r\nCe camp est déclaré au Service départemental à la jeunesse et répond à ses exigences quant à l’encadrement, avec des BAFD et BAFA. Le projet éducatif répond aux exigences de la charte proposée par « LE CEP » .\r\n\r\nLa prochaine édition de l\'école de prière aura lieu à l\'Institution Lamartine à Belley du 16 au 22 août 2026.\r\n\r\nRéservez les dates dès maintenant !', 200.00, 'https://www.venio.fr/fr/organisation/belleyars', '', 7, 17, '2026-08-16 10:30:00', '2026-08-22 16:30:00', 0, 'Belley', 'France', '[]', 'uploads/69661ca767fdd.png', 0, '2025-12-29 15:47:19'),
(4, 'Week-end \"Dieu est Là\"', '\"Me voici, envoie-moi\" Is 6,8\r\nDe la 5ème à la 2nde\r\n\r\nUn Week-end pour venir, en Sa présence, et s’écrier comme le saint curé d’Ars : « Il est là !»\r\n\r\nPar la louange, l’adoration, les témoignages la prière des frères, la vie fraternelle et le jeu : viens expérimenter la joie de l’Esprit Saint.', 50.00, 'https://weekenddieuestla2026-bourg.venio.fr/fr', 'https://youtu.be/j_PNDu0tmZ8', 13, 18, '2026-05-01 09:30:00', '2026-05-03 16:30:00', 0, 'Bourg-en-Bresse', 'France', '[\"CB\",\"Ch\\u00e8ques\",\"Esp\\u00e8ces\",\"Ch\\u00e8ques Vacances\"]', 'uploads/6952a323df302.jpg', 0, '2025-12-29 15:49:55'),
(5, 'Retraite 10-12 ans', 'Proposition de retraite pour les jeunes de 10 à 12 ans\r\nPour compléter ou commencer une préparation à la confirmation, la première communion ou le baptême,\r\nPour revivre un temps fort un an après sa retraite de confirmation,\r\nPour faire le point de sa relation avec Dieu,\r\nPour approfondir sa foi en Jésus.\r\n \r\n\r\nPendant une retraite \r\nLouange, témoignages et enseignements, écoute de la Parole de Dieu, temps de partage en fraternité, petits groupes de 8 à 10 jeunes autour d\'un animateur, temps d\'adoration et de confession, grands jeux.', 10.00, 'https://retraite2026-arssurformans.venio.fr/fr', '', 10, 12, '2026-03-14 09:30:00', '2026-03-14 18:00:00', 0, 'Sanctuaire d\'Ars-sur-Formans', 'France', '[\"CB\",\"Ch\\u00e8ques\",\"Esp\\u00e8ces\",\"Ch\\u00e8ques Vacances\"]', 'uploads/6952a3dc3ab36.jpg', 0, '2025-12-29 15:53:00'),
(6, 'Marche pour les vocations - Jubilé des servants d\'autel et des servantes d\'assemblée', 'Programme de la Marche pour les Vocations\r\nA reconfirmer pour 2026\r\nINSCRIPTION GRATUITE ET OBLIGATOIRE\r\n\r\n8h30  Accueil et Laudes Eglise de Saint-Bernard, Rue du Bac, 01600\r\n\r\n9h30  DÉPART DE LA MARCHE de Saint-Bernard\r\n\r\n11h00  ADORATION, à l\'Eglise de Trévoux\r\n\r\n12h30  REPAS TIRÉ DU SAC (apporter son pique-nique) au Collège La Sidoine, Trévoux\r\n\r\n13h45 DÉPART MARCHE de Trévoux, Collège la Sidoine\r\n\r\n16h00 Procession et démarche jubilaire depuis le Monument de la Rencontre, Ars\r\n\r\n18h00 SAINTE MESSE POUR LES VOCATIONS, Eglise Notre-Dame de la Miséricorde, Ars\r\n\r\n19h00 Retour des chauffeurs à Trévoux et Saint-Bernard', 0.00, 'https://www.helloasso.com/associations/la-pa-je-association-pour-la-pastorale-des-jeunes/evenements/inscription-marche-des-vocations', '', 10, 25, '2026-02-07 08:30:00', '2026-02-07 19:00:00', 1, 'Eglise de Saint-Bernard', 'France', '[]', 'uploads/6952a491d4064.png', 0, '2025-12-29 15:56:01'),
(7, 'Week-end FEU', 'Week-end pour les étudiants et jeunes professionnels 18-35 ans', 0.00, 'https://catholique-belley-ars.fr/jeunes-familles/agenda-de-la-paje/pele-du-puy', '', 18, 25, '2026-04-18 08:00:00', '2026-04-19 18:00:00', 0, 'Belley', 'France', '[\"CB\",\"Ch\\u00e8ques\",\"Esp\\u00e8ces\",\"Ch\\u00e8ques Vacances\"]', 'uploads/6952a56e79f41.jpg', 0, '2025-12-29 15:59:42'),
(8, 'Pèlerinage des lycéens à Assise 2026', 'Pèlerinage des troisièmes et lycéens à Assise, du 6 au 12 avril 2026 avec la Pastorale des Jeunes du diocèse de Belley-Ars.\r\nDate limite d\'inscription : le dimanche 25 janvier 2026.\r\n \r\n\r\nSpiritualité de ce pèlerinage : \r\nPartir à Assise, c’est bien plus qu’un simple voyage :\r\nc’est marcher sur les pas de saint François et de sainte Claire,\r\ndécouvrir leur simplicité, leur joie et leur radicalité évangélique.\r\n\r\nAu long de cette semaine, les jeunes seront invités à vivre des temps de prière et de partage,\r\nà découvrir les lieux emblématiques où François a rencontré le Christ,\r\net à laisser résonner dans leur cœur l’appel à une vie plus simple, plus vraie,\r\nplus ouverte à Dieu et aux autres.\r\n\r\n« François, lève-toi, répare ma maison. » : Cet appel du Christ à François peut encore aujourd’hui transformer nos vies.', 520.00, 'https://pelerinagedeslyceensaassise2026-assise.venio.fr/fr', '', 15, 18, '2026-04-06 06:00:00', '2026-04-12 17:30:00', 0, 'Assises', 'Italie', '[\"CB\",\"Ch\\u00e8ques\",\"Esp\\u00e8ces\",\"Ch\\u00e8ques Vacances\"]', 'uploads/6952a6a836249.jpg', 0, '2025-12-29 16:04:56'),
(9, 'Week-end d\'initiation à l\'oraison', 'Le Programme :\r\nEnseignements pratiques : Qu\'est-ce que l\'oraison ? Comment commencer ? Comment gérer les distractions ?\r\nTemps de silence : Pour mettre en pratique et écouter la Parole de Dieu.\r\nPrière liturgique : Avec la communauté des sœurs.\r\nFraternité : Repas partagés et balades face au Mont-Blanc.\r\nAccompagnement', 0.00, '', '', 16, 35, '2026-02-14 09:00:00', '2026-02-15 17:00:00', 0, 'Monts Voirons', 'France', '[]', 'uploads/69665ebcb484a.png', 0, '2026-01-13 15:03:24');

-- --------------------------------------------------------

--
-- Structure de la table `camp_category`
--

CREATE TABLE `camp_category` (
  `camp_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `camp_category`
--

INSERT INTO `camp_category` (`camp_id`, `category_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(8, 1),
(6, 2),
(7, 2),
(9, 2),
(6, 3),
(7, 3),
(9, 3);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `slug` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `slug`) VALUES
(1, '10-17 ans', 'ados'),
(2, '18-35 ans', 'jeunes-pros'),
(3, 'Vocations', 'vocations');

-- --------------------------------------------------------

--
-- Structure de la table `films`
--

CREATE TABLE `films` (
  `tmdb_id` int(11) NOT NULL,
  `a_l_affiche` tinyint(1) DEFAULT 1,
  `date_ajout` timestamp NULL DEFAULT current_timestamp(),
  `is_featured` tinyint(1) DEFAULT 0,
  `url_affiche` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `films`
--

INSERT INTO `films` (`tmdb_id`, `a_l_affiche`, `date_ajout`, `is_featured`, `url_affiche`) VALUES
(550, 0, '2025-12-17 13:40:44', 0, 'https://image.tmdb.org/t/p/original/xRyINp9KfMLVjRiO5nCsoRDdvvF.jpg'),
(671, 0, '2025-12-28 20:35:15', 0, 'https://image.tmdb.org/t/p/original/bfh9Z3Ghz4FOJAfLOAhmc3ccnHU.jpg'),
(49051, 0, '2025-12-28 19:04:52', 0, 'https://image.tmdb.org/t/p/original/aUtpXRCbX0yz2erJSKOxft3iu9Y.jpg'),
(57158, 1, '2025-12-28 19:07:20', 0, 'https://image.tmdb.org/t/p/original/8UWD1JkCIQOS8UQoJYzQW74ypnd.jpg'),
(83533, 1, '2025-12-17 13:40:44', 1, 'https://image.tmdb.org/t/p/original/Auz9TwKsGrGhqKbR6dKOElyFj9t.jpg'),
(122917, 0, '2025-12-28 19:07:24', 0, 'https://image.tmdb.org/t/p/original/8fRq5FAQNpjLelXBAcy03xHfAV3.jpg'),
(157336, 0, '2025-12-17 13:40:44', 0, 'https://image.tmdb.org/t/p/original/vgnoBSVzWAV9sNQUORaDGvDp7wx.jpg'),
(425274, 0, '2025-12-17 14:32:09', 0, 'https://image.tmdb.org/t/p/original/9LzXmDMINrBqrNE5gdBCCKy6RFF.jpg'),
(944931, 1, '2025-12-17 14:37:06', 0, 'https://image.tmdb.org/t/p/original/zfjbzbB9Y421DK62XxJCiL2yGhD.jpg'),
(975335, 1, '2025-12-28 21:29:42', 0, 'https://image.tmdb.org/t/p/original/qpgGmrsb4DYHlpODAVmpy7p7kWg.jpg'),
(991494, 1, '2025-12-27 14:10:05', 0, 'https://image.tmdb.org/t/p/original/3bLq61308Dlk7cf787ViN2bAjmx.jpg'),
(1039902, 1, '2025-12-27 14:26:35', 0, 'https://cinemalegyptis.org/app/uploads/sites/2/2022/11/Vive-le-vent-3.jpg'),
(1084242, 1, '2025-12-17 14:16:42', 0, 'https://image.tmdb.org/t/p/original/iAMxwAtriQkAOTt1jeGER0oLMNN.jpg'),
(1166170, 1, '2025-12-27 14:27:12', 0, 'https://image.tmdb.org/t/p/original/aLw0jnLpvYHfB8BTwzj9RBRp0qI.jpg'),
(1228246, 1, '2025-12-27 14:28:09', 0, 'https://image.tmdb.org/t/p/original/54BOXpX2ieTXMDzHymdDMnUIzYG.jpg'),
(1234731, 1, '2025-12-28 15:05:43', 0, 'https://image.tmdb.org/t/p/original/kLApBgtLOfpCdT9bJWfRVCcRYMY.jpg'),
(1235277, 1, '2025-12-27 14:27:00', 0, 'https://fr.web.img2.acsta.net/img/4a/84/4a844cac98b2225c6a14af1700e6364b.jpg'),
(1308618, 1, '2025-12-28 21:30:09', 0, 'https://image.tmdb.org/t/p/original/eNwmIX59vlDZS94xWgFRsbcGud6.jpg'),
(1309373, 1, '2025-12-27 14:27:24', 0, 'https://image.tmdb.org/t/p/original/f6Usxb7hGreZre15Qt06zEF91Hu.jpg'),
(1368166, 1, '2025-12-27 14:26:48', 0, 'https://image.tmdb.org/t/p/original/ae0p8SqtMNNxbmbUHyCaE20D2aQ.jpg'),
(1427833, 1, '2025-12-27 14:28:28', 0, 'https://image.tmdb.org/t/p/original/vOHineMfXUIrm6eleIuxuWCMf1M.jpg'),
(1432812, 1, '2025-12-27 14:27:36', 0, 'https://media.pathe.fr/movie/mx/44478/lg/1/media'),
(1437985, 1, '2025-12-17 14:37:28', 0, 'https://image.tmdb.org/t/p/original/nABJiQaUieY63EYueemftxCSK2j.jpg'),
(1455686, 1, '2025-12-27 14:27:58', 0, 'https://image.tmdb.org/t/p/original/uwQOwvn68stDyjrnEHKtHypP8EH.jpg'),
(1464374, 1, '2025-12-28 21:31:11', 0, 'https://media.senscritique.com/media/000023362911/0/los_tigres.jpg');

-- --------------------------------------------------------

--
-- Structure de la table `historique_scans`
--

CREATE TABLE `historique_scans` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `nb_entrees` int(11) NOT NULL,
  `date_scan` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Déchargement des données de la table `historique_scans`
--

INSERT INTO `historique_scans` (`id`, `reservation_id`, `admin_id`, `nb_entrees`, `date_scan`) VALUES
(1, 13, 2, 1, '2025-12-28 16:13:31'),
(2, 9, 2, 1, '2025-12-28 16:14:26'),
(3, 9, 2, 1, '2025-12-28 16:14:33'),
(4, 14, 2, 1, '2025-12-28 16:15:15'),
(5, 15, 2, 1, '2025-12-28 16:15:45'),
(6, 16, 2, 1, '2025-12-28 16:16:38'),
(7, 16, 2, 1, '2025-12-28 16:16:48'),
(8, 17, 2, 5, '2025-12-28 16:17:26'),
(9, 17, 2, 4, '2025-12-28 16:17:47'),
(10, 17, 2, 2, '2025-12-28 16:17:56'),
(11, 12, 2, 28, '2025-12-28 16:22:32'),
(12, 12, 2, 39, '2025-12-28 16:22:39'),
(13, 12, 2, 10, '2025-12-28 16:22:45'),
(14, 12, 2, 28, '2025-12-28 16:22:50'),
(15, 12, 2, 5, '2025-12-28 16:22:53'),
(16, 18, 2, 1, '2025-12-28 16:23:49');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `nom_complet` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `sujet` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `est_lu` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `nom_complet`, `email`, `sujet`, `message`, `est_lu`, `created_at`) VALUES
(1, 'Plex', 'cyprien.veyret@gmail.com', 'test', 'test', 1, '2025-12-29 14:14:34'),
(2, 'Plex', 'cyprien.veyret@gmail.com', 'test', 'test', 1, '2025-12-29 14:15:33'),
(3, 'VEYRET', 'cyprien.veyret@gmail.com', 'TEST DU JOURS', 'TEST MESSAGERIE !', 1, '2026-01-13 10:16:30'),
(4, 'VEYRET CYPRIEN', 'cyprien.veyret@gmail.com', 'TEST questions', 'Bonjour l&#039;équipe de la PaJe,\r\n\r\nJe me permets de vous contacter avec une question concernant l&#039;organisation de votre structure :\r\n\r\n« Au vu de la large tranche d&#039;âge que vous accompagnez (de 6 à 35 ans), comment vos activités — telles que les camps et les retraites — sont-elles structurées pour répondre de manière adaptée aux besoins spécifiques des enfants, des adolescents et des jeunes adultes au sein d&#039;une même organisation ? »\r\n\r\nDans l&#039;attente de votre réponse, je vous souhaite une belle journée.\r\n\r\nCordialement,', 1, '2026-01-13 10:19:43');

-- --------------------------------------------------------

--
-- Structure de la table `photos`
--

CREATE TABLE `photos` (
  `id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `position` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `photos`
--

INSERT INTO `photos` (`id`, `album_id`, `nom_fichier`, `position`) VALUES
(2, 3, 'uploads/gallery/3_1767026432_1.jpg', 1),
(3, 3, 'uploads/gallery/3_1767026432_2.jpg', 2),
(4, 3, 'uploads/gallery/3_1767026432_3.jpg', 3),
(6, 3, 'uploads/gallery/3_1767026432_5.jpg', 5),
(7, 3, 'uploads/gallery/3_1767026432_6.jpg', 6),
(8, 3, 'uploads/gallery/3_1767026432_7.jpg', 7),
(10, 3, 'uploads/gallery/3_1767026432_9.jpg', 9),
(11, 3, 'uploads/gallery/3_1767026432_10.jpg', 10),
(12, 3, 'uploads/gallery/3_1767026432_11.jpg', 11),
(13, 3, 'uploads/gallery/3_1767026433_12.jpg', 12),
(14, 3, 'uploads/gallery/3_1767026433_13.jpg', 13),
(15, 3, 'uploads/gallery/3_1767026433_14.jpg', 14),
(16, 3, 'uploads/gallery/3_1767026433_15.jpg', 15),
(17, 3, 'uploads/gallery/3_1767026433_16.jpg', 16),
(18, 3, 'uploads/gallery/3_1767026433_17.jpg', 17),
(19, 3, 'uploads/gallery/3_1767026433_18.jpg', 18),
(20, 3, 'uploads/gallery/3_1767026434_19.jpg', 19),
(21, 3, 'uploads/gallery/3_1767026620_0.jpg', 20);

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `seance_id` int(11) NOT NULL,
  `nb_places` int(11) NOT NULL,
  `nb_lunettes` int(11) DEFAULT 0,
  `montant_total` decimal(10,2) NOT NULL,
  `mode_paiement` varchar(50) NOT NULL,
  `code_reservation` varchar(20) NOT NULL,
  `date_reservation` datetime DEFAULT current_timestamp(),
  `nb_places_scanned` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `seance_id`, `nb_places`, `nb_lunettes`, `montant_total`, `mode_paiement`, `code_reservation`, `date_reservation`, `nb_places_scanned`) VALUES
(7, 2, 78, 1, 0, 10.00, 'ecine', 'C77A03B1', '2025-12-28 14:53:52', 0),
(8, 2, 73, 11, 0, 110.00, 'ecine', '05D75AE8', '2025-12-28 14:58:29', 0),
(9, 2, 32, 2, 0, 20.00, 'stripe', 'C7FD2DFA', '2025-12-28 15:15:36', 2),
(10, 2, 78, 1, 0, 10.00, 'ecine', '3FEF7DB6', '2025-12-28 15:17:13', 0),
(11, 2, 28, 23, 23, 287.50, 'ecine', 'D0E9E08B', '2025-12-28 15:21:40', 23),
(12, 2, 27, 110, 74, 1321.00, 'ecine', '1959D98E', '2025-12-28 15:25:09', 110),
(13, 2, 27, 1, 1, 12.50, 'stripe', 'E6A8B7CB', '2025-12-28 15:33:56', 1),
(14, 2, 40, 1, 0, 10.00, 'stripe', 'B4A1C57A', '2025-12-28 16:15:04', 1),
(15, 2, 40, 1, 0, 10.00, 'stripe', '7638D392', '2025-12-28 16:15:37', 1),
(16, 2, 38, 2, 0, 20.00, 'stripe', '02743548', '2025-12-28 16:16:06', 2),
(17, 2, 35, 11, 0, 110.00, 'ecine', '1ECC545D', '2025-12-28 16:17:13', 11),
(18, 2, 39, 1, 0, 10.00, 'ecine', '8176A415', '2025-12-28 16:23:46', 1),
(19, 2, 103, 1, 0, 10.00, 'ecine', '82532942', '2025-12-28 16:33:44', 0),
(20, 2, 28, 1, 0, 11.00, 'ecine', '82ABC37C', '2025-12-28 18:25:31', 0),
(21, 2, 142, 1, 0, 11.00, 'universel', '4BB8ED3A', '2025-12-28 19:00:19', 0),
(22, 2, 46, 1, 0, 10.00, 'universel', '6B03B1CD', '2025-12-28 19:03:44', 0),
(23, 2, 171, 1, 0, 10.00, 'ecine', '81F1F0D6', '2025-12-28 20:31:08', 0),
(24, 2, 171, 1, 0, 10.00, 'ecine', 'D07B0C15', '2025-12-28 21:21:51', 0);

-- --------------------------------------------------------

--
-- Structure de la table `seances`
--

CREATE TABLE `seances` (
  `id` int(11) NOT NULL,
  `tmdb_id` int(11) NOT NULL,
  `heure_debut` datetime NOT NULL,
  `heure_fin` datetime NOT NULL,
  `salle` varchar(10) NOT NULL,
  `is_3d` tinyint(1) DEFAULT 0,
  `is_vo` tinyint(1) DEFAULT 0,
  `is_vf` tinyint(1) DEFAULT 1,
  `prix` decimal(5,2) DEFAULT 12.50,
  `is_dolby` int(11) DEFAULT 0,
  `duree_pre` int(11) DEFAULT 20,
  `duree_post` int(11) DEFAULT 20,
  `duree_pub` int(11) DEFAULT 10,
  `is_avant_premiere` tinyint(1) DEFAULT 0,
  `is_imax` tinyint(1) DEFAULT 0,
  `is_screenx` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `seances`
--

INSERT INTO `seances` (`id`, `tmdb_id`, `heure_debut`, `heure_fin`, `salle`, `is_3d`, `is_vo`, `is_vf`, `prix`, `is_dolby`, `duree_pre`, `duree_post`, `duree_pub`, `is_avant_premiere`, `is_imax`, `is_screenx`, `is_active`) VALUES
(20, 83533, '2025-05-22 14:10:00', '2025-05-22 17:28:00', '1', 1, 0, 1, 12.50, 1, 20, 20, 10, 0, 0, 0, 0),
(21, 83533, '2025-12-28 14:20:00', '2025-12-28 17:38:00', '1', 1, 0, 1, 12.50, 1, 20, 15, 10, 0, 0, 0, 1),
(26, 83533, '2025-12-28 15:50:00', '2025-12-28 19:08:00', '2', 1, 0, 1, 12.50, 1, 20, 20, 10, 0, 0, 0, 1),
(27, 83533, '2025-12-28 18:20:00', '2025-12-28 21:38:00', '1', 1, 0, 1, 12.50, 1, 15, 95, 10, 0, 0, 0, 1),
(28, 83533, '2025-12-28 19:40:00', '2025-12-28 22:58:00', '3', 1, 0, 1, 12.50, 1, 15, 10, 10, 0, 0, 0, 1),
(32, 1084242, '2025-12-28 17:05:00', '2025-12-28 18:53:00', '3', 0, 0, 1, 12.50, 1, 5, 20, 10, 0, 0, 0, 1),
(34, 1084242, '2025-12-28 18:40:00', '2025-12-28 20:28:00', '4', 0, 0, 1, 12.50, 1, 15, 7, 10, 0, 0, 0, 1),
(35, 1084242, '2025-12-28 20:00:00', '2025-12-28 21:48:00', '2', 0, 0, 1, 12.50, 1, 20, 80, 10, 0, 0, 0, 1),
(36, 1039902, '2025-12-28 14:55:00', '2025-12-28 15:30:00', '7', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(38, 991494, '2025-12-28 16:20:00', '2025-12-28 17:56:00', '4', 0, 0, 1, 12.50, 0, 20, 15, 10, 0, 0, 0, 1),
(39, 991494, '2025-12-28 18:45:00', '2025-12-28 20:21:00', '5', 0, 0, 1, 12.50, 1, 10, 10, 10, 0, 0, 0, 1),
(40, 1368166, '2025-12-28 17:40:00', '2025-12-28 19:51:00', '6', 0, 0, 1, 12.50, 0, 10, 5, 10, 0, 0, 0, 1),
(42, 1368166, '2025-12-28 20:15:00', '2025-12-28 22:26:00', '6', 0, 0, 1, 12.50, 0, 5, 50, 10, 0, 0, 0, 1),
(44, 1235277, '2025-12-28 18:40:00', '2025-12-28 20:06:00', '7', 0, 0, 1, 12.50, 0, 20, 2, 10, 0, 0, 0, 1),
(46, 1235277, '2025-12-28 21:00:00', '2025-12-28 22:26:00', '4', 0, 0, 1, 12.50, 0, 10, 45, 10, 0, 0, 0, 1),
(47, 944931, '2025-12-28 15:35:00', '2025-12-28 16:55:00', '8', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(48, 1166170, '2025-12-28 16:30:00', '2025-12-28 18:08:00', '5', 0, 0, 1, 12.50, 0, 15, 15, 10, 0, 0, 0, 1),
(49, 1166170, '2025-12-28 20:55:00', '2025-12-28 22:33:00', '5', 0, 0, 1, 12.50, 0, 10, 40, 10, 0, 0, 0, 1),
(50, 1309373, '2025-12-28 15:30:00', '2025-12-28 17:05:00', '6', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(51, 1309373, '2025-12-28 20:30:00', '2025-12-28 22:05:00', '7', 0, 0, 1, 12.50, 0, 10, 70, 10, 0, 0, 0, 1),
(53, 1432812, '2025-12-28 18:05:00', '2025-12-28 19:44:00', '8', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(55, 1437985, '2025-12-28 20:30:00', '2025-12-28 22:10:00', '8', 0, 0, 1, 12.50, 0, 10, 65, 10, 0, 0, 0, 1),
(61, 1455686, '2025-12-28 14:20:00', '2025-12-28 15:50:00', '5', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(65, 1228246, '2025-12-28 20:25:00', '2025-12-28 22:09:00', '9', 0, 0, 1, 12.50, 0, 20, 65, 10, 0, 0, 0, 1),
(66, 1427833, '2025-12-28 16:20:00', '2025-12-28 17:40:00', '7', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(67, 1084242, '2025-12-28 14:50:00', '2025-12-28 16:38:00', '3', 0, 0, 1, 12.50, 1, 20, 10, 10, 0, 0, 0, 1),
(68, 944931, '2025-12-28 18:10:00', '2025-12-28 19:30:00', '9', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(69, 1084242, '2025-12-29 13:50:00', '2025-12-29 15:38:00', '3', 0, 0, 1, 12.50, 1, 10, 10, 10, 0, 0, 0, 1),
(70, 1084242, '2025-12-29 16:10:00', '2025-12-29 17:58:00', '3', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(71, 1084242, '2025-12-29 18:30:00', '2025-12-29 20:18:00', '3', 0, 0, 1, 12.50, 0, 10, 5, 10, 0, 0, 0, 1),
(72, 1084242, '2025-12-29 20:50:00', '2025-12-29 22:38:00', '6', 0, 0, 1, 12.50, 1, 5, 25, 10, 0, 0, 0, 1),
(73, 991494, '2025-12-29 14:10:00', '2025-12-29 15:46:00', '6', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(74, 991494, '2025-12-29 16:40:00', '2025-12-29 18:16:00', '6', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(75, 991494, '2025-12-29 18:45:00', '2025-12-29 20:21:00', '6', 0, 0, 1, 12.50, 0, 5, 10, 10, 0, 0, 0, 1),
(76, 1368166, '2025-12-29 18:05:00', '2025-12-29 20:16:00', '9', 0, 0, 1, 12.50, 0, 5, 10, 10, 0, 0, 0, 1),
(77, 1368166, '2025-12-29 20:30:00', '2025-12-29 22:41:00', '5', 0, 0, 1, 12.50, 0, 20, 25, 10, 0, 0, 0, 1),
(78, 1368166, '2025-12-29 15:10:00', '2025-12-29 17:21:00', '5', 0, 1, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(79, 83533, '2025-12-29 14:00:00', '2025-12-29 17:18:00', '1', 1, 0, 1, 12.50, 1, 10, 10, 10, 0, 0, 0, 1),
(80, 83533, '2025-12-29 14:30:00', '2025-12-29 17:48:00', '2', 0, 0, 1, 12.50, 1, 40, 25, 10, 0, 0, 0, 1),
(81, 83533, '2025-12-29 16:30:00', '2025-12-29 19:48:00', '4', 1, 0, 1, 12.50, 1, 15, 20, 10, 0, 0, 0, 1),
(82, 83533, '2025-12-29 18:45:00', '2025-12-29 22:03:00', '2', 0, 0, 1, 12.50, 1, 20, 60, 10, 0, 0, 0, 1),
(83, 83533, '2025-12-29 19:30:00', '2025-12-29 22:48:00', '1', 1, 0, 1, 12.50, 1, 40, 15, 10, 0, 0, 0, 1),
(84, 944931, '2025-12-29 14:30:00', '2025-12-29 15:50:00', '4', 0, 0, 1, 12.50, 0, 40, 10, 10, 0, 0, 0, 1),
(86, 1166170, '2025-12-29 16:30:00', '2025-12-29 18:08:00', '8', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(87, 1166170, '2025-12-29 20:40:00', '2025-12-29 22:18:00', '4', 0, 0, 1, 12.50, 0, 20, 45, 10, 0, 0, 0, 1),
(88, 1309373, '2025-12-29 14:10:00', '2025-12-29 15:45:00', '7', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(89, 1309373, '2025-12-29 16:20:00', '2025-12-29 17:55:00', '7', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(90, 1309373, '2025-12-29 20:30:00', '2025-12-29 22:05:00', '7', 0, 0, 1, 12.50, 0, 5, 55, 10, 0, 0, 0, 1),
(91, 1039902, '2025-12-29 14:00:00', '2025-12-29 14:35:00', '5', 0, 0, 1, 12.50, 0, 10, 12, 10, 0, 0, 0, 1),
(92, 1235277, '2025-12-29 14:10:00', '2025-12-29 15:36:00', '8', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(93, 1235277, '2025-12-29 18:45:00', '2025-12-29 20:11:00', '8', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(94, 1235277, '2025-12-29 20:55:00', '2025-12-29 22:21:00', '8', 0, 0, 1, 12.50, 0, 20, 40, 10, 0, 0, 0, 1),
(98, 1427833, '2025-12-29 14:10:00', '2025-12-29 15:30:00', '9', 0, 0, 1, 12.50, 0, 20, 5, 10, 0, 0, 0, 1),
(99, 1234731, '2025-12-29 20:50:00', '2025-12-29 22:30:00', '9', 0, 0, 1, 12.50, 0, 10, 30, 10, 0, 0, 0, 1),
(100, 1437985, '2025-12-28 15:30:00', '2025-12-28 17:10:00', '9', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(101, 1437985, '2025-12-29 18:30:00', '2025-12-29 20:10:00', '7', 0, 0, 1, 12.50, 0, 10, 5, 10, 0, 0, 0, 1),
(103, 1437985, '2025-12-29 20:50:00', '2025-12-29 22:30:00', '3', 0, 0, 1, 12.50, 0, 10, 35, 10, 0, 0, 0, 1),
(106, 1437985, '2025-12-29 18:00:00', '2025-12-29 19:40:00', '5', 0, 0, 1, 12.50, 0, 15, 20, 10, 0, 0, 0, 1),
(140, 1084242, '2025-12-30 13:50:00', '2025-12-30 15:38:00', '3', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(141, 1039902, '2025-12-30 14:00:00', '2025-12-30 14:35:00', '5', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(142, 83533, '2025-12-30 14:00:00', '2025-12-30 17:18:00', '1', 1, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(143, 1427833, '2025-12-30 14:10:00', '2025-12-30 15:30:00', '9', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(144, 1235277, '2025-12-30 14:10:00', '2025-12-30 15:36:00', '8', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(145, 991494, '2025-12-30 14:10:00', '2025-12-30 15:46:00', '6', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(146, 1309373, '2025-12-30 14:10:00', '2025-12-30 15:45:00', '7', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(147, 944931, '2025-12-30 14:30:00', '2025-12-30 15:50:00', '4', 0, 0, 1, 12.50, 0, 20, 15, 10, 0, 0, 0, 1),
(148, 83533, '2025-12-30 14:30:00', '2025-12-30 17:48:00', '2', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(149, 1368166, '2025-12-30 15:10:00', '2025-12-30 17:21:00', '5', 0, 1, 1, 12.50, 0, 20, 15, 10, 0, 0, 0, 1),
(151, 1084242, '2025-12-30 16:10:00', '2025-12-30 17:58:00', '3', 0, 0, 1, 12.50, 0, 10, 20, 10, 0, 0, 0, 1),
(152, 1309373, '2025-12-30 16:20:00', '2025-12-30 17:55:00', '7', 0, 0, 1, 12.50, 0, 10, 20, 10, 0, 0, 0, 1),
(153, 83533, '2025-12-30 16:30:00', '2025-12-30 19:48:00', '4', 1, 0, 1, 12.50, 0, 15, 20, 10, 0, 0, 0, 1),
(154, 1166170, '2025-12-30 16:30:00', '2025-12-30 18:08:00', '8', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(155, 991494, '2025-12-30 16:40:00', '2025-12-30 18:16:00', '6', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(156, 1437985, '2025-12-30 18:00:00', '2025-12-30 19:40:00', '5', 0, 0, 1, 12.50, 0, 15, 20, 10, 0, 0, 0, 1),
(157, 1368166, '2025-12-30 18:25:00', '2025-12-30 20:36:00', '9', 0, 0, 1, 12.50, 0, 5, 5, 10, 0, 0, 0, 1),
(158, 1437985, '2025-12-30 18:30:00', '2025-12-30 20:10:00', '7', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(159, 1084242, '2025-12-30 18:30:00', '2025-12-30 20:18:00', '3', 0, 0, 1, 12.50, 0, 20, 15, 10, 0, 0, 0, 1),
(160, 1235277, '2025-12-30 18:45:00', '2025-12-30 20:11:00', '8', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(161, 83533, '2025-12-30 18:45:00', '2025-12-30 22:03:00', '2', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(162, 991494, '2025-12-30 18:45:00', '2025-12-30 20:21:00', '6', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(163, 83533, '2025-12-30 19:30:00', '2025-12-30 22:48:00', '1', 1, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(164, 1309373, '2025-12-30 20:40:00', '2025-12-30 22:15:00', '7', 0, 0, 1, 12.50, 0, 10, 20, 10, 0, 0, 0, 1),
(165, 1368166, '2025-12-30 20:30:00', '2025-12-30 22:41:00', '5', 0, 0, 1, 12.50, 0, 15, 20, 10, 0, 0, 0, 1),
(166, 1166170, '2025-12-30 20:40:00', '2025-12-30 22:18:00', '4', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(167, 1234731, '2025-12-30 21:00:00', '2025-12-30 22:40:00', '9', 0, 0, 1, 12.50, 0, 5, 20, 10, 0, 0, 0, 1),
(168, 1437985, '2025-12-30 20:50:00', '2025-12-30 22:30:00', '3', 0, 0, 1, 12.50, 0, 5, 20, 10, 0, 0, 0, 1),
(169, 1084242, '2025-12-30 20:50:00', '2025-12-30 22:38:00', '6', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(170, 1235277, '2025-12-30 20:55:00', '2025-12-30 22:21:00', '8', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(171, 49051, '2025-12-31 21:00:00', '2025-12-31 23:54:00', '4', 0, 0, 1, 12.50, 0, 7, 22, 10, 0, 0, 0, 1),
(173, 57158, '2026-01-07 21:10:00', '2026-01-07 23:51:00', '4', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(174, 122917, '2026-01-14 21:10:00', '2026-01-14 23:34:00', '4', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 0),
(175, 671, '2026-01-21 21:10:00', '2026-01-21 23:43:00', '4', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 0),
(176, 1432812, '2025-12-29 16:00:00', '2025-12-29 17:37:00', '9', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(177, 1432812, '2025-12-30 16:15:00', '2025-12-30 17:52:00', '9', 0, 0, 1, 12.50, 0, 20, 15, 10, 0, 0, 0, 1),
(194, 83533, '2025-12-31 18:10:00', '2025-12-31 21:28:00', '1', 1, 0, 1, 12.50, 0, 10, 70, 10, 0, 0, 0, 1),
(195, 83533, '2025-12-31 14:10:00', '2025-12-31 17:28:00', '1', 1, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(196, 83533, '2025-12-31 16:25:00', '2025-12-31 19:43:00', '2', 0, 0, 1, 12.50, 0, 10, 15, 10, 0, 0, 0, 1),
(198, 83533, '2025-12-31 20:20:00', '2025-12-31 23:38:00', '2', 1, 0, 1, 12.50, 0, 10, 25, 10, 0, 0, 0, 1),
(199, 83533, '2025-12-31 17:10:00', '2025-12-31 20:28:00', '4', 0, 0, 1, 12.50, 0, 70, 10, 10, 0, 0, 0, 1),
(200, 1084242, '2025-12-31 14:10:00', '2025-12-31 15:58:00', '2', 0, 0, 1, 12.50, 0, 20, 5, 10, 0, 0, 0, 1),
(201, 1084242, '2025-12-31 16:25:00', '2025-12-31 18:13:00', '3', 0, 0, 1, 12.50, 0, 10, 20, 10, 0, 0, 0, 1),
(202, 1464374, '2025-12-31 18:55:00', '2025-12-31 20:41:00', '3', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(203, 1464374, '2025-12-31 14:10:00', '2025-12-31 15:56:00', '3', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(204, 1234731, '2025-12-31 21:10:00', '2025-12-31 22:50:00', '3', 0, 0, 1, 12.50, 0, 5, 20, 10, 0, 0, 0, 1),
(205, 1235277, '2025-12-31 14:10:00', '2025-12-31 15:36:00', '4', 0, 0, 1, 12.50, 0, 15, 10, 10, 0, 0, 0, 1),
(206, 1368166, '2025-12-31 14:10:00', '2025-12-31 16:21:00', '5', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(207, 1368166, '2025-12-31 16:50:00', '2025-12-31 19:01:00', '5', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(208, 1368166, '2025-12-31 19:40:00', '2025-12-31 21:51:00', '5', 0, 0, 1, 12.50, 0, 10, 45, 10, 0, 0, 0, 1),
(210, 1309373, '2025-12-31 10:55:00', '2025-12-31 12:30:00', '5', 0, 1, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(211, 1234731, '2025-12-31 14:10:00', '2025-12-31 15:50:00', '6', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(212, 1234731, '2025-12-31 16:25:00', '2025-12-31 18:05:00', '6', 0, 0, 1, 12.50, 0, 10, 20, 10, 0, 0, 0, 1),
(214, 944931, '2025-12-31 14:10:00', '2025-12-31 15:30:00', '7', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(215, 991494, '2025-12-31 16:25:00', '2025-12-31 18:01:00', '7', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(216, 991494, '2025-12-31 18:55:00', '2025-12-31 20:31:00', '7', 0, 0, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1),
(217, 1228246, '2025-12-31 21:10:00', '2025-12-31 22:54:00', '6', 0, 0, 1, 12.50, 0, 5, 20, 10, 0, 0, 0, 1),
(218, 1166170, '2025-12-31 21:10:00', '2025-12-31 22:48:00', '7', 0, 0, 1, 12.50, 0, 5, 20, 10, 0, 0, 0, 1),
(219, 1166170, '2025-12-31 14:10:00', '2025-12-31 15:48:00', '8', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(220, 1308618, '2025-12-31 16:25:00', '2025-12-31 18:01:00', '8', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(221, 1308618, '2025-12-31 18:55:00', '2025-12-31 20:31:00', '8', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(222, 1308618, '2025-12-31 21:10:00', '2025-12-31 22:46:00', '8', 0, 0, 1, 12.50, 0, 15, 20, 10, 0, 0, 0, 1),
(223, 1084242, '2025-12-31 18:55:00', '2025-12-31 20:43:00', '6', 0, 0, 1, 12.50, 0, 10, 10, 10, 0, 0, 0, 1),
(224, 1464374, '2025-12-31 22:10:00', '2025-12-31 23:58:00', '9', 0, 0, 1, 12.50, 0, 2, 20, 10, 0, 0, 0, 1),
(225, 1235277, '2025-12-31 20:25:00', '2025-12-31 21:48:00', '9', 0, 0, 1, 12.50, 0, 2, 5, 10, 0, 0, 0, 1),
(226, 1235277, '2025-12-31 18:30:00', '2025-12-31 19:58:00', '9', 0, 0, 1, 12.50, 0, 7, 10, 10, 0, 0, 0, 1),
(228, 944931, '2025-12-31 16:40:00', '2025-12-31 18:00:00', '9', 0, 0, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(231, 975335, '2025-12-31 10:55:00', '2025-12-31 13:31:00', '4', 0, 1, 1, 12.50, 0, 20, 10, 10, 0, 0, 0, 1),
(232, 975335, '2025-12-31 13:10:00', '2025-12-31 15:46:00', '9', 0, 1, 1, 12.50, 0, 20, 20, 10, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `solde_cinepass` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `is_admin`, `reset_token`, `reset_expires`, `solde_cinepass`) VALUES
(2, 'Cyprien', 'cyprien.veyret@gmail.com', '$2y$10$WspdQzN0PYRdwnXbsl2kv.U69Rk10N.gBSs3kwPfPT0POgX4x4ZkK', 1, NULL, NULL, 0),
(3, 'pveyret77@gmail.com', 'pveyret77@gmail.com', '$2y$10$ZJ8ZePXx6nKhBd1he9tjReL2YrkmPnwdJRSNTgZQTMgEu6h61J4Se', 0, NULL, NULL, 0);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `billets`
--
ALTER TABLE `billets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_qr` (`token_qr`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `seance_id` (`seance_id`);

--
-- Index pour la table `camps`
--
ALTER TABLE `camps`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `camp_category`
--
ALTER TABLE `camp_category`
  ADD PRIMARY KEY (`camp_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `films`
--
ALTER TABLE `films`
  ADD PRIMARY KEY (`tmdb_id`);

--
-- Index pour la table `historique_scans`
--
ALTER TABLE `historique_scans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `photos`
--
ALTER TABLE `photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `album_id` (`album_id`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_reservation` (`code_reservation`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `seance_id` (`seance_id`);

--
-- Index pour la table `seances`
--
ALTER TABLE `seances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tmdb_id` (`tmdb_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `billets`
--
ALTER TABLE `billets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `camps`
--
ALTER TABLE `camps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `historique_scans`
--
ALTER TABLE `historique_scans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `photos`
--
ALTER TABLE `photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `seances`
--
ALTER TABLE `seances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=233;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `billets`
--
ALTER TABLE `billets`
  ADD CONSTRAINT `billets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `billets_ibfk_2` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`);

--
-- Contraintes pour la table `camp_category`
--
ALTER TABLE `camp_category`
  ADD CONSTRAINT `camp_category_ibfk_1` FOREIGN KEY (`camp_id`) REFERENCES `camps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `camp_category_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `photos`
--
ALTER TABLE `photos`
  ADD CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`seance_id`) REFERENCES `seances` (`id`);

--
-- Contraintes pour la table `seances`
--
ALTER TABLE `seances`
  ADD CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`tmdb_id`) REFERENCES `films` (`tmdb_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
