<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_Mysql4_Pricematch_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
  protected function _construct()
  {
    parent::_construct();
    $this->_init('pricematch/pricematch');
  }
}