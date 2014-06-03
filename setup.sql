--
-- Setup module cip
--

-- --------------------------------------------------------

--
-- Table structure for table `cip_cip`
--

CREATE TABLE IF NOT EXISTS `cip_cip` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(11) unsigned DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `idFeasibility` int(11) unsigned DEFAULT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 valorization, 2 edit, 3 edit technical parameters, 4 edit chemical analysis',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `request` text COLLATE utf8_unicode_ci NOT NULL,
  `urgent` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 false, 1 true',
  `development` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 false, 1 true',
  `critical` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 false, 1 true',
  `criticalReason` text COLLATE utf8_unicode_ci,
  `currentCost` double unsigned NOT NULL DEFAULT '0',
  `evaluationIFP` text COLLATE utf8_unicode_ci,
  `evaluationCOGE` text COLLATE utf8_unicode_ci,
  `cost` double unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '0 draft, 1 emitted, 21 analisysAT, 22 analysisIFP, 23 analysisCOGE, 3 infeasible, 4 approved, 51 standardizationAT, 52 standardizationIFP, 53 standardizationCOGE, 6 standardized',
  `addDate` datetime DEFAULT NULL,
  `addIdAccount` int(11) DEFAULT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  `appDate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idFeasibility` (`idFeasibility`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cip_details`
--

CREATE TABLE IF NOT EXISTS `cip_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCip` int(11) unsigned NOT NULL,
  `productLine` varchar(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `execution` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `profile` varchar(8) COLLATE utf8_unicode_ci DEFAULT NULL,
  `grade` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `size1Min` double unsigned NOT NULL DEFAULT '0',
  `size1Max` double unsigned NOT NULL DEFAULT '0',
  `size2Min` double unsigned NOT NULL DEFAULT '0',
  `size2Max` double unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idCip` (`idCip`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cip_attachments`
--

CREATE TABLE IF NOT EXISTS `cip_attachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCip` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) NOT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file` longblob NOT NULL,
  `label` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma separated tag',
  `txtContent` text COLLATE utf8_unicode_ci COMMENT 'textual file content for search queries',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) NOT NULL DEFAULT '0',
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  `del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idCip` (`idCip`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Dati della tabelle `settings_permissions`
--

INSERT IGNORE INTO `settings_permissions` (`id`, `module`, `action`, `description`, `locked`) VALUES
(NULL, 'cip', 'cip_view', 'View CIP', 0),
(NULL, 'cip', 'cip_costs', 'View CIP cost', 0),
(NULL, 'cip', 'cip_emit', 'Emit CIP', 0),
(NULL, 'cip', 'cip_analisysAT', 'Analisys AT', 0),
(NULL, 'cip', 'cip_analisysIFP', 'Analisys IFP', 0),
(NULL, 'cip', 'cip_analisysCOGE', 'Analisys COGE', 0),
(NULL, 'cip', 'cip_revision', 'Revision request', 0),
(NULL, 'cip', 'cip_standardization', 'Standardization request', 0),
(NULL, 'cip', 'cip_standardizationBKO', 'Standardization BKO', 0),
(NULL, 'cip', 'cip_standardizationIFP', 'Standardization IFP', 0),
(NULL, 'cip', 'cip_standardizationCOGE', 'Standardization COGE', 0),
(NULL, 'cip', 'cip_reset', 'Reset CIP status', 0);

-- --------------------------------------------------------

--
-- Dumping data for table `logs_triggers`
--

INSERT INTO `logs_triggers` (`id`, `trigger`, `module`, `action`, `condition`) VALUES
(NULL, 'logs_cip_cipDisponible', 'cip', 'cipEmitted', 'cip_conditions_cipProcessable'),
(NULL, 'logs_cip_cipDisponible', 'cip', 'cipStatus', 'cip_conditions_cipProcessable'),
(NULL, 'logs_cip_cipDisponible', 'cip', 'cipResetted', 'cip_conditions_cipProcessable');

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cip_details`
--
ALTER TABLE `cip_details`
  ADD CONSTRAINT `cip_details_ibfk_1` FOREIGN KEY (`idCip`) REFERENCES `cip_cip` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cip_attachments`
--
ALTER TABLE `cip_attachments`
  ADD CONSTRAINT `cip_attachments_ibfk_1` FOREIGN KEY (`idCip`) REFERENCES `cip_cip` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

-- --------------------------------------------------------