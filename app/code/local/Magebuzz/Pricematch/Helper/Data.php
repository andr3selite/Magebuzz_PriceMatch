<?php
/**
 * @copyright Copyright (c) 2015 www.magebuzz.com
 */

class Magebuzz_Pricematch_Helper_Data extends Mage_Core_Helper_Abstract
{
  public function getDateToRemoveCoupon($date, $days)
  {
    $new_date = strtotime($days . ' day', strtotime($date));
    $new_date = date('Y-m-d', $new_date);
    return $new_date;
  }
  
  public function getPricematchButton() {
    return Mage::getStoreConfig('pricematch/product_page/button', Mage::app()->getStore());
  }

  public function getFormUrl() {
    $params = Mage::app()->getRequest()->getParam('id');
    if (!$params) return;
    $_product = Mage::getModel('catalog/product')->load($params);
    return $formUrl = Mage::getUrl('pricematch/index/form', array(
                      'id'       => $_product->getId(),
                      'category' => $_product->getCategoryId()
    ));
  }
}