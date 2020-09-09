DROP TABLE IF EXISTS `#__gavoting_positions`;
DROP TABLE IF EXISTS `#__gavoting_nominations`;
DROP TABLE IF EXISTS `#__gavoting_voters`;

DELETE from `#__content_types` WHERE `type_alias` = 'com_gavoting';
DELETE from `#__content_types` WHERE `type_alias` = 'com_gavoting.category';
DELETE from `#__categories` WHERE `extension` = 'com_gavoting';
