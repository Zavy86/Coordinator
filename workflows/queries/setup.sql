--
-- Setup module workflows
--

-- --------------------------------------------------------

--
-- Table structure for table `workflows_actions`
--

CREATE TABLE IF NOT EXISTS `workflows_actions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idFlow` int(11) unsigned NOT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 ticket, 2 external ticket, 3 authorization',
  `requiredAction` int(11) unsigned DEFAULT NULL COMMENT 'id of the action required',
  `conditionedField` int(11) unsigned DEFAULT NULL COMMENT 'id of the condition field',
  `conditionedValue` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'value of the condition field',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `idGroup` int(11) unsigned DEFAULT NULL,
  `idAssigned` int(11) unsigned DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `difficulty` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 low, 2 medium, 3 high',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `slaAssignment` int(5) unsigned NOT NULL DEFAULT '0',
  `slaClosure` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idFlow` (`idFlow`),
  KEY `conditionedField` (`conditionedField`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows_attachments`
--

CREATE TABLE IF NOT EXISTS `workflows_attachments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` int(11) NOT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `file` longblob NOT NULL,
  `label` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'comma separated tag',
  `txtContent` text COLLATE utf8_unicode_ci COMMENT 'Textual file content for search queries',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) NOT NULL DEFAULT '0',
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  `del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows_categories`
--

CREATE TABLE IF NOT EXISTS `workflows_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCategory` (`idCategory`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `workflows_categories`
--

INSERT IGNORE INTO `workflows_categories` (`id`, `idCategory`, `name`, `description`, `idGroup`, `addDate`, `addIdAccount`, `updDate`, `updIdAccount`) VALUES
(1, 0, 'Support', 'Reporting problems and support requests', 1, now(), 1, NULL, NULL),
(2, 1, 'Hardware', 'Support for hardware issues', 1, now(), 1, NULL, NULL),
(3, 1, 'Software', 'Support for software issues', 1, now(), 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workflows_fields`
--

CREATE TABLE IF NOT EXISTS `workflows_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idFlow` int(11) unsigned NOT NULL DEFAULT '0',
  `typology` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `placeholder` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options_method` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `options_values` text COLLATE utf8_unicode_ci,
  `options_query` text COLLATE utf8_unicode_ci,
  `required` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `position` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idFlow` (`idFlow`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows_flows`
--

CREATE TABLE IF NOT EXISTS `workflows_flows` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) unsigned DEFAULT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 request, 2 incident',
  `pinned` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `advice` text COLLATE utf8_unicode_ci,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `sla` int(5) unsigned NOT NULL DEFAULT '0',
  `guide` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCategory` (`idCategory`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows_tickets`
--

CREATE TABLE IF NOT EXISTS `workflows_tickets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idWorkflow` int(11) unsigned NOT NULL,
  `idCategory` int(11) unsigned DEFAULT NULL,
  `requiredTicket` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'id of the required ticket',
  `requiredAction` int(11) unsigned DEFAULT NULL COMMENT 'id of the required flow action',
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 ticket, 2 external ticket, 3 authorization',
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `idGroup` int(11) unsigned DEFAULT NULL,
  `idAssigned` int(11) unsigned DEFAULT NULL,
  `difficulty` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 low, 2 medium, 3 high',
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `slaAssignment` int(5) unsigned NOT NULL DEFAULT '0',
  `slaClosure` int(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 open, 2 assigned, 3 standby, 4 closed, 5 locked',
  `solved` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 unexecuted, 1 executed, 2 unnecessary',
  `approved` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 no, 1 yes',
  `hostname` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned DEFAULT NULL,
  `updDate` datetime DEFAULT NULL,
  `assDate` datetime DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idWorkflow` (`idWorkflow`),
  KEY `idCategory` (`idCategory`),
  KEY `requiredAction` (`requiredAction`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `workflows_tickets_notes`
--

CREATE TABLE IF NOT EXISTS `workflows_tickets_notes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idTicket` int(11) unsigned NOT NULL,
  `note` text COLLATE utf8_unicode_ci NOT NULL,
  `addDate` datetime DEFAULT NULL,
  `addIdAccount` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idTicket` (`idTicket`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Constraints for table `workflows_tickets_notes`
--

ALTER TABLE `workflows_tickets_notes`
  ADD CONSTRAINT `workflows_tickets_notes_ibfk_1` FOREIGN KEY (`idTicket`) REFERENCES `workflows_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `workflows_workflows`
--

CREATE TABLE IF NOT EXISTS `workflows_workflows` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCategory` int(11) unsigned DEFAULT NULL,
  `idFlow` int(11) unsigned DEFAULT NULL,
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 request, 2 incident',
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `note` text COLLATE utf8_unicode_ci,
  `priority` tinyint(1) unsigned NOT NULL DEFAULT '3' COMMENT '1 highest, 2 high, 3 medium, 4 low, 5 lowest',
  `sla` int(5) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 open, 2 assigned, 3 standby, 4 closed',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned DEFAULT NULL,
  `endDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCategory` (`idCategory`),
  KEY `idFlow` (`idFlow`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `settings_permissions`
--

INSERT IGNORE INTO `settings_permissions` (`id`,`module`,`action`,`description`,`locked`) VALUES
(NULL,'workflows','workflows_view','View workflows','0'),
(NULL,'workflows','workflows_add','Open a workflow','0'),
(NULL,'workflows','workflows_','Process a workflow','0'),
(NULL,'workflows','workflows_admin','Administer workflows and categories','0');

-- --------------------------------------------------------

--
-- Dumping data for table `logs_triggers`
--

INSERT INTO `logs_triggers` (`id`, `trigger`, `module`, `action`, `condition`) VALUES
(NULL, 'logs_workflows_workflowCreated', 'workflows', 'workflowCreated', 'workflows_conditions_workflowOwner'),
(NULL, 'logs_workflows_workflowAssigned', 'workflows', 'workflowAssigned', 'workflows_conditions_workflowOwner'),
(NULL, 'logs_workflows_workflowClosed', 'workflows', 'workflowClosed', 'workflows_conditions_workflowOwner'),
(NULL, 'logs_workflows_ticketDisponible', 'workflows', 'ticketCreated', 'workflows_conditions_ticketProcessable'),
(NULL, 'logs_workflows_ticketDisponible', 'workflows', 'ticketUnlocked', 'workflows_conditions_ticketProcessable'),
(NULL, 'logs_workflows_ticketClosed', 'workflows', 'ticketClosed', 'workflows_conditions_ticketOwner'),
(NULL, 'logs_workflows_ticketStandby', 'workflows', 'ticketStandby', 'workflows_conditions_ticketOwner');

-- --------------------------------------------------------

--
-- Constraints for table `workflows_actions`
--

ALTER TABLE `workflows_actions`
  ADD CONSTRAINT `workflows_actions_ibfk_1` FOREIGN KEY (`idFlow`) REFERENCES `workflows_flows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflows_actions_ibfk_2` FOREIGN KEY (`conditionedField`) REFERENCES `workflows_fields` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `workflows_fields`
--

ALTER TABLE `workflows_fields`
  ADD CONSTRAINT `workflows_fields_ibfk_1` FOREIGN KEY (`idFlow`) REFERENCES `workflows_flows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `workflows_flows`
--

ALTER TABLE `workflows_flows`
  ADD CONSTRAINT `workflows_flows_ibfk_1` FOREIGN KEY (`idCategory`) REFERENCES `workflows_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `workflows_tickets`
--

ALTER TABLE `workflows_tickets`
  ADD CONSTRAINT `workflows_tickets_ibfk_1` FOREIGN KEY (`idWorkflow`) REFERENCES `workflows_workflows` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `workflows_tickets_ibfk_2` FOREIGN KEY (`idCategory`) REFERENCES `workflows_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `workflows_tickets_ibfk_3` FOREIGN KEY (`requiredAction`) REFERENCES `workflows_actions` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `workflows_workflows`
--

ALTER TABLE `workflows_workflows`
  ADD CONSTRAINT `workflows_workflows_ibfk_1` FOREIGN KEY (`idCategory`) REFERENCES `workflows_categories` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `workflows_workflows_ibfk_2` FOREIGN KEY (`idFlow`) REFERENCES `workflows_flows` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
