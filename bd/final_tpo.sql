-- Drop the database if it exists
DROP DATABASE IF EXISTS final_tpo;

-- Create the database if it does not exist
CREATE DATABASE IF NOT EXISTS final_tpo;
USE final_tpo;

-- Set the default character set
ALTER DATABASE `final_tpo` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

--
-- Base de données : `final_tpo`
--

-- --------------------------------------------------------

--
-- Structure de la table `archive_conge`
--

DROP TABLE IF EXISTS `archive_conge`;
CREATE TABLE IF NOT EXISTS `archive_conge` (
  `id_demande` int NOT NULL DEFAULT '0',
  `id_membre` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `raison` text NOT NULL,
  `date_demande` date NOT NULL,
  `superviseur` int NOT NULL,
  `chef_departement` int DEFAULT NULL,
  `type_conge` enum('annuel','maladie','maternité') NOT NULL,
  `statut` enum('en attente','approuvé','non approuvé') DEFAULT 'en attente',
  `superviseur_status` enum('approuvée','non approuvée','en attente') DEFAULT 'en attente',
  `chef_departement_status` enum('approuvée','non approuvée','en attente') DEFAULT 'en attente'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_conge`
--

DROP TABLE IF EXISTS `demandes_conge`;
CREATE TABLE IF NOT EXISTS `demandes_conge` (
  `id_demande` int NOT NULL AUTO_INCREMENT,
  `id_membre` int NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `raison` text NOT NULL,
  `date_demande` date NOT NULL,
  `superviseur` int NOT NULL,
  `chef_departement` int DEFAULT NULL,
  `type_conge` enum('annuel','maladie','maternité') NOT NULL,
  `statut` enum('en attente','approuvé','non approuvé') DEFAULT 'en attente',
  `superviseur_status` enum('approuvée','non approuvée','en attente') DEFAULT 'en attente',
  `chef_departement_status` enum('approuvée','non approuvée','en attente') DEFAULT 'en attente',
  `archivage_time` datetime DEFAULT NULL,
  PRIMARY KEY (`id_demande`),
  KEY `id_membre` (`id_membre`),
  KEY `superviseur` (`superviseur`),
  KEY `fk_chef_departement_conge` (`chef_departement`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `demandes_conge`
--

INSERT INTO `demandes_conge` (`id_demande`, `id_membre`, `date_debut`, `date_fin`, `raison`, `date_demande`, `superviseur`, `chef_departement`, `type_conge`, `statut`, `superviseur_status`, `chef_departement_status`, `archivage_time`) VALUES
(13, 34, '2024-08-26', '2024-08-27', 'ddd', '2024-08-23', 1, 1, 'annuel', 'approuvé', 'en attente', 'en attente', '2024-08-24 10:51:49'),
(14, 36, '2024-08-26', '2024-08-27', 'hkll', '2024-08-23', 1, 1, 'annuel', 'en attente', '', '', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `department`
--

DROP TABLE IF EXISTS `department`;
CREATE TABLE IF NOT EXISTS `department` (
  `id_department` int NOT NULL AUTO_INCREMENT,
  `nom_department` varchar(100) NOT NULL,
  `description_department` text,
  `effectif` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_department`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `department`
--

INSERT INTO `department` (`id_department`, `nom_department`, `description_department`, `effectif`) VALUES
(2, 'Direction', 'la directionD ./DN?D/', 0),
(4, 'Programme', 'gère tout les projets', 3),
(6, 'Finance', 'Organe financier', 0),
(8, 'ICT', 'La communication et l\'informatique', 1),
(11, 'Economies', 'JYTJY', 0);

-- --------------------------------------------------------

--
-- Structure de la table `documents_membres`
--

DROP TABLE IF EXISTS `documents_membres`;
CREATE TABLE IF NOT EXISTS `documents_membres` (
  `id_document` int NOT NULL AUTO_INCREMENT,
  `id_membre` int NOT NULL,
  `nom_document` varchar(255) NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_upload` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_document`),
  KEY `id_membre` (`id_membre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `fichiers`
--

DROP TABLE IF EXISTS `fichiers`;
CREATE TABLE IF NOT EXISTS `fichiers` (
  `id_fichier` int NOT NULL AUTO_INCREMENT,
  `id_membre` int NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `type_fichier` varchar(50) NOT NULL,
  `taille_fichier` int NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `date_ajout` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_fichier`),
  KEY `id_membre` (`id_membre`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `historique`
--

DROP TABLE IF EXISTS `historique`;
CREATE TABLE IF NOT EXISTS `historique` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `date_action` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb3;

--
-- --------------------------------------------------------

--
-- Structure de la table `membres`
--

DROP TABLE IF EXISTS `membres`;
CREATE TABLE IF NOT EXISTS `membres` (
  `id_membre` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `genre` enum('femme','homme') DEFAULT 'homme',
  `numero_telephone` varchar(20) DEFAULT NULL,
  `email_prive` varchar(100) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `photo` varchar(255) DEFAULT NULL,
  `type_contrat` enum('CDD','CDI') DEFAULT 'CDD',
  `fonction` varchar(100) DEFAULT NULL,
  `date_entree` date DEFAULT NULL,
  `postnom` varchar(255) DEFAULT NULL,
  `nom_pere` varchar(255) DEFAULT NULL,
  `nom_mere` varchar(255) DEFAULT NULL,
  `nationalite` varchar(100) DEFAULT NULL,
  `province_origine` varchar(100) DEFAULT NULL,
  `lieu_affectation` varchar(255) DEFAULT NULL,
  `matricule` varchar(50) DEFAULT NULL,
  `num_identification` varchar(50) DEFAULT NULL,
  `etat_civil` enum('marié','célibataire','divorcé','veuf') DEFAULT 'célibataire',
  `deuxieme_adresse` varchar(255) DEFAULT NULL,
  `email_professionel` varchar(255) DEFAULT NULL,
  `num_compte_banque` varchar(50) DEFAULT NULL,
  `personne_reference` varchar(255) DEFAULT NULL,
  `lieu_naissance` varchar(100) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `date_debut_contrat` date DEFAULT NULL,
  `date_fin_contrat` date DEFAULT NULL,
  `status` enum('actif','en congé') DEFAULT 'actif',
  `performance` decimal(5,2) DEFAULT NULL,
  `salaire` decimal(10,2) DEFAULT NULL,
  `salaire_net` decimal(10,2) DEFAULT NULL,
  `brut_B` decimal(10,2) DEFAULT NULL,
  `net_B` decimal(10,2) DEFAULT NULL,
  `id_project` int DEFAULT NULL,
  `id_user` int DEFAULT NULL,
  `id_department` int DEFAULT NULL,
  `contrat` enum('3 mois','6 mois','12 mois') NOT NULL,
  `jours_conge_restants` int DEFAULT '0',
  `personne_de_contact` varchar(255) DEFAULT NULL,
  `partenaire` varchar(255) DEFAULT NULL,
  `nombre_enfant` int DEFAULT NULL,
  PRIMARY KEY (`id_membre`),
  UNIQUE KEY `email_professionel` (`email_professionel`),
  UNIQUE KEY `email_prive` (`email_prive`),
  KEY `fk_department` (`id_department`),
  KEY `fk_user` (`id_user`),
  KEY `fk_project_member` (`id_project`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `membres`
--

INSERT INTO `membres` (`id_membre`, `nom`, `prenom`, `adresse`, `genre`, `numero_telephone`, `email_prive`, `age`, `created_at`, `updated_at`, `photo`, `type_contrat`, `fonction`, `date_entree`, `postnom`, `nom_pere`, `nom_mere`, `nationalite`, `province_origine`, `lieu_affectation`, `matricule`, `num_identification`, `etat_civil`, `deuxieme_adresse`, `email_professionel`, `num_compte_banque`, `personne_reference`, `lieu_naissance`, `date_naissance`, `date_debut_contrat`, `date_fin_contrat`, `status`, `performance`, `salaire`, `salaire_net`, `brut_B`, `net_B`, `id_project`, `id_user`, `id_department`, `contrat`, `jours_conge_restants`, `personne_de_contact`, `partenaire`, `nombre_enfant`) VALUES
(34, 'Mihona', 'Edouard', 'Bukavu', 'homme', '09876543', 'edo@gmail.com', NULL, '2024-08-23 10:22:45', '2024-08-23 12:42:12', NULL, 'CDI', 'IT', NULL, 'Edouard', '', '', '', '', '', 'TPOM6S7', '', 'célibataire', '', 'edo@tpordc.org', '', '', '', '2000-08-08', '2024-08-23', '2025-01-22', 'en congé', NULL, 566.00, 396.20, NULL, 0.00, 6, NULL, 8, '3 mois', 0, NULL, NULL, NULL),
(36, 'Haricot', 'GG', 'bukavu', 'homme', '0976666666', 'eedt@gmail.com', NULL, '2024-08-23 12:24:33', '2024-08-27 17:05:20', NULL, 'CDD', 'GGG', NULL, 'RER', '', '', '', '', '', 'TPO4577', '', 'marié', '', 'edFSdy@tpordc.org', '', '', '', '2000-08-23', '2024-08-23', '2025-01-30', 'en congé', NULL, 5000.00, 3500.00, NULL, 200.00, 6, NULL, 4, '3 mois', 0, '', 'eddy', 4);

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id_notification` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `message` text NOT NULL,
  `date_notification` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_notification`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`id_notification`, `id_user`, `message`, `date_notification`, `is_read`) VALUES
(6, 32, 'Votre demande de congé a été approuvée.', '2024-08-23 10:45:20', 0),
(7, 32, 'Votre demande de congé a été approuvée.', '2024-08-23 10:46:14', 0),
(8, 32, 'Votre demande de congé a été approuvée.', '2024-08-23 10:51:49', 0),
(9, 34, 'Votre demande de congé a été approuvée.', '2024-08-23 12:59:18', 0);

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

DROP TABLE IF EXISTS `paiement`;
CREATE TABLE IF NOT EXISTS `paiement` (
  `id_paiement` int NOT NULL AUTO_INCREMENT,
  `id_membre` int NOT NULL,
  `mois_paiement` varchar(50) NOT NULL,
  `jour_paiement` date NOT NULL,
  `salaire_paye` decimal(10,2) DEFAULT NULL,
  `etat` enum('en attente','payé','avance') DEFAULT 'en attente',
  PRIMARY KEY (`id_paiement`),
  KEY `fk_membre` (`id_membre`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb3;

--
-- --------------------------------------------------------

--
-- Structure de la table `presences`
--

DROP TABLE IF EXISTS `presences`;
CREATE TABLE IF NOT EXISTS `presences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `noms` varchar(255) NOT NULL,
  `heure_entree` time NOT NULL,
  `heure_sortie` time NOT NULL,
  `id_membre` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_id_membre` (`id_membre`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `presences`
--

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id_project` int NOT NULL AUTO_INCREMENT,
  `nom_project` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_project` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `bailleur` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `create_by` int DEFAULT NULL,
  `effectif` int DEFAULT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` timestamp NULL DEFAULT NULL,
  `modified_by` int DEFAULT NULL,
  PRIMARY KEY (`id_project`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id_project`, `nom_project`, `description_project`, `bailleur`, `budget`, `create_by`, `effectif`, `create_date`, `modified_date`, `modified_by`) VALUES
(1, 'PMNS', '', 'Banque mondial', 1222.00, NULL, 0, '2024-07-16 08:17:58', NULL, NULL),
(2, 'PEMIR', '', 'MONUSCO', 12333.00, 1, 0, '2024-07-16 09:24:14', NULL, NULL),
(4, 'ddc-ah', 'mlskmlksml', 'USAID', 12333.00, 1, 2, '2024-07-18 00:54:43', NULL, NULL),
(5, 'VBG CERFF', 'kklmk', 'CERF', 2000.00, 1, 3, '2024-08-23 06:32:17', NULL, NULL),
(6, 'FFF', 'JJ', 'USAID', 12333.00, 1, 3, '2024-08-23 12:41:55', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','rh','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `profile_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `role`, `created_at`, `updated_at`, `status`, `profile_picture`) VALUES
(1, 'admin', 'admin@example.com', '$2y$10$Yy1e9RMtbFRnfzCrMCIbPejVaRr3IRGwhpi1iAC1SKeSyK2DtQ2MK', 'admin', '2024-07-16 01:35:23', NULL, 'active', NULL);
--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `documents_membres`
--
ALTER TABLE `documents_membres`
  ADD CONSTRAINT `documents_membres_ibfk_1` FOREIGN KEY (`id_membre`) REFERENCES `membres` (`id_membre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fichiers`
--
ALTER TABLE `fichiers`
  ADD CONSTRAINT `fk_membre_fichiers` FOREIGN KEY (`id_membre`) REFERENCES `membres` (`id_membre`);

--
-- Contraintes pour la table `historique`
--
ALTER TABLE `historique`
  ADD CONSTRAINT `fk_user_historique` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `membres`
--
ALTER TABLE `membres`
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`id_department`) REFERENCES `department` (`id_department`),
  ADD CONSTRAINT `fk_project_member` FOREIGN KEY (`id_project`) REFERENCES `projects` (`id_project`),
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `fk_membre_paiement` FOREIGN KEY (`id_membre`) REFERENCES `membres` (`id_membre`);
COMMIT;
