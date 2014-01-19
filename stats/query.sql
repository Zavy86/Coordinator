--
-- Database: `intranet`
--

-- --------------------------------------------------------

--
-- Dati della tabelle `settings_permissions`
--

INSERT INTO `settings_permissions` (`id`, `module`, `action`, `description`, `locked`) VALUES
(NULL ,  'stats',  'stats_intranet',  'Visualizzare le statistiche della intranet',  '0'),
(NULL ,  'stats',  'stats_server',  'Visualizzare le statistiche del server',  '0');

-- --------------------------------------------------------