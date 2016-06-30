<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Model_Status extends Varien_Object
{
  const STATUS_APPROVED = 1;
  const STATUS_REJECTED = 2;
  const STATUS_PENDING = 3;

  static public function getOptionArray()
  {
    return array(
      self::STATUS_APPROVED => Mage::helper('pricematch')->__('Approved'),
      self::STATUS_REJECTED => Mage::helper('pricematch')->__('Rejected'),
      self::STATUS_PENDING  => Mage::helper('pricematch')->__('Pending')
    );
  }
}