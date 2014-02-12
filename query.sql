--
-- Database: `coordinator`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `accounts_accounts`
--

CREATE TABLE IF NOT EXISTS `accounts_accounts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `secret` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 disabled, 1 administrator, 2 user',
  `idCompany` int(11) unsigned NOT NULL DEFAULT '0',
  `registration` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lastLogin` datetime DEFAULT NULL,
  `ldapUsername` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account` (`account`),
  UNIQUE KEY `ldapUsername` (`ldapUsername`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Accounts utente';

--
-- Dump dei dati per la tabella `accounts_accounts`
--

INSERT INTO `accounts_accounts` (`id`, `account`, `password`, `secret`, `name`, `typology`, `idCompany`, `registration`, `lastLogin`, `ldapUsername`) VALUES
(NULL, 'root', '63a9f0ea7bb98050796b649e85481845', NULL, 'Administrator', 1, 0, '2010-01-01 10:00:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `accounts_companies`
--

CREATE TABLE IF NOT EXISTS `accounts_companies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `division` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Società';

--
-- Dump dei dati per la tabella `accounts_companies`
--

INSERT INTO `accounts_companies` (`id`, `company`, `division`, `name`) VALUES
(NULL, 'IT58', '0001', 'Cogne Acciai Speciali s.p.a.');

-- --------------------------------------------------------

--
-- Struttura della tabella `accounts_groups`
--

CREATE TABLE IF NOT EXISTS `accounts_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Gruppi';

--
-- Dump dei dati per la tabella `accounts_groups`
--

INSERT INTO `accounts_groups` (`id`, `name`, `description`) VALUES
(NULL, 'Sistemi Informativi', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `accounts_grouproles`
--

CREATE TABLE IF NOT EXISTS `accounts_grouproles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Ruoli di gruppo';

--
-- Dump dei dati per la tabella `accounts_grouproles`
--

INSERT INTO `accounts_grouproles` (`id`, `name`, `description`) VALUES
(NULL, 'Consulter', 'Consultatore'),
(NULL, 'Redactor', 'Editore'),
(NULL, 'Employee', 'Impiegato'),
(NULL, 'Leader', 'Responsabile'),
(NULL, 'Manager', 'Dirigente');

-- --------------------------------------------------------

--
-- Struttura della tabella `accounts_groups_join_accounts`
--

CREATE TABLE IF NOT EXISTS `accounts_groups_join_accounts` (
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `idAccount` int(11) unsigned NOT NULL DEFAULT '0',
  `idGrouprole` int(11) NOT NULL DEFAULT '1',
  KEY `idGroup` (`idGroup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `logs_logs`
--

CREATE TABLE IF NOT EXISTS `logs_logs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `typology` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 notice, 2 warning, 3 error',
  `timestamp` datetime NOT NULL,
  `module` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `log` text COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `idAccount` int(11) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `new` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 false, 1 true',
  PRIMARY KEY (`id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Registro eventi';

-- --------------------------------------------------------

--
-- Struttura della tabella `notifications_notifications`
--

CREATE TABLE IF NOT EXISTS `notifications_notifications` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `idAction` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `idAccountTo` int(11) unsigned NOT NULL,
  `idAccountFrom` int(11) unsigned NOT NULL DEFAULT '1',
  `idAccountArchived` int(11) DEFAULT NULL,
  `typology` tinyint(1) unsigned NOT NULL COMMENT '1 notification, 2 action',
  `module` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` datetime NOT NULL,
  `archived` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 created, 2 archived',
  PRIMARY KEY (`id`),
  KEY `idAccountTo` (`idAccountTo`),
  KEY `typology` (`typology`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Notifiche';

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_permissions`
--

CREATE TABLE IF NOT EXISTS `settings_permissions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `module` (`module`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Permessi';

--
-- Dump dei dati per la tabella `settings_permissions`
--

INSERT INTO `settings_permissions` (`id`, `module`, `action`, `description`, `locked`) VALUES
(NULL, 'settings', 'settings_edit', 'Gestire le impostazioni', 1),
(NULL, 'settings', 'permissions_edit', 'Gestire i permessi', 1),
(NULL, 'settings', 'menus_edit', 'Gestire i menu', 0),
(NULL, 'accounts', 'accounts_list', 'Visualizzare la lista degli accounts', 0),
(NULL, 'accounts', 'accounts_add', 'Aggiungere un account', 0),
(NULL, 'accounts', 'accounts_edit', 'Modificare un account', 0),
(NULL, 'accounts', 'accounts_delete', 'Eliminare un account', 1),
(NULL, 'accounts', 'groups_list', 'Visualizzare la lista dei gruppi', 0),
(NULL, 'accounts', 'groups_add', 'Aggiungere un gruppo', 0),
(NULL, 'accounts', 'groups_edit', 'Modificare un gruppo', 0),
(NULL, 'accounts', 'groups_delete', 'Eliminare un gruppo', 1),
(NULL, 'accounts', 'companies_list', 'Visualizzare la lista delle società', 0),
(NULL, 'accounts', 'companies_add', 'Aggiungere una società', 0),
(NULL, 'accounts', 'companies_edit', 'Modificare una società', 0),
(NULL, 'accounts', 'companies_delete', 'Eliminare una società', 1),
(NULL, 'dashboard', 'notifications_send', 'Inviare notifiche', 0),
(NULL, 'dashboard', 'notifications_send_all', 'Inviare notifiche a tutti gli utenti', 0),
(NULL, 'logs', 'logs_list', 'Visualizzare gli eventi', 0),
(NULL, 'applications', 'applications_list', 'Visualizzare i link delle applicazioni esterne', 0),
(NULL, 'applications', 'applications_test', 'Visualizzare i link delle applicazioni in fase di test', 0);


-- --------------------------------------------------------

--
-- Struttura della tabella `settings_permissions_join_accounts_groups`
--

CREATE TABLE IF NOT EXISTS `settings_permissions_join_accounts_groups` (
  `idPermission` int(11) unsigned NOT NULL DEFAULT '0',
  `idGroup` int(11) unsigned NOT NULL DEFAULT '0',
  `idGrouprole` int(11) unsigned NOT NULL DEFAULT '0',
  KEY `idPermission` (`idPermission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_menus`
--

CREATE TABLE IF NOT EXISTS `settings_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idMenu` int(11) NOT NULL DEFAULT '0',
  `menu` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `module` varchar(25) COLLATE utf8_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Menu';

--
-- Dump dei dati per la tabella `settings_menus`
--

INSERT INTO `settings_menus` (`id`, `idMenu`, `menu`, `module`, `url`, `position`) VALUES
(1, 0, 'main', '', '', 0),
(2, 0, 'user', '', '', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `settings_modules`
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
-- Struttura della tabella `settings_settings`
--

CREATE TABLE IF NOT EXISTS `settings_settings` (
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Impostazioni';

--
-- Dump dei dati per la tabella `settings_settings`
--

INSERT INTO `settings_settings` (`code`, `value`) VALUES
('cron_token', 'c4312c2a07bf7ded608a4d7cee2228dd'),
('google_analytics', ''),
('ldap', '0'),
('ldap_dn', NULL),
('ldap_domain', NULL),
('ldap_group', NULL),
('ldap_host', NULL),
('maintenance', '0'),
('maintenance_description', 'This service is currently undergoing scheduled maintenance. Please try back in 60 minutes. Sorry for the inconvenience.'),
('owner', 'Coordinator'),
('owner_mail', 'info@coordiantor.it'),
('owner_mail_from', 'Coordiantor'),
('owner_url', 'http://www.coordinator.it'),
('piwik_analytics', ''),
('show_logo', '0'),
('temp_token', ''),
('title', 'Coordinator');

-- --------------------------------------------------------