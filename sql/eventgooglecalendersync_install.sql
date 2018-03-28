CREATE TABLE IF NOT EXISTS `civicrm_google_event` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `g_event_id` int(11) NOT NULL,
  `c_event_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_civicrm_event_c_event_id` (`c_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `civicrm_google_event`
  ADD CONSTRAINT `FK_civicrm_event_c_event_id` FOREIGN KEY (`c_event_id`)
    REFERENCES `civicrm_event` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
