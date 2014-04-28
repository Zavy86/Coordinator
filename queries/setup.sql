--
-- Setup Coordinator
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts_accounts`
--

CREATE TABLE IF NOT EXISTS `accounts_accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 disabled, 1 administrator, 2 user',
  `language` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `idCompany` int(11) unsigned DEFAULT NULL,
  `registration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastLogin` datetime DEFAULT NULL,
  `ldapUsername` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account` (`account`),
  UNIQUE KEY `ldapUsername` (`ldapUsername`),
  KEY `idCompany` (`idCompany`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts_accounts`
--

INSERT IGNORE INTO `accounts_accounts` (`id`, `account`, `password`, `secret`, `name`, `typology`, `idCompany`, `registration`, `lastLogin`, `ldapUsername`) VALUES
('1', 'root', '63a9f0ea7bb98050796b649e85481845', NULL, 'Administrator', 1, NULL, '2009-06-01 10:00:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `accounts_companies`
--

CREATE TABLE IF NOT EXISTS `accounts_companies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `division` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fiscal_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_vat` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_code` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_rea` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_capital` int(11) unsigned NOT NULL DEFAULT '0',
  `fiscal_currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'EUR',
  `address_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_zip` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_district` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_office` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_mobile` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_fax` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts_companies`
--

INSERT IGNORE INTO `accounts_companies` (`id`, `company`, `division`, `name`) VALUES
(NULL, 'Default Company', 'Default Division', 'Default Company Inc.');

-- --------------------------------------------------------

--
-- Table structure for table `accounts_grouproles`
--

CREATE TABLE IF NOT EXISTS `accounts_grouproles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts_grouproles`
--

INSERT IGNORE INTO `accounts_grouproles` (`id`, `name`, `description`) VALUES
(NULL, 'Consulter', 'External read only role'),
(NULL, 'Redactor', 'External read and write role'),
(NULL, 'Employee', 'Employee role'),
(NULL, 'Leader', 'Leader role'),
(NULL, 'Manager', 'Manager role');

-- --------------------------------------------------------

--
-- Table structure for table `accounts_groups`
--

CREATE TABLE IF NOT EXISTS `accounts_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idGroup` (`idGroup`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts_groups`
--

INSERT IGNORE INTO `accounts_groups` (`id`, `name`, `description`) VALUES
(NULL, 'Deafault', 'Default group');

-- --------------------------------------------------------

--
-- Table structure for table `accounts_groups_join_accounts`
--

CREATE TABLE IF NOT EXISTS `accounts_groups_join_accounts` (
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `idAccount` int(11) unsigned NOT NULL DEFAULT '0',
  `idGrouprole` int(11) unsigned NOT NULL DEFAULT '1',
  `main` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `idGroup` (`idGroup`),
  KEY `idAccount` (`idAccount`),
  KEY `idGrouprole` (`idGrouprole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Constraints for table `accounts_accounts`
--

ALTER TABLE `accounts_accounts`
  ADD CONSTRAINT `accounts_accounts_ibfk_1` FOREIGN KEY (`idCompany`) REFERENCES `accounts_companies` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `accounts_groups_join_accounts`
--

ALTER TABLE `accounts_groups_join_accounts`
  ADD CONSTRAINT `accounts_groups_join_accounts_ibfk_1` FOREIGN KEY (`idGroup`) REFERENCES `accounts_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `accounts_groups_join_accounts_ibfk_2` FOREIGN KEY (`idAccount`) REFERENCES `accounts_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `accounts_groups_join_accounts_ibfk_3` FOREIGN KEY (`idGrouprole`) REFERENCES `accounts_grouproles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `logs_logs`
--

CREATE TABLE IF NOT EXISTS `logs_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 notice, 2 warning, 3 error',
  `timestamp` datetime NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `event` text COLLATE utf8_unicode_ci,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idAccount` int(11) unsigned DEFAULT NULL,
  `ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `typology` (`typology`),
  KEY `timestamp` (`timestamp`),
  KEY `module` (`module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Table structure for table `logs_notifications`
--

CREATE TABLE IF NOT EXISTS `logs_notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idAccount` int(11) unsigned NOT NULL,
  `timestamp` datetime NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 received, 2 readed, 3 archived',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs_subscriptions`
--

CREATE TABLE IF NOT EXISTS `logs_subscriptions` (
  `idAccount` int(11) unsigned NOT NULL,
  `trigger` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mail` tinyint(1) NOT NULL DEFAULT '0',
  KEY `idAccount` (`idAccount`),
  KEY `trigger` (`trigger`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs_triggers`
--

CREATE TABLE IF NOT EXISTS `logs_triggers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `trigger` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `condition` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trigger` (`trigger`),
  KEY `module` (`module`),
  KEY `action` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs_subscriptions`
--

ALTER TABLE `logs_subscriptions`
  ADD CONSTRAINT `logs_subscriptions_ibfk_1` FOREIGN KEY (`idAccount`) REFERENCES `accounts_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `logs_subscriptions_ibfk_2` FOREIGN KEY (`trigger`) REFERENCES `logs_triggers` (`trigger`) ON DELETE CASCADE ON UPDATE CASCADE;


-- --------------------------------------------------------

--
-- Table structure for table `settings_dashboards`
--

CREATE TABLE IF NOT EXISTS `settings_dashboards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idAccount` int(11) unsigned NOT NULL,
  `position` tinyint(2) NOT NULL DEFAULT '0',
  `span` tinyint(2) unsigned NOT NULL DEFAULT '6',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parameters` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `refresh` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings_menus`
--

CREATE TABLE IF NOT EXISTS `settings_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `menu` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `module` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings_menus`
--

INSERT IGNORE INTO `settings_menus` (`id`, `idMenu`, `menu`, `module`, `url`, `position`) VALUES
(1, 0, 'main', '', '', 0),
(2, 0, 'user', '', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `settings_modules`
--

CREATE TABLE IF NOT EXISTS `settings_modules` (
  `module` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '1.0.0',
  `title` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings_permissions`
--

CREATE TABLE IF NOT EXISTS `settings_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings_permissions`
--

INSERT IGNORE INTO `settings_permissions` (`id`, `module`, `action`, `description`, `locked`) VALUES
(NULL, 'settings', 'settings_edit', 'Manage settings', 1),
(NULL, 'settings', 'modules_edit', 'Manage modules', 1),
(NULL, 'settings', 'permissions_edit', 'Manage permissions', 1),
(NULL, 'settings', 'menus_edit', 'Manage menus', 0),
(NULL, 'logs', 'logs_list', 'View events log', 0),
(NULL, 'logs', 'notifications_send', 'Send notifications', 0),
(NULL, 'logs', 'notifications_send_all', 'Send notifications to all users', 0),
(NULL, 'stats', 'stats_server', 'View server stats', 0),
(NULL, 'accounts', 'accounts_list', 'View accounts list', 0),
(NULL, 'accounts', 'accounts_add', 'Add an account', 0),
(NULL, 'accounts', 'accounts_edit', 'Edit an account', 0),
(NULL, 'accounts', 'accounts_delete', 'Delete an account', 1),
(NULL, 'accounts', 'groups_list', 'View groups list', 0),
(NULL, 'accounts', 'groups_add', 'Add a group', 0),
(NULL, 'accounts', 'groups_edit', 'Edit group', 0),
(NULL, 'accounts', 'groups_delete', 'Delete a group', 1),
(NULL, 'accounts', 'companies_list', 'View company list', 0),
(NULL, 'accounts', 'companies_add', 'Add a company', 0),
(NULL, 'accounts', 'companies_edit', 'Edit a company', 0),
(NULL, 'accounts', 'companies_delete', 'Delete a company', 1),
(NULL, 'database', 'database_view', 'View Coordinator database', 0);

-- --------------------------------------------------------

--
-- Table structure for table `settings_permissions_join_accounts_groups`
--

CREATE TABLE IF NOT EXISTS `settings_permissions_join_accounts_groups` (
  `idPermission` int(11) unsigned NOT NULL DEFAULT '0',
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `idGrouprole` int(11) unsigned NOT NULL DEFAULT '0',
  KEY `idPermission` (`idPermission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings_settings`
--

CREATE TABLE IF NOT EXISTS `settings_settings` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings_settings`
--

INSERT IGNORE INTO `settings_settings` (`code`, `value`) VALUES
('cron_token', 'c4312c2a07bf7ded608a4d7cee2228dd'),
('google_analytics', ''),
('ldap', '0'),
('ldap_dn',''),
('ldap_domain',''),
('ldap_group',''),
('ldap_host',''),
('ldap_userfield',''),
('maintenance', '0'),
('maintenance_description', 'This service is currently undergoing scheduled maintenance. Please try back in 60 minutes. Sorry for the inconvenience.'),
('owner', 'Default Company Inc.'),
('owner_mail', 'info@defaultcompany.com'),
('owner_mail_from', 'Default Company'),
('owner_url', 'http://www.defaultcompany.com'),
('piwik_analytics', ''),
('show_logo', '0'),
('temp_token', ''),
('title', 'Coordinator'),
('version', '1.0.0');

-- --------------------------------------------------------

--
-- Dumping data for table `logs_triggers`
--

INSERT INTO `logs_triggers` (`id`, `trigger`, `module`, `action`, `condition`) VALUES
(NULL, 'logs_accounts_accountCreated', 'accounts', 'accountCreatedLDAP', NULL);

-- --------------------------------------------------------

--
-- Constraints for dumped tables
--

--
-- Constraints for table `settings_permissions_join_accounts_groups`
--
ALTER TABLE `settings_permissions_join_accounts_groups`
  ADD CONSTRAINT `settings_permissions_join_accounts_groups_ibfk_1` FOREIGN KEY (`idPermission`) REFERENCES `settings_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Struttura della tabella `uploads_uploads`
--

CREATE TABLE IF NOT EXISTS `uploads_uploads` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------