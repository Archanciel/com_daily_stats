INSERT INTO `jos_extensions` (`name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `manifest_cache`, `params`, `custom_data`, `system_data`, `checked_out`, `checked_out_time`, `ordering`, `state`) VALUES
('daily_stats', 'component', 'com_daily_stats', '', 1, 1, 0, 0, '{"legacy":true,"name":"Daily Stats","type":"component","creationDate":"25 February 2013","author":"Les Arbres Design","copyright":"Les Arbres Design 2010-2013","authorEmail":"","authorUrl":"http:\\/\\/extensions.lesarbresdesign.info","version":"3.00","description":"A sample component embedding Plotalot in the front end. To see SimplePlot in action, create a menu item of type SimplePlot, go to your site front end, and click on the new menu item.","group":""}', '{}', '', '', 0, '0000-00-00 00:00:00', 0, 0);

INSERT INTO `jos_assets` (`parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`) VALUES
(1, 73, 74, 1, 'com_daily_stats', 'daily_stats', '{}');

// WARNING !!! intégrité référentielle: componcent_id 808 below must be the id of the inserted daily_stats component !
INSERT INTO `jos_menu` (`menutype`, `title`, `alias`, `note`, `path`, `link`, `type`, `published`, `parent_id`, `level`, `component_id`, `ordering`, `checked_out`, `checked_out_time`, `browserNav`, `access`, `img`, `template_style_id`, `params`, `lft`, `rgt`, `home`, `language`, `client_id`) VALUES
('main', 'Daily stats', 'daily_stats', '', 'daily_stats', 'index.php?option=com_daily_stats', 'component', 0, 1, 1, 808, 0, 0, '0000-00-00 00:00:00', 0, 1, '../administrator/components/com_plotalot/assets/plotalot-16.png', 0, '', 47, 48, 0, '', 1);