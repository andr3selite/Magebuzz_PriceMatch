<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_Session extends Mage_Core_Model_Session_Abstract
{
  public function __construct()
  {
    $this->init('pricematch');
  }
}
