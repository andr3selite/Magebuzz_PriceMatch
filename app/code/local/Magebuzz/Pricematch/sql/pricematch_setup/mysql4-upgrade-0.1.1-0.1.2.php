<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

$installer = $this;
$installer->startSetup();
$installer->run("
  ALTER TABLE {$this->getTable('pricematch')} ADD `coupon_code` text NULL;  
  
");
$installer->endSetup(); 