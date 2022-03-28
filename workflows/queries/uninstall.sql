--
-- Uninstall module workflows
--

-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE `workflows_actions`;
DROP TABLE `workflows_attachments`;
DROP TABLE `workflows_categories`;
DROP TABLE `workflows_fields`;
DROP TABLE `workflows_flows`;
DROP TABLE `workflows_mails`;
DROP TABLE `workflows_tickets`;
DROP TABLE `workflows_tickets_notes`;
DROP TABLE `workflows_workflows`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------