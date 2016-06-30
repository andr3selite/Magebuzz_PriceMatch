<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_Mysql4_Pricematch extends Mage_Core_Model_Mysql4_Abstract
{
  protected function _construct()
  {
    // Note that the pricematch_id refers to the key field in your database table.
    $this->_init('pricematch/pricematch', 'pricematch_id');
  }
}