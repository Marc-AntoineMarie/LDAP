-- phpMyAdmin SQL Dump
-- version 5.1.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mar. 04 fév. 2025 à 14:18
-- Version du serveur : 8.0.37
-- Version de PHP : 8.1.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `LDAP`
--

-- --------------------------------------------------------

--
-- Structure de la table `Annuaires`
--

CREATE TABLE `Annuaires` (
  `idAnnuaires` int NOT NULL,
  `Nom` varchar(255) DEFAULT NULL,
  `clients_idclients` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `Annuaires`
--

INSERT INTO `Annuaires` (`idAnnuaires`, `Nom`, `clients_idclients`) VALUES
(1, 'Contact Test', 1);

-- --------------------------------------------------------

--
-- Structure de la table `Clients`
--

CREATE TABLE `Clients` (
  `idclients` int NOT NULL,
  `Nom` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Telephone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `Adresse` varchar(255) DEFAULT NULL,
  `Plateforme` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'Type de plateforme téléphonique (Wazo, OVH, Yeastar, 3CX, etc)',
  `PlateformeURL` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci COMMENT 'URL si nécessaire (pour le 3CX par exemple)',
  `partenaires_idpartenaires` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `Clients`
--

INSERT INTO `Clients` (`idclients`, `Nom`, `Email`, `Telephone`, `Adresse`, `Plateforme`, `PlateformeURL`, `partenaires_idpartenaires`) VALUES
(1, 'Client Test', 'client.test@example.com', '0601020304', '123 Rue Exemple', '', NULL, 2),
(2, 'Pharmacie du centre (3eme du nom)', NULL, '0123456789', 'Rue du centre, évidemment', 'Wazo', '141.94.69.47', 2),
(3, 'GC Service', 'commande@gcdeveloppement.fr', '0272108260', '2 place Michelange 49300 Cholet', 'Wazo', '141.94.251.137', 2),
(4, 'Test2 qui ne doit pas s\'afficher', NULL, NULL, NULL, NULL, NULL, 15),
(6, 'Commune de Prahecq', 'support@gcdeveloppement.fr', ' 0549264755', '29 PLACE DE L\'EGLISE 79230 PRAHECQ', 'OVH', 'fr.proxysip.eu', 11),
(7, 'Gorini & associes', '', '', '', 'Yeastar', '192.168.1.150', 5),
(8, 'Commune Audierne', 'support@gcdeveloppement.fr', '0298700504', '12 Quai Jean Jaurès 29770 AUDIERNE', 'OVH', 'fr.proxysip.eu', 11),
(9, 'Commune de Doussay', 'support@gcdeveloppement.fr', '0549194221', '1 rue de la Mairie 86140 DOUSSAY', 'OVH', 'fr.proxysip.eu', 11),
(10, 'Commune de Loncon', 'support@gcdeveloppement.fr', '0559773667', '2808 route des Pyrénées, 64410 Loncon', 'OVH', 'fr.proxysip.eu', 11),
(11, 'Commune de Cussac', 'support@gcdeveloppement.fr', '0471730904', '4 ROUTE DE LA NARSE 15430 Cussac', 'OVH', 'fr.proxysip.eu', 11),
(12, 'Commune de Coquainvilliers', 'support@gcdeveloppement.fr', '0231329767', 'Le Bourg 1595 rte Regiment Ecossais 14130 Coquainvilliers', 'OVH', 'fr.proxysip.eu', 11),
(24, 'Fédération Départementale des Chasseurs B DU R', 'fdcbr@gmx.fr', '0442921675', '950 chemin de Maliverny, 13540 AIX-EN-PROVENCE', 'Yeastar', '192.168.1.150', 9);

-- --------------------------------------------------------

--
-- Structure de la table `Partenaires`
--

CREATE TABLE `Partenaires` (
  `idpartenaires` int NOT NULL,
  `Nom` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Telephone` varchar(255) DEFAULT NULL,
  `Adresse` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `Partenaires`
--

INSERT INTO `Partenaires` (`idpartenaires`, `Nom`, `Email`, `Telephone`, `Adresse`) VALUES
(1, 'ALLIPCOM (PRINT SYSTEM)', 'olivier.anouiz@printsystem.fr', '06 99 54 26 26', NULL),
(2, 'LEA NUMERIQUE', 'leanumerique@soaped.fr', '09 80 05 28 00', NULL),
(3, 'BUREAUTIK SERVICES', 'contact@bureautikservices.fr', '01 84 74 20 25', NULL),
(4, 'CG-CONEKT', 'contact@cg-conekt.com', '08 82 77 22 30', NULL),
(5, 'COM2S', 'sr@com2s.fr', '06 66 49 60 00', NULL),
(6, 'DB TELECOM', 'contact@db-telecom.fr', '07 69 68 02 20', NULL),
(7, 'DIEFFREY IT', 'camille@dieffrey-it.fr', '01 88 29 16 12', NULL),
(8, 'ECS', 'fkohl@ecs-groupe.com', '03 88 69 28 05', NULL),
(9, 'ENTREPRISE PRO', 'mattali@entreprisepro.fr', '06 50 97 57 01', NULL),
(10, 'GOOD MORNING OFFICE', 'e.eudes@goodmorningoffice.com', '01 84 20 89 58', NULL),
(11, 'ITADEPT', 'contact@itadept.fr', '01 71 36 61 11', NULL),
(12, 'LDS SOLUTIONS', 'rlombardo@lds-solutions.fr', '06 19 80 99 92', NULL),
(13, 'MY IBS', 'smelloul@myibs.fr', '01 47 79 48 20', NULL),
(14, 'OMNITEL', 'dorian@omni-tel.fr', '06 15 43 28 31', NULL),
(15, 'PRO FIBRE (ALLIANCE RESEAU)', 'support@alliance-reseau.fr', '01 89 40 48 12', NULL),
(16, 'RESEAULINE', 'jeremy.lefevre@reseauline.fr', '04 68 31 16 00', NULL),
(17, 'SQUARTIS', 'support@squartis.com', '02 67 74 60 70', NULL),
(18, 'TELPRO', 'shirley.telpro@orange.fr', '03 92 10 04 87', NULL),
(19, 'TOPLINE', 'peters@topline.fr', '03 88 13 25 18', NULL),
(20, 'YOWICO', 'contact@yowico.fr', '01 69 90 99 09', NULL),
(339, 'test', 'tete@teet', '7451230', '8754210'),
(340, 'TEST', 'test@test', '185', 'ZDZE'),
(341, 'GORINI', 'rgorini@excellia-experts.com', '498102890', ''),
(342, 'GORINI ET ASSOCIES (COM2S)', 'rgorini@excellia-exprets.com', '498102890', '');

-- --------------------------------------------------------

--
-- Structure de la table `Plateformes`
--

CREATE TABLE `Plateformes` (
  `idplateforme` int NOT NULL,
  `PlateformeNom` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `Plateformes`
--

INSERT INTO `Plateformes` (`idplateforme`, `PlateformeNom`) VALUES
(1, 'Wazo'),
(2, 'OVH'),
(3, 'Yeastar');

-- --------------------------------------------------------

--
-- Structure de la table `Roles`
--

CREATE TABLE `Roles` (
  `idRole` int NOT NULL,
  `Login` varchar(255) DEFAULT NULL,
  `MDP` varchar(255) DEFAULT NULL,
  `Status` varchar(255) DEFAULT NULL,
  `partenaires_idpartenaires` int DEFAULT NULL,
  `clients_idclients` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `Roles`
--

INSERT INTO `Roles` (`idRole`, `Login`, `MDP`, `Status`, `partenaires_idpartenaires`, `clients_idclients`) VALUES
(5, 'Quentin', '$2y$10$cNKklGpD2U82awQSgqHE2.RUrgEy5B7xhg1zzQGKLtutATe0EPf52', 'Admin', NULL, NULL),
(10, 'test', '$2y$10$yg.M1si.WGlPM2JeuPlIBe3hOed28Z6Ie4kJwToj/85dzbg9r/8Ia', 'Client', 2, 1),
(11, 'Geoffrey', '$2y$10$rdHlkhgi7E5q8Gd9HcWbjOpc38cycwbc95X7twiBalRPTVRgdoOuS', 'Partenaire', 14, NULL),
(12, 'Guillaume', '$2y$10$69R92hyCw7uT4OmrFC9E7.I/U2uYgLgI6JE9.Va0zx0z91POd6JU.', 'Admin', NULL, NULL),
(13, 'Marco', '$2y$10$NQECpOyZJ..fgj87jaD3luPHVCL5zF97c/eeUnJvZl539IF/3kztu', 'Admin', NULL, NULL),
(14, 'Pierre', '$2y$10$774FMeVUO8JcLh.FLOO/s.8LWcbTOi.Bfas9Zaqtl8yvBdOAkN4u2', 'Admin', NULL, NULL),
(15, 'Lysa', '$2y$10$BWYKQ2baQX9enn3p/KmDxOjlO8S21P5tga5R8EVtjEVVpIjUBphZW', 'Admin', NULL, NULL),
(16, 'Geoffroy', '$2y$10$/6Obr3AULBY3leERKwwS2ujQdpT2KFN12LgQibu7CPygTDrvc23Ke', 'Admin', NULL, NULL),
(17, 'Lucie', '$2y$10$Z7ojkEYVcrP0wZjMJg4TaOosM3Q8KmTNfGDcNzCIPFVumpn4Nc7zi', 'Admin', NULL, NULL),
(18, 'Mailys', '$2y$10$rZ7GE/xrYZm6wUWdnZoWNe4lpxq6AB6gt9lDyzEHBOIcmMDw77BYG', 'Admin', NULL, NULL),
(19, 'Laurent', '$2y$10$Kx8lCYiqSpXaWd8wB/Tu5edZD3V8Fzkd4hA467HcH0AFOp8Z7gRXO', 'Admin', NULL, NULL),
(20, 'Dorian', '$2y$10$w26OulLlbr6lA0SEbyqYM.M6DKZdsvmTKBTQj4DUztHwP4I/ql1nK', 'Partenaire', 14, NULL),
(21, 'Olivier', '$2y$10$zHNN7hMXirQEI7s1oKnOi.991J3aGlYxTOwfB1F9sjlSEaU5aSQE6', 'Partenaire', 1, NULL),
(22, 'Seb', '$2y$10$mJiiJeSraTNfnRx.W6XnZuTIsRmFua.TBfHEXNc5tR/OfGUazhXAK', 'Partenaire', 2, NULL),
(23, 'Bureautik', '$2y$10$GkJZNZ3.VcLBmkaRzrpHH.R2gW6pIAHKMKoUn8dsFZ21W4.k7eb4q', 'Partenaire', 3, NULL),
(24, 'Pierre Antoine', '$2y$10$u2QOB.PjC1N2DemoywFE8Ov6lTr4V8t4IhVHaOFCSQs47EHMPP5t2', 'Partenaire', 4, NULL),
(25, 'Sébastien', '$2y$10$JKx42U5nFuUyKMEaZq6kvupIJQDoE0TRpx8u0jXvC6qoZ.aOxg1OK', 'Partenaire', 5, NULL),
(26, 'Salomon', '$2y$10$4AfvULOlDwakC02tmnrlAu89qnuI5c6p/STLkluSL5pjPu1DngFEa', 'Partenaire', 6, NULL),
(27, 'Jonathan', '$2y$10$aNATss/v8mPXNsgIxlvtZOk3H9HHEwGsXpqbvecwB57nWg2xXwjuO', 'Partenaire', 7, NULL),
(28, 'David', '$2y$10$LAIZk4nIh2xVt09ZTqsK6udspdy.tp2AF8h8.UbD5L0gKWmas8gx6', 'Partenaire', 7, NULL),
(29, 'Felix', '$2y$10$VSLAMWUKvemw0yIhycFLy.JuMHt9QoNBj9av03bd5K8vBMp7rtDIy', 'Partenaire', 8, NULL),
(30, 'Michael', '$2y$10$5bGBaNp9CIO/LiBDvad5N.iX7Xk6t/onR5q3.paJmy8mP1xAYjLAG', 'Partenaire', 9, NULL),
(31, 'Emilien', '$2y$10$ofE8ngrfYcGC/Wd/NloEV.CW.QM/up/NZ7Ma4vuQ.x4lfZrXy73n2', 'Partenaire', 10, NULL),
(32, 'Bruno', '$2y$10$nV21hcorhHCKorEsrOKAOe29xShV/R7xIDXVEamdY2GJp.Ia0isEa', 'Partenaire', 11, NULL),
(33, 'Bastien', '$2y$10$ZvczkFVUdGONxQXPFap/je3YLHLCELjiehLGzxKwIzqBUkJCu9hZi', 'Partenaire', 12, NULL),
(34, 'Rudy', '$2y$10$tLTPmNfToVrfk/xuZ143quMStgL5GZYb6JKFoVEpfvDzozYLY79pq', 'Partenaire', 12, NULL),
(35, 'Sami', '$2y$10$gOYsGzQ5SZFHN9u5.LgD2uN2MBWcLRQKUqyEGQrkoSh97Oo8XaQSO', 'Partenaire', 13, NULL),
(36, 'Profibre', '$2y$10$IcDFW1bpAVmXdfNTvQjOLe3OODDoWtXgwdyBDW091M0fx/X9.lrRa', 'Partenaire', 15, NULL),
(37, 'Jeremy', '$2y$10$O7V6P6f2ufZ6yJuuIRfqN.YciPvqdS6r3M9fGuL1NKZ5lzBP7GLQS', 'Partenaire', 16, NULL),
(38, 'Squartis', '$2y$10$VrDSPu5rThvbSpybhiz/n..zvoXXFLbZMXhpkFPJzkBbBwc3SsWMO', 'Partenaire', 17, NULL),
(39, 'Shirley', '$2y$10$YIZ/d6vYdJ2D2QaRPt3cN.qXk06.vMD6ONEDVq8sn2Fri5nESkuaO', 'Partenaire', 18, NULL),
(40, 'Samia', '$2y$10$VQ5YUOVcxg2kDAKiMSJmSOxMvLI5BMV5lT.q5ZVXlbYmB78eTFC6G', 'Partenaire', 18, NULL),
(41, 'Dilara', '$2y$10$gS6GWa/QuFV3nXqpPyCh1utHaYJmyNV6OtjYtTNxDAnmUoFjDOdOS', 'Partenaire', 19, NULL),
(42, 'Semra', '$2y$10$R5G6JRLAd7KiV.prZB5J.eqe/QTA5PnWOVrMEHDaFnrUcFpApq/zq', 'Partenaire', 19, NULL),
(43, 'yowigo', '$2y$10$3FayIJgmOKT2tpmIiIZFselXwjIAbllKcf6cNHgL98D2LsDyqMJq2', 'Partenaire', 20, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `TypePostes`
--

CREATE TABLE `TypePostes` (
  `idtypeposte` int NOT NULL,
  `TypePoste` varchar(250) NOT NULL,
  `PosteCategorie` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `TypePostes`
--

INSERT INTO `TypePostes` (`idtypeposte`, `TypePoste`, `PosteCategorie`) VALUES
(1, 'Yealink T54', 'YeaT5X'),
(2, 'Yealink T48', 'YeaT4X'),
(3, 'Yealink T53', 'YeaT5X'),
(4, 'Fanvil X4U', 'Fanvil'),
(5, 'Yealink W70B', 'YeaW70');

-- --------------------------------------------------------

--
-- Structure de la table `User_annuaire`
--

CREATE TABLE `User_annuaire` (
  `idUserAnnuaire` int NOT NULL,
  `annuaire_id` int DEFAULT NULL,
  `Prenom` varchar(255) DEFAULT NULL,
  `Nom` varchar(255) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Societe` varchar(255) DEFAULT NULL,
  `Adresse` varchar(255) DEFAULT NULL,
  `Ville` varchar(255) DEFAULT NULL,
  `Telephone` varchar(50) DEFAULT NULL,
  `Commentaire` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `Utilisateurs`
--

CREATE TABLE `Utilisateurs` (
  `idutilisateurs` int NOT NULL,
  `Nom` varchar(100) NOT NULL,
  `Extension` varchar(20) NOT NULL,
  `TypePoste` varchar(100) NOT NULL,
  `AdresseMAC` varchar(12) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL COMMENT 'Adresse mac sans séparateur ni majuscule',
  `SIPLogin` varchar(250) NOT NULL,
  `SIPPassword` varchar(250) NOT NULL,
  `SIPServeur` varchar(250) NOT NULL,
  `clients_idclients` int NOT NULL,
  `annuaires_idannuaires` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `Utilisateurs`
--

INSERT INTO `Utilisateurs` (`idutilisateurs`, `Nom`, `Extension`, `TypePoste`, `AdresseMAC`, `SIPLogin`, `SIPPassword`, `SIPServeur`, `clients_idclients`, `annuaires_idannuaires`) VALUES
(1, 'Test Laurent', '125', 'Yealink T54', '44dbd24bcf17', 'in0uh80t', 'j7hb10k6', '141.94.251.137', 3, 0),
(2, 'Pierre', '105', 'Yealink T48', '805e0ce36b37', 'aw1fzf4m', '7cxtt2mb', '141.94.251.137', 3, 0),
(9, 'SALLE POLYVALENTE', '0033579680626', 'Yealink T53', '249ad891b532', '0033579680626', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(10, 'COMPLEXE SPORTIF', '0033579680627', 'Yealink T53', '44dbd2589110', '0033579680627', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(11, 'MAISON POUR TOUS', '0033579680628', 'Yealink T53', '44dbd2588630', '0033579680628', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(12, 'LAITERIE SALLE', '0033579680629', 'Yealink T53', '44dbd2588662', '0033579680629', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(13, 'LAITERIE CROIX ROUGE', '0033579680631', 'Yealink T53', '44dbd25886df', '0033579680631', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(14, 'CHATEAU DE LA VOUTE', '0033579680632', 'Yealink T53', '249ad891b4fc', '0033579680632', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(15, 'DEPOT COMUNAL LAITERIE', '0033579680634', 'Yealink T53', '44dbd258916e', '0033579680634', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(16, 'ECOLE PRIMAIRE', '0033579680635', 'Yealink T53', '44dbd25897a9', '0033579680635', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(17, 'ECOLE PRIMAIRE PSY', '0033579680636', 'Yealink T53', '44dbd2589345', '0033579680636', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(18, 'RESTAURANT SCOLAIRE', '0033579680637', 'Yealink T53', '44dbd2589384', '0033579680637', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(19, 'SALLE OMNISPORTS', '0033579680639', 'Yealink T53', '44dbd2588aaa', '0033579680639', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(20, 'DEPOT COMUNAL ZA', '0033579680641', 'Yealink T53', '44dbd2588ddf', '0033579680641', 'Apolline@0709', 'fr.proxysip.eu', 6, 0),
(21, 'Béatrice VADOT ', '1000', 'Yealink T54', '44dbd25f61a2', '1000', 'N7^~huK3oFNOHGMY', '192.168.127.245', 7, 0),
(22, 'Reynald GORINI', '1001', 'Yealink T54', '44dbd25f6eda', '1001', '4Xo?^QPVLPRKFUOP', '192.168.127.245', 7, 0),
(23, 'Jean-Charles ACCIAIOLI ', '1002', 'Yealink T54', '44dbd25f7ca8', '1002', '3^-^iThwjNLRXIOP', '192.168.127.245', 7, 0),
(24, 'Manon GORINI ', '1003', 'Yealink T54', '44dbd25f72c0', '1003', 'Y@Yw3LNDJ-MIHBFD', '192.168.127.245', 7, 0),
(25, 'Emilie FORNARESIO ', '1004', 'Yealink T54', '44dbd25f7278', '1004', 't_ATDg80?T_FABAI', '192.168.127.245', 7, 0),
(26, 'Sophie MAYNIAL ', '1005', 'Yealink T54', '44dbd25f7bba', '1005', '6EWt?*CKMKACBOFI', '192.168.127.245', 7, 0),
(27, 'Karen CUCCHIETTI ', '1006', 'Yealink T54', '44dbd25f6cf0', '1006', '?JI5?o_AdI3ciWJN', '192.168.127.245', 7, 0),
(28, 'Stéphanie BARGES ', '1007', 'Yealink T53', '44dbd258885a', '1007', '5_Hu^-O@NHpt?u^^', '192.168.127.245', 7, 0),
(29, 'Jessica PINOIS ', '1008', 'Yealink T53', '44dbd25893d2', '1008', 'rQ-3V*N?*VAUMIDR', '192.168.127.245', 7, 0),
(30, 'Séverine TYLINSKI ', '1009', 'Yealink T53', '44dbd2588a42', '1009', 'YJa-3uY5*1A2R625', '192.168.127.245', 7, 0),
(31, 'Aube CASELLA ', '1010', 'Yealink T53', '44dbd2588596', '1010', 'K@f_AU@IL504JTY8', '192.168.127.245', 7, 0),
(32, 'Lolo', '123', 'Yealink W70B', '44dbd26f81e0', 'gr7hmj6n', 'qbpxtvff', '141.94.251.137', 3, 0),
(34, 'Chloé MARIOTTI ', '1011', 'Yealink T53', '44dbd2588925', '1011', '3UYP?~-sPQGJVHFS', '192.168.127.245', 7, 0),
(36, 'Jennifer MEREL ', '1012', 'Yealink T53', '44dbd25887c8', '1012', 'S~y-6O4BF?X@-?J?', '192.168.127.245', 7, 0),
(37, 'Dylan DEMARTINI ', '1013', 'Yealink T53', '44dbd25885dd', '1013', '0Ne_~41042040675', '192.168.127.245', 7, 0),
(38, 'Salle de réunion', '1014', 'Yealink T53', '44dbd25892d3', '1014', '3??@BmURmdDAwNKK', '192.168.127.245', 7, 0),
(41, 'Stagiaire 1', '1015', 'Yealink T53', '44dbd25895dc', '1015', 'rHd^2?^^~@~-_~~-', '192.168.127.245', 7, 0),
(42, 'Bureau 2', '1016', 'Yealink T53', '44dbd25895e9', '1016', '6V~o^jCTAPMUQLAC', '192.168.127.245', 7, 0),
(43, 'Test 3', '245', 'Fanvil X4U', '0c383e68af47', 'ttt', '123456789', '141.94.251.137', 3, 0),
(44, 'FOYER', '0033222941241', 'Yealink W70B', '44dbd26ee4e7', '0033222941241', 'Apolline@0709', 'fr.proxysip.eu', 8, 0),
(45, 'MUSEE', '0033222941242', 'Yealink T53', '44dbd2588c73', '0033222941242', 'Apolline@0709', 'fr.proxysip.eu', 8, 0),
(46, 'STADE', '0033222941243', 'Yealink T53', '44dbd258890a', '0033222941243', 'Apolline@0709', 'fr.proxysip.eu', 8, 0),
(47, 'SALLE DES FETES', '0033586240089', 'Yealink T53', '44dbd2588902', '0033586240089', 'Apolline@0709', 'fr.proxysip.eu', 9, 0),
(48, 'AGENCE POSTALE', '0033586240091', 'Yealink T53', '44dbd2588900', '0033586240091', 'Apolline@0709', 'fr.proxysip.eu', 9, 0),
(49, 'SALLE DES FETES LONCON', '0033564271135', 'Yealink T53', '44dbd258925a', '0033564271135', 'Apolline@0709', 'fr.proxysip.eu', 10, 0),
(50, 'SALLE DES FETES CUSSAC', '0033415580050', 'Yealink T53', '44dbd258870e', '0033415580050', 'Apolline@0709', 'fr.proxysip.eu', 11, 0),
(51, 'BIBLIOTHEQUE', '0033261750109', 'Yealink T53', '44dbd2588ed4', '0033261750109', 'Apolline@0709', 'fr.proxysip.eu', 12, 0);

-- --------------------------------------------------------

--
-- Structure de la table `UtilisateursBLF`
--

CREATE TABLE `UtilisateursBLF` (
  `idblf` int NOT NULL,
  `TypeBLF` varchar(50) NOT NULL,
  `Etiquette` varchar(100) NOT NULL,
  `Valeur` varchar(100) NOT NULL DEFAULT '',
  `Position` int NOT NULL,
  `utilisateurs_idutilisateurs` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `UtilisateursBLF`
--

INSERT INTO `UtilisateursBLF` (`idblf`, `TypeBLF`, `Etiquette`, `Valeur`, `Position`, `utilisateurs_idutilisateurs`) VALUES
(32, 'BLF', 'Marco', '108', 4, 3),
(36, 'BLF', 'Marco', '108', 4, 3),
(40, 'BLF', 'Marco', '108', 4, 3),
(54, 'BLF', 'Marco', '108', 4, 3),
(58, 'BLF', 'Marco', '108', 4, 3),
(62, 'BLF', 'Marco', '108', 4, 3),
(66, 'BLF', 'Marco', '108', 4, 3),
(84, 'Ligne', 'Ligne 1', '', 1, 5),
(85, 'Ligne', 'Ligne 2', '', 2, 5),
(88, 'Ligne', 'Ligne 1', '', 1, 7),
(89, 'Ligne', 'Ligne 2', '', 2, 7),
(90, 'BLF', 'Test', '0033579680613', 3, 7),
(91, 'Ligne', 'Ligne 1', '', 1, 8),
(92, 'Ligne', 'Ligne 2', '', 2, 8),
(93, 'BLF', 'Test', '0033579680613', 3, 8),
(94, 'BLF', 'Test 2', 'tititit', 4, 8),
(95, 'Ligne', 'Ligne 1', '', 1, 20),
(96, 'Ligne', 'Ligne 2', '', 2, 20),
(97, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 20),
(98, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 20),
(99, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 20),
(100, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 20),
(101, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 20),
(102, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 8, 20),
(103, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 9, 20),
(104, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 10, 20),
(105, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 11, 20),
(106, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 12, 20),
(107, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 13, 20),
(108, 'Ligne', 'Ligne 1', '', 1, 9),
(109, 'Ligne', 'Ligne 2', '', 2, 9),
(110, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 3, 9),
(111, 'BLF', 'MAISON POUR TOUS', '0033579680628', 4, 9),
(112, 'BLF', 'LAITERIE SALLE', '0033579680629', 5, 9),
(113, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 6, 9),
(114, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 7, 9),
(115, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 8, 9),
(116, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 9),
(117, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 9),
(118, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 9),
(119, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 9),
(120, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 9),
(121, 'Ligne', 'Ligne 1', '', 1, 10),
(122, 'Ligne', 'Ligne 2', '', 2, 10),
(123, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 10),
(124, 'BLF', 'MAISON POUR TOUS', '0033579680628', 4, 10),
(125, 'BLF', 'LAITERIE SALLE', '0033579680629', 5, 10),
(126, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 6, 10),
(127, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 7, 10),
(128, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 8, 10),
(129, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 10),
(130, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 10),
(131, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 10),
(132, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 10),
(133, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 10),
(134, 'Ligne', 'Ligne 1', '', 1, 11),
(135, 'Ligne', 'Ligne 2', '', 2, 11),
(136, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 11),
(137, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 11),
(138, 'BLF', 'LAITERIE SALLE', '0033579680629', 5, 11),
(139, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 6, 11),
(140, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 7, 11),
(141, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 8, 11),
(142, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 11),
(143, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 11),
(144, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 11),
(145, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 11),
(146, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 11),
(147, 'Ligne', 'Ligne 1', '', 1, 12),
(148, 'Ligne', 'Ligne 2', '', 2, 12),
(149, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 12),
(150, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 12),
(151, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 12),
(152, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 6, 12),
(153, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 7, 12),
(154, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 8, 12),
(155, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 12),
(156, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 12),
(157, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 12),
(158, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 12),
(159, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 12),
(160, 'Ligne', 'Ligne 1', '', 1, 13),
(161, 'Ligne', 'Ligne 2', '', 2, 13),
(162, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 13),
(163, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 13),
(164, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 13),
(165, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 13),
(166, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 7, 13),
(167, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 8, 13),
(168, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 13),
(169, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 13),
(170, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 13),
(171, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 13),
(172, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 13),
(173, 'Ligne', 'Ligne 1', '', 1, 14),
(174, 'Ligne', 'Ligne 2', '', 2, 14),
(175, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 14),
(176, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 14),
(177, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 14),
(178, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 14),
(179, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 14),
(180, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 8, 14),
(181, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 14),
(182, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 14),
(183, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 14),
(184, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 14),
(185, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 14),
(186, 'Ligne', 'Ligne 1', '', 1, 15),
(187, 'Ligne', 'Ligne 2', '', 2, 15),
(188, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 15),
(189, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 15),
(190, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 15),
(191, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 15),
(192, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 15),
(193, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 8, 15),
(194, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 9, 15),
(195, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 15),
(196, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 15),
(197, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 15),
(198, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 15),
(199, 'Ligne', 'Ligne 1', '', 1, 16),
(200, 'Ligne', 'Ligne 2', '', 2, 16),
(201, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 16),
(202, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 16),
(203, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 16),
(204, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 16),
(205, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 16),
(206, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 8, 16),
(207, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 9, 16),
(208, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 10, 16),
(209, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 16),
(210, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 16),
(211, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 16),
(212, 'Ligne', 'Ligne 1', '', 1, 17),
(213, 'Ligne', 'Ligne 2', '', 2, 17),
(214, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 17),
(215, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 17),
(216, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 17),
(217, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 17),
(218, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 17),
(219, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 8, 17),
(220, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 9, 17),
(221, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 10, 17),
(222, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 11, 17),
(223, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 17),
(224, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 17),
(225, 'Ligne', 'Ligne 1', '', 1, 18),
(226, 'Ligne', 'Ligne 2', '', 2, 18),
(227, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 18),
(228, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 18),
(229, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 18),
(230, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 18),
(231, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 18),
(232, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 8, 18),
(233, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 9, 18),
(234, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 10, 18),
(235, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 11, 18),
(236, 'BLF', 'SALLE OMNISPORTS', '0033579680639', 12, 18),
(237, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 18),
(238, 'Ligne', 'Ligne 1', '', 1, 19),
(239, 'Ligne', 'Ligne 2', '', 2, 19),
(240, 'BLF', 'SALLE POLYVALENTE', '0033579680626', 3, 19),
(241, 'BLF', 'COMPLEXE SPORTIF', '0033579680627', 4, 19),
(242, 'BLF', 'MAISON POUR TOUS', '0033579680628', 5, 19),
(243, 'BLF', 'LAITERIE SALLE', '0033579680629', 6, 19),
(244, 'BLF', 'LAITERIE CROIX ROUGE', '0033579680631', 7, 19),
(245, 'BLF', 'CHATEAU DE LA VOUTE', '0033579680632', 8, 19),
(246, 'BLF', 'DEPOT COMUNAL LAITERIE', '0033579680634', 9, 19),
(247, 'BLF', 'ECOLE PRIMAIRE', '0033579680635', 10, 19),
(248, 'BLF', 'ECOLE PRIMAIRE PSY', '0033579680636', 11, 19),
(249, 'BLF', 'RESTAURANT SCOLAIRE', '0033579680637', 12, 19),
(250, 'BLF', 'DEPOT COMUNAL ZA', '0033579680641', 13, 19),
(299, 'Ligne', 'Ligne 1', '', 1, 33),
(300, 'Ligne', 'Ligne 2', '', 2, 33),
(305, 'Ligne', 'Ligne 1', '', 1, 1),
(306, 'Ligne', 'Ligne 2', '', 2, 1),
(307, 'BLF', 'Pierre', '105', 3, 1),
(308, 'BLF', 'Marco', '108', 4, 1),
(309, 'Ligne', 'Ligne 1', '', 1, 2),
(310, 'Ligne', 'Ligne 2', '', 2, 2),
(311, 'BLF', 'Test Laurent', '125', 3, 2),
(312, 'BLF', 'Test 2', '145', 4, 2),
(313, 'Ligne', 'Ligne 1', '', 1, 32),
(314, 'Ligne', 'Ligne 2', '', 2, 32),
(315, 'BLF', 'Pierre', '105', 3, 32),
(316, 'BLF', 'Test Laurent', '125', 4, 32),
(336, 'Ligne', 'Ligne 1', '', 1, 42),
(337, 'Ligne', 'Ligne 2', '', 2, 42),
(338, 'BLF', 'Béatrice VADOT ', '1000', 4, 42),
(339, 'BLF', 'Reynald GORINI', '1001', 5, 42),
(340, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 42),
(341, 'BLF', 'Manon GORINI ', '1003', 7, 42),
(342, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 42),
(343, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 42),
(344, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 42),
(345, 'BLF', 'Stéphanie BARGES ', '1007', 11, 42),
(346, 'BLF', 'Jessica PINOIS ', '1008', 12, 42),
(347, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 42),
(348, 'BLF', 'Aube CASELLA ', '1010', 14, 42),
(349, 'BLF', 'Chloé MARIOTTI ', '1011', 15, 42),
(350, 'BLF', 'Jennifer MEREL ', '1012', 16, 42),
(351, 'BLF', 'Dylan DEMARTINI ', '1013', 17, 42),
(352, 'BLF', 'Salle de réunion', '1014', 18, 42),
(353, 'BLF', 'Stagiaire 1', '1015', 19, 42),
(354, '', '', '', 3, 42),
(355, 'Ligne', 'Ligne 1', '', 1, 21),
(356, 'Ligne', 'Ligne 2', '', 2, 21),
(357, 'BLF', 'Reynald GORINI', '1001', 7, 21),
(358, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 8, 21),
(359, 'BLF', 'Manon GORINI ', '1003', 9, 21),
(360, 'BLF', 'Emilie FORNARESIO ', '1004', 10, 21),
(361, 'BLF', 'Sophie MAYNIAL ', '1005', 11, 21),
(362, 'BLF', 'Karen CUCCHIETTI ', '1006', 12, 21),
(363, 'BLF', 'Stéphanie BARGES ', '1007', 13, 21),
(364, 'BLF', 'Jessica PINOIS ', '1008', 14, 21),
(365, 'BLF', 'Séverine TYLINSKI ', '1009', 15, 21),
(366, 'BLF', 'Aube CASELLA ', '1010', 16, 21),
(367, 'BLF', 'Chloé MARIOTTI ', '1011', 17, 21),
(368, 'BLF', 'Jennifer MEREL ', '1012', 18, 21),
(369, 'BLF', 'Dylan DEMARTINI ', '1013', 19, 21),
(370, 'BLF', 'Salle de réunion', '1014', 20, 21),
(371, 'BLF', 'Stagiaire 1', '1015', 21, 21),
(372, 'BLF', 'Stagiaire 2', '1016', 22, 21),
(373, '', '', '', 3, 21),
(374, 'Ligne', 'Ligne 1', '', 1, 22),
(375, 'Ligne', 'Ligne 2', '', 2, 22),
(376, 'BLF', 'Béatrice VADOT ', '1000', 4, 22),
(377, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 5, 22),
(378, 'BLF', 'Manon GORINI ', '1003', 6, 22),
(379, 'BLF', 'Emilie FORNARESIO ', '1004', 7, 22),
(380, 'BLF', 'Sophie MAYNIAL ', '1005', 8, 22),
(381, 'BLF', 'Karen CUCCHIETTI ', '1006', 9, 22),
(382, 'BLF', 'Stéphanie BARGES ', '1007', 10, 22),
(383, 'BLF', 'Jessica PINOIS ', '1008', 11, 22),
(384, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 22),
(385, 'BLF', 'Aube CASELLA ', '1010', 13, 22),
(386, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 22),
(387, 'BLF', 'Jennifer MEREL ', '1012', 15, 22),
(388, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 22),
(389, 'BLF', 'Salle de réunion', '1014', 17, 22),
(390, 'BLF', 'Stagiaire 1', '1015', 18, 22),
(391, 'BLF', 'Stagiaire 2', '1016', 19, 22),
(392, '', '', '', 3, 22),
(393, 'Ligne', 'Ligne 1', '', 1, 23),
(394, 'Ligne', 'Ligne 2', '', 2, 23),
(395, 'BLF', 'Béatrice VADOT ', '1000', 4, 23),
(396, 'BLF', 'Reynald GORINI', '1001', 5, 23),
(397, 'BLF', 'Manon GORINI ', '1003', 6, 23),
(398, 'BLF', 'Emilie FARNARESIO ', '1004', 7, 23),
(399, 'BLF', 'Sophie MAYNIAL ', '1005', 8, 23),
(400, 'BLF', 'Karen CUCCHIETTI ', '1006', 9, 23),
(401, 'BLF', 'Stéphanie BARGES ', '1007', 10, 23),
(402, 'BLF', 'Jessica PINOIS ', '1008', 11, 23),
(403, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 23),
(404, 'BLF', 'Aube CASELLA ', '1010', 13, 23),
(405, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 23),
(406, 'BLF', 'Jennifer MEREL ', '1012', 15, 23),
(407, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 23),
(408, 'BLF', 'Salle de réunion', '1014', 17, 23),
(409, 'BLF', 'Stagiaire 1', '1015', 18, 23),
(410, 'BLF', 'Stagiaire 2', '1016', 19, 23),
(411, '', '', '', 3, 23),
(412, 'Ligne', 'Ligne 1', '', 1, 24),
(413, 'Ligne', 'Ligne 2', '', 2, 24),
(414, 'BLF', 'Béatrice VADOT ', '1000', 4, 24),
(415, 'BLF', 'Reynald GORINI', '1001', 5, 24),
(416, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 24),
(417, 'BLF', 'Emilie FORNARESIO ', '1004', 7, 24),
(418, 'BLF', 'Sophie MAYNIAL ', '1005', 8, 24),
(419, 'BLF', 'Karen CUCCHIETTI ', '1006', 9, 24),
(420, 'BLF', 'Stéphanie BARGES ', '1007', 10, 24),
(421, 'BLF', 'Jessica PINOIS ', '1008', 11, 24),
(422, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 24),
(423, 'BLF', 'Aube CASELLA ', '1010', 13, 24),
(424, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 24),
(425, 'BLF', 'Jennifer MEREL ', '1012', 15, 24),
(426, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 24),
(427, 'BLF', 'Salle de réunion', '1014', 17, 24),
(428, 'BLF', 'Stagiaire 1', '1015', 18, 24),
(429, 'BLF', 'Stagiaire 2', '1016', 19, 24),
(430, '', '', '', 3, 24),
(431, 'Ligne', 'Ligne 1', '', 1, 26),
(432, 'Ligne', 'Ligne 2', '', 2, 26),
(433, 'BLF', 'Béatrice VADOT ', '1000', 7, 26),
(434, 'BLF', 'Reynald GORINI', '1001', 8, 26),
(435, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 9, 26),
(436, 'BLF', 'Manon GORINI ', '1003', 10, 26),
(437, 'BLF', 'Emilie FORNARESIO ', '1004', 11, 26),
(438, 'BLF', 'Karen CUCCHIETTI ', '1006', 12, 26),
(439, 'BLF', 'Stéphanie BARGES ', '1007', 13, 26),
(440, 'BLF', 'Jessica PINOIS ', '1008', 14, 26),
(441, 'BLF', 'Séverine TYLINSKI ', '1009', 15, 26),
(442, 'BLF', 'Aube CASELLA ', '1010', 16, 26),
(443, 'BLF', 'Chloé MARIOTTI ', '1011', 17, 26),
(444, 'BLF', 'Jennifer MEREL ', '1012', 18, 26),
(445, 'BLF', 'Dylan DEMARTINI ', '1013', 19, 26),
(446, 'BLF', 'Salle de réunion', '1014', 20, 26),
(447, 'BLF', 'Stagiaire 1', '1015', 21, 26),
(448, 'BLF', 'Stagiaire 2', '1016', 22, 26),
(449, '', '', '', 3, 26),
(450, 'Ligne', 'Ligne 1', '', 1, 27),
(451, 'Ligne', 'Ligne 2', '', 2, 27),
(452, 'BLF', 'Béatrice VADOT ', '1000', 4, 27),
(453, 'BLF', 'Reynald GORINI', '1001', 5, 27),
(454, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 27),
(455, 'BLF', 'Manon GORINI ', '1003', 7, 27),
(456, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 27),
(457, 'BLF', 'Sophie MALYAL ', '1005', 9, 27),
(458, 'BLF', 'Stéphanie BARGES ', '1007', 10, 27),
(459, 'BLF', 'Jessica PINOIS ', '1008', 11, 27),
(460, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 27),
(461, 'BLF', 'Aube CASELLA ', '1010', 13, 27),
(462, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 27),
(463, 'BLF', 'Jennifer MEREL ', '1012', 15, 27),
(464, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 27),
(465, 'BLF', 'Salle de réunion', '1014', 17, 27),
(466, 'BLF', 'Stagiaire 1', '1015', 18, 27),
(467, 'BLF', 'Stagiaire 2', '1016', 19, 27),
(468, '', '', '', 3, 27),
(469, 'Ligne', 'Ligne 1', '', 1, 28),
(470, 'Ligne', 'Ligne 2', '', 2, 28),
(471, 'BLF', 'Béatrice VADOT ', '1000', 4, 28),
(472, 'BLF', 'Reynald GORINI', '1001', 5, 28),
(473, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 28),
(474, 'BLF', 'Manon GORINI ', '1003', 7, 28),
(475, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 28),
(476, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 28),
(477, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 28),
(478, 'BLF', 'Jessica PINOIS ', '1008', 11, 28),
(479, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 28),
(480, 'BLF', 'Aube CASELLA ', '1010', 13, 28),
(481, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 28),
(482, 'BLF', 'Jennifer MEREL ', '1012', 15, 28),
(483, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 28),
(484, 'BLF', 'Salle de réunion', '1014', 17, 28),
(485, 'BLF', 'Stagiaire 1', '1015', 18, 28),
(486, 'BLF', 'Stagiaire 2', '1016', 19, 28),
(487, '', '', '', 3, 28),
(488, 'Ligne', 'Ligne 1', '', 1, 29),
(489, 'Ligne', 'Ligne 2', '', 2, 29),
(490, 'BLF', 'Béatrice VADOT ', '1000', 4, 29),
(491, 'BLF', 'Reynald GORINI', '1001', 5, 29),
(492, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 29),
(493, 'BLF', 'Manon GORINI ', '1003', 7, 29),
(494, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 29),
(495, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 29),
(496, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 29),
(497, 'BLF', 'Stéphanie BARGES ', '1007', 11, 29),
(498, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 29),
(499, 'BLF', 'Aube CASELLA ', '1010', 13, 29),
(500, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 29),
(501, 'BLF', 'Jennifer MEREL ', '1012', 15, 29),
(502, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 29),
(503, 'BLF', 'Salle de réunion', '1014', 17, 29),
(504, 'BLF', 'Stagiaire 1', '1015', 18, 29),
(505, 'BLF', 'Stagiaire 2', '1016', 19, 29),
(506, '', '', '', 3, 29),
(507, 'Ligne', 'Ligne 1', '', 1, 30),
(508, 'Ligne', 'Ligne 2', '', 2, 30),
(509, 'BLF', 'Béatrice VADOT ', '1000', 4, 30),
(510, 'BLF', 'Reynald GORINI', '1001', 5, 30),
(511, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 30),
(512, 'BLF', 'Manon GORINI ', '1003', 7, 30),
(513, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 30),
(514, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 30),
(515, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 30),
(516, 'BLF', 'Stéphanie BARGES ', '1007', 11, 30),
(517, 'BLF', 'Jessica PINOIS ', '1008', 12, 30),
(518, 'BLF', 'Aube CASELLA ', '1010', 13, 30),
(519, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 30),
(520, 'BLF', 'Jennifer MEREL ', '1012', 15, 30),
(521, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 30),
(522, 'BLF', 'Salle de réunion', '1014', 17, 30),
(523, 'BLF', 'Stagiaire 1', '1015', 18, 30),
(524, 'BLF', 'Stagiaire 2', '1016', 19, 30),
(525, '', '', '', 3, 30),
(526, 'Ligne', 'Ligne 1', '', 1, 31),
(527, 'Ligne', 'Ligne 2', '', 2, 31),
(528, 'BLF', 'Béatrice VADOT ', '1000', 4, 31),
(529, 'BLF', 'Reynald GORINI', '1001', 5, 31),
(530, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 31),
(531, 'BLF', 'Manon GORINI ', '1003', 7, 31),
(532, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 31),
(533, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 31),
(534, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 31),
(535, 'BLF', 'Stéphanie BARGES ', '1007', 11, 31),
(536, 'BLF', 'Jessica PINOIS ', '1008', 12, 31),
(537, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 31),
(538, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 31),
(539, 'BLF', 'Jennifer MEREL ', '1012', 15, 31),
(540, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 31),
(541, 'BLF', 'Salle de réunion', '1014', 17, 31),
(542, 'BLF', 'Stagiaire 1', '1015', 18, 31),
(543, 'BLF', 'Stagiaire 2', '1016', 19, 31),
(544, '', '', '', 3, 31),
(545, 'Ligne', 'Ligne 1', '', 1, 34),
(546, 'Ligne', 'Ligne 2', '', 2, 34),
(547, 'BLF', 'Béatrice VADOT ', '1000', 4, 34),
(548, 'BLF', 'Reynald GORINI', '1001', 5, 34),
(549, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 34),
(550, 'BLF', 'Manon GORINI ', '1003', 7, 34),
(551, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 34),
(552, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 34),
(553, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 34),
(554, 'BLF', 'Stéphanie BARGES ', '1007', 11, 34),
(555, 'BLF', 'Jessica PINOIS ', '1008', 12, 34),
(556, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 34),
(557, 'BLF', 'Aube CASELLA ', '1010', 14, 34),
(558, 'BLF', 'Jennifer MEREL ', '1012', 15, 34),
(559, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 34),
(560, 'BLF', 'Salle de réunion', '1014', 17, 34),
(561, 'BLF', 'Stagiaire 1', '1015', 18, 34),
(562, 'BLF', 'Stagiaire 2', '1016', 19, 34),
(563, '', '', '', 3, 34),
(564, 'Ligne', 'Ligne 1', '', 1, 36),
(565, 'Ligne', 'Ligne 2', '', 2, 36),
(566, 'BLF', 'Béatrice VADOT ', '1000', 4, 36),
(567, 'BLF', 'Reynald GORINI', '1001', 5, 36),
(568, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 36),
(569, 'BLF', 'Manon GORINI ', '1003', 7, 36),
(570, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 36),
(571, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 36),
(572, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 36),
(573, 'BLF', 'Stéphanie BARGES ', '1007', 11, 36),
(574, 'BLF', 'Jessica PINOIS ', '1008', 12, 36),
(575, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 36),
(576, 'BLF', 'Aube CASELLA ', '1010', 14, 36),
(577, 'BLF', 'Chloé MARIOTTI ', '1011', 15, 36),
(578, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 36),
(579, 'BLF', 'Salle de réunion', '1014', 17, 36),
(580, 'BLF', 'Stagiaire 1', '1015', 18, 36),
(581, 'BLF', 'Stagiaire 2', '1016', 19, 36),
(582, '', '', '', 3, 36),
(583, 'Ligne', 'Ligne 1', '', 1, 37),
(584, 'Ligne', 'Ligne 2', '', 2, 37),
(585, 'BLF', 'Béatrice VADOT ', '1000', 4, 37),
(586, 'BLF', 'Reynald GORINI', '1001', 5, 37),
(587, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 37),
(588, 'BLF', 'Manon GORINI ', '1003', 7, 37),
(589, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 37),
(590, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 37),
(591, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 37),
(592, 'BLF', 'Stéphanie BARGES ', '1007', 11, 37),
(593, 'BLF', 'Jessica PINOIS ', '1008', 12, 37),
(594, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 37),
(595, 'BLF', 'Aube CASELLA ', '1010', 14, 37),
(596, 'BLF', 'Chloé MARIOTTI ', '1011', 15, 37),
(597, 'BLF', 'Jennifer MEREL ', '1012', 16, 37),
(598, 'BLF', 'Salle de réunion', '1014', 17, 37),
(599, 'BLF', 'Stagiaire 1', '1015', 18, 37),
(600, 'BLF', 'Stagiaire 2', '1016', 19, 37),
(601, '', '', '', 3, 37),
(602, 'Ligne', 'Ligne 1', '', 1, 38),
(603, 'Ligne', 'Ligne 2', '', 2, 38),
(604, 'BLF', 'Béatrice VADOT ', '1000', 4, 38),
(605, 'BLF', 'Reynald GORINI', '1001', 5, 38),
(606, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 38),
(607, 'BLF', 'Manon GORINI ', '1003', 7, 38),
(608, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 38),
(609, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 38),
(610, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 38),
(611, 'BLF', 'Stéphanie BARGES ', '1007', 11, 38),
(612, 'BLF', 'Jessica PINOIS ', '1008', 12, 38),
(613, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 38),
(614, 'BLF', 'Aube CASELLA ', '1010', 14, 38),
(615, 'BLF', 'Chloé MARIOTTI ', '1011', 15, 38),
(616, 'BLF', 'Jennifer MEREL ', '1012', 16, 38),
(617, 'BLF', 'Dylan DEMARTINI ', '1013', 17, 38),
(618, 'BLF', 'Stagiaire 1', '1015', 18, 38),
(619, 'BLF', 'Stagiaire 2', '1016', 19, 38),
(620, '', '', '', 3, 38),
(621, 'Ligne', 'Ligne 1', '', 1, 41),
(622, 'Ligne', 'Ligne 2', '', 2, 41),
(623, 'BLF', 'Béatrice VADOT ', '1000', 4, 41),
(624, 'BLF', 'Reynald GORINI', '1001', 5, 41),
(625, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 41),
(626, 'BLF', 'Manon GORINI ', '1003', 7, 41),
(627, 'BLF', 'Emilie FORNARESIO ', '1004', 8, 41),
(628, 'BLF', 'Sophie MAYNIAL ', '1005', 9, 41),
(629, 'BLF', 'Karen CUCCHIETTI ', '1006', 10, 41),
(630, 'BLF', 'Stéphanie BARGES ', '1007', 11, 41),
(631, 'BLF', 'Jessica PINOIS ', '1008', 12, 41),
(632, 'BLF', 'Séverine TYLINSKI ', '1009', 13, 41),
(633, 'BLF', 'Aube CASELLA ', '1010', 14, 41),
(634, 'BLF', 'Chloé MARIOTTI ', '1011', 15, 41),
(635, 'BLF', 'Jennifer MEREL ', '1012', 16, 41),
(636, 'BLF', 'Dylan DEMARTINI ', '1013', 17, 41),
(637, 'BLF', 'Salle de réunion', '1014', 18, 41),
(638, 'BLF', 'Stagiaire 2', '1016', 19, 41),
(639, '', '', '', 3, 41),
(640, 'Numerotation', 'LoLo', '0625331426', 5, 1),
(641, 'Numerotation', 'LoLo2', '0625331426', 6, 1),
(642, 'Ligne', 'Ligne 1', '', 1, 43),
(643, 'Ligne', 'Ligne 2', '', 2, 43),
(644, 'BLF', 'Pierre', '105', 3, 43),
(645, 'BLF', 'Test Laurent', '125', 4, 43),
(646, 'BLF', 'Test 2', '145', 5, 43),
(647, 'Numerotation', 'TestP2', '123', 7, 1),
(648, 'Numerotation', 'TestP2', '123', 8, 1),
(649, 'Numerotation', 'Geoffroy', '116', 9, 1),
(650, 'Numerotation', 'TestP2', '1243', 10, 1),
(651, 'Numerotation', 'TestP2', '123', 11, 1),
(652, 'Numerotation', 'TestP2', '2354', 12, 1),
(653, 'Numerotation', 'TestP3', '4356', 13, 1),
(654, 'Ligne', '', '', 1, 46),
(655, 'Ligne', '', '', 1, 45),
(656, 'Ligne', '', '', 1, 44),
(657, 'Ligne', 'Ligne 1', '', 1, 25),
(658, 'Ligne', 'Ligne 2', '', 2, 25),
(659, 'BLF', 'Béatrice VADOT ', '1000', 4, 25),
(660, 'BLF', 'Reynald GORINI', '1001', 5, 25),
(661, 'BLF', 'Jean-Charles ACCIAIOLI ', '1002', 6, 25),
(662, 'BLF', 'Manon GORINI ', '1003', 7, 25),
(663, 'BLF', 'Sophie MAYNIAL ', '1005', 8, 25),
(664, 'BLF', 'Karen CUCCHIETTI ', '1006', 9, 25),
(665, 'BLF', 'Stéphanie BARGES ', '1007', 10, 25),
(666, 'BLF', 'Jessica PINOIS ', '1008', 11, 25),
(667, 'BLF', 'Séverine TYLINSKI ', '1009', 12, 25),
(668, 'BLF', 'Aube CASELLA ', '1010', 13, 25),
(669, 'BLF', 'Chloé MARIOTTI ', '1011', 14, 25),
(670, 'BLF', 'Jennifer MEREL ', '1012', 15, 25),
(671, 'BLF', 'Dylan DEMARTINI ', '1013', 16, 25),
(672, 'BLF', 'Salle de réunion', '1014', 17, 25),
(673, 'BLF', 'Stagiaire 1', '1015', 18, 25),
(674, 'BLF', 'Stagiaire 2', '1016', 19, 25),
(675, '', '', '', 3, 25),
(676, 'BLF', 'Rep. Automatique', '*800', 4, 21),
(677, 'BLF', 'Ouverture', '*802', 6, 21),
(678, 'BLF', 'Rep. Fermeture', '*801', 5, 21),
(679, 'BLF', 'Rep. Automatique', '*810', 4, 26),
(680, 'BLF', 'Rep. Fermeture', '*811', 5, 26),
(681, 'BLF', 'Ouverture', '*812', 6, 26);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Annuaires`
--
ALTER TABLE `Annuaires`
  ADD PRIMARY KEY (`idAnnuaires`),
  ADD KEY `fk_Annuaires_clients1_idx` (`clients_idclients`);

--
-- Index pour la table `Clients`
--
ALTER TABLE `Clients`
  ADD PRIMARY KEY (`idclients`),
  ADD KEY `fk_clients_partenaires_idx` (`partenaires_idpartenaires`);

--
-- Index pour la table `Partenaires`
--
ALTER TABLE `Partenaires`
  ADD PRIMARY KEY (`idpartenaires`);

--
-- Index pour la table `Plateformes`
--
ALTER TABLE `Plateformes`
  ADD PRIMARY KEY (`idplateforme`);

--
-- Index pour la table `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`idRole`),
  ADD KEY `fk_Role_partenaires1_idx` (`partenaires_idpartenaires`),
  ADD KEY `fk_Role_clients1_idx` (`clients_idclients`);

--
-- Index pour la table `TypePostes`
--
ALTER TABLE `TypePostes`
  ADD PRIMARY KEY (`idtypeposte`);

--
-- Index pour la table `User_annuaire`
--
ALTER TABLE `User_annuaire`
  ADD PRIMARY KEY (`idUserAnnuaire`),
  ADD KEY `annuaire_id` (`annuaire_id`);

--
-- Index pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  ADD PRIMARY KEY (`idutilisateurs`);

--
-- Index pour la table `UtilisateursBLF`
--
ALTER TABLE `UtilisateursBLF`
  ADD PRIMARY KEY (`idblf`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Annuaires`
--
ALTER TABLE `Annuaires`
  MODIFY `idAnnuaires` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `Clients`
--
ALTER TABLE `Clients`
  MODIFY `idclients` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `Partenaires`
--
ALTER TABLE `Partenaires`
  MODIFY `idpartenaires` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=346;

--
-- AUTO_INCREMENT pour la table `Plateformes`
--
ALTER TABLE `Plateformes`
  MODIFY `idplateforme` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `Roles`
--
ALTER TABLE `Roles`
  MODIFY `idRole` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `TypePostes`
--
ALTER TABLE `TypePostes`
  MODIFY `idtypeposte` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `User_annuaire`
--
ALTER TABLE `User_annuaire`
  MODIFY `idUserAnnuaire` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Utilisateurs`
--
ALTER TABLE `Utilisateurs`
  MODIFY `idutilisateurs` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT pour la table `UtilisateursBLF`
--
ALTER TABLE `UtilisateursBLF`
  MODIFY `idblf` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=682;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Annuaires`
--
ALTER TABLE `Annuaires`
  ADD CONSTRAINT `fk_Annuaires_clients1` FOREIGN KEY (`clients_idclients`) REFERENCES `Clients` (`idclients`);

--
-- Contraintes pour la table `Clients`
--
ALTER TABLE `Clients`
  ADD CONSTRAINT `fk_clients_partenaires` FOREIGN KEY (`partenaires_idpartenaires`) REFERENCES `Partenaires` (`idpartenaires`);

--
-- Contraintes pour la table `Roles`
--
ALTER TABLE `Roles`
  ADD CONSTRAINT `fk_Role_clients1` FOREIGN KEY (`clients_idclients`) REFERENCES `Clients` (`idclients`),
  ADD CONSTRAINT `fk_Role_partenaires1` FOREIGN KEY (`partenaires_idpartenaires`) REFERENCES `Partenaires` (`idpartenaires`);

--
-- Contraintes pour la table `User_annuaire`
--
ALTER TABLE `User_annuaire`
  ADD CONSTRAINT `User_annuaire_ibfk_1` FOREIGN KEY (`annuaire_id`) REFERENCES `Annuaires` (`idAnnuaires`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
