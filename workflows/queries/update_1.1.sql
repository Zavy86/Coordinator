--
-- Update module workflows
--
-- From 1.1 to 1.2
--

-- --------------------------------------------------------

--
-- Alter table `workflows_tickets`
--

ALTER TABLE `workflows_tickets` ADD `urged` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 false, 1 true' AFTER `approved`;

-- --------------------------------------------------------

--
-- Dumping data for table `logs_triggers`
--

INSERT INTO `logs_triggers` (`id`, `trigger`, `module`, `action`, `condition`) VALUES
(NULL, 'logs_workflows_ticketDisponible', 'workflows', 'ticketUrged', 'workflows_conditions_ticketProcessable');

-- --------------------------------------------------------