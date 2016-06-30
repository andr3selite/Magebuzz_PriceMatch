<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS {$this->getTable('pricematch')};
CREATE TABLE {$this->getTable('pricematch')} (
  `pricematch_id` int(11) unsigned NOT NULL auto_increment,
  `customer_name` varchar(255) NOT NULL default '',
  `customer_email` varchar(255) NOT NULL default '',
  `customer_phonenumb` text NOT NULL default '',
	`customer_skype` varchar(255) NOT NULL default '',
  `competitor_price` text NOT NULL default '',
  `competitor_link` text NOT NULL default '',
  `information` text NOT NULL default '',
  `product_id` INT(11) UNSIGNED NOT NULL,
  `status` smallint(6) NOT NULL default '0',
  `store_id` smallint(6) NOT NULL default '0',
  `date` datetime NULL,
  PRIMARY KEY (`pricematch_id`),
  CONSTRAINT `FK_PRICEMATCH_CATALOG_PRODUCT_ENTITY` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 