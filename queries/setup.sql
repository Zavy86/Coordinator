--
-- Setup Coordinator
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts_accounts`
--

CREATE TABLE IF NOT EXISTS `accounts_accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ldap` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `superuser` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  `accDate` datetime DEFAULT NULL,
  `del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ldap` (`ldap`),
  KEY `account` (`account`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts_accounts`
--

INSERT IGNORE INTO `accounts_accounts` (`id`, `account`, `password`, `secret`, `name`, `phone`, `ldap`, `language`, `enabled`, `superuser`, `addDate`, `addIdAccount`, `del`) VALUES
(1, 'root', '63a9f0ea7bb98050796b649e85481845', NULL, 'Administrator', NULL, NULL, 'it_IT', 1, 1, '2009-06-01 10:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `accounts_accounts_join_companies`
--

CREATE TABLE IF NOT EXISTS `accounts_accounts_join_companies` (
  `idAccount` int(11) unsigned NOT NULL,
  `idCompany` int(11) unsigned NOT NULL,
  `idRole` int(11) unsigned NOT NULL,
  `main` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `idAccount` (`idAccount`),
  KEY `idCompany` (`idCompany`),
  KEY `idRole` (`idRole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounts_accounts_join_groups`
--

CREATE TABLE IF NOT EXISTS `accounts_accounts_join_groups` (
  `idAccount` int(11) unsigned NOT NULL,
  `idGroup` int(11) unsigned NOT NULL,
  `main` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `idAccount` (`idAccount`),
  KEY `idGroup` (`idGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `accounts_companies`
--

CREATE TABLE IF NOT EXISTS `accounts_companies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `division` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `address_address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_zip` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_district` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_country` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_vat` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_code` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_rea` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `fiscal_capital` int(11) unsigned NOT NULL DEFAULT '0',
  `fiscal_currency` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'EUR',
  `phone_office` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_mobile` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone_fax` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `del` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Dumping data for table `accounts_companies`
--

INSERT IGNORE INTO `accounts_companies` (`id`, `company`, `division`, `name`) VALUES
(1, 'C001', 'D001', 'Default Company');

-- --------------------------------------------------------

--
-- Table structure for table `accounts_groups`
--

CREATE TABLE IF NOT EXISTS `accounts_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idCompany` int(11) unsigned DEFAULT NULL,
  `idGroup` int(11) unsigned DEFAULT NULL,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idCompany` (`idCompany`),
  KEY `idGroup` (`idGroup`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Gruppi';

-- --------------------------------------------------------

--
-- Dumping data for table `accounts_groups`
--

INSERT IGNORE INTO `accounts_groups` (`id`, `idCompany`, `idGroup`, `name`, `description`, `addDate`, `addIdAccount`) VALUES
(1, 1, NULL, 'ADM', 'Administrators', '2010-01-01 10:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `accounts_roles`
--

CREATE TABLE IF NOT EXISTS `accounts_roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `level` (`level`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `accounts_roles`
--

INSERT IGNORE INTO `accounts_roles` (`id`, `level`, `name`, `description`, `addDate`, `addIdAccount`) VALUES
(1, 1, 'Administrator', NULL, '2010-01-01 00:00:00', 1),
(2, 2, 'Director', NULL, '2010-01-01 00:00:00', 1),
(3, 3, 'Manager', NULL, '2010-01-01 00:00:00', 1),
(4, 4, 'Responsible', NULL, '2010-01-01 00:00:00', 1),
(5, 5, 'Employee', NULL, '2010-01-01 00:00:00', 1),
(6, 6, 'Assistant', NULL, '2010-01-01 00:00:00', 1),
(7, 8, 'Consultant', NULL, '2010-01-01 00:00:00', 1);

-- --------------------------------------------------------

--
-- Constraints for table `accounts_accounts_join_companies`
--
ALTER TABLE `accounts_accounts_join_companies`
  ADD CONSTRAINT `accounts_accounts_join_companies_ibfk_1` FOREIGN KEY (`idAccount`) REFERENCES `accounts_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `accounts_accounts_join_companies_ibfk_2` FOREIGN KEY (`idCompany`) REFERENCES `accounts_companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `accounts_accounts_join_companies_ibfk_3` FOREIGN KEY (`idRole`) REFERENCES `accounts_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `accounts_accounts_join_groups`
--
ALTER TABLE `accounts_accounts_join_groups`
  ADD CONSTRAINT `accounts_accounts_join_groups_ibfk_1` FOREIGN KEY (`idAccount`) REFERENCES `accounts_accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `accounts_accounts_join_groups_ibfk_2` FOREIGN KEY (`idGroup`) REFERENCES `accounts_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `accounts_groups`
--
ALTER TABLE `accounts_groups`
  ADD CONSTRAINT `accounts_groups_ibfk_1` FOREIGN KEY (`idCompany`) REFERENCES `accounts_companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `accounts_groups_ibfk_2` FOREIGN KEY (`idGroup`) REFERENCES `accounts_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs_mails`
--

CREATE TABLE IF NOT EXISTS `logs_mails` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to` text COLLATE utf8_unicode_ci NOT NULL,
  `cc` text COLLATE utf8_unicode_ci,
  `bcc` text COLLATE utf8_unicode_ci,
  `from` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sender` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci,
  `attachments` text COLLATE utf8_unicode_ci,
  `html` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 false, 1 true',
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0 draft, 1 sended, 2 failed',
  `error` text COLLATE utf8_unicode_ci,
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `sendDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  `mail` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `archived` tinyint(1) unsigned NOT NULL DEFAULT '0',
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

--
-- Dumping data for table `logs_triggers`
--

INSERT INTO `logs_triggers` (`id`, `trigger`, `module`, `action`, `condition`) VALUES
(NULL, 'logs_accounts_accountLDAP', 'accounts', 'accountLDAP', NULL);

-- --------------------------------------------------------

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings_menus`
--

INSERT IGNORE INTO `settings_menus` (`id`, `idMenu`, `menu`, `module`, `url`, `position`) VALUES
(1, 0, 'main', '', '', 0),
(2, 0, 'user', '', '', 0),
(3, 1, 'Uploads', 'uploads', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `settings_menus_join_accounts_groups`
--

CREATE TABLE IF NOT EXISTS `settings_menus_join_accounts_groups` (
  `idMenu` int(11) unsigned NOT NULL DEFAULT '0',
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  KEY `idMenu` (`idMenu`),
  KEY `idGroup` (`idGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

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
  KEY `module` (`module`),
  KEY `action` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `settings_permissions`
--

INSERT IGNORE INTO `settings_permissions` (`id`, `module`, `action`, `description`, `locked`) VALUES
(1, 'settings', 'settings_edit', 'Manage Coordinator settings', 1),
(2, 'settings', 'permissions_manage', 'Manage company groups permissions', 1),
(3, 'settings', 'modules_edit', 'Manage Coordinator modules', 1),
(4, 'settings', 'menus_edit', 'Manage menus', 0),
(5, 'accounts', 'accounts_view', 'View accounts', 0),
(6, 'accounts', 'accounts_edit', 'Edit accounts', 0),
(7, 'accounts', 'accounts_customize', 'Edit own account and name ', 0),
(8, 'accounts', 'accounts_manage', 'Delete, restore and manage accounts', 1),
(9, 'accounts', 'roles_view', 'View company roles', 0),
(10, 'accounts', 'roles_edit', 'Edit company roles', 0),
(11, 'accounts', 'companies_view', 'View companies', 0),
(12, 'accounts', 'companies_edit', 'Edit companies', 0),
(13, 'accounts', 'companies_delete', 'Delete companies', 1),
(14, 'accounts', 'groups_view', 'View companies groups', 0),
(15, 'accounts', 'groups_edit', 'Edit companies groups', 0),
(16, 'chats', 'chats_chat', 'Use integrated chat', 0),
(17, 'logs', 'logs_list', 'View event logs', 0),
(18, 'logs', 'notifications_send', 'Send notifications', 0),
(19, 'logs', 'notifications_send_all', 'Send notifications to all users', 0),
(20, 'stats', 'stats_server', 'View server stats', 0),
(21, 'database', 'database_view', 'View Coordinator database', 0),
(22, 'uploads', 'uploads_view', 'View uploads', 0),
(23, 'uploads', 'files_edit', 'Edit folders', 0),
(24, 'uploads', 'folders_edit', 'Edit files', 0);

-- --------------------------------------------------------

--
-- Table structure for table `settings_permissions_join_accounts_groups`
--

CREATE TABLE IF NOT EXISTS `settings_permissions_join_accounts_groups` (
  `idPermission` int(11) unsigned NOT NULL,
  `idCompany` int(11) unsigned DEFAULT NULL,
  `idGroup` int(11) unsigned DEFAULT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  KEY `idPermission` (`idPermission`),
  KEY `idCompany` (`idCompany`),
  KEY `idGroup` (`idGroup`)
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
('owner', 'Default Company Inc.'),
('owner_mail', 'info@defaultcompany.com'),
('owner_mail_from', 'Default Company'),
('owner_url', 'http://www.defaultcompany.com'),
('piwik_analytics', ''),
('sendmail_asynchronous', '0'),
('show_logo', '0'),
('smtp', '0'),
('smtp_host',''),
('smtp_username',''),
('smtp_password',''),
('smtp_secure',''),
('temp_token', ''),
('title', 'Coordinator'),
('version', '1.0.0');

-- --------------------------------------------------------

--
-- Constraints for table `settings_permissions_join_accounts_groups`
--
ALTER TABLE `settings_permissions_join_accounts_groups`
  ADD CONSTRAINT `settings_permissions_join_accounts_groups_ibfk_1` FOREIGN KEY (`idPermission`) REFERENCES `settings_permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `settings_permissions_join_accounts_groups_ibfk_2` FOREIGN KEY (`idCompany`) REFERENCES `accounts_companies` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `settings_permissions_join_accounts_groups_ibfk_3` FOREIGN KEY (`idGroup`) REFERENCES `accounts_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `uploads_folders`
--

CREATE TABLE IF NOT EXISTS `uploads_folders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idFolder` int(10) unsigned DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(11) unsigned NOT NULL DEFAULT '0',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idFolder` (`idFolder`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploads_links`
--

CREATE TABLE IF NOT EXISTS `uploads_links` (
  `id` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `idUpload` int(11) unsigned NOT NULL,
  `public` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 false, 1 true',
  `password` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `counter` int(11) unsigned NOT NULL DEFAULT '0',
  `addDate` datetime NOT NULL,
  `addIdAccount` int(11) unsigned NOT NULL,
  `updDate` datetime DEFAULT NULL,
  `updIdAccount` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idUpload` (`idUpload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploads_uploads`
--

CREATE TABLE IF NOT EXISTS `uploads_uploads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idFolder` int(11) unsigned DEFAULT NULL,
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
  KEY `idFolder` (`idFolder`),
  KEY `hash` (`hash`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Constraints for table `uploads_folders`
--
ALTER TABLE `uploads_folders`
  ADD CONSTRAINT `uploads_folders_ibfk_1` FOREIGN KEY (`idFolder`) REFERENCES `uploads_folders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `uploads_links`
--
ALTER TABLE `uploads_links`
  ADD CONSTRAINT `uploads_links_ibfk_1` FOREIGN KEY (`idUpload`) REFERENCES `uploads_uploads` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `uploads_uploads`
--
ALTER TABLE `uploads_uploads`
  ADD CONSTRAINT `uploads_uploads_ibfk_1` FOREIGN KEY (`idFolder`) REFERENCES `uploads_folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------