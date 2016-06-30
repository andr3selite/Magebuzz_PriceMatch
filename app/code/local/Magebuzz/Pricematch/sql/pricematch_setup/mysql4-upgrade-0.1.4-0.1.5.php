<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

$installer = $this;
$installer->startSetup();
$installer->run("
  ALTER TABLE {$this->getTable('pricematch')} ADD `discount_amount` decimal(12,4) NULL;
");
$installer->endSetup(); 