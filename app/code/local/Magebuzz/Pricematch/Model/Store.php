<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_Store
{
  static public function toOptionArray()
  {
    return Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(FALSE, TRUE);
  }
}